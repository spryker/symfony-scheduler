<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Plugin\SymfonyMessenger;

use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\AvailableTransportProviderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 */
class SchedulerAvailableTransportProviderPlugin extends AbstractPlugin implements AvailableTransportProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Compiles transport DSN mappings from all registered SchedulerHandlerProviderPluginInterface implementations.
     * - Returns a merged array of all transport names to DSN mappings from all scheduler handler providers.
     * - Allows scheduler-based transports to be automatically registered with Symfony Messenger.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getTransportDSNByTransportName(): array
    {
        $transportMappings = [];

        foreach ($this->getFactory()->getSchedulerHandlerProviderPlugins() as $plugin) {
            $scheduleNames = array_keys($plugin->getSchedules());
            foreach ($scheduleNames as $scheduleName) {
                $transportMappings[$scheduleName] = sprintf('schedule://%s', $scheduleName);
            }
        }

        return $transportMappings;
    }
}
