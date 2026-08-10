<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\AvailableTransportProviderPluginInterface;

/**
 * @deprecated Use {@link \Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger\SchedulerAvailableTransportConfigProviderPlugin} instead.
 *
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
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
