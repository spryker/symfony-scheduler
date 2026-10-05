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

    protected const string DEFAULT_JOB_TRANSPORT_NAME = 'scheduler-jobs';

    protected const string DEFAULT_AMQP_USERNAME = 'spryker';

    protected const int DEFAULT_AMQP_PORT = 5672;

    protected const string DEFAULT_AMQP_VIRTUAL_HOST = '/';

    protected const float DEFAULT_JOB_EXECUTION_LOCK_TTL_SECONDS = 360.0;

    protected const string SCHEDULE_STATE_STORAGE_KEY_PREFIX = 'scheduler:state:';

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
     * Specification:
     * - Returns the transport name the asynchronous scheduler job queue is registered under.
     * - The name is what `symfonymessenger:consume` takes as a receiver argument.
     *
     * @api
     */
    public function getJobTransportName(): string
    {
        return static::DEFAULT_JOB_TRANSPORT_NAME;
    }

    /**
     * Specification:
     * - Returns the DSN of the asynchronous scheduler job transport.
     * - The scheme is owned by {@link \Spryker\Client\SymfonyScheduler\Transport\Amqp\SchedulerJobTransportFactory},
     *   so the transport is built by this module instead of the shared Symfony Messenger transport creation.
     * - The host segment carries the queue name, which leaves room for per-priority queues later.
     *
     * @api
     */
    public function getJobTransportDsn(): string
    {
        return SharedSymfonySchedulerConfig::JOB_TRANSPORT_DSN_SCHEME . $this->getJobTransportName();
    }

    /**
     * Specification:
     * - Returns the AMQP DSN the scheduler job queue is published to and consumed from.
     * - The module already owns a dedicated Redis connection rather than borrowing another module's;
     *   its AMQP connection follows the same decision.
     * - Credentials and virtual host are URL-encoded, so passwords with special characters are safe.
     *
     * @api
     */
    public function getAmqpConnectionDsn(): string
    {
        $host = (string)$this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_AMQP_HOST, '');

        if ($host === '') {
            return (string)$this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_AMQP_DSN, '');
        }

        return sprintf(
            'amqp://%s:%s@%s:%s/%s',
            rawurlencode((string)$this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_AMQP_USERNAME, static::DEFAULT_AMQP_USERNAME)),
            rawurlencode((string)$this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_AMQP_PASSWORD, '')),
            $host,
            $this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_AMQP_PORT, static::DEFAULT_AMQP_PORT),
            rawurlencode(ltrim((string)$this->get(SymfonySchedulerConstants::SYMFONY_SCHEDULER_AMQP_VIRTUAL_HOST, static::DEFAULT_AMQP_VIRTUAL_HOST), '/')),
        );
    }

    /**
     * Specification:
     * - Returns the AMQP transport options for the scheduler job queue.
     * - Only keys accepted by the Symfony AMQP transport may be returned; it rejects anything else.
     * - `auto_setup` is enabled here so the queue is declared on first use without changing the
     *   publish & synchronize queue defaults, which deliberately disable it.
     *
     * @api
     *
     * @return array<string, mixed>
     */
    public function getJobQueueTransportOptions(string $queueName): array
    {
        return [
            'auto_setup' => true,
            'exchange' => [
                'name' => $queueName,
                'type' => 'direct',
            ],
            'queues' => [
                $queueName => [],
            ],
            'persistent' => 'true',
            'connect_timeout' => 3,
            'read_timeout' => 130,
            'write_timeout' => 130,
            'heartbeat' => 0,
            'rpc_timeout' => 0,
        ];
    }

    /**
     * Specification:
     * - Returns the key prefix the schedule state is stored under.
     *
     * @api
     */
    public function getScheduleStateStorageKeyPrefix(): string
    {
        return static::SCHEDULE_STATE_STORAGE_KEY_PREFIX;
    }

    /**
     * Specification:
     * - Returns the time in seconds a per-job execution lock is held while the job runs.
     *
     * @api
     */
    public function getJobExecutionLockTtl(): float
    {
        return static::DEFAULT_JOB_EXECUTION_LOCK_TTL_SECONDS;
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
