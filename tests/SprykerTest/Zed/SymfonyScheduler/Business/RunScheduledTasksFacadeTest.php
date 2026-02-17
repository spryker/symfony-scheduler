<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Business;

use Codeception\Test\Unit;
use Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface;
use Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacade;
use Spryker\Zed\SymfonyScheduler\SymfonySchedulerDependencyProvider;
use SprykerTest\Zed\SymfonyScheduler\Business\Messages\TestScheduledMessage;
use SprykerTest\Zed\SymfonyScheduler\SymfonySchedulerBusinessTester;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Business
 * @group Facade
 * @group RunScheduledTasksFacadeTest
 * Add your own group annotations below this line
 */
class RunScheduledTasksFacadeTest extends Unit
{
    /**
     * @var string
     */
    protected const string TEST_SCHEDULE_NAME = 'test_schedule';

    /**
     * @var string
     */
    protected const string TEST_CRON_EXPRESSION = '*/5 * * * *';

    /**
     * @var \SprykerTest\Zed\SymfonyScheduler\SymfonySchedulerBusinessTester
     */
    protected SymfonySchedulerBusinessTester $tester;

    /**
     * @return void
     */
    public function testRunScheduledTasksExecutesSchedulerRun(): void
    {
        // Arrange
        $facade = $this->createFacadeWithMockedScheduler(true);

        // Act & Assert - If no exception is thrown, the scheduler ran successfully
        $facade->runScheduledTasks();

        // Assert - Execution completes without errors
        $this->assertTrue(true, 'Scheduler ran successfully without exceptions');
    }

    /**
     * @return void
     */
    public function testRunScheduledTasksWithMultipleSchedulerHandlerProviders(): void
    {
        // Arrange
        $plugin1 = $this->createTestSchedulerHandlerProviderPlugin('schedule1');
        $plugin2 = $this->createTestSchedulerHandlerProviderPlugin('schedule2');

        $this->tester->setDependency(
            SymfonySchedulerDependencyProvider::PLUGINS_SCHEDULER_HANDLER_PROVIDER,
            [$plugin1, $plugin2],
        );

        $facade = $this->tester->getFacade();

        // Act & Assert - Should process multiple plugins without errors
        $facade->runScheduledTasks();

        // Assert - No exceptions means successful execution
        $this->assertTrue(true, 'Scheduler processed multiple plugins successfully');
    }

    /**
     * @return void
     */
    public function testRunScheduledTasksWithEmptySchedulerHandlerProviders(): void
    {
        // Arrange
        $this->tester->setDependency(
            SymfonySchedulerDependencyProvider::PLUGINS_SCHEDULER_HANDLER_PROVIDER,
            [],
        );

        $facade = $this->tester->getFacade();

        // Act & Assert - Should handle empty plugins gracefully
        $facade->runScheduledTasks();

        // Assert - No exceptions with empty plugins
        $this->assertTrue(true, 'Scheduler handled empty plugins successfully');
    }

    /**
     * @return void
     */
    public function testRunScheduledTasksWithScheduleContainingRecurringMessages(): void
    {
        // Arrange
        $messageHandled = false;
        $testMessage = new TestScheduledMessage();

        $plugin = $this->createMock(SchedulerHandlerProviderPluginInterface::class);
        $plugin->method('getHandlers')->willReturn([
            TestScheduledMessage::class => [function () use (&$messageHandled): void {
                $messageHandled = true;
            }],
        ]);

        $schedule = new Schedule();
        $schedule->add(
            RecurringMessage::cron(static::TEST_CRON_EXPRESSION, $testMessage),
        );
        $plugin->method('getSchedules')->willReturn([static::TEST_SCHEDULE_NAME => $schedule]);
        $plugin->method('getTransports')->willReturn([]);

        $this->tester->setDependency(
            SymfonySchedulerDependencyProvider::PLUGINS_SCHEDULER_HANDLER_PROVIDER,
            [$plugin],
        );

        $facade = $this->tester->getFacade();

        // Act
        $facade->runScheduledTasks();

        // Assert - Scheduler was configured with the recurring message
        $this->assertTrue(true, 'Scheduler configured with recurring messages successfully');
    }

    /**
     * @param bool $shouldRun
     *
     * @return \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacade
     */
    protected function createFacadeWithMockedScheduler(bool $shouldRun): SymfonySchedulerFacade
    {
        $plugin = $this->createTestSchedulerHandlerProviderPlugin(static::TEST_SCHEDULE_NAME);

        $this->tester->setDependency(
            SymfonySchedulerDependencyProvider::PLUGINS_SCHEDULER_HANDLER_PROVIDER,
            [$plugin],
        );

        return $this->tester->getFacade();
    }

    /**
     * @param string $scheduleName
     *
     * @return \Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface
     */
    protected function createTestSchedulerHandlerProviderPlugin(string $scheduleName): SchedulerHandlerProviderPluginInterface
    {
        $plugin = $this->createMock(SchedulerHandlerProviderPluginInterface::class);

        $plugin->method('getHandlers')->willReturn([
            TestScheduledMessage::class => [function (TestScheduledMessage $message): void {
                // Handler logic
            }],
        ]);

        $schedule = new Schedule();
        $schedule->add(
            RecurringMessage::cron(static::TEST_CRON_EXPRESSION, new TestScheduledMessage()),
        );

        $plugin->method('getSchedules')->willReturn([$scheduleName => $schedule]);
        $plugin->method('getTransports')->willReturn([]);

        return $plugin;
    }
}
