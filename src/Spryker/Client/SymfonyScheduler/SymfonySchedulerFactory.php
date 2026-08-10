<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler;

use Psr\Container\ContainerInterface;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\Lock\LockClientInterface;
use Spryker\Client\Redis\RedisClientInterface;
use Spryker\Client\SymfonyScheduler\Builder\ConfigBasedCronJobsBuilder;
use Spryker\Client\SymfonyScheduler\Builder\CronJobsBuilderInterface;
use Spryker\Client\SymfonyScheduler\Builder\ScheduleContainerBuilder;
use Spryker\Client\SymfonyScheduler\Builder\ScheduleContainerBuilderInterface;
use Spryker\Client\SymfonyScheduler\Builder\TransportConfigBuilder;
use Spryker\Client\SymfonyScheduler\Builder\TransportConfigBuilderInterface;
use Spryker\Client\SymfonyScheduler\Guard\SchedulerTransportConsumeGuard;
use Spryker\Client\SymfonyScheduler\Guard\SchedulerTransportConsumeGuardInterface;
use Spryker\Client\SymfonyScheduler\Mapper\JobStatusMapper;
use Spryker\Client\SymfonyScheduler\Mapper\JobStatusMapperInterface;
use Spryker\Client\SymfonyScheduler\Message\CommandMessage;
use Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface;
use Spryker\Client\SymfonyScheduler\MessageHandler\CommandHandler;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReader;
use Spryker\Client\SymfonyScheduler\Reader\JobRunRequestReaderInterface;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReader;
use Spryker\Client\SymfonyScheduler\Reader\JobStateReaderInterface;
use Spryker\Client\SymfonyScheduler\Reader\JobStatusReader;
use Spryker\Client\SymfonyScheduler\Reader\JobStatusReaderInterface;
use Spryker\Client\SymfonyScheduler\Reader\ScheduleReader;
use Spryker\Client\SymfonyScheduler\Reader\ScheduleReaderInterface;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapper;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\Transport\RunRequestAwareSchedulerTransportFactory;
use Spryker\Client\SymfonyScheduler\Writer\JobRunRequestWriter;
use Spryker\Client\SymfonyScheduler\Writer\JobRunRequestWriterInterface;
use Spryker\Client\SymfonyScheduler\Writer\JobStateWriter;
use Spryker\Client\SymfonyScheduler\Writer\JobStateWriterInterface;
use Spryker\Client\SymfonyScheduler\Writer\JobStatusWriter;
use Spryker\Client\SymfonyScheduler\Writer\JobStatusWriterInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Scheduler\Messenger\SchedulerTransportFactory;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 */
class SymfonySchedulerFactory extends AbstractFactory
{
    public function createSymfonySchedulerRedisWrapper(): SymfonySchedulerRedisWrapperInterface
    {
        return new SymfonySchedulerRedisWrapper(
            $this->getRedisClient(),
            $this->getConfig(),
        );
    }

    public function createJobStatusReader(): JobStatusReaderInterface
    {
        return new JobStatusReader(
            $this->createSymfonySchedulerRedisWrapper(),
            $this->getConfig(),
            $this->createJobStatusMapper(),
        );
    }

    public function createJobStatusWriter(): JobStatusWriterInterface
    {
        return new JobStatusWriter(
            $this->createSymfonySchedulerRedisWrapper(),
            $this->getConfig(),
            $this->createJobStatusMapper(),
        );
    }

    public function createJobStateReader(): JobStateReaderInterface
    {
        return new JobStateReader(
            $this->createSymfonySchedulerRedisWrapper(),
            $this->getConfig(),
        );
    }

    public function createJobStateWriter(): JobStateWriterInterface
    {
        return new JobStateWriter(
            $this->createSymfonySchedulerRedisWrapper(),
            $this->getConfig(),
        );
    }

    public function createJobRunRequestReader(): JobRunRequestReaderInterface
    {
        return new JobRunRequestReader(
            $this->createSymfonySchedulerRedisWrapper(),
            $this->getConfig(),
        );
    }

    public function createJobRunRequestWriter(): JobRunRequestWriterInterface
    {
        return new JobRunRequestWriter(
            $this->createSymfonySchedulerRedisWrapper(),
            $this->getConfig(),
        );
    }

    public function createJobStatusMapper(): JobStatusMapperInterface
    {
        return new JobStatusMapper();
    }

    public function createScheduleReader(): ScheduleReaderInterface
    {
        return new ScheduleReader(
            $this->getSchedulerHandlerProviderPlugins(),
            $this->getConfig(),
        );
    }

    public function createSchedulerTransportConsumeGuard(): SchedulerTransportConsumeGuardInterface
    {
        return new SchedulerTransportConsumeGuard($this->createJobStateReader());
    }

    public function createCommandHandler(): CommandHandler
    {
        return new CommandHandler($this->createJobStatusWriter());
    }

    public function createCommandMessage(): CommandMessageInterface
    {
        return new CommandMessage();
    }

    public function createSchedulerCronJobsBuilder(): CronJobsBuilderInterface
    {
        return new ConfigBasedCronJobsBuilder(
            $this->getConfig(),
            $this->createCommandMessage(),
            $this->getLockClient(),
            $this->createJobRunRequestReader(),
        );
    }

    public function createTransportConfigBuilder(): TransportConfigBuilderInterface
    {
        return new TransportConfigBuilder($this->getSchedulerHandlerProviderPlugins());
    }

    public function createScheduleContainerBuilder(): ScheduleContainerBuilderInterface
    {
        return new ScheduleContainerBuilder($this->getSchedulerHandlerProviderPlugins());
    }

    /**
     * @return \Symfony\Component\Messenger\Transport\TransportFactoryInterface<\Symfony\Component\Messenger\Transport\TransportInterface>
     */
    public function createSchedulerTransportFactory(): TransportFactoryInterface
    {
        $scheduleContainer = $this->createScheduleContainerBuilder()->build();

        // Wrap the framework transport factory so each scheduler transport becomes run-request aware.
        return new RunRequestAwareSchedulerTransportFactory(
            $this->createBaseSchedulerTransportFactory($scheduleContainer),
            $scheduleContainer,
            $this->createJobRunRequestReader(),
        );
    }

    /**
     * @return \Symfony\Component\Messenger\Transport\TransportFactoryInterface<\Symfony\Component\Messenger\Transport\TransportInterface>
     */
    public function createBaseSchedulerTransportFactory(ContainerInterface $scheduleContainer): TransportFactoryInterface
    {
        return new SchedulerTransportFactory($scheduleContainer);
    }

    public function getRedisClient(): RedisClientInterface
    {
        return $this->getProvidedDependency(SymfonySchedulerDependencyProvider::CLIENT_REDIS);
    }

    public function getLockClient(): LockClientInterface
    {
        return $this->getProvidedDependency(SymfonySchedulerDependencyProvider::CLIENT_LOCK);
    }

    /**
     * @return array<\Spryker\Shared\SymfonySchedulerExtension\Dependency\Plugin\SchedulerHandlerProviderPluginInterface>
     */
    public function getSchedulerHandlerProviderPlugins(): array
    {
        return $this->getProvidedDependency(SymfonySchedulerDependencyProvider::PLUGINS_SCHEDULER_HANDLER_PROVIDER);
    }
}
