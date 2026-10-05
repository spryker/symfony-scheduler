<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\MessageHandler;

use Spryker\Client\Lock\LockClientInterface;
use Spryker\Client\SymfonyScheduler\Message\CommandMessage;
use Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface;
use Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage;
use Spryker\Client\SymfonyScheduler\Reader\CronJobConfigReaderInterface;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReaderInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Shared\Log\LoggerTrait;
use Symfony\Component\Lock\Exception\ExceptionInterface as LockExceptionInterface;
use Symfony\Component\Lock\LockInterface;
use Throwable;

class LockingCommandHandler
{
    use LoggerTrait;

    protected const string LOCK_RESOURCE_PATTERN = 'scheduler:job:running:%s';

    protected const string LOG_MESSAGE_EXECUTION_FAILED = 'Scheduler job threw while executing; the message is acknowledged so the queue keeps draining.';

    protected const string LOG_MESSAGE_LOCK_RELEASE_FAILED = 'Scheduler job execution lock could not be released; it will expire on its own.';

    protected const string LOG_KEY_EXCEPTION = 'exception';

    protected const string LOG_KEY_NAME = 'name';

    protected const string LOG_KEY_ORIGIN = 'origin';

    protected const string LOG_KEY_TRIGGERED_AT = 'triggeredAt';

    public function __construct(
        protected CommandHandler $commandHandler,
        protected LockClientInterface $lockClient,
        protected JobStateReaderInterface $jobStateReader,
        protected CronJobConfigReaderInterface $cronJobConfigReader,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    public function __invoke(ScheduledCommandMessage $scheduledCommandMessage): ?string
    {
        $name = $scheduledCommandMessage->getName();

        if ($this->jobStateReader->isJobDisabled($name)) {
            return null;
        }

        if ($this->cronJobConfigReader->isLockDisabled($name)) {
            return $this->runCommand($scheduledCommandMessage);
        }

        $lock = $this->lockClient->createLock(
            sprintf(static::LOCK_RESOURCE_PATTERN, $name),
            $this->symfonySchedulerConfig->getJobExecutionLockTtl(),
        );

        try {
            $isLockAcquired = $lock->acquire();
        } catch (LockExceptionInterface $lockException) {
            return null;
        }

        if (!$isLockAcquired) {
            return null;
        }

        try {
            return $this->runCommand($scheduledCommandMessage);
        } finally {
            $this->releaseQuietly($lock, $scheduledCommandMessage);
        }
    }

    /**
     * A job that merely exits non-zero is reported by CommandHandler as a result string and still
     * acknowledged; only an actual throw is converted here, because an exception escaping the handler
     * would leave the message unacknowledged and the broker would redeliver it forever.
     */
    protected function runCommand(ScheduledCommandMessage $scheduledCommandMessage): ?string
    {
        try {
            return ($this->commandHandler)($this->mapToCommandMessage($scheduledCommandMessage));
        } catch (Throwable $exception) {
            $this->getLogger()->error(
                static::LOG_MESSAGE_EXECUTION_FAILED,
                $this->buildLogContext($scheduledCommandMessage) + [static::LOG_KEY_EXCEPTION => $exception],
            );

            return null;
        }
    }

    /**
     * release() throws when the lock store is unreachable. Thrown from a finally block it would replace
     * the return value and escape the handler, which is the failure mode this class exists to prevent.
     */
    protected function releaseQuietly(LockInterface $lock, ScheduledCommandMessage $scheduledCommandMessage): void
    {
        try {
            $lock->release();
        } catch (LockExceptionInterface $lockException) {
            $this->getLogger()->warning(
                static::LOG_MESSAGE_LOCK_RELEASE_FAILED,
                $this->buildLogContext($scheduledCommandMessage) + [static::LOG_KEY_EXCEPTION => $lockException],
            );
        }
    }

    protected function mapToCommandMessage(ScheduledCommandMessage $scheduledCommandMessage): CommandMessageInterface
    {
        return (new CommandMessage())
            ->setCommand($scheduledCommandMessage->getCommand())
            ->setName($scheduledCommandMessage->getName());
    }

    /**
     * @return array<string, string>
     */
    protected function buildLogContext(ScheduledCommandMessage $scheduledCommandMessage): array
    {
        return [
            static::LOG_KEY_NAME => $scheduledCommandMessage->getName(),
            static::LOG_KEY_ORIGIN => $scheduledCommandMessage->getOrigin(),
            static::LOG_KEY_TRIGGERED_AT => $scheduledCommandMessage->getTriggeredAt(),
        ];
    }
}
