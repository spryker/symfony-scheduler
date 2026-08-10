<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Transport;

use Psr\Container\ContainerInterface;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Scheduler\Schedule;

/**
 * Wraps the framework scheduler transport factory so every produced transport gets run-request awareness.
 *
 * @implements \Symfony\Component\Messenger\Transport\TransportFactoryInterface<\Symfony\Component\Messenger\Transport\TransportInterface>
 */
class RunRequestAwareSchedulerTransportFactory implements TransportFactoryInterface
{
    /**
     * @param \Symfony\Component\Messenger\Transport\TransportFactoryInterface<\Symfony\Component\Messenger\Transport\TransportInterface> $innerTransportFactory
     */
    public function __construct(
        protected TransportFactoryInterface $innerTransportFactory,
        protected ContainerInterface $scheduleProviders,
        protected JobRunRequestReaderInterface $jobRunRequestReader
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $innerTransport = $this->innerTransportFactory->createTransport($dsn, $options, $serializer);

        $scheduleName = parse_url($dsn, \PHP_URL_HOST);
        if (!is_string($scheduleName)) {
            return $innerTransport;
        }

        $schedule = $this->scheduleProviders->get($scheduleName);
        if (!$schedule instanceof Schedule) {
            return $innerTransport;
        }

        return new RunRequestAwareSchedulerTransport(
            $innerTransport,
            $schedule,
            $this->jobRunRequestReader,
            $scheduleName,
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public function supports(string $dsn, array $options): bool
    {
        return $this->innerTransportFactory->supports($dsn, $options);
    }
}
