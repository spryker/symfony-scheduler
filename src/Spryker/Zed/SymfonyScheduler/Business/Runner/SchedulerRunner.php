<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Business\Runner;

use Spryker\Zed\SymfonyScheduler\Business\Builder\SchedulerBuilderInterface;
use Symfony\Component\Scheduler\Scheduler;

class SchedulerRunner implements SchedulerRunnerInterface
{
    protected Scheduler $scheduler;

    public function __construct(SchedulerBuilderInterface $schedulerBuilder)
    {
        $this->scheduler = $schedulerBuilder->build();
    }

    /**
     * @return void
     */
    public function runScheduledTasks(): void
    {
        $this->scheduler->run();
    }
}
