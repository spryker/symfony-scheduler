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
use Spryker\Client\SymfonyScheduler\Writer\JobRunRequestWriter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group JobRunRequestWriterTest
 * Add your own group annotations below this line
 */
class JobRunRequestWriterTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'test-job';

    public function testRequestRunStoresMarkerUnderPrefixedKeyWithTtl(): void
    {
        // Arrange
        $symfonySchedulerConfig = new SymfonySchedulerConfig();
        $expectedKey = $symfonySchedulerConfig->getJobRunRequestStorageKeyPrefix() . static::TEST_JOB_NAME;

        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedKey, '1', $symfonySchedulerConfig->getJobRunRequestTtl())
            ->willReturn(true);

        $jobRunRequestWriter = new JobRunRequestWriter($redisWrapperMock, $symfonySchedulerConfig);

        // Act
        $isRunRequested = $jobRunRequestWriter->requestRun(static::TEST_JOB_NAME);

        // Assert
        $this->assertTrue($isRunRequested);
    }

    public function testRequestRunReturnsFalseAndSwallowsRedisFailures(): void
    {
        // Arrange
        $redisWrapperMock = $this->createMock(SymfonySchedulerRedisWrapperInterface::class);
        $redisWrapperMock
            ->method('set')
            ->willThrowException(new RuntimeException('Redis unavailable'));

        $jobRunRequestWriter = new JobRunRequestWriter($redisWrapperMock, new SymfonySchedulerConfig());

        // Act
        $isRunRequested = $jobRunRequestWriter->requestRun(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($isRunRequested);
    }
}
