<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Plugin\SymfonyMessenger;

use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\MessageMappingProviderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
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
     * - Uses the transport name from getTransportDSNByTransportName() for each message type.q
     *
     * @api
     *
     * @return array<string, string|array<string>>
     */
    public function getMessageToTransportMap(): array
    {
        $messageToTransportMap = [];

        $scheduleInfo = $this->getBusinessFactory()->createScheduleReader()->getScheduledTasks();

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
