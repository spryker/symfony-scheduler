<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\TransportConsumeGuardPluginInterface;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class DisabledSchedulerJobTransportGuardPlugin extends AbstractPlugin implements TransportConsumeGuardPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns false when the transport belongs to a scheduled job that has been disabled from the Back Office.
     * - Returns true otherwise (enabled scheduled jobs and all non-scheduler transports).
     *
     * @api
     */
    public function canConsumeTransport(string $transportName): bool
    {
        return $this->getFactory()
            ->createSchedulerTransportConsumeGuard()
            ->canConsumeTransport($transportName);
    }
}
