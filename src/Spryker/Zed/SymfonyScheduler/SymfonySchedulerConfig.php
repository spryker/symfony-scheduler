<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class SymfonySchedulerConfig extends AbstractBundleConfig
{
    public const string STATUS_WAITING = 'Waiting';

    public const string STATUS_RUNNING = 'Running';

    public const string STATUS_SUCCESS = 'Success';

    public const string STATUS_ERROR = 'Error';

    /**
     * View-only status: surfaced in the Back Office when a job is disabled. It is never persisted to the
     * per-job status record in Redis; it is resolved for display from the disabled marker.
     */
    public const string STATUS_DISABLED = 'Disabled';

    protected const string JOB_STATUS_STORAGE_KEY_PREFIX = 'scheduler:job:status:';

    protected const int JOB_STATUS_TTL_SECONDS = 86400;

    protected const string JOB_DISABLED_STORAGE_KEY_PREFIX = 'scheduler:job:disabled:';

    protected const string JOB_RUN_REQUEST_STORAGE_KEY_PREFIX = 'scheduler:job:run:';

    protected const int JOB_RUN_REQUEST_TTL_SECONDS = 300;

    /**
     * Specification:
     * - Returns the Redis key prefix under which per-job status entries are stored.
     *
     * @api
     */
    public function getJobStatusStorageKeyPrefix(): string
    {
        return static::JOB_STATUS_STORAGE_KEY_PREFIX;
    }

    /**
     * Specification:
     * - Returns the Redis key prefix under which per-job "disabled" markers are stored.
     * - A job is considered disabled while a key `<prefix><jobName>` exists; absence of the key means enabled.
     *
     * @api
     */
    public function getJobDisabledStorageKeyPrefix(): string
    {
        return static::JOB_DISABLED_STORAGE_KEY_PREFIX;
    }

    /**
     * Specification:
     * - Returns the time-to-live in seconds for a per-job status entry in Redis.
     * - Stale entries (for example from a crashed worker) auto-expire after this period.
     *
     * @api
     */
    public function getJobStatusTtl(): int
    {
        return static::JOB_STATUS_TTL_SECONDS;
    }

    /**
     * Specification:
     * - Returns the Redis key prefix under which per-job on-demand "run request" markers are stored.
     * - While a key `<prefix><jobName>` exists, the scheduler fires the job immediately, ahead of its cron schedule.
     * - The marker is consumed (deleted) the moment the job is triggered, so it results in exactly one extra run.
     *
     * @api
     */
    public function getJobRunRequestStorageKeyPrefix(): string
    {
        return static::JOB_RUN_REQUEST_STORAGE_KEY_PREFIX;
    }

    /**
     * Specification:
     * - Returns the time-to-live in seconds for a per-job "run request" marker in Redis.
     * - Acts as a safety net: a request that is never consumed (worker down, job disabled) auto-expires after this period.
     *
     * @api
     */
    public function getJobRunRequestTtl(): int
    {
        return static::JOB_RUN_REQUEST_TTL_SECONDS;
    }

    /**
     * Specification:
     * - Returns an array of jobs to be scheduled by the Symfony Scheduler.
     * - Each job is represented as an associative array with keys as an unique job name and values as an array of job configuration options.
     * - Schedule option follows the cron expression format. Or it can be a predefined string like '@daily', '@hourly', etc.
     * - Optional `priority` option defines the consumption order of the job transport. The higher the number, the earlier it is polled by the worker. Defaults to `0` when omitted.
     *
     * @api
     *
     * @deprecated Define cron jobs in {@link \Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig::getCronJobs()} instead. The scheduler runtime now runs at the Client layer.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getCronJobs(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Returns the time in seconds for which the lock will be held when a scheduled job is running.
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig::getLockTTL()} instead. The scheduler runtime now runs at the Client layer.
     *
     * @return float
     */
    public function getLockTTL(): float
    {
        return 300.0;
    }
}
