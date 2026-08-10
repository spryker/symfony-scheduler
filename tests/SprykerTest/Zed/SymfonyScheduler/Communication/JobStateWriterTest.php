<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use RuntimeException;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Client\SymfonyScheduler\Writer\JobStateWriter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group JobStateWriterTest
 * Add your own group annotations below this line
 */
class JobStateWriterTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'test-job';

    public function testDisableJobStoresPersistentMarkerUnderPrefixedKey(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $expectedKey = $symfonySchedulerConfig->getJobDisabledStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedKey, '1')
            ->willReturn(true);
        $redisWrapperMock
            ->expects($this->never())
            ->method('delete');

        $jobStateWriter = new JobStateWriter($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $isDisabled = $jobStateWriter->disableJob(static::TEST_JOB_NAME);

        // Assert
        $this->assertTrue($isDisabled);
    }

    public function testDisableJobReturnsFalseWhenRedisThrows(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('set')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        $jobStateWriter = new JobStateWriter($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $isDisabled = $jobStateWriter->disableJob(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($isDisabled);
    }

    public function testEnableJobDeletesTheDisabledMarker(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $expectedKey = $symfonySchedulerConfig->getJobDisabledStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->expects($this->once())
            ->method('delete')
            ->with([$expectedKey])
            ->willReturn(1);
        $redisWrapperMock
            ->expects($this->never())
            ->method('set');

        $jobStateWriter = new JobStateWriter($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $isEnabled = $jobStateWriter->enableJob(static::TEST_JOB_NAME);

        // Assert
        $this->assertTrue($isEnabled);
    }

    public function testEnableJobReturnsFalseWhenRedisThrows(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('delete')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        $jobStateWriter = new JobStateWriter($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $isEnabled = $jobStateWriter->enableJob(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($isEnabled);
    }
}
