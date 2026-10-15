<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Plugin\SymfonyScheduler;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\SymfonyScheduler\Message\CommandMessage;
use Spryker\Client\SymfonyScheduler\Message\SyncCommandMessage;
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
     * - Runs a job marked `sync` in its cron job configuration in the polling loop instead, which blocks
     *   that loop until the job finishes.
     *
     * The two entries are keyed by concrete message class rather than by the shared interface on purpose.
     * Symfony's HandlersLocator resolves a message's interfaces as well as its class, so an interface key
     * would match both message types and every synchronous job would also be enqueued for a second run.
     *
     * @api
     *
     * @return array<string, array<callable>>
     */
    public function getHandlers(): array
    {
        return [
            CommandMessage::class => [
                $this->getFactory()->createEnqueueingCommandHandler(),
            ],
            SyncCommandMessage::class => [
                $this->getFactory()->createCommandHandler(),
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
