<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class SymfonySchedulerConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Returns an array of jobs to be scheduled by the Symfony Scheduler.
     * - Each job is represented as an associative array with keys as an unique job name and values as an array of job configuration options.
     * - Schedule option follows the cron expression format. Or it can be a predefined string like '@daily', '@hourly', etc.
     *
     * @api
     *
     * @example
     * return [
     *    'foo_bar' => [
     *      'command' => '$PHP_BIN vendor/bin/console foo:bar',
     *      'schedule' => '0 6 * * *',
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
     *
     * @return float
     */
    public function getLockTTL(): float
    {
        return 300.0;
    }
}
