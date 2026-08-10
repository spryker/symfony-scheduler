<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Trigger;

use DateTimeImmutable;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Symfony\Component\Scheduler\Trigger\AbstractDecoratedTrigger;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

/**
 * Wraps a cron trigger so an on-demand "run request" stored in Redis wins over the cron schedule:
 * when a request exists the job fires immediately (at $run), otherwise the inner cron trigger decides.
 */
class RunRequestAwareCronTrigger extends AbstractDecoratedTrigger
{
    public function __construct(
        TriggerInterface $innerTrigger,
        protected JobRunRequestReaderInterface $jobRunRequestReader,
        protected string $jobName
    ) {
        parent::__construct($innerTrigger);
    }

    public function getNextRunDate(DateTimeImmutable $run): ?DateTimeImmutable
    {
        if ($this->jobRunRequestReader->consumeRunRequest($this->jobName)) {
            return $run;
        }

        return $this->inner()->getNextRunDate($run);
    }

    public function __toString(): string
    {
        return (string)$this->inner();
    }
}
