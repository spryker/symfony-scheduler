<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Controller;

use Spryker\Zed\Kernel\Communication\BusinessFactoryResolverAwareTrait;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerBusinessFactory getBusinessFactory()
 */
class IndexController extends AbstractController
{
    use BusinessFactoryResolverAwareTrait;

    /**
     * @return array<string, mixed>
     */
    public function indexAction(): array
    {
        return [
            'tasks' => $this->getBusinessFactory()->createScheduleReader()->getScheduledTasks(),
        ];
    }
}
