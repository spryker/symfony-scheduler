<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Transport\Amqp;

use SensitiveParameter;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig as ClientSymfonySchedulerConfig;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\Connection;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Builds the AMQP transport that carries scheduled jobs from the trigger tier to the execution tier.
 *
 * @implements \Symfony\Component\Messenger\Transport\TransportFactoryInterface<\Symfony\Component\Messenger\Transport\TransportInterface>
 */
class SchedulerJobTransportFactory implements TransportFactoryInterface
{
    public function __construct(protected ClientSymfonySchedulerConfig $symfonySchedulerConfig)
    {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function supports(#[SensitiveParameter] string $dsn, array $options): bool
    {
        return str_starts_with($dsn, SymfonySchedulerConfig::JOB_TRANSPORT_DSN_SCHEME);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createTransport(
        #[SensitiveParameter] string $dsn,
        array $options,
        SerializerInterface $serializer
    ): TransportInterface {
        return $this->createSchedulerJobTransport((string)parse_url($dsn, PHP_URL_HOST));
    }

    public function createSchedulerJobTransport(string $queueName): TransportInterface
    {
        return new AmqpTransport(
            Connection::fromDsn(
                $this->symfonySchedulerConfig->getAmqpConnectionDsn(),
                $this->symfonySchedulerConfig->getJobQueueTransportOptions($queueName),
            ),
            $this->createSerializer(),
        );
    }

    protected function createSerializer(): SerializerInterface
    {
        return new Serializer();
    }
}
