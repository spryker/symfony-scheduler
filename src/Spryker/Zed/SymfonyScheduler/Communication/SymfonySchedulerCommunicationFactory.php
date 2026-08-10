<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication;

use Spryker\Client\SymfonyScheduler\SymfonySchedulerClientInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\SymfonyScheduler\Communication\Form\ToggleSchedulerJobForm;
use Spryker\Zed\SymfonyScheduler\Communication\Table\SchedulerJobTable;
use Spryker\Zed\SymfonyScheduler\SymfonySchedulerDependencyProvider;
use Symfony\Component\Form\FormInterface;

/**
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 */
class SymfonySchedulerCommunicationFactory extends AbstractCommunicationFactory
{
    public function createSchedulerJobTable(): SchedulerJobTable
    {
        return new SchedulerJobTable(
            $this->getSymfonySchedulerClient(),
            $this->getConfig(),
        );
    }

    public function createToggleSchedulerJobForm(): FormInterface
    {
        return $this->getFormFactory()->create(ToggleSchedulerJobForm::class);
    }

    public function getSymfonySchedulerClient(): SymfonySchedulerClientInterface
    {
        return $this->getProvidedDependency(SymfonySchedulerDependencyProvider::CLIENT_SYMFONY_SCHEDULER);
    }
}
