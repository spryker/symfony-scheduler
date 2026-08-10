<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use RuntimeException;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReader;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group JobRunRequestReaderTest
 * Add your own group annotations below this line
 */
class JobRunRequestReaderTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'test-job';

    public function testHasRunRequestReturnsTrueWhenMarkerExists(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $key = $symfonySchedulerConfig->getJobRunRequestStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getMulti')
            ->with([$key])
            ->willReturn([$key => '1']);

        $jobRunRequestReader = new JobRunRequestReader($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $hasRunRequest = $jobRunRequestReader->hasRunRequest(static::TEST_JOB_NAME);

        // Assert
        $this->assertTrue($hasRunRequest);
    }

    public function testHasRunRequestReturnsFalseWhenMarkerMissing(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $key = $symfonySchedulerConfig->getJobRunRequestStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getMulti')
            ->willReturn([$key => null]);

        $jobRunRequestReader = new JobRunRequestReader($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $hasRunRequest = $jobRunRequestReader->hasRunRequest(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($hasRunRequest);
    }

    public function testHasRunRequestFailsOpenWhenRedisThrows(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getMulti')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        $jobRunRequestReader = new JobRunRequestReader($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $hasRunRequest = $jobRunRequestReader->hasRunRequest(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($hasRunRequest);
    }

    public function testConsumeRunRequestReturnsTrueWhenMarkerWasDeleted(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $key = $symfonySchedulerConfig->getJobRunRequestStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->expects($this->once())
            ->method('delete')
            ->with([$key])
            ->willReturn(1);

        $jobRunRequestReader = new JobRunRequestReader($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $consumeRunRequest = $jobRunRequestReader->consumeRunRequest(static::TEST_JOB_NAME);

        // Assert
        $this->assertTrue($consumeRunRequest);
    }

    public function testConsumeRunRequestReturnsFalseWhenNoMarkerWasDeleted(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('delete')
            ->willReturn(0);

        $jobRunRequestReader = new JobRunRequestReader($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $consumeRunRequest = $jobRunRequestReader->consumeRunRequest(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($consumeRunRequest);
    }

    public function testConsumeRunRequestFailsOpenWhenRedisThrows(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('delete')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        $jobRunRequestReader = new JobRunRequestReader($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $consumeRunRequest = $jobRunRequestReader->consumeRunRequest(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($consumeRunRequest);
    }
}
