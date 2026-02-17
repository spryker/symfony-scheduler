<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Builder;

use Spryker\Zed\Lock\Business\LockFacadeInterface;
use Spryker\Zed\SymfonyScheduler\Communication\Messages\CommandMessageInterface;
use Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

class ConfigBasedCronJobsBuilder implements CronJobsBuilderInterface
{
    /**
     * @var array<string, \Symfony\Component\Scheduler\Schedule>
     */
    protected static array $schedules = [];

    public function __construct(
        protected SymfonySchedulerConfig $config,
        protected CommandMessageInterface $commandMessage,
        protected LockFacadeInterface $lockFacade
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

            $schedule = new Schedule();
            $schedule->add(RecurringMessage::cron($cronJob['schedule'], $message));
            if (!isset($cronJob['no_lock']) || $cronJob['no_lock'] === false) {
                $schedule->lock($this->lockFacade->createLock($this->generateLockKey($key), $this->config->getLockTTL()));
            }

            static::$schedules[$key] = $schedule;
        }

        return static::$schedules;
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
