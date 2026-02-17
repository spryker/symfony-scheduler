<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Builder;

use Psr\Container\ContainerInterface;
use Symfony\Component\Scheduler\Schedule;

class ScheduleContainerBuilder implements ScheduleContainerBuilderInterface
{
    /**
     * @param array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface> $scheduleProviderPlugins
     */
    public function __construct(protected array $scheduleProviderPlugins = [])
    {
    }

    public function build(): ContainerInterface
    {
        return new class ($this->scheduleProviderPlugins) implements ContainerInterface
        {
            /**
             * @var array <string, \Symfony\Component\Scheduler\Schedule>
             */
            protected array $indexedSchedules = [];

            /**
             * @param array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface> $scheduleProviderPlugins
             */
            public function __construct(protected array $scheduleProviderPlugins)
            {
                foreach ($this->scheduleProviderPlugins as $scheduleProviderPlugin) {
                    $this->indexedSchedules = array_merge($this->indexedSchedules, $scheduleProviderPlugin->getSchedules());
                }
            }

            public function get(string $id): Schedule
            {
                return $this->indexedSchedules[$id];
            }

            public function has(string $id): bool
            {
                return isset($this->indexedSchedules[$id]);
            }
        };
    }
}
