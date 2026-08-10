<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\MessageMappingProviderPluginInterface;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class SchedulerMessageMappingProviderPlugin extends AbstractPlugin implements MessageMappingProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Compiles message-to-handler mappings from all registered SchedulerHandlerProviderPluginInterface implementations.
     * - Returns a merged array of all message class names to their handler callables from all scheduler handler providers.
     * - Allows scheduler-based messages to be automatically routed to their handlers.
     *
     * @api
     *
     * @return array<string, array<callable>>
     */
    public function getMessageToHandlerMap(): array
    {
        $messageToHandlerMap = [];

        foreach ($this->getFactory()->getSchedulerHandlerProviderPlugins() as $plugin) {
            $messageToHandlerMap = array_merge(
                $messageToHandlerMap,
                $plugin->getHandlers(),
            );
        }

        return $messageToHandlerMap;
    }

    /**
     * {@inheritDoc}
     * - Compiles message-to-transport mappings from all registered SchedulerHandlerProviderPluginInterface implementations.
     * - Returns a merged array mapping message class names to transport names from all scheduler handler providers.
     * - Uses the transport name from the scheduled task definitions for each message type.
     *
     * @api
     *
     * @return array<string, string|array<string>>
     */
    public function getMessageToTransportMap(): array
    {
        $messageToTransportMap = [];

        $scheduleInfo = $this->getFactory()->createScheduleReader()->getScheduledTasks();

        foreach ($this->getFactory()->getSchedulerHandlerProviderPlugins() as $plugin) {
            $handlers = $plugin->getHandlers();

            foreach ($handlers as $messageClass => $handler) {
                foreach ($scheduleInfo as $taskDefinition) {
                    if ($taskDefinition['message_class'] !== $messageClass) {
                        continue;
                    }

                    $messageToTransportMap[$messageClass][] = sprintf('schedule://%s', $taskDefinition['name']);
                }
            }
        }

        return $messageToTransportMap;
    }
}
