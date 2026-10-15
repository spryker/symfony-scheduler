<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use DateTimeImmutable;
use ReflectionClass;
use Spryker\Client\SymfonyScheduler\Builder\ConfigBasedCronJobsBuilder;
use Spryker\Client\SymfonyScheduler\Cache\ScheduleStateCacheProviderInterface;
use Spryker\Client\SymfonyScheduler\Lock\ScheduleLockProviderInterface;
use Spryker\Client\SymfonyScheduler\Message\CommandMessage;
use Spryker\Client\SymfonyScheduler\Message\SyncCommandMessage;
use Spryker\Client\SymfonyScheduler\Reader\CronJobConfigReaderInterface;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group ConfigBasedCronJobsBuilderTest
 * Add your own group annotations below this line
 */
class ConfigBasedCronJobsBuilderTest extends Unit
{
    /**
     * @var string
     */
    protected const ASYNC_JOB_NAME = 'check-workflow-conditions';

    /**
     * @var string
     */
    protected const SYNC_JOB_NAME = 'queue-worker-start';

    protected function setUp(): void
    {
        parent::setUp();

        // The builder memoises its schedules in a static property, which would otherwise leak between tests.
        $reflectionProperty = (new ReflectionClass(ConfigBasedCronJobsBuilder::class))->getProperty('schedules');
        $reflectionProperty->setValue(null, []);
    }

    public function testBuildScheduleCarriesSyncMessageOnlyForJobsFlaggedSync(): void
    {
        // Arrange
        $cronJobsBuilder = $this->createCronJobsBuilder();

        // Act
        $schedules = $cronJobsBuilder->buildSchedule();

        // Assert
        $this->assertInstanceOf(
            CommandMessage::class,
            $this->extractMessage($schedules[static::ASYNC_JOB_NAME], static::ASYNC_JOB_NAME),
        );
        $this->assertInstanceOf(
            SyncCommandMessage::class,
            $this->extractMessage($schedules[static::SYNC_JOB_NAME], static::SYNC_JOB_NAME),
        );
    }

    public function testBuildScheduleKeepsNameAndCommandOnTheSyncMessage(): void
    {
        // Arrange
        $cronJobsBuilder = $this->createCronJobsBuilder();

        // Act
        $message = $this->extractMessage($cronJobsBuilder->buildSchedule()[static::SYNC_JOB_NAME], static::SYNC_JOB_NAME);

        // Assert
        $this->assertSame(static::SYNC_JOB_NAME, $message->getName());
        $this->assertStringContainsString('queue:worker:start', $message->getCommand());
    }

    protected function createCronJobsBuilder(): ConfigBasedCronJobsBuilder
    {
        $symfonySchedulerConfigMock = $this->createMock(SymfonySchedulerConfig::class);
        $symfonySchedulerConfigMock
            ->method('getCronJobs')
            ->willReturn([
                static::ASYNC_JOB_NAME => [
                    'command' => 'vendor/bin/console workflow:check-conditions',
                    'schedule' => '* * * * *',
                ],
                static::SYNC_JOB_NAME => [
                    'command' => 'vendor/bin/console queue:worker:start',
                    'schedule' => '* * * * *',
                    'sync' => true,
                ],
            ]);

        $cronJobConfigReaderMock = $this->createMock(CronJobConfigReaderInterface::class);
        $cronJobConfigReaderMock
            ->method('isSyncExecutionEnabled')
            ->willReturnCallback(fn (string $name): bool => $name === static::SYNC_JOB_NAME);
        // Keeps the schedule free of a lock, which this test does not exercise.
        $cronJobConfigReaderMock
            ->method('isLockDisabled')
            ->willReturn(true);

        $scheduleStateCacheProviderMock = $this->createMock(ScheduleStateCacheProviderInterface::class);
        $scheduleStateCacheProviderMock
            ->method('findCache')
            ->willReturn(null);

        return new ConfigBasedCronJobsBuilder(
            $symfonySchedulerConfigMock,
            new CommandMessage(),
            $this->createMock(ScheduleLockProviderInterface::class),
            $this->createMock(JobRunRequestReaderInterface::class),
            $scheduleStateCacheProviderMock,
            $cronJobConfigReaderMock,
            new SyncCommandMessage(),
        );
    }

    /**
     * @param \Symfony\Component\Scheduler\Schedule $schedule
     * @param string $scheduleName
     *
     * @return \Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface
     */
    protected function extractMessage(Schedule $schedule, string $scheduleName)
    {
        $reflectionProperty = (new ReflectionClass($schedule))->getProperty('messages');
        /** @var array<\Symfony\Component\Scheduler\RecurringMessage> $recurringMessages */
        $recurringMessages = $reflectionProperty->getValue($schedule);

        /** @var \Symfony\Component\Scheduler\RecurringMessage $recurringMessage */
        $recurringMessage = reset($recurringMessages);
        $this->assertInstanceOf(RecurringMessage::class, $recurringMessage);

        $messageContext = new MessageContext(
            $scheduleName,
            $recurringMessage->getId(),
            $recurringMessage->getTrigger(),
            new DateTimeImmutable(),
        );

        $messages = iterator_to_array($recurringMessage->getProvider()->getMessages($messageContext), false);
        $this->assertNotEmpty($messages);

        return $messages[0];
    }
}
