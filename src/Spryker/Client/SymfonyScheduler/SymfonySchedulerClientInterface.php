<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler;

use Generated\Shared\Transfer\SchedulerJobStatusTransfer;

interface SymfonySchedulerClientInterface
{
    /**
     * Specification:
     * - Returns the latest recorded status of every scheduled job, keyed by job name.
     *
     * @api
     *
     * @return array<string, \Generated\Shared\Transfer\SchedulerJobStatusTransfer>
     */
    public function getJobStatuses(): array;

    /**
     * Specification:
     * - Returns the latest recorded status of the given job, or null when none has been recorded.
     *
     * @api
     */
    public function findJobStatusByName(string $name): ?SchedulerJobStatusTransfer;

    /**
     * Specification:
     * - Persists the given job status. Best-effort: a storage failure is logged and swallowed.
     *
     * @api
     */
    public function saveJobStatus(SchedulerJobStatusTransfer $schedulerJobStatusTransfer): void;

    /**
     * Specification:
     * - Returns whether the given job is currently disabled.
     * - Fail-open: an unreachable store is treated as "enabled" so an outage never pauses every job.
     *
     * @api
     */
    public function isJobDisabled(string $name): bool;

    /**
     * Specification:
     * - Returns the names of all currently disabled jobs.
     *
     * @api
     *
     * @return array<string>
     */
    public function getDisabledJobNames(): array;

    /**
     * Specification:
     * - Disables the given job by persisting a marker (no expiration) until it is explicitly re-enabled.
     * - Fail-open: a storage failure is logged and swallowed.
     * - Returns true when the marker was persisted; false when the store was unreachable.
     *
     * @api
     */
    public function disableJob(string $name): bool;

    /**
     * Specification:
     * - Enables the given job by removing its disabled marker.
     * - Fail-open: a storage failure is logged and swallowed.
     * - Returns true when the marker was removed; false when the store was unreachable.
     *
     * @api
     */
    public function enableJob(string $name): bool;

    /**
     * Specification:
     * - Requests an immediate, one-off run of the given job ahead of its cron schedule.
     * - Fail-open: a storage failure is logged and swallowed. A TTL guards an unconsumed request.
     * - Returns true when the run request was persisted; false when the store was unreachable.
     *
     * @api
     */
    public function requestRun(string $name): bool;

    /**
     * Specification:
     * - Returns whether an on-demand run request currently exists for the given job. Read-only, never consumes it.
     *
     * @api
     */
    public function hasRunRequest(string $name): bool;

    /**
     * Specification:
     * - Atomically consumes (deletes) the on-demand run request for the given job.
     * - Returns true only for the caller that removed the marker, so a request triggers exactly one run.
     *
     * @api
     */
    public function consumeRunRequest(string $name): bool;

    /**
     * Specification:
     * - Returns a list of all configured scheduled tasks.
     * - Includes task name, message class, trigger type, schedule expression, command, and priority.
     * - Compiles information from all registered SchedulerHandlerProviderPluginInterface implementations.
     *
     * @api
     *
     * @return array<array<string, mixed>>
     */
    public function getScheduledTasks(): array;
}
