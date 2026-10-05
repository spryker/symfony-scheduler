<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Lock;

use Symfony\Component\Lock\LockInterface;

interface ScheduleLockProviderInterface
{
    /**
     * Specification:
     * - Returns the lock guarding a single cron job's schedule against being advanced by more than one
     *   worker at a time.
     *
     * @api
     */
    public function createScheduleLock(string $cronJobKey): LockInterface;
}
