<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication;

use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Lock\Business\LockFacadeInterface;
use Spryker\Zed\SymfonyScheduler\Communication\Builder\ConfigBasedCronJobsBuilder;
use Spryker\Zed\SymfonyScheduler\Communication\Builder\CronJobsBuilderInterface;
use Spryker\Zed\SymfonyScheduler\Communication\Builder\ScheduleContainerBuilder;
use Spryker\Zed\SymfonyScheduler\Communication\Builder\ScheduleContainerBuilderInterface;
use Spryker\Zed\SymfonyScheduler\Communication\MessageHandlers\CommandHandler;
use Spryker\Zed\SymfonyScheduler\Communication\Messages\CommandMessage;
use Spryker\Zed\SymfonyScheduler\Communication\Messages\CommandMessageInterface;
use Spryker\Zed\SymfonyScheduler\SymfonySchedulerDependencyProvider;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Scheduler\Messenger\SchedulerTransportFactory;

/**
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 */
class SymfonySchedulerCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \Symfony\Component\Messenger\Transport\TransportFactoryInterface
     */
    public function createSchedulerTransportFactory(): TransportFactoryInterface
    {
        return new SchedulerTransportFactory($this->createScheduleContainerBuilder()->build());
    }

    public function createScheduleContainerBuilder(): ScheduleContainerBuilderInterface
    {
        return new ScheduleContainerBuilder($this->getSchedulerHandlerProviderPlugins());
    }

    /**
     * @return array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface>
     */
    public function getSchedulerHandlerProviderPlugins(): array
    {
        return $this->getProvidedDependency(SymfonySchedulerDependencyProvider::PLUGINS_SCHEDULER_HANDLER_PROVIDER);
    }

    public function createCommandHandler(): CommandHandler
    {
        return new CommandHandler();
    }

    public function createCommandMessage(): CommandMessageInterface
    {
        return new CommandMessage();
    }

    public function createSchedulerCronJobsBuilder(): CronJobsBuilderInterface
    {
        return new ConfigBasedCronJobsBuilder($this->getConfig(), $this->createCommandMessage(), $this->getLockFacade());
    }

    public function getLockFacade(): LockFacadeInterface
    {
        return $this->getProvidedDependency(SymfonySchedulerDependencyProvider::FACADE_LOCK);
    }
}
