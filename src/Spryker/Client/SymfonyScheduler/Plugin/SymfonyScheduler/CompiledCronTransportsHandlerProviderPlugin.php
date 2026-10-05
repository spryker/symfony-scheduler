<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyScheduler;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface;
use Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface;
use Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerTransportInfoProviderPluginInterface;

/**
 * @deprecated Use {@link \Spryker\Client\SymfonyScheduler\Plugin\SymfonyScheduler\AsyncCompiledCronTransportsHandlerProviderPlugin}
 *   instead, which hands each due job to a queue rather than running it in the polling loop, so a
 *   long job cannot hold up the jobs behind it.
 *
 *   Switching requires the execution tier to be in place: the queue registered through
 *   {@link \Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger\AsyncSchedulerJobTransportFactoryProviderPlugin}
 *   and {@link \Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger\AsyncSchedulerJobTransportConfigProviderPlugin},
 *   the routing from {@link \Spryker\Client\SymfonyScheduler\Plugin\SymfonyMessenger\AsyncSchedulerJobMessageMappingProviderPlugin},
 *   and a worker consuming that queue. Until those exist this plugin remains the working option.
 *
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class CompiledCronTransportsHandlerProviderPlugin extends AbstractPlugin implements SchedulerHandlerProviderPluginInterface, SchedulerTransportInfoProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Runs a due job in the polling loop itself, which blocks that loop until the job finishes.
     *
     * @api
     *
     * @return array<string, array<callable>>
     */
    public function getHandlers(): array
    {
        return [
            CommandMessageInterface::class => [
                $this->getFactory()->createCommandHandler(),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<string, \Symfony\Component\Scheduler\Schedule>
     */
    public function getSchedules(): array
    {
        return $this->getFactory()->createSchedulerCronJobsBuilder()->buildSchedule();
    }

    /**
     * {@inheritDoc}
     * - Returns transport info for each compiled cron job, carrying the configured priority.
     *
     * @api
     *
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    public function getTransportInfo(): array
    {
        return $this->getFactory()->createSchedulerCronJobsBuilder()->buildTransportInfo();
    }
}
