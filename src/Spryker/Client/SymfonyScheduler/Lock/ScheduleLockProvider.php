<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Lock;

use Spryker\Client\Lock\LockClientInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Lock\LockInterface;

/**
 * Hands out the per-job schedule locks, so the resource naming lives in one place and the code that
 * compiles schedules does not build locks of its own.
 */
class ScheduleLockProvider implements ScheduleLockProviderInterface
{
    protected const string LOCK_RESOURCE_PATTERN = 'schedule_lock:%s';

    public function __construct(
        protected LockClientInterface $lockClient,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    public function createScheduleLock(string $cronJobKey): LockInterface
    {
        return new TtlCappedLock(
            $this->lockClient,
            $this->symfonySchedulerConfig,
            sprintf(static::LOCK_RESOURCE_PATTERN, $cronJobKey),
        );
    }
}
