<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Guard;

use Spryker\Client\SymfonyScheduler\Reader\JobStateReaderInterface;

class SchedulerTransportConsumeGuard implements SchedulerTransportConsumeGuardInterface
{
    public function __construct(protected JobStateReaderInterface $jobStateReader)
    {
    }

    public function canConsumeTransport(string $transportName): bool
    {
        // Each scheduler cron job is registered as a uniquely-named transport whose name equals the job name,
        // and a "disabled" marker only ever exists for a real job name, so a match unambiguously identifies a
        // paused scheduler job. Non-scheduler transports never carry a marker and are therefore always allowed.
        return !$this->jobStateReader->isJobDisabled($transportName);
    }
}
