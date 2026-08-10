<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 */
class SymfonySchedulerDependencyProvider extends AbstractDependencyProvider
{
    public const string CLIENT_REDIS = 'CLIENT_REDIS';

    public const string CLIENT_LOCK = 'CLIENT_LOCK';

    public const string PLUGINS_SCHEDULER_HANDLER_PROVIDER = 'PLUGINS_SCHEDULER_HANDLER_PROVIDER';

    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = parent::provideServiceLayerDependencies($container);
        $container = $this->addRedisClient($container);
        $container = $this->addLockClient($container);
        $container = $this->addSchedulerHandlerProviderPlugins($container);

        return $container;
    }

    protected function addRedisClient(Container $container): Container
    {
        $container->set(static::CLIENT_REDIS, function (Container $container) {
            return $container->getLocator()->redis()->client();
        });

        return $container;
    }

    protected function addLockClient(Container $container): Container
    {
        $container->set(static::CLIENT_LOCK, function (Container $container) {
            return $container->getLocator()->lock()->client();
        });

        return $container;
    }

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
}
