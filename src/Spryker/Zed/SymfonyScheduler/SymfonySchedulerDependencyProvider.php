<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class SymfonySchedulerDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string PLUGINS_SCHEDULER_HANDLER_PROVIDER = 'PLUGINS_SCHEDULER_HANDLER_PROVIDER';

    public const string FACADE_LOCK = 'FACADE_LOCK';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addSchedulerHandlerProviderPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addSchedulerHandlerProviderPlugins($container);
        $container = $this->addLockFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addSchedulerHandlerProviderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_SCHEDULER_HANDLER_PROVIDER, function () {
            return $this->getSchedulerHandlerProviderPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface>
     */
    protected function getSchedulerHandlerProviderPlugins(): array
    {
        return [];
    }

    protected function addLockFacade(Container $container): Container
    {
        $container->set(static::FACADE_LOCK, function (Container $container) {
            return $container->getLocator()->lock()->facade();
        });

        return $container;
    }
}
