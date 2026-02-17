<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Business;

interface SymfonySchedulerFacadeInterface
{
    /**
     * Specification:
     * - Runs scheduled tasks that are due for execution.
     * - Uses the configured scheduler transport to manage task scheduling.
     *
     * @api
     *
     * @return void
     */
    public function runScheduledTasks(): void;

    /**
     * Specification:
     * - Returns a list of all configured scheduled tasks.
     * - Includes task name, message class, trigger type, schedule expression, and command.
     * - Compiles information from all registered SchedulerHandlerProviderPluginInterface implementations.
     *
     * @api
     *
     * @return array<array<string, mixed>>
     */
    public function getScheduledTasks(): array;
}
