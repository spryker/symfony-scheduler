<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use RuntimeException;
use Spryker\Client\Redis\RedisClientInterface;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapper;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group SymfonySchedulerRedisWrapperTest
 * Add your own group annotations below this line
 */
class SymfonySchedulerRedisWrapperTest extends Unit
{
    public function testConstructionDoesNotThrowWhenConnectionSetupFails(): void
    {
        // Arrange
        $redisClientMock = $this->createMock(RedisClientInterface::class);
        $redisClientMock
            ->method('setupConnection')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        // Act
        $symfonySchedulerRedisWrapper = new SymfonySchedulerRedisWrapper($redisClientMock, new SymfonySchedulerConfig());

        // Assert
        $this->assertInstanceOf(SymfonySchedulerRedisWrapperInterface::class, $symfonySchedulerRedisWrapper);
    }
}
