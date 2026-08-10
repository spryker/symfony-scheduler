<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler;

use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Spryker\Client\Kernel\AbstractBundleConfig;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConfig as SharedSymfonySchedulerConfig;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConstants;

class SymfonySchedulerConfig extends AbstractBundleConfig
{
    protected const string SYMFONY_SCHEDULER_REDIS_CONNECTION_KEY = 'SYMFONY_SCHEDULER';

    protected const int REDIS_DEFAULT_DATABASE = 1;

    protected const int SCAN_CHUNK_SIZE = 100;

    protected const float DEFAULT_LOCK_TTL_SECONDS = 300.0;

    /**
     * Specification:
     * - Returns an array of jobs to be scheduled by the Symfony Scheduler.
     * - Each job is represented as an associative array with keys as an unique job name and values as an array of job configuration options.
     * - Schedule option follows the cron expression format. Or it can be a predefined string like '@daily', '@hourly', etc.
     * - Optional `priority` option defines the consumption order of the job transport. The higher the number, the earlier it is polled by the worker. Defaults to `0` when omitted.
     *
     * @api
     *
     * @example
     * return [
     *    'foo_bar' => [
     *      'command' => '$PHP_BIN vendor/bin/console foo:bar',
     *      'schedule' => '0 6 * * *',
     *      'priority' => 100,
     *    ],
     * ];
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
     */
    public function getLockTTL(): float
    {
        return static::DEFAULT_LOCK_TTL_SECONDS;
    }

    /**
     * @api
     */
    public function getJobStatusStorageKeyPrefix(): string
    {
        return SharedSymfonySchedulerConfig::JOB_STATUS_STORAGE_KEY_PREFIX;
    }

    /**
     * @api
     */
    public function getJobStatusTtl(): int
    {
        return SharedSymfonySchedulerConfig::JOB_STATUS_TTL_SECONDS;
    }

    /**
     * @api
     */
    public function getJobDisabledStorageKeyPrefix(): string
    {
        return SharedSymfonySchedulerConfig::JOB_DISABLED_STORAGE_KEY_PREFIX;
    }

    /**
     * @api
     */
    public function getJobRunRequestStorageKeyPrefix(): string
    {
        return SharedSymfonySchedulerConfig::JOB_RUN_REQUEST_STORAGE_KEY_PREFIX;
    }

    /**
     * @api
     */
    public function getJobRunRequestTtl(): int
    {
        return SharedSymfonySchedulerConfig::JOB_RUN_REQUEST_TTL_SECONDS;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getRedisConnectionKey(): string
    {
        return static::SYMFONY_SCHEDULER_REDIS_CONNECTION_KEY;
    }

    /**
     * @api
     *
     * @return \Generated\Shared\Transfer\RedisConfigurationTransfer
     */
    public function getRedisConnectionConfiguration(): RedisConfigurationTransfer
    {
        return (new RedisConfigurationTransfer())
            ->setDataSourceNames($this->getDataSourceNames())
            ->setConnectionCredentials($this->getConnectionCredentials())
            ->setClientOptions($this->getConnectionOptions());
    }

    /**
     * @api
     *
     * @return int
     */
    public function getRedisScanChunkSize(): int
    {
        return static::SCAN_CHUNK_SIZE;
    }

    /**
     * @return array<string>
     */
    protected function getDataSourceNames(): array
    {
        return $this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_DATA_SOURCE_NAMES, []);
    }

    protected function getConnectionCredentials(): RedisCredentialsTransfer
    {
        return (new RedisCredentialsTransfer())
            ->setScheme($this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_SCHEME, 'tcp'))
            ->setHost($this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_HOST))
            ->setPort($this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_PORT))
            ->setUsername($this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_USER, ''))
            ->setDatabase($this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_DATABASE, static::REDIS_DEFAULT_DATABASE))
            ->setPassword($this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_PASSWORD, ''))
            ->setIsPersistent($this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_PERSISTENT_CONNECTION, false));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConnectionOptions(): array
    {
        return $this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_REDIS_CONNECTION_OPTIONS, []);
    }
}
