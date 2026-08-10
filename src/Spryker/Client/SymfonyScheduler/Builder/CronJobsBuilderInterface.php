<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Builder;

interface CronJobsBuilderInterface
{
    /**
     * @return array<string, \Symfony\Component\Scheduler\Schedule>
     */
    public function buildSchedule(): array;

    /**
     * @return array<string, \Generated\Shared\Transfer\MessengerTransportConfigTransfer>
     */
    public function buildTransportInfo(): array;
}
