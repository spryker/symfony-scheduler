<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Sender;

use Spryker\Client\SymfonyScheduler\Message\ScheduledCommandMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

class SchedulerJobSender implements SchedulerJobSenderInterface
{
    public function __construct(protected TransportInterface $transport)
    {
    }

    public function send(ScheduledCommandMessage $scheduledCommandMessage): void
    {
        $this->transport->send(new Envelope($scheduledCommandMessage));
    }
}
