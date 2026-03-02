<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\SymfonyScheduler\Business\Builder\SchedulerBuilder;
use Spryker\Zed\SymfonyScheduler\Business\Builder\SchedulerBuilderInterface;
use Spryker\Zed\SymfonyScheduler\Business\Reader\ScheduleReader;
use Spryker\Zed\SymfonyScheduler\Business\Reader\ScheduleReaderInterface;
use Spryker\Zed\SymfonyScheduler\Business\Runner\SchedulerRunner;
use Spryker\Zed\SymfonyScheduler\Business\Runner\SchedulerRunnerInterface;
use Spryker\Zed\SymfonyScheduler\SymfonySchedulerDependencyProvider;

/**
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 */
class SymfonySchedulerBusinessFactory extends AbstractBusinessFactory
{
    public function createSchedulerRunner(): SchedulerRunnerInterface
    {
        return new SchedulerRunner(
            $this->createSchedulerBuilder(),
        );
    }

    public function createSchedulerBuilder(): SchedulerBuilderInterface
    {
        return new SchedulerBuilder(
            $this->getSchedulerHandlerProviderPlugins(),
        );
    }

    public function createScheduleReader(): ScheduleReaderInterface
    {
        return new ScheduleReader(
            $this->getSchedulerHandlerProviderPlugins(),
        );
    }

    /**
     * @return array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface>
     */
    public function getSchedulerHandlerProviderPlugins(): array
    {
        return $this->getProvidedDependency(SymfonySchedulerDependencyProvider::PLUGINS_SCHEDULER_HANDLER_PROVIDER);
    }
}
