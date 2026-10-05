<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\TransportFactoryProviderPluginInterface;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class AsyncSchedulerJobTransportFactoryProviderPlugin extends AbstractPlugin implements TransportFactoryProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns the transport factory backing the scheduler-amqp:// transport the execution tier consumes.
     * - Kept apart from the schedule:// transport factory, which serves the registered schedules and has
     *   nothing to do with the queue the jobs are executed from.
     *
     * @api
     *
     * @return array<\Symfony\Component\Messenger\Transport\TransportFactoryInterface<\Symfony\Component\Messenger\Transport\TransportInterface>>
     */
    public function getTransportFactories(): array
    {
        return [
            $this->getFactory()->createSchedulerJobTransportFactory(),
        ];
    }
}
