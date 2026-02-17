<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Plugin\SymfonyScheduler;

use Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SymfonyScheduler\Communication\Messages\CommandMessageInterface;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerBusinessFactory getBusinessFactory()
 */
class CompiledCronTransportsHandlerProviderPlugin extends AbstractPlugin implements SchedulerHandlerProviderPluginInterface
{
    /**
     * {@inheritDoc}
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
        $builder = $this->getFactory()->createSchedulerCronJobsBuilder();

        return $builder->buildSchedule();
    }
}
