<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Builder;

use Generated\Shared\Transfer\MessengerTransportConfigTransfer;
use Spryker\Client\Lock\LockClientInterface;
use Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Client\SymfonyScheduler\Trigger\RunRequestAwareCronTrigger;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

class ConfigBasedCronJobsBuilder implements CronJobsBuilderInterface
{
    protected const int DEFAULT_PRIORITY = 0;

    protected const string CRON_JOB_PRIORITY_KEY = 'priority';

    /**
     * @var array<string, \Symfony\Component\Scheduler\Schedule>
     */
    protected static array $schedules = [];

    public function __construct(
        protected SymfonySchedulerConfig $config,
        protected CommandMessageInterface $commandMessage,
        protected LockClientInterface $lockClient,
        protected JobRunRequestReaderInterface $jobRunRequestReader
    ) {
    }

    /**
     * @return array<string, \Symfony\Component\Scheduler\Schedule>
     */
    public function buildSchedule(): array
    {
        if (static::$schedules !== []) {
            return static::$schedules;
        }

        foreach ($this->config->getCronJobs() as $key => $cronJob) {
            $message = clone $this->commandMessage;
            $message = $message->setCommand($this->buildCommand($cronJob));
            $message->setName($key);

            // Reuse RecurringMessage::cron() to build the cron trigger (preserving hashed-expression and timezone
            // handling), then wrap it so a Redis "run request" can pre-empt the schedule and fire the job now.
            $cronTrigger = RecurringMessage::cron($cronJob['schedule'], $message)->getTrigger();
            $trigger = new RunRequestAwareCronTrigger($cronTrigger, $this->jobRunRequestReader, $key);

            $schedule = new Schedule();
            $schedule->add(RecurringMessage::trigger($trigger, $message));
            if (!isset($cronJob['no_lock']) || $cronJob['no_lock'] === false) {
                $schedule->lock($this->lockClient->createLock($this->generateLockKey($key), $this->config->getLockTTL()));
            }

            static::$schedules[$key] = $schedule;
        }

        return static::$schedules;
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    public function buildTransportInfo(): array
    {
        $transportInfo = [];
        foreach ($this->config->getCronJobs() as $key => $cronJob) {
            $transportInfo[$key] = (new MessengerTransportConfigTransfer())
                ->setName($key)
                ->setPriority($cronJob[static::CRON_JOB_PRIORITY_KEY] ?? static::DEFAULT_PRIORITY);
        }

        return $transportInfo;
    }

    /**
     * @param array<string, mixed> $cronJob
     */
    protected function buildCommand(array $cronJob): string
    {
        $region = isset($cronJob['region']) ? ' export SPRYKER_CURRENT_REGION=' . $cronJob['region'] : '';
        $store = isset($cronJob['store']) ? ' export APPLICATION_STORE=' . $cronJob['store'] : '';

        return sprintf('%s%s %s', $store, $region, $cronJob['command']);
    }

    protected function generateLockKey(string $cronJobKey): string
    {
        return sprintf('schedule_lock:%s', $cronJobKey);
    }
}
