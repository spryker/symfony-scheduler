<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Message;

class ScheduledCommandMessage
{
    public const string ORIGIN_CRON = 'cron';

    public function __construct(
        protected string $name,
        protected string $command,
        protected string $origin,
        protected string $triggeredAt
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getTriggeredAt(): string
    {
        return $this->triggeredAt;
    }
}
