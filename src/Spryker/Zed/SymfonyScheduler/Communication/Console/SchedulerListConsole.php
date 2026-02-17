<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig getConfig()
 */
class SchedulerListConsole extends Console
{
    /**
     * @var string
     */
    public const string COMMAND_NAME = 'scheduler:list';

    /**
     * @var string
     */
    public const string COMMAND_DESCRIPTION = 'Lists all configured scheduled tasks with their details.';

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(static::COMMAND_NAME)
            ->setDescription(static::COMMAND_DESCRIPTION)
            ->setHelp(
                'This command displays a list of all scheduled tasks including:' . PHP_EOL .
                '  - Task name' . PHP_EOL .
                '  - Command that will be executed' . PHP_EOL .
                '  - Schedule expression (cron format or interval)' . PHP_EOL .
                '  - Trigger type (cron, interval, etc.)' . PHP_EOL .
                '  - Message class used for the task',
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tasks = $this->getFacade()->getScheduledTasks();

        if (!$tasks) {
            $output->writeln('<comment>No scheduled tasks configured.</comment>');

            return static::CODE_SUCCESS;
        }

        $output->writeln(sprintf(
            '<info>Found %d scheduled task%s:</info>',
            count($tasks),
            count($tasks) !== 1 ? 's' : '',
        ));
        $output->writeln('');

        $this->renderTasksTable($output, $tasks);

        return static::CODE_SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array<array<string, mixed>> $tasks
     *
     * @return void
     */
    protected function renderTasksTable(OutputInterface $output, array $tasks): void
    {
        $table = new Table($output);
        $table->setHeaders([
            'Name',
            'Command',
            'Schedule',
            'Trigger Type',
            'Message Class',
        ]);

        foreach ($tasks as $task) {
            $table->addRow([
                $task['name'] ?? 'N/A',
                $task['command'] ?? 'N/A',
                $task['schedule'] ?? 'N/A',
                $task['trigger_type'] ?? 'N/A',
                $task['message_class'] ?? 'N/A',
            ]);
        }

        $table->render();
    }
}
