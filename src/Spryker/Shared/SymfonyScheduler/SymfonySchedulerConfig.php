<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\SymfonyScheduler;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class SymfonySchedulerConfig extends AbstractSharedConfig
{
    /**
     * @api
     */
    public const string STATUS_WAITING = 'Waiting';

    /**
     * Set by the trigger tier once a job has been handed to the asynchronous execution queue, and
     * replaced by STATUS_RUNNING as soon as an executor picks it up.
     *
     * @api
     */
    public const string STATUS_QUEUED = 'Queued';

    /**
     * @api
     */
    public const string STATUS_RUNNING = 'Running';

    /**
     * @api
     */
    public const string STATUS_SUCCESS = 'Success';

    /**
     * @api
     */
    public const string STATUS_ERROR = 'Error';

    /**
     * View-only status: surfaced in the Back Office when a job is disabled. It is never persisted to the
     * per-job status record in Redis; it is resolved for display from the disabled marker.
     *
     * @api
     */
    public const string STATUS_DISABLED = 'Disabled';

    /**
     * DSN scheme claimed by {@link \Spryker\Client\SymfonyScheduler\Transport\Amqp\SchedulerJobTransportFactory}.
     * A scheme of its own is required rather than tidy: Symfony picks the first transport factory whose
     * supports() matches, and the built-in AMQP factory is registered ahead of the plugin-provided ones.
     *
     * @api
     */
    public const string JOB_TRANSPORT_DSN_SCHEME = 'scheduler-amqp://';

    /**
     * @api
     */
    public const string JOB_STATUS_STORAGE_KEY_PREFIX = 'scheduler:job:status:';

    /**
     * @api
     */
    public const int JOB_STATUS_TTL_SECONDS = 86400;

    /**
     * @api
     */
    public const string JOB_DISABLED_STORAGE_KEY_PREFIX = 'scheduler:job:disabled:';

    /**
     * @api
     */
    public const string JOB_RUN_REQUEST_STORAGE_KEY_PREFIX = 'scheduler:job:run:';

    /**
     * @api
     */
    public const int JOB_RUN_REQUEST_TTL_SECONDS = 300;
}
