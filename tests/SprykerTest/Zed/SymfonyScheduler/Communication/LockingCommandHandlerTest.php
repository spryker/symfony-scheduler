<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Spryker\Client\Lock\LockClientInterface;
use Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage;
use Spryker\Client\SymfonyScheduler\MessageHandler\CommandHandler;
use Spryker\Client\SymfonyScheduler\MessageHandler\LockingCommandHandler;
use Spryker\Client\SymfonyScheduler\Reader\CronJobConfigReaderInterface;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReaderInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group LockingCommandHandlerTest
 * Add your own group annotations below this line
 */
class LockingCommandHandlerTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'check-workflow-conditions';

    /**
     * @var string
     */
    protected const TEST_COMMAND = 'vendor/bin/console workflow:check-condition';

    public function testExecutesTheCommandWhenTheLockIsFree(): void
    {
        // Arrange
        $lockMock = $this->createLockMock(true);
        $lockMock->expects($this->once())->method('release');

        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->expects($this->once())->method('__invoke')->willReturn(null);

        $lockingCommandHandler = $this->createLockingCommandHandler($commandHandlerMock, $lockMock, false);

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        $this->assertNull($result);
    }

    public function testSkipsTheCommandWhenAnotherWorkerHoldsTheLock(): void
    {
        // Arrange
        $lockMock = $this->createLockMock(false);

        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->expects($this->never())->method('__invoke');

        $lockingCommandHandler = $this->createLockingCommandHandler($commandHandlerMock, $lockMock, false);

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        // Null rather than a message: a dropped duplicate is the intended outcome, and a non-empty
        // result would be treated as failure output and fail the whole worker run.
        $this->assertNull($result);
    }

    public function testSkipsTheCommandWhenTheJobWasDisabledAfterQueueing(): void
    {
        // Arrange
        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->expects($this->never())->method('__invoke');

        $lockClientMock = $this->createMock(LockClientInterface::class);
        $lockClientMock->expects($this->never())->method('createLock');

        $lockingCommandHandler = new LockingCommandHandler(
            $commandHandlerMock,
            $lockClientMock,
            $this->createJobStateReaderMock(true),
            $this->createCronJobConfigReaderMock(false),
            new SymfonySchedulerConfig(),
        );

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        $this->assertNull($result);
    }

    /**
     * acquire() answers false only for contention; an unreachable lock store throws. If that escaped,
     * the worker would die before acknowledging and the broker would redeliver the same job forever.
     */
    public function testSkipsTheCommandWhenTheLockStoreIsUnreachable(): void
    {
        // Arrange
        $lockMock = $this->createMock(LockInterface::class);
        $lockMock->method('acquire')->willThrowException(new LockAcquiringException('redis is down'));

        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->expects($this->never())->method('__invoke');

        $lockingCommandHandler = $this->createLockingCommandHandler($commandHandlerMock, $lockMock, false);

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        $this->assertNull($result);
    }

    /**
     * Same reasoning: a throwing job must not leave the message unacknowledged and wedge the queue.
     */
    public function testAcknowledgesTheMessageWhenTheCommandThrows(): void
    {
        // Arrange
        $lockMock = $this->createLockMock(true);

        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->method('__invoke')->willThrowException(new RuntimeException('process exploded'));

        $lockingCommandHandler = $this->createLockingCommandHandler($commandHandlerMock, $lockMock, false);

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        $this->assertNull($result);
    }

    public function testSurvivesALockThatCannotBeReleased(): void
    {
        // Arrange
        $lockMock = $this->createLockMock(true);
        $lockMock->method('release')->willThrowException(new LockReleasingException('redis went away mid-run'));

        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->method('__invoke')->willReturn(null);

        $lockingCommandHandler = $this->createLockingCommandHandler($commandHandlerMock, $lockMock, false);

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        // A throw from the finally block would replace the return value and escape the handler.
        $this->assertNull($result);
    }

    public function testReleasesTheLockWhenTheCommandThrows(): void
    {
        // Arrange
        $lockMock = $this->createLockMock(true);
        $lockMock->expects($this->once())->method('release');

        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->method('__invoke')->willThrowException(new RuntimeException('boom'));

        $lockingCommandHandler = $this->createLockingCommandHandler($commandHandlerMock, $lockMock, false);

        // Act
        $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        // Expectation on the release() mock above: the lock must not be left held for its whole TTL.
    }

    /**
     * A job configured with `no_lock` is declaring itself safe to overlap, so the executor must not
     * serialise it - otherwise the setting would silently mean nothing on this tier.
     */
    public function testRunsWithoutALockWhenTheJobOptedOut(): void
    {
        // Arrange
        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->expects($this->once())->method('__invoke')->willReturn(null);

        $lockClientMock = $this->createMock(LockClientInterface::class);
        $lockClientMock->expects($this->never())->method('createLock');

        $lockingCommandHandler = new LockingCommandHandler(
            $commandHandlerMock,
            $lockClientMock,
            $this->createJobStateReaderMock(false),
            $this->createCronJobConfigReaderMock(true),
            new SymfonySchedulerConfig(),
        );

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        $this->assertNull($result);
    }

    public function testStillSkipsADisabledJobThatOptedOutOfLocking(): void
    {
        // Arrange
        $commandHandlerMock = $this->createMock(CommandHandler::class);
        $commandHandlerMock->expects($this->never())->method('__invoke');

        $lockingCommandHandler = new LockingCommandHandler(
            $commandHandlerMock,
            $this->createMock(LockClientInterface::class),
            $this->createJobStateReaderMock(true),
            $this->createCronJobConfigReaderMock(true),
            new SymfonySchedulerConfig(),
        );

        // Act
        $result = $lockingCommandHandler($this->createScheduledCommandMessage());

        // Assert
        $this->assertNull($result);
    }

    protected function createCronJobConfigReaderMock(bool $isLockDisabled): CronJobConfigReaderInterface|MockObject
    {
        $cronJobConfigReaderMock = $this->createMock(CronJobConfigReaderInterface::class);
        $cronJobConfigReaderMock->method('isLockDisabled')->willReturn($isLockDisabled);

        return $cronJobConfigReaderMock;
    }

    protected function createScheduledCommandMessage(): ScheduledCommandMessage
    {
        return new ScheduledCommandMessage(
            static::TEST_JOB_NAME,
            static::TEST_COMMAND,
            ScheduledCommandMessage::ORIGIN_CRON,
            '2026-09-15T09:00:00+00:00',
        );
    }

    protected function createLockMock(bool $isAcquired): LockInterface|MockObject
    {
        $lockMock = $this->createMock(LockInterface::class);
        $lockMock->method('acquire')->willReturn($isAcquired);

        return $lockMock;
    }

    protected function createJobStateReaderMock(bool $isDisabled): JobStateReaderInterface|MockObject
    {
        $jobStateReaderMock = $this->createMock(JobStateReaderInterface::class);
        $jobStateReaderMock->method('isJobDisabled')->willReturn($isDisabled);

        return $jobStateReaderMock;
    }

    protected function createLockingCommandHandler(
        CommandHandler $commandHandler,
        LockInterface $lock,
        bool $isDisabled
    ): LockingCommandHandler {
        $lockClientMock = $this->createMock(LockClientInterface::class);
        $lockClientMock->method('createLock')->willReturn($lock);

        return new LockingCommandHandler(
            $commandHandler,
            $lockClientMock,
            $this->createJobStateReaderMock($isDisabled),
            $this->createCronJobConfigReaderMock(false),
            new SymfonySchedulerConfig(),
        );
    }
}
