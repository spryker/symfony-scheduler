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
class AsyncSchedulerJobTransportConfigProviderPlugin extends AbstractPlugin implements AvailableTransportConfigProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Registers the queue the asynchronous execution tier consumes, so it can be named as a receiver.
     * - Kept apart from the schedule:// transports, which are compiled from the registered schedules.
     *
     * @api
     *
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    public function getTransportConfigByTransportName(): array
    {
        return $this->getFactory()->createAsyncJobTransportConfigBuilder()->buildTransportConfig();
    }
}
