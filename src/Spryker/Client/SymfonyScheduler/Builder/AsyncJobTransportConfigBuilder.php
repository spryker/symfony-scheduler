<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Builder;

use Generated\Shared\Transfer\MessengerTransportConfigTransfer;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;

class AsyncJobTransportConfigBuilder implements TransportConfigBuilderInterface
{
    protected const int DEFAULT_PRIORITY = 0;

    public function __construct(protected SymfonySchedulerConfig $symfonySchedulerConfig)
    {
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    public function buildTransportConfig(): array
    {
        $transportName = $this->symfonySchedulerConfig->getJobTransportName();

        return [
            $transportName => (new MessengerTransportConfigTransfer())
                ->setName($transportName)
                ->setDsn($this->symfonySchedulerConfig->getJobTransportDsn())
                ->setPriority(static::DEFAULT_PRIORITY),
        ];
    }
}
