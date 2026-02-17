<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Plugin\SymfonyMessenger;

use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\TransportFactoryProviderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerBusinessFactory getBusinessFactory()
 */
class SchedulerTransportFactoryProviderPlugin extends AbstractPlugin implements TransportFactoryProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns SchedulerTransportFactory instance to be used by Symfony Messenger.
     *
     * @api
     *
     * @return array<\Symfony\Component\Messenger\Transport\TransportFactoryInterface>
     */
    public function getTransportFactories(): array
    {
        return [
            $this->getFactory()->createSchedulerTransportFactory(),
        ];
    }
}
