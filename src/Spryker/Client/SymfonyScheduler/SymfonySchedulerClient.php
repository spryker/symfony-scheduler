<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler;

use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Spryker\Client\SymfonyScheduler\SymfonySchedulerFactory getFactory()
 */
class SymfonySchedulerClient extends AbstractClient implements SymfonySchedulerClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<string, \Generated\Shared\Transfer\SchedulerJobStatusTransfer>
     */
    public function getJobStatuses(): array
    {
        return $this->getFactory()->createJobStatusReader()->getJobStatuses();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function findJobStatusByName(string $name): ?SchedulerJobStatusTransfer
    {
        return $this->getFactory()->createJobStatusReader()->findJobStatusByName($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function saveJobStatus(SchedulerJobStatusTransfer $schedulerJobStatusTransfer): void
    {
        $this->getFactory()->createJobStatusWriter()->saveJobStatus($schedulerJobStatusTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function isJobDisabled(string $name): bool
    {
        return $this->getFactory()->createJobStateReader()->isJobDisabled($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<string>
     */
    public function getDisabledJobNames(): array
    {
        return $this->getFactory()->createJobStateReader()->getDisabledJobNames();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function disableJob(string $name): bool
    {
        return $this->getFactory()->createJobStateWriter()->disableJob($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function enableJob(string $name): bool
    {
        return $this->getFactory()->createJobStateWriter()->enableJob($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function requestRun(string $name): bool
    {
        return $this->getFactory()->createJobRunRequestWriter()->requestRun($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function hasRunRequest(string $name): bool
    {
        return $this->getFactory()->createJobRunRequestReader()->hasRunRequest($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function consumeRunRequest(string $name): bool
    {
        return $this->getFactory()->createJobRunRequestReader()->consumeRunRequest($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<array<string, mixed>>
     */
    public function getScheduledTasks(): array
    {
        return $this->getFactory()->createScheduleReader()->getScheduledTasks();
    }
}
