<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Plugin\SymfonyMessenger;

use Spryker\Shared\SymfonyMessengerExtension\Dependency\Plugin\GroupAwareTransportsPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerBusinessFactory getBusinessFactory()
 */
class CompiledCronTransportGroupAwarePlugin extends AbstractPlugin implements GroupAwareTransportsPluginInterface
{
    protected const string GROUP_KEY = 'compiled-cron-scheduler';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<string, array<string>>
     */
    public function getGroupMapping(): array
    {
        $builder = $this->getFactory()->createSchedulerCronJobsBuilder();

        return [
            static::GROUP_KEY => array_keys($builder->buildSchedule()),
        ];
    }
}
