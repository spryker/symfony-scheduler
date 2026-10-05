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
 * Caps how far a schedule lock's expiry can be pushed out.
 *
 * The scheduler extends the lock to cover the wait until the next occurrence, so for a job that runs
 * once a day the expiry lands roughly a day out. A worker killed without unwinding - OOM, SIGKILL, a
 * container eviction - therefore leaves the schedule locked for that long, and the job silently does
 * not run until it expires.
 *
 * Capping is safe because the schedule keeps its position in shared state rather than in the lock
 * owner's memory: whichever worker next acquires the lock reads the stored checkpoint and carries on
 * from there, so the lock only has to cover the moment of emission rather than ownership between runs.
 */
class TtlCappedLock implements LockInterface
{
    /**
     * Built once, because acquire() and release() have to act on the same lock to correlate.
     */
    protected LockInterface $lock;

    public function __construct(
        protected LockClientInterface $lockClient,
        protected SymfonySchedulerConfig $symfonySchedulerConfig,
        string $resource
    ) {
        $this->lock = $this->lockClient->createLock($resource, $this->symfonySchedulerConfig->getLockTTL());
    }

    public function refresh(?float $ttl = null): void
    {
        $maxTtlSeconds = $this->symfonySchedulerConfig->getLockTTL();

        // Null means "use the lock's own configured TTL", which is already within the cap.
        if ($ttl !== null && $ttl > $maxTtlSeconds) {
            $ttl = $maxTtlSeconds;
        }

        $this->lock->refresh($ttl);
    }

    public function acquire(bool $blocking = false): bool
    {
        return $this->lock->acquire($blocking);
    }

    public function isAcquired(): bool
    {
        return $this->lock->isAcquired();
    }

    public function release(): void
    {
        $this->lock->release();
    }

    public function isExpired(): bool
    {
        return $this->lock->isExpired();
    }

    public function getRemainingLifetime(): ?float
    {
        return $this->lock->getRemainingLifetime();
    }
}
