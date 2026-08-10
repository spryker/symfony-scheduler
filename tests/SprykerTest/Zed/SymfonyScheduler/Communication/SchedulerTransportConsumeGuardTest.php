<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Spryker\Client\SymfonyScheduler\Guard\SchedulerTransportConsumeGuard;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReaderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group SchedulerTransportConsumeGuardTest
 * Add your own group annotations below this line
 */
class SchedulerTransportConsumeGuardTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_TRANSPORT_NAME = 'check-oms-conditions';

    public function testCanConsumeTransportReturnsFalseForDisabledJob(): void
    {
        // Arrange
        $jobStateReaderMock = $this->createMock(JobStateReaderInterface::class);
        $jobStateReaderMock
            ->method('isJobDisabled')
            ->with(static::TEST_TRANSPORT_NAME)
            ->willReturn(true);

        $schedulerTransportConsumeGuard = new SchedulerTransportConsumeGuard($jobStateReaderMock);

        // Act
        $canConsumeTransport = $schedulerTransportConsumeGuard->canConsumeTransport(static::TEST_TRANSPORT_NAME);

        // Assert
        $this->assertFalse($canConsumeTransport);
    }

    public function testCanConsumeTransportReturnsTrueForEnabledJob(): void
    {
        // Arrange
        $jobStateReaderMock = $this->createMock(JobStateReaderInterface::class);
        $jobStateReaderMock
            ->method('isJobDisabled')
            ->with(static::TEST_TRANSPORT_NAME)
            ->willReturn(false);

        $schedulerTransportConsumeGuard = new SchedulerTransportConsumeGuard($jobStateReaderMock);

        // Act
        $canConsumeTransport = $schedulerTransportConsumeGuard->canConsumeTransport(static::TEST_TRANSPORT_NAME);

        // Assert
        $this->assertTrue($canConsumeTransport);
    }
}
