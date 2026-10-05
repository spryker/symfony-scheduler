<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Sender;

use Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage;

interface SchedulerJobSenderInterface
{
    public function send(ScheduledCommandMessage $scheduledCommandMessage): void;
}
