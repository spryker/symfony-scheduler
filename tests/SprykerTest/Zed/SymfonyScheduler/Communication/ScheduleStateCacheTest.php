<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Psr\Cache\CacheItemInterface;
use Spryker\Client\SymfonyScheduler\Cache\ScheduleStateCache;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group ScheduleStateCacheTest
 * Add your own group annotations below this line
 */
class ScheduleStateCacheTest extends Unit
{
    /**
     * @var string
     */
    protected const KEY_PREFIX = 'scheduler:state:';

    /**
     * @var string
     */
    protected const KEY = 'scheduler_checkpoint_check-oms-conditions';

    public function testReturnsTheStoredValueWithoutCallingTheCallback(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock->method('getMulti')->willReturn([static::KEY_PREFIX . static::KEY => serialize(['stored'])]);
        $redisWrapperMock->expects($this->never())->method('set');

        $scheduleStateCache = new ScheduleStateCache($redisWrapperMock, static::KEY_PREFIX);

        // Act
        $value = $scheduleStateCache->get(static::KEY, fn () => ['computed']);

        // Assert
        $this->assertSame(['stored'], $value);
    }

    public function testComputesAndStoresOnAMiss(): void
    {
        // Arrange
        $storedValue = null;
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock->method('getMulti')->willReturn([]);
        $redisWrapperMock
            ->expects($this->once())
            ->method('set')
            ->willReturnCallback(function (string $key, string $value, ?int $ttl) use (&$storedValue): bool {
                $storedValue = $value;

                return true;
            });

        $scheduleStateCache = new ScheduleStateCache($redisWrapperMock, static::KEY_PREFIX);

        // Act
        $value = $scheduleStateCache->get(static::KEY, fn () => ['computed']);

        // Assert
        $this->assertSame(['computed'], $value);
        $this->assertSame(['computed'], unserialize((string)$storedValue));
    }

    /**
     * The scheduler forces its writes through by passing an infinite beta, which means the stored
     * value must not be trusted even when one is present.
     */
    public function testRecomputesWhenTheCallerForcesItWithAnInfiniteBeta(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock->method('getMulti')->willReturn([static::KEY_PREFIX . static::KEY => serialize(['stale'])]);
        $redisWrapperMock->expects($this->once())->method('set');

        $scheduleStateCache = new ScheduleStateCache($redisWrapperMock, static::KEY_PREFIX);

        // Act
        $value = $scheduleStateCache->get(static::KEY, fn () => ['fresh'], INF);

        // Assert
        $this->assertSame(['fresh'], $value);
    }

    public function testTreatsAnUnreadableStoredValueAsAMiss(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock->method('getMulti')->willReturn([static::KEY_PREFIX . static::KEY => 'not-serialised-at-all']);
        $redisWrapperMock->expects($this->once())->method('set');

        $scheduleStateCache = new ScheduleStateCache($redisWrapperMock, static::KEY_PREFIX);

        // Act
        $value = $scheduleStateCache->get(static::KEY, fn () => ['recovered']);

        // Assert
        // Returning false here would hand the scheduler something it cannot destructure.
        $this->assertSame(['recovered'], $value);
    }

    public function testPassesTheRequestedExpiryToTheWrapperAsATtl(): void
    {
        // Arrange
        $capturedTtl = -1;
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock->method('getMulti')->willReturn([]);
        $redisWrapperMock
            ->method('set')
            ->willReturnCallback(function (string $key, string $value, ?int $ttl) use (&$capturedTtl): bool {
                $capturedTtl = $ttl;

                return true;
            });

        $scheduleStateCache = new ScheduleStateCache($redisWrapperMock, static::KEY_PREFIX);

        // Act
        $scheduleStateCache->get(static::KEY, function (CacheItemInterface $item) {
            $item->expiresAfter(1234);

            return ['value'];
        });

        // Assert
        $this->assertSame(1234, $capturedTtl);
    }

    public function testDeletesThroughTheWrapperWithThePrefixApplied(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->expects($this->once())
            ->method('delete')
            ->with([static::KEY_PREFIX . static::KEY])
            ->willReturn(1);

        $scheduleStateCache = new ScheduleStateCache($redisWrapperMock, static::KEY_PREFIX);

        // Act
        $isDeleted = $scheduleStateCache->delete(static::KEY);

        // Assert
        $this->assertTrue($isDeleted);
    }
}
