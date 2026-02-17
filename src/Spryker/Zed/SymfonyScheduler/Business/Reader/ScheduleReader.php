<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Business\Reader;

use DateTimeImmutable;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;

class ScheduleReader implements ScheduleReaderInterface
{
    /**
     * @param array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface> $schedulerHandlerProviderPlugins
     */
    public function __construct(protected array $schedulerHandlerProviderPlugins)
    {
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getScheduledTasks(): array
    {
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
                        $tasks[] = $this->extractTaskInfo($scheduleName, $recurringMessage);
                    }
                }
            }
        }

        return $tasks;
    }

    /**
     * @param string $scheduleName
     * @param \Symfony\Component\Scheduler\RecurringMessage $recurringMessage
     *
     * @return array<string, mixed>
     */
    protected function extractTaskInfo(string $scheduleName, RecurringMessage $recurringMessage): array
    {
        $provider = $recurringMessage->getProvider();
        $trigger = $recurringMessage->getTrigger();

        // Get the first message from the provider
        $context = new MessageContext($scheduleName, $recurringMessage->getId(), $trigger, new DateTimeImmutable());
        $messages = iterator_to_array($provider->getMessages($context));
        $message = $messages ? reset($messages) : null;

        $taskInfo = [
            'name' => $scheduleName,
            'message_class' => $message ? get_class($message) : 'N/A',
            'trigger_type' => $this->getTriggerType($trigger),
            'schedule' => $this->getScheduleExpression($trigger),
            'command' => $message ? $this->extractCommand($message) : 'N/A',
        ];

        return $taskInfo;
    }

    /**
     * @param object $trigger
     *
     * @return string
     */
    protected function getTriggerType(object $trigger): string
    {
        if ($trigger instanceof CronExpressionTrigger) {
            return 'cron';
        }

        $className = get_class($trigger);
        $parts = explode('\\', $className);
        $shortName = end($parts);

        return str_replace('Trigger', '', $shortName);
    }

    /**
     * @param object $trigger
     *
     * @return string
     */
    protected function getScheduleExpression(object $trigger): string
    {
        // Use reflection to get the cron expression
        $reflection = new ReflectionClass($trigger);
        $expressionProperty = $reflection->getProperty('expression');
        $expressionProperty->setAccessible(true);

        return (string)$expressionProperty->getValue($trigger);
    }

    /**
     * @param object $message
     *
     * @return string
     */
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
