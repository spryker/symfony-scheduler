<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use DateTimeImmutable;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Spryker\Client\SymfonyScheduler\Trigger\RunRequestAwareCronTrigger;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group RunRequestAwareCronTriggerTest
 * Add your own group annotations below this line
 */
class RunRequestAwareCronTriggerTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'test-job';

    public function testGetNextRunDateReturnsRunImmediatelyWhenRunRequestIsConsumed(): void
    {
        // Arrange
        $run = new DateTimeImmutable('2026-07-21 10:00:00');

        $jobRunRequestReaderMock = $this->createMock(JobRunRequestReaderInterface::class);
        $jobRunRequestReaderMock
            ->expects($this->once())
            ->method('consumeRunRequest')
            ->with(static::TEST_JOB_NAME)
            ->willReturn(true);

        $innerTriggerMock = $this->createMock(TriggerInterface::class);
        $innerTriggerMock
            ->expects($this->never())
            ->method('getNextRunDate');

        $trigger = new RunRequestAwareCronTrigger($innerTriggerMock, $jobRunRequestReaderMock, static::TEST_JOB_NAME);

        // Act
        $nextRunDate = $trigger->getNextRunDate($run);

        // Assert
        $this->assertSame($run, $nextRunDate);
    }

    public function testGetNextRunDateDelegatesToInnerTriggerWhenNoRunRequestExists(): void
    {
        // Arrange
        $run = new DateTimeImmutable('2026-07-21 10:00:00');
        $nextCronRun = new DateTimeImmutable('2026-07-22 06:00:00');

        $jobRunRequestReaderMock = $this->createMock(JobRunRequestReaderInterface::class);
        $jobRunRequestReaderMock
            ->method('consumeRunRequest')
            ->willReturn(false);

        $innerTriggerMock = $this->createMock(TriggerInterface::class);
        $innerTriggerMock
            ->expects($this->once())
            ->method('getNextRunDate')
            ->with($run)
            ->willReturn($nextCronRun);

        $trigger = new RunRequestAwareCronTrigger($innerTriggerMock, $jobRunRequestReaderMock, static::TEST_JOB_NAME);

        // Act
        $nextRunDate = $trigger->getNextRunDate($run);

        // Assert
        $this->assertSame($nextCronRun, $nextRunDate);
    }

    public function testToStringDelegatesToInnerTriggerToKeepIdentityStable(): void
    {
        // Arrange
        $innerTriggerMock = $this->createMock(TriggerInterface::class);
        $innerTriggerMock
            ->method('__toString')
            ->willReturn('0 6 * * *');

        $trigger = new RunRequestAwareCronTrigger(
            $innerTriggerMock,
            $this->createMock(JobRunRequestReaderInterface::class),
            static::TEST_JOB_NAME,
        );

        // Act
        $triggerExpression = (string)$trigger;

        // Assert
        $this->assertSame('0 6 * * *', $triggerExpression);
    }
}
