<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Builder;

use Generated\Shared\Transfer\MessengerTransportConfigTransfer;
use Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerTransportInfoProviderPluginInterface;

class TransportConfigBuilder implements TransportConfigBuilderInterface
{
    protected const string DSN_PATTERN = 'schedule://%s';

    protected const int DEFAULT_PRIORITY = 0;

    /**
     * @param array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface> $schedulerHandlerProviderPlugins
     */
    public function __construct(protected array $schedulerHandlerProviderPlugins)
    {
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    public function buildTransportConfig(): array
    {
        $transportConfig = [];

        foreach ($this->schedulerHandlerProviderPlugins as $plugin) {
            if ($plugin instanceof SchedulerTransportInfoProviderPluginInterface) {
                $transportConfig += $this->buildTransportConfigFromInfo($plugin);

                continue;
            }

            $transportConfig += $this->buildTransportConfigFromScheduleNames(array_keys($plugin->getSchedules()));
        }

        return $transportConfig;
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    protected function buildTransportConfigFromInfo(SchedulerTransportInfoProviderPluginInterface $plugin): array
    {
        $transportConfig = [];
        foreach ($plugin->getTransportInfo() as $transportName => $messengerTransportConfigTransfer) {
            $transportConfig[$transportName] = $messengerTransportConfigTransfer
                ->setDsn(sprintf(static::DSN_PATTERN, $transportName));
        }

        return $transportConfig;
    }

    /**
     * @param array<string> $scheduleNames
     *
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    protected function buildTransportConfigFromScheduleNames(array $scheduleNames): array
    {
        $transportConfig = [];
        foreach ($scheduleNames as $scheduleName) {
            $transportConfig[$scheduleName] = (new MessengerTransportConfigTransfer())
                ->setName($scheduleName)
                ->setDsn(sprintf(static::DSN_PATTERN, $scheduleName))
                ->setPriority(static::DEFAULT_PRIORITY);
        }

        return $transportConfig;
    }
}
