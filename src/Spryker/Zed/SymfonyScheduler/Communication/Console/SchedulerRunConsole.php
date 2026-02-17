<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 */
class SchedulerRunConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'scheduler:run';

    /**
     * @var string
     */
    public const COMMAND_DESCRIPTION = 'Runs scheduled tasks that are due for execution.';

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(static::COMMAND_NAME)
            ->setDescription(static::COMMAND_DESCRIPTION);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Running scheduled tasks...</info>');

        $this->getFacade()->runScheduledTasks();

        $output->writeln('<info>Scheduled tasks execution completed.</info>');

        return static::CODE_SUCCESS;
    }
}
