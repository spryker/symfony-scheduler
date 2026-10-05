<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage;
use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\MessageMappingProviderPluginInterface;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class AsyncSchedulerJobMessageMappingProviderPlugin extends AbstractPlugin implements MessageMappingProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Maps the asynchronous scheduler job message to the handler that executes it under a per-job lock.
     *
     * @api
     *
     * @return array<string, array<callable>>
     */
    public function getMessageToHandlerMap(): array
    {
        return [
            ScheduledCommandMessage::class => [
                $this->getFactory()->createLockingCommandHandler(),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     * - Returns no routing: the module publishes onto its own transport through
     *   {@link \Spryker\Client\SymfonyScheduler\Sender\SchedulerJobSenderInterface} rather than through
     *   the message bus, so that both ends of the queue are built by the same code.
     * - Only the handler mapping above is needed, so a received job reaches its handler.
     *
     * @api
     *
     * @return array<string, string|array<string>>
     */
    public function getMessageToTransportMap(): array
    {
        return [];
    }
}
