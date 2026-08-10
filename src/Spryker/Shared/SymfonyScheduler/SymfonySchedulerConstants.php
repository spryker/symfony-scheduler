<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\SymfonyScheduler;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface SymfonySchedulerConstants
{
    /**
     * Specification:
     * - Defines a scheme|protocol (tcp:// or redis://) for the scheduler job-status Redis connection.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_SCHEME = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_SCHEME';

    /**
     * Specification:
     * - Defines a host for the scheduler job-status Redis connection.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_HOST = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_HOST';

    /**
     * Specification:
     * - Defines a port for the scheduler job-status Redis connection.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_PORT = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_PORT';

    /**
     * Specification:
     * - Defines a Redis database to connect to for scheduler job status.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_DATABASE = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_DATABASE';

    /**
     * Specification:
     * - Defines a password for connecting to the scheduler job-status Redis.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_PASSWORD = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_PASSWORD';

    /**
     * Specification:
     * - Defines a username for Redis ACL authentication for the scheduler job-status Redis.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_USER = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_USER';

    /**
     * Specification:
     * - Specifies an array of DSN strings for a multi-instance cluster/replication Redis setup.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_DATA_SOURCE_NAMES = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_DATA_SOURCE_NAMES';

    /**
     * Specification:
     * - Specifies an array of connection options.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_CONNECTION_OPTIONS = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_CONNECTION_OPTIONS';

    /**
     * Specification:
     * - Enables/disables data persistence for the scheduler job-status Redis connection.
     *
     * @api
     */
    public const string SYMFONY_SCHEDULER_REDIS_PERSISTENT_CONNECTION = 'SYMFONY_SCHEDULER:SYMFONY_SCHEDULER_REDIS_PERSISTENT_CONNECTION';
}
