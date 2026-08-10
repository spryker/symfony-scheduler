<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use RuntimeException;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReader;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group JobStateReaderTest
 * Add your own group annotations below this line
 */
class JobStateReaderTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'test-job';

    public function testIsJobDisabledReturnsTrueWhenMarkerExists(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $key = $symfonySchedulerConfig->getJobDisabledStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getMulti')
            ->with([$key])
            ->willReturn([$key => '1']);

        $jobStateReader = new JobStateReader($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $isJobDisabled = $jobStateReader->isJobDisabled(static::TEST_JOB_NAME);

        // Assert
        $this->assertTrue($isJobDisabled);
    }

    public function testIsJobDisabledReturnsFalseWhenMarkerMissing(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $key = $symfonySchedulerConfig->getJobDisabledStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getMulti')
            ->willReturn([$key => null]);

        $jobStateReader = new JobStateReader($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $isJobDisabled = $jobStateReader->isJobDisabled(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($isJobDisabled);
    }

    public function testIsJobDisabledFailsOpenWhenRedisThrows(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getMulti')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        $jobStateReader = new JobStateReader($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $isJobDisabled = $jobStateReader->isJobDisabled(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($isJobDisabled);
    }

    public function testGetDisabledJobNamesStripsThePrefix(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $prefix = $symfonySchedulerConfig->getJobDisabledStorageKeyPrefix();

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getKeys')
            ->with($prefix . '*')
            ->willReturn([$prefix . 'job-a', $prefix . 'job-b']);

        $jobStateReader = new JobStateReader($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $disabledJobNames = $jobStateReader->getDisabledJobNames();

        // Assert
        $this->assertSame(['job-a', 'job-b'], $disabledJobNames);
    }

    public function testGetDisabledJobNamesFailsOpenWhenRedisThrows(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('getKeys')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        $jobStateReader = new JobStateReader($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $disabledJobNames = $jobStateReader->getDisabledJobNames();

        // Assert
        $this->assertSame([], $disabledJobNames);
    }
}
