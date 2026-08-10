<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Transport;

use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Scheduler\Schedule;

/**
 * Decorates the scheduler transport so an on-demand run request is noticed between cron boundaries.
 *
 * The underlying MessageGenerator caches a "wait until" timestamp and skips trigger evaluation until the
 * next scheduled time is reached. Flipping the schedule's restart flag forces the generator to rebuild its
 * trigger heap on the very next get(), which re-invokes {@see \Spryker\Client\SymfonyScheduler\Trigger\RunRequestAwareCronTrigger}
 * and lets it fire (and consume) the pending request immediately.
 */
class RunRequestAwareSchedulerTransport implements TransportInterface
{
    public function __construct(
        protected TransportInterface $innerTransport,
        protected Schedule $schedule,
        protected JobRunRequestReaderInterface $jobRunRequestReader,
        protected string $jobName
    ) {
    }

    /**
     * @return iterable<\Symfony\Component\Messenger\Envelope>
     */
    public function get(): iterable
    {
        // Read-only probe: only the trigger consumes the marker, so a present request survives until the
        // generator actually rebuilds and fires it. Cheap enough to run every poll (one Redis read per job).
        if ($this->jobRunRequestReader->hasRunRequest($this->jobName)) {
            $this->schedule->setRestart(true);
        }

        return $this->innerTransport->get();
    }

    public function ack(Envelope $envelope): void
    {
        $this->innerTransport->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $this->innerTransport->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->innerTransport->send($envelope);
    }
}
