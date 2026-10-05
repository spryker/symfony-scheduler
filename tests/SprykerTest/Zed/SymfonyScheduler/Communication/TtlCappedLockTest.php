<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Spryker\Client\Lock\LockClientInterface;
use Spryker\Client\SymfonyScheduler\Lock\TtlCappedLock;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Lock\LockInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group TtlCappedLockTest
 * Add your own group annotations below this line
 */
class TtlCappedLockTest extends Unit
{
    /**
     * @var string
     */
    protected const RESOURCE = 'schedule_lock:check-product-validity';

    /**
     * The scheduler extends the lock to cover the wait until the next occurrence, so a daily job asks
     * for roughly a day. A worker killed without unwinding would otherwise hold the schedule that long.
     */
    protected function getMaxTtlSeconds(): float
    {
        return (new SymfonySchedulerConfig())->getLockTTL();
    }

    protected function createTtlCappedLock(LockInterface $lock): TtlCappedLock
    {
        $lockClientMock = $this->createMock(LockClientInterface::class);
        $lockClientMock->method('createLock')->willReturn($lock);

        return new TtlCappedLock($lockClientMock, new SymfonySchedulerConfig(), static::RESOURCE);
    }

    public function testCapsAnExtensionThatReachesBeyondTheMaximum(): void
    {
        // Arrange
        $lockMock = $this->createMock(LockInterface::class);
        $lockMock->expects($this->once())->method('refresh')->with($this->getMaxTtlSeconds());

        $ttlCappedLock = $this->createTtlCappedLock($lockMock);

        // Act
        $ttlCappedLock->refresh(86400.0);
    }

    public function testLeavesAnExtensionWithinTheMaximumAlone(): void
    {
        // Arrange
        $lockMock = $this->createMock(LockInterface::class);
        $lockMock->expects($this->once())->method('refresh')->with(60.0);

        $ttlCappedLock = $this->createTtlCappedLock($lockMock);

        // Act
        $ttlCappedLock->refresh(60.0);
    }

    public function testPassesNullThroughSoTheLockUsesItsOwnTtl(): void
    {
        // Arrange
        $lockMock = $this->createMock(LockInterface::class);
        $lockMock->expects($this->once())->method('refresh')->with(null);

        $ttlCappedLock = $this->createTtlCappedLock($lockMock);

        // Act
        $ttlCappedLock->refresh();
    }

    public function testBuildsItsLockFromTheClientForTheGivenResource(): void
    {
        // Arrange
        $lockMock = $this->createMock(LockInterface::class);
        $lockClientMock = $this->createMock(LockClientInterface::class);
        $lockClientMock
            ->expects($this->once())
            ->method('createLock')
            ->with(static::RESOURCE, $this->getMaxTtlSeconds())
            ->willReturn($lockMock);

        // Act
        new TtlCappedLock($lockClientMock, new SymfonySchedulerConfig(), static::RESOURCE);

        // Assert
        // Expectation on the client mock above: the lock is built once, from the injected collaborators.
    }

    public function testDelegatesEverythingElseUntouched(): void
    {
        // Arrange
        $lockMock = $this->createMock(LockInterface::class);
        $lockMock->method('acquire')->willReturn(true);
        $lockMock->method('isAcquired')->willReturn(true);
        $lockMock->method('isExpired')->willReturn(false);
        $lockMock->method('getRemainingLifetime')->willReturn(42.0);
        $lockMock->expects($this->once())->method('release');

        $ttlCappedLock = $this->createTtlCappedLock($lockMock);

        // Act
        $isAcquired = $ttlCappedLock->acquire();
        $ttlCappedLock->release();

        // Assert
        $this->assertTrue($isAcquired);
        $this->assertTrue($ttlCappedLock->isAcquired());
        $this->assertFalse($ttlCappedLock->isExpired());
        $this->assertSame(42.0, $ttlCappedLock->getRemainingLifetime());
    }
}
