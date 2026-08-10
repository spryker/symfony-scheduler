<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Table;

use DateTimeImmutable;
use Exception;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerClientInterface;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\SymfonyScheduler\Communication\Form\ToggleSchedulerJobForm;
use Spryker\Zed\SymfonyScheduler\SymfonySchedulerConfig;

class SchedulerJobTable extends AbstractTable
{
    protected const string TABLE_IDENTIFIER = 'scheduler-job-table';

    /**
     * @uses \Spryker\Zed\SymfonyScheduler\Communication\Controller\IndexController::tableDataAction()
     */
    protected const string TABLE_URL = 'table-data';

    /**
     * @uses \Spryker\Zed\SymfonyScheduler\Communication\Controller\IndexController::viewAction()
     */
    protected const string URL_VIEW = '/symfony-scheduler/index/view';

    /**
     * @uses \Spryker\Zed\SymfonyScheduler\Communication\Controller\IndexController::enableAction()
     */
    protected const string URL_ENABLE = '/symfony-scheduler/index/enable';

    /**
     * @uses \Spryker\Zed\SymfonyScheduler\Communication\Controller\IndexController::disableAction()
     */
    protected const string URL_DISABLE = '/symfony-scheduler/index/disable';

    /**
     * @uses \Spryker\Zed\SymfonyScheduler\Communication\Controller\IndexController::runAction()
     */
    protected const string URL_RUN = '/symfony-scheduler/index/run';

    protected const string PARAM_NAME = 'name';

    protected const string TITLE_VIEW = 'View';

    protected const string TITLE_ENABLE = 'Enable';

    protected const string TITLE_DISABLE = 'Disable';

    protected const string TITLE_RUN = 'Run now';

    protected const string COL_NAME = 'name';

    protected const string COL_COMMAND = 'command';

    protected const string COL_SCHEDULE = 'schedule';

    protected const string COL_PRIORITY = 'priority';

    protected const string COL_STATUS = 'status';

    protected const string COL_STARTED_AT = 'started_at';

    protected const string COL_FINISHED_AT = 'finished_at';

    protected const string COL_DURATION = 'duration';

    protected const string COL_ACTIONS = 'actions';

    protected const string CRON_JOB_PRIORITY_KEY = 'priority';

    protected const int DEFAULT_PRIORITY = 0;

    protected const string LABEL_CLASS_DEFAULT = 'label-default';

    protected const string ICON_DEFAULT = 'schedule';

    /**
     * @var array<string, string>
     */
    protected const array STATUS_LABEL_CLASSES = [
        SymfonySchedulerConfig::STATUS_RUNNING => 'label-info',
        SymfonySchedulerConfig::STATUS_SUCCESS => 'label-primary',
        SymfonySchedulerConfig::STATUS_ERROR => 'label-danger',
        SymfonySchedulerConfig::STATUS_WAITING => 'label-warning',
        SymfonySchedulerConfig::STATUS_DISABLED => 'label-default',
    ];

    /**
     * @var array<string, string>
     */
    protected const array STATUS_ICONS = [
        SymfonySchedulerConfig::STATUS_RUNNING => 'sync',
        SymfonySchedulerConfig::STATUS_SUCCESS => 'check_circle',
        SymfonySchedulerConfig::STATUS_ERROR => 'error',
        SymfonySchedulerConfig::STATUS_WAITING => 'schedule',
        SymfonySchedulerConfig::STATUS_DISABLED => 'block',
    ];

    /**
     * @var array<string, string>
     */
    protected const array STATUS_LABEL_STYLES = [
        SymfonySchedulerConfig::STATUS_DISABLED => 'background-color:#000000;color:#ffffff;',
    ];

    public function __construct(
        protected SymfonySchedulerClientInterface $symfonySchedulerClient,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setHeader([
            static::COL_NAME => 'Name',
            static::COL_COMMAND => 'Command',
            static::COL_SCHEDULE => 'Schedule',
            static::COL_PRIORITY => 'Priority',
            static::COL_STATUS => 'Status',
            static::COL_STARTED_AT => 'Started',
            static::COL_FINISHED_AT => 'Finished',
            static::COL_DURATION => 'Duration',
            static::COL_ACTIONS => 'Actions',
        ]);

        $config->setUrl(static::TABLE_URL);
        $config->setRawColumns([static::COL_STATUS, static::COL_ACTIONS]);
        $config->setSortable([
            static::COL_NAME,
            static::COL_PRIORITY,
            static::COL_STATUS,
            static::COL_STARTED_AT,
            static::COL_FINISHED_AT,
        ]);
        $config->setSearchable([
            static::COL_NAME,
            static::COL_COMMAND,
            static::COL_STATUS,
        ]);
        $config->setPaging(false);

        $this->setTableIdentifier(static::TABLE_IDENTIFIER);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array<array<string, mixed>>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $schedulerJobStatusTransfers = $this->symfonySchedulerClient->getJobStatuses();
        $disabledJobNames = $this->symfonySchedulerClient->getDisabledJobNames();

        $data = [];
        foreach ($this->symfonySchedulerClient->getScheduledTasks() as $task) {
            $name = (string)($task[static::COL_NAME] ?? '');
            $schedulerJobStatusTransfer = $schedulerJobStatusTransfers[$name] ?? null;
            $isDisabled = in_array($name, $disabledJobNames, true);
            $isRunning = $schedulerJobStatusTransfer?->getStatus() === SymfonySchedulerConfig::STATUS_RUNNING;

            // "Disabled" is a view-only status resolved from the disabled marker and takes display precedence;
            // the persisted per-job status record is intentionally left untouched.
            $status = $isDisabled ? SymfonySchedulerConfig::STATUS_DISABLED : $schedulerJobStatusTransfer?->getStatus();

            $data[] = [
                static::COL_NAME => $name,
                static::COL_COMMAND => (string)($task[static::COL_COMMAND] ?? ''),
                static::COL_SCHEDULE => (string)($task[static::COL_SCHEDULE] ?? ''),
                static::COL_PRIORITY => (int)($task[static::COL_PRIORITY] ?? static::DEFAULT_PRIORITY),
                static::COL_STATUS => $this->formatStatusLabel($status),
                static::COL_STARTED_AT => (string)($schedulerJobStatusTransfer?->getStartedAt() ?? ''),
                static::COL_FINISHED_AT => (string)($schedulerJobStatusTransfer?->getFinishedAt() ?? ''),
                static::COL_DURATION => $this->formatDuration(
                    $schedulerJobStatusTransfer?->getStartedAt(),
                    $schedulerJobStatusTransfer?->getFinishedAt(),
                ),
                static::COL_ACTIONS => $this->buildLinks($name, $isDisabled, $isRunning),
            ];
        }

        $this->setTotal(count($data));
        $this->setFiltered(count($data));

        return $data;
    }

    protected function buildLinks(string $name, bool $isDisabled, bool $isRunning): string
    {
        // Reuse the Back Office primary button styling (same teal as the layout menu button).
        $viewButton = $this->generateButton(
            Url::generate(static::URL_VIEW, [static::PARAM_NAME => $name]),
            static::TITLE_VIEW,
            [
                static::BUTTON_CLASS => 'btn-primary',
                static::BUTTON_ICON => 'fa-eye',
            ],
        );

        return implode(' ', [
            $viewButton,
            $this->buildRunButton($name, $isDisabled, $isRunning),
            $this->buildToggleButton($name, $isDisabled),
        ]);
    }

    protected function buildRunButton(string $name, bool $isDisabled, bool $isRunning): string
    {
        $options = [
            static::BUTTON_CLASS => 'btn-primary',
            static::BUTTON_ICON => 'fa-bolt',
        ];

        // Only actionable for an enabled job that is not already running; otherwise render it greyed-out and inert
        // (a second run while one is in progress would just queue another execution behind the current lock).
        if ($isDisabled || $isRunning) {
            $options['disabled'] = 'disabled';
        } else {
            $options['onclick'] = $this->buildConfirmParameter(static::TITLE_RUN, $name);
        }

        return $this->generateFormButton(
            Url::generate(static::URL_RUN, [static::PARAM_NAME => $name]),
            static::TITLE_RUN,
            ToggleSchedulerJobForm::class,
            $options,
        );
    }

    protected function buildToggleButton(string $name, bool $isDisabled): string
    {
        if ($isDisabled) {
            return $this->generateFormButton(
                Url::generate(static::URL_ENABLE, [static::PARAM_NAME => $name]),
                static::TITLE_ENABLE,
                ToggleSchedulerJobForm::class,
                [
                    static::BUTTON_CLASS => 'btn-create',
                    static::BUTTON_ICON => 'fa-play',
                    'onclick' => $this->buildConfirmParameter(static::TITLE_ENABLE, $name),
                ],
            );
        }

        return $this->generateFormButton(
            Url::generate(static::URL_DISABLE, [static::PARAM_NAME => $name]),
            static::TITLE_DISABLE,
            ToggleSchedulerJobForm::class,
            [
                static::BUTTON_CLASS => 'btn-danger',
                static::BUTTON_ICON => 'fa-pause',
                'onclick' => $this->buildConfirmParameter(static::TITLE_DISABLE, $name),
            ],
        );
    }

    protected function buildConfirmParameter(string $action, string $name): string
    {
        return sprintf('return confirm("%s scheduled job \'%s\'?")', $action, $name);
    }

    protected function formatStatusLabel(?string $status): string
    {
        $status = $status ?? SymfonySchedulerConfig::STATUS_WAITING;
        $labelClass = static::STATUS_LABEL_CLASSES[$status] ?? static::LABEL_CLASS_DEFAULT;
        $icon = static::STATUS_ICONS[$status] ?? static::ICON_DEFAULT;
        $styleAttribute = isset(static::STATUS_LABEL_STYLES[$status])
            ? sprintf(' style="%s"', static::STATUS_LABEL_STYLES[$status])
            : '';

        return sprintf(
            '<span class="label %s"%s><i class="material-symbols-outlined" style="font-size:1em;line-height:1;vertical-align:text-bottom;">%s</i> %s</span>',
            $labelClass,
            $styleAttribute,
            $icon,
            $status,
        );
    }

    protected function formatDuration(?string $startedAt, ?string $finishedAt): string
    {
        // Without a start time there is nothing to measure (e.g. a job that has never run yet).
        if ($startedAt === null || $startedAt === '') {
            return '';
        }

        try {
            $startedAtDateTime = new DateTimeImmutable($startedAt);
            // A running job has no finish time yet, so measure the elapsed time up to now instead.
            $endDateTime = ($finishedAt === null || $finishedAt === '')
                ? new DateTimeImmutable()
                : new DateTimeImmutable($finishedAt);
        } catch (Exception $exception) {
            // A malformed stored timestamp is a data issue, not an infra failure: show no duration instead of failing the row.
            return '';
        }

        $seconds = $endDateTime->getTimestamp() - $startedAtDateTime->getTimestamp();

        // Guard against clock skew / out-of-order timestamps yielding a negative duration.
        if ($seconds < 0) {
            return '';
        }

        return $this->humanizeSeconds($seconds);
    }

    protected function humanizeSeconds(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }

        $parts[] = $remainingSeconds . 's';

        return implode(' ', $parts);
    }
}
