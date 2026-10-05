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
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class AsyncCompiledCronTransportsHandlerProviderPlugin extends AbstractPlugin implements SchedulerHandlerProviderPluginInterface, SchedulerTransportInfoProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Hands a due job to the execution queue rather than running it, so the polling loop stays free
     *   and a long job cannot hold up the jobs behind it.
     *
     * @api
     *
     * @return array<string, array<callable>>
     */
    public function getHandlers(): array
    {
        return [
            CommandMessageInterface::class => [
                $this->getFactory()->createEnqueueingCommandHandler(),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     * - Returns a schedule per configured cron job.
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
