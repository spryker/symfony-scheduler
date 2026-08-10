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
