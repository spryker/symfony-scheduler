<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;

/**
 * Answers questions about a single cron job's settings, so the shape of the configuration array is
 * known in one place rather than at each site that needs to read it.
 */
class CronJobConfigReader implements CronJobConfigReaderInterface
{
    protected const string CRON_JOB_NO_LOCK_KEY = 'no_lock';

    public function __construct(protected SymfonySchedulerConfig $symfonySchedulerConfig)
    {
    }

    public function isLockDisabled(string $name): bool
    {
        $cronJob = $this->symfonySchedulerConfig->getCronJobs()[$name] ?? [];

        return ($cronJob[static::CRON_JOB_NO_LOCK_KEY] ?? false) === true;
    }
}
