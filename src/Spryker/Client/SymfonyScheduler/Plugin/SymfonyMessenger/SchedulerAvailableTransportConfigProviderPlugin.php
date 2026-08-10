<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\AvailableTransportConfigProviderPluginInterface;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class SchedulerAvailableTransportConfigProviderPlugin extends AbstractPlugin implements AvailableTransportConfigProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Compiles transport configurations from all registered SchedulerHandlerProviderPluginInterface implementations.
     * - Uses the priority from SchedulerTransportInfoProviderPluginInterface implementations when available, otherwise falls back to the default priority.
     * - Allows scheduler-based transports to be automatically registered with Symfony Messenger together with their priority.
     *
     * @api
     *
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    public function getTransportConfigByTransportName(): array
    {
        return $this->getFactory()->createTransportConfigBuilder()->buildTransportConfig();
    }
}
