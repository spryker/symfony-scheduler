<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Client\SymfonyScheduler\Transport\Amqp\SchedulerJobTransportFactory;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group SchedulerJobTransportFactoryTest
 * Add your own group annotations below this line
 */
class SchedulerJobTransportFactoryTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_DSN = 'scheduler-amqp://scheduler-jobs';

    public function testSupportsItsOwnDsnScheme(): void
    {
        // Arrange
        $schedulerJobTransportFactory = new SchedulerJobTransportFactory(new SymfonySchedulerConfig());

        // Act
        $isSupported = $schedulerJobTransportFactory->supports(static::TEST_DSN, []);

        // Assert
        $this->assertTrue($isSupported);
    }

    /**
     * The framework picks the first factory whose supports() matches and registers the built-in AMQP
     * factory ahead of the plugin-provided ones, so claiming `amqp://` here would be dead code at best.
     *
     * @dataProvider foreignDsnDataProvider
     */
    public function testDoesNotClaimForeignDsnSchemes(string $dsn): void
    {
        // Arrange
        $schedulerJobTransportFactory = new SchedulerJobTransportFactory(new SymfonySchedulerConfig());

        // Act
        $isSupported = $schedulerJobTransportFactory->supports($dsn, []);

        // Assert
        $this->assertFalse($isSupported);
    }

    /**
     * @return array<string, array<string>>
     */
    public function foreignDsnDataProvider(): array
    {
        return [
            'plain amqp' => ['amqp://guest:guest@broker:5672/%2f'],
            'amqp over tls' => ['amqps://guest:guest@broker:5671/%2f'],
            'scheduler transport' => ['schedule://check-oms-conditions'],
        ];
    }

    /**
     * The worker hands its own runtime options to every transport closure, and the AMQP connection
     * rejects any option outside its own whitelist. This transport has to ignore them rather than fail,
     * which is the whole reason it builds its connection from module configuration instead.
     */
    public function testIgnoresTheWorkerRuntimeOptions(): void
    {
        // Arrange
        $schedulerJobTransportFactory = new SchedulerJobTransportFactory($this->createStubConfig());
        $workerRuntimeOptions = [
            'time-limit' => 900,
            'sleep' => 1000000,
            'output' => new NullOutput(),
        ];

        // Act
        $transport = $schedulerJobTransportFactory->createTransport(
            static::TEST_DSN,
            $workerRuntimeOptions,
            new Serializer(),
        );

        // Assert
        $this->assertInstanceOf(AmqpTransport::class, $transport);
    }

    public function testBuildsTheQueueNamedInTheDsnHost(): void
    {
        // Arrange
        $stubConfig = $this->createStubConfig();
        $schedulerJobTransportFactory = new SchedulerJobTransportFactory($stubConfig);

        // Act
        $schedulerJobTransportFactory->createTransport('scheduler-amqp://scheduler-jobs-high', [], new Serializer());

        // Assert
        $this->assertSame('scheduler-jobs-high', $stubConfig->requestedQueueName);
    }

    /**
     * An anonymous subclass rather than a mock: the unit under test is the factory, so the connection
     * details must not depend on whatever the project happens to have configured.
     */
    protected function createStubConfig(): SymfonySchedulerConfig
    {
        return new class extends SymfonySchedulerConfig {
            public ?string $requestedQueueName = null;

            public function getAmqpConnectionDsn(): string
            {
                // Not connected to during construction; the transport opens the connection lazily.
                return 'amqp://guest:guest@broker:5672/%2f';
            }

            /**
             * @return array<string, mixed>
             */
            public function getJobQueueTransportOptions(string $queueName): array
            {
                $this->requestedQueueName = $queueName;

                return [
                    'auto_setup' => true,
                    'exchange' => ['name' => $queueName, 'type' => 'direct'],
                    'queues' => [$queueName => []],
                ];
            }
        };
    }
}
