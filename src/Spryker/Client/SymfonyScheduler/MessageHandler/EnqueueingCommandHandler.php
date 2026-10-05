<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\MessageHandler;

use DateTimeImmutable;
use DateTimeInterface;
use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface;
use Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReaderInterface;
use Spryker\Client\SymfonyScheduler\Sender\SchedulerJobSenderInterface;
use Spryker\Client\SymfonyScheduler\Writer\JobStatusWriterInterface;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConfig;

class EnqueueingCommandHandler
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_ENQUEUED = 'Scheduler job enqueued for asynchronous execution.';

    protected const string LOG_KEY_NAME = 'name';

    protected const string LOG_KEY_QUEUED_AT = 'queuedAt';

    public function __construct(
        protected SchedulerJobSenderInterface $schedulerJobSender,
        protected JobStatusWriterInterface $jobStatusWriter,
        protected JobStateReaderInterface $jobStateReader
    ) {
    }

    public function __invoke(CommandMessageInterface $commandMessage): ?string
    {
        $name = (string)$commandMessage->getName();

        if ($this->jobStateReader->isJobDisabled($name)) {
            return null;
        }

        $command = $commandMessage->getCommand();
        $queuedAt = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);

        $this->jobStatusWriter->saveJobStatus($this->createQueuedJobStatusTransfer($name, $command, $queuedAt));

        $this->schedulerJobSender->send(new ScheduledCommandMessage(
            $name,
            $command,
            ScheduledCommandMessage::ORIGIN_CRON,
            $queuedAt,
        ));

        $this->getLogger()->info(static::LOG_MESSAGE_ENQUEUED, [
            static::LOG_KEY_NAME => $name,
            static::LOG_KEY_QUEUED_AT => $queuedAt,
        ]);

        // Must stay null. A non-empty handler result is treated as failure output by the worker, which
        // flags the whole run through hadErrors() and turns it into a non-zero console exit code.
        return null;
    }

    protected function createQueuedJobStatusTransfer(string $name, string $command, string $queuedAt): SchedulerJobStatusTransfer
    {
        return (new SchedulerJobStatusTransfer())
            ->setName($name)
            ->setCommand($command)
            ->setStatus(SymfonySchedulerConfig::STATUS_QUEUED)
            ->setQueuedAt($queuedAt)
            ->setUpdatedAt($queuedAt);
    }
}
