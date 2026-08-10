<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

use DateTimeImmutable;
use ReflectionClass;
use ReflectionException;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Trigger\AbstractDecoratedTrigger;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

class ScheduleReader implements ScheduleReaderInterface
{
    protected const string TASK_KEY_NAME = 'name';

    protected const string TASK_KEY_PRIORITY = 'priority';

    protected const string CRON_JOB_PRIORITY_KEY = 'priority';

    protected const int DEFAULT_PRIORITY = 0;

    /**
     * @param array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface> $schedulerHandlerProviderPlugins
     */
    public function __construct(
        protected array $schedulerHandlerProviderPlugins,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getScheduledTasks(): array
    {
        $cronJobs = $this->symfonySchedulerConfig->getCronJobs();
        $tasks = [];

        foreach ($this->schedulerHandlerProviderPlugins as $plugin) {
            $schedules = $plugin->getSchedules();

            foreach ($schedules as $scheduleName => $schedule) {
                $reflection = new ReflectionClass($schedule);
                $messagesProperty = $reflection->getProperty('messages');
                $messagesProperty->setAccessible(true);
                $messages = $messagesProperty->getValue($schedule);

                foreach ($messages as $recurringMessage) {
                    if ($recurringMessage instanceof RecurringMessage) {
                        $tasks[] = $this->extractTaskInfo($scheduleName, $recurringMessage, $cronJobs);
                    }
                }
            }
        }

        return $tasks;
    }

    /**
     * @param string $scheduleName
     * @param \Symfony\Component\Scheduler\RecurringMessage $recurringMessage
     * @param array<string, array<string, mixed>> $cronJobs
     *
     * @return array<string, mixed>
     */
    protected function extractTaskInfo(string $scheduleName, RecurringMessage $recurringMessage, array $cronJobs): array
    {
        $provider = $recurringMessage->getProvider();
        $trigger = $recurringMessage->getTrigger();

        // Get the first message from the provider
        $context = new MessageContext($scheduleName, $recurringMessage->getId(), $trigger, new DateTimeImmutable());
        $messages = iterator_to_array($provider->getMessages($context));
        $message = $messages ? reset($messages) : null;

        return [
            static::TASK_KEY_NAME => $scheduleName,
            'message_class' => $message ? get_class($message) : 'N/A',
            'trigger_type' => $this->getTriggerType($trigger),
            'schedule' => $this->getScheduleExpression($trigger),
            'command' => $message ? $this->extractCommand($message) : 'N/A',
            static::TASK_KEY_PRIORITY => (int)($cronJobs[$scheduleName][static::CRON_JOB_PRIORITY_KEY] ?? static::DEFAULT_PRIORITY),
        ];
    }

    protected function getTriggerType(TriggerInterface $trigger): string
    {
        $trigger = $this->unwrapTrigger($trigger);

        if ($trigger instanceof CronExpressionTrigger) {
            return 'cron';
        }

        $className = get_class($trigger);
        $parts = explode('\\', $className);
        $shortName = end($parts);

        return str_replace('Trigger', '', $shortName);
    }

    protected function getScheduleExpression(TriggerInterface $trigger): string
    {
        // Every trigger is Stringable and cron triggers stringify to their expression, so this reads the
        // schedule without reflecting on a concrete trigger's internals (which decorators would hide).
        return (string)$this->unwrapTrigger($trigger);
    }

    /**
     * Returns the innermost trigger, peeling off any framework decorators (e.g. the run-request wrapper).
     */
    protected function unwrapTrigger(TriggerInterface $trigger): TriggerInterface
    {
        if ($trigger instanceof AbstractDecoratedTrigger) {
            return $trigger->inner();
        }

        return $trigger;
    }

    protected function extractCommand(object $message): string
    {
        // Try to extract command from message
        if (method_exists($message, 'getCommand')) {
            return $message->getCommand();
        }

        // Try to get command via reflection
        try {
            $reflection = new ReflectionClass($message);

            if ($reflection->hasProperty('command')) {
                $commandProperty = $reflection->getProperty('command');
                $commandProperty->setAccessible(true);
                $command = $commandProperty->getValue($message);

                if (is_string($command)) {
                    return $command;
                }
            }
        } catch (ReflectionException $e) {
        }

        return 'N/A';
    }
}
