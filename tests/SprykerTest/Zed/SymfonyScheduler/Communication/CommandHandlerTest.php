<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use Spryker\Client\SymfonyScheduler\Message\CommandMessage;
use Spryker\Client\SymfonyScheduler\MessageHandler\CommandHandler;
use Spryker\Client\SymfonyScheduler\Writer\JobStatusWriterInterface;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group CommandHandlerTest
 * Add your own group annotations below this line
 */
class CommandHandlerTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'test-job';

    public function testInvokeWritesRunningThenSuccessForSuccessfulCommand(): void
    {
        // Arrange
        // Act
        $savedTransfers = $this->runCommand('true');

        // Assert
        $this->assertCount(2, $savedTransfers);

        $this->assertSame(SymfonySchedulerConfig::STATUS_RUNNING, $savedTransfers[0]->getStatus());
        $this->assertSame(static::TEST_JOB_NAME, $savedTransfers[0]->getName());
        $this->assertNotNull($savedTransfers[0]->getStartedAt());
        $this->assertNotNull($savedTransfers[0]->getWorkerName());

        $this->assertSame(SymfonySchedulerConfig::STATUS_SUCCESS, $savedTransfers[1]->getStatus());
        $this->assertNotNull($savedTransfers[1]->getFinishedAt());
        $this->assertNull($savedTransfers[1]->getErrorMessage());
    }

    public function testInvokeWritesErrorForFailingCommand(): void
    {
        // Arrange
        // Act
        $savedTransfers = $this->runCommand('exit 1');

        // Assert
        $this->assertCount(2, $savedTransfers);
        $this->assertSame(SymfonySchedulerConfig::STATUS_RUNNING, $savedTransfers[0]->getStatus());
        $this->assertSame(SymfonySchedulerConfig::STATUS_ERROR, $savedTransfers[1]->getStatus());
        $this->assertNotNull($savedTransfers[1]->getFinishedAt());
    }

    /**
     * @param string $command
     *
     * @return array<\Generated\Shared\Transfer\SchedulerJobStatusTransfer>
     */
    protected function runCommand(string $command): array
    {
        $savedTransfers = [];

        $jobStatusWriterMock = $this->createMock(JobStatusWriterInterface::class);
        $jobStatusWriterMock
            ->method('saveJobStatus')
            ->willReturnCallback(function (SchedulerJobStatusTransfer $schedulerJobStatusTransfer) use (&$savedTransfers): void {
                // The handler mutates one transfer instance; clone to snapshot its state at each save.
                $savedTransfers[] = clone $schedulerJobStatusTransfer;
            });

        $commandMessage = (new CommandMessage())
            ->setCommand($command)
            ->setName(static::TEST_JOB_NAME);

        $commandHandler = new CommandHandler($jobStatusWriterMock);
        $commandHandler($commandMessage);

        return $savedTransfers;
    }
}
