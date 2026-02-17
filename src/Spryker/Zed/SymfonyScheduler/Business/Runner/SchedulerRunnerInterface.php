<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Business\Runner;

interface SchedulerRunnerInterface
{
    /**
     * @return void
     */
    public function runScheduledTasks(): void;
}
