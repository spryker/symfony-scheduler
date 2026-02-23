<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Business\Builder;

use Symfony\Component\Scheduler\Scheduler;

class SchedulerBuilder implements SchedulerBuilderInterface
{
    /**
     * @param array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface> $schedulerHandlerProviderPlugins
     */
    public function __construct(protected array $schedulerHandlerProviderPlugins)
    {
    }

    /**
     * @return \Symfony\Component\Scheduler\Scheduler
     */
    public function build(): Scheduler
    {
        $handlers = [];
        $schedules = [];

        foreach ($this->schedulerHandlerProviderPlugins as $plugin) {
            $handlers = array_merge($handlers, $plugin->getHandlers());
            $schedules = array_merge($schedules, $plugin->getSchedules());
        }

        return new Scheduler($handlers, $schedules);
    }
}
