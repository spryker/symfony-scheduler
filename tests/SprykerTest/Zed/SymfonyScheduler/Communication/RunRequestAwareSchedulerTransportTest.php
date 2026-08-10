<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Spryker\Client\SymfonyScheduler\Transport\RunRequestAwareSchedulerTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Scheduler\Schedule;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group RunRequestAwareSchedulerTransportTest
 * Add your own group annotations below this line
 */
class RunRequestAwareSchedulerTransportTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'test-job';

    public function testGetForcesScheduleRestartWhenRunRequestExists(): void
    {
        // Arrange
        $schedule = new Schedule();

        $jobRunRequestReaderMock = $this->createMock(JobRunRequestReaderInterface::class);
        $jobRunRequestReaderMock
            ->method('hasRunRequest')
            ->with(static::TEST_JOB_NAME)
            ->willReturn(true);

        $innerTransportMock = $this->createMock(TransportInterface::class);
        $innerTransportMock
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $transport = new RunRequestAwareSchedulerTransport(
            $innerTransportMock,
            $schedule,
            $jobRunRequestReaderMock,
            static::TEST_JOB_NAME,
        );

        // Act
        iterator_to_array($this->toIterator($transport->get()));

        // Assert
        $this->assertTrue($schedule->shouldRestart());
    }

    public function testGetDoesNotForceScheduleRestartWhenNoRunRequestExists(): void
    {
        // Arrange
        $schedule = new Schedule();

        $jobRunRequestReaderMock = $this->createMock(JobRunRequestReaderInterface::class);
        $jobRunRequestReaderMock
            ->method('hasRunRequest')
            ->willReturn(false);

        $innerTransportMock = $this->createMock(TransportInterface::class);
        $innerTransportMock
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $transport = new RunRequestAwareSchedulerTransport(
            $innerTransportMock,
            $schedule,
            $jobRunRequestReaderMock,
            static::TEST_JOB_NAME,
        );

        // Act
        iterator_to_array($this->toIterator($transport->get()));

        // Assert
        $this->assertFalse($schedule->shouldRestart());
    }

    /**
     * @param iterable<mixed> $iterable
     *
     * @return \Generator<mixed>
     */
    protected function toIterator(iterable $iterable): iterable
    {
        yield from $iterable;
    }
}
