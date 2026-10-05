<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\SymfonyScheduler\Message\CommandMessage;
use Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage;
use Spryker\Client\SymfonyScheduler\MessageHandler\EnqueueingCommandHandler;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReaderInterface;
use Spryker\Client\SymfonyScheduler\Sender\SchedulerJobSenderInterface;
use Spryker\Client\SymfonyScheduler\Writer\JobStatusWriterInterface;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group EnqueueingCommandHandlerTest
 * Add your own group annotations below this line
 */
class EnqueueingCommandHandlerTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'check-workflow-conditions';

    /**
     * @var string
     */
    protected const TEST_COMMAND = 'vendor/bin/console workflow:check-condition';

    public function testDispatchesTheJobToTheExecutionQueue(): void
    {
        // Arrange
        $dispatchedMessages = [];
        $schedulerJobSenderMock = $this->createMock(SchedulerJobSenderInterface::class);
        $schedulerJobSenderMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (object $message) use (&$dispatchedMessages): void {
                $dispatchedMessages[] = $message;
            });

        $enqueueingCommandHandler = new EnqueueingCommandHandler(
            $schedulerJobSenderMock,
            $this->createMock(JobStatusWriterInterface::class),
            $this->createJobStateReaderMock(false),
        );

        // Act
        $result = $enqueueingCommandHandler($this->createCommandMessage());

        // Assert
        // A non-empty handler result is treated as failure output by the worker and fails the run.
        $this->assertNull($result);
        $this->assertCount(1, $dispatchedMessages);

        /** @var \Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage $scheduledCommandMessage */
        $scheduledCommandMessage = $dispatchedMessages[0];
        $this->assertInstanceOf(ScheduledCommandMessage::class, $scheduledCommandMessage);
        $this->assertSame(static::TEST_JOB_NAME, $scheduledCommandMessage->getName());
        $this->assertSame(static::TEST_COMMAND, $scheduledCommandMessage->getCommand());
        $this->assertSame(ScheduledCommandMessage::ORIGIN_CRON, $scheduledCommandMessage->getOrigin());
        $this->assertNotSame('', $scheduledCommandMessage->getTriggeredAt());
    }

    public function testMarksTheJobQueuedBeforeDispatchingIt(): void
    {
        // Arrange
        $callOrder = [];

        $jobStatusWriterMock = $this->createMock(JobStatusWriterInterface::class);
        $jobStatusWriterMock
            ->method('saveJobStatus')
            ->willReturnCallback(function (SchedulerJobStatusTransfer $transfer) use (&$callOrder): void {
                $callOrder[] = 'status:' . $transfer->getStatus();
            });

        $schedulerJobSenderMock = $this->createMock(SchedulerJobSenderInterface::class);
        $schedulerJobSenderMock
            ->method('send')
            ->willReturnCallback(function () use (&$callOrder): void {
                $callOrder[] = 'dispatch';
            });

        $enqueueingCommandHandler = new EnqueueingCommandHandler(
            $schedulerJobSenderMock,
            $jobStatusWriterMock,
            $this->createJobStateReaderMock(false),
        );

        // Act
        $enqueueingCommandHandler($this->createCommandMessage());

        // Assert
        // Both tiers write the same status key without fencing, so dispatching first would let a fast
        // executor record Running only for this write to land afterwards and overwrite it.
        $this->assertSame(['status:' . SymfonySchedulerConfig::STATUS_QUEUED, 'dispatch'], $callOrder);
    }

    public function testLeavesStartedAtUnsetSoQueueWaitIsNotReportedAsRuntime(): void
    {
        // Arrange
        $savedTransfers = [];
        $jobStatusWriterMock = $this->createMock(JobStatusWriterInterface::class);
        $jobStatusWriterMock
            ->method('saveJobStatus')
            ->willReturnCallback(function (SchedulerJobStatusTransfer $transfer) use (&$savedTransfers): void {
                $savedTransfers[] = $transfer;
            });

        $enqueueingCommandHandler = new EnqueueingCommandHandler(
            $this->createMock(SchedulerJobSenderInterface::class),
            $jobStatusWriterMock,
            $this->createJobStateReaderMock(false),
        );

        // Act
        $enqueueingCommandHandler($this->createCommandMessage());

        // Assert
        $this->assertCount(1, $savedTransfers);
        $this->assertNull($savedTransfers[0]->getStartedAt());
        $this->assertNotNull($savedTransfers[0]->getQueuedAt());
    }

    /**
     * Applied here rather than by pausing the transport: a paused transport never runs its generator,
     * so the stored checkpoint freezes and re-enabling replays the whole disabled period.
     */
    public function testDoesNotEnqueueADisabledJob(): void
    {
        // Arrange
        $schedulerJobSenderMock = $this->createMock(SchedulerJobSenderInterface::class);
        $schedulerJobSenderMock->expects($this->never())->method('send');

        $jobStatusWriterMock = $this->createMock(JobStatusWriterInterface::class);
        $jobStatusWriterMock->expects($this->never())->method('saveJobStatus');

        $enqueueingCommandHandler = new EnqueueingCommandHandler(
            $schedulerJobSenderMock,
            $jobStatusWriterMock,
            $this->createJobStateReaderMock(true),
        );

        // Act
        $result = $enqueueingCommandHandler($this->createCommandMessage());

        // Assert
        $this->assertNull($result);
    }

    protected function createJobStateReaderMock(bool $isDisabled): JobStateReaderInterface|MockObject
    {
        $jobStateReaderMock = $this->createMock(JobStateReaderInterface::class);
        $jobStateReaderMock->method('isJobDisabled')->willReturn($isDisabled);

        return $jobStateReaderMock;
    }

    protected function createCommandMessage(): CommandMessage
    {
        return (new CommandMessage())
            ->setCommand(static::TEST_COMMAND)
            ->setName(static::TEST_JOB_NAME);
    }
}
