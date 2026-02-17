<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Business;

use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerBusinessFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 */
class SymfonySchedulerFacade extends AbstractFacade implements SymfonySchedulerFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return void
     */
    public function runScheduledTasks(): void
    {
        $this->getFactory()
            ->createSchedulerRunner()
            ->runScheduledTasks();
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
        return $this->getFactory()
            ->createScheduleReader()
            ->getScheduledTasks();
    }
}
