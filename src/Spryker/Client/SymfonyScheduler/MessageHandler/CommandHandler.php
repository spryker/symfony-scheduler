<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\MessageHandler;

use DateTimeImmutable;
use DateTimeInterface;
use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use Monolog\Logger;
use Spryker\Client\SymfonyScheduler\Message\CommandMessageInterface;
use Spryker\Client\SymfonyScheduler\Writer\JobStatusWriterInterface;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConfig;
use Symfony\Component\Process\Process;

class CommandHandler
{
    use LoggerTrait;

    /**
     * @var array<int>
     */
    protected const array HANDLED_SIGNALS = [SIGTERM, SIGINT];

    protected const int PROCESS_TIMEOUT_SECONDS = 300;

    protected const int STOP_TIMEOUT_SECONDS = 10;

    protected const int POLL_INTERVAL_MICROSECONDS = 100000;

    protected const string LOG_MESSAGE = 'Scheduler job executed.';

    protected const string LOG_KEY_NAME = 'name';

    protected const string LOG_KEY_COMMAND = 'command';

    protected const string LOG_KEY_EXIT_CODE = 'exitCode';

    protected const string LOG_KEY_STARTED_AT = 'startedAt';

    protected const string LOG_KEY_FINISHED_AT = 'finishedAt';

    protected const string LOG_KEY_PEAK_MEMORY_BYTES = 'peakMemoryBytes';

    protected const string LOG_KEY_OUTPUT = 'output';

    /**
     * The approximate peak memory is derived from the child process resident set size (RSS),
     * exposed by the Linux kernel in kilobytes via /proc/<pid>/status.
     */
    protected const string PROC_STATUS_PATH_PATTERN = '/proc/%d/status';

    protected const string PROC_STATUS_RSS_PATTERN = '/^VmRSS:\s+(\d+)\s+kB/m';

    protected const int BYTES_PER_KILOBYTE = 1024;

    /**
     * @var array<string>
     */
    protected array $output = [];

    public function __construct(protected JobStatusWriterInterface $jobStatusWriter)
    {
    }

    public function __invoke(CommandMessageInterface $commandMessage): ?string
    {
        $startedAt = new DateTimeImmutable();
        $schedulerJobStatusTransfer = $this->createRunningJobStatusTransfer($commandMessage, $startedAt);
        $this->jobStatusWriter->saveJobStatus($schedulerJobStatusTransfer);

        $process = Process::fromShellCommandline($commandMessage->getCommand());
        $process->setTimeout(static::PROCESS_TIMEOUT_SECONDS);

        $previousHandlers = $this->installSignalHandlers($process);
        $peakMemoryBytes = 0;

        try {
            $process->start(function ($type, $buffer): void {
                $this->output[] = $buffer;
            });

            $pid = $process->getPid();

            while ($process->isRunning()) {
                $process->checkTimeout();
                $peakMemoryBytes = max($peakMemoryBytes, $this->readProcessMemoryUsage($pid));
                usleep(static::POLL_INTERVAL_MICROSECONDS);
            }
        } finally {
            $this->restoreSignalHandlers($previousHandlers);
        }

        $isSuccessful = $process->isSuccessful();
        $finishedAt = new DateTimeImmutable();
        $result = $this->prepareResult($isSuccessful);

        $this->updateFinishedJobStatus($schedulerJobStatusTransfer, $isSuccessful, $result, $finishedAt);
        $this->jobStatusWriter->saveJobStatus($schedulerJobStatusTransfer);

        $this->logExecution($commandMessage, $process, $startedAt, $finishedAt, $peakMemoryBytes);

        $this->output = [];

        return $commandMessage->getCommand() . ' ' . $result;
    }

    protected function createRunningJobStatusTransfer(
        CommandMessageInterface $commandMessage,
        DateTimeImmutable $startedAt
    ): SchedulerJobStatusTransfer {
        $now = $startedAt->format(DateTimeInterface::ATOM);

        return (new SchedulerJobStatusTransfer())
            ->setName($commandMessage->getName())
            ->setCommand($commandMessage->getCommand())
            ->setWorkerName($this->getWorkerName())
            ->setStatus(SymfonySchedulerConfig::STATUS_RUNNING)
            ->setStartedAt($now)
            ->setUpdatedAt($now);
    }

    protected function updateFinishedJobStatus(
        SchedulerJobStatusTransfer $schedulerJobStatusTransfer,
        bool $isSuccessful,
        ?string $result,
        DateTimeImmutable $finishedAt
    ): void {
        $now = $finishedAt->format(DateTimeInterface::ATOM);

        $schedulerJobStatusTransfer
            ->setStatus($isSuccessful ? SymfonySchedulerConfig::STATUS_SUCCESS : SymfonySchedulerConfig::STATUS_ERROR)
            ->setFinishedAt($now)
            ->setUpdatedAt($now)
            ->setErrorMessage($isSuccessful ? null : $result);
    }

    protected function logExecution(
        CommandMessageInterface $commandMessage,
        Process $process,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $finishedAt,
        int $peakMemoryBytes
    ): void {
        $logLevel = $process->isSuccessful() ? Logger::INFO : Logger::ERROR;

        $this->getLogger()->log($logLevel, static::LOG_MESSAGE, [
            static::LOG_KEY_NAME => $commandMessage->getName(),
            static::LOG_KEY_COMMAND => $commandMessage->getCommand(),
            static::LOG_KEY_EXIT_CODE => $process->getExitCode(),
            static::LOG_KEY_STARTED_AT => $startedAt->format(DateTimeInterface::ATOM),
            static::LOG_KEY_FINISHED_AT => $finishedAt->format(DateTimeInterface::ATOM),
            static::LOG_KEY_PEAK_MEMORY_BYTES => $peakMemoryBytes,
            static::LOG_KEY_OUTPUT => implode(PHP_EOL, $this->output),
        ]);
    }

    /**
     * Reads the approximate peak resident set size (RSS) of the child process from the Linux
     * /proc filesystem. Returns 0 when the PID is unknown or /proc is unavailable (e.g. non-Linux),
     * so the measurement degrades gracefully rather than breaking execution.
     */
    protected function readProcessMemoryUsage(?int $pid): int
    {
        if ($pid === null) {
            return 0;
        }

        $statusFilePath = sprintf(static::PROC_STATUS_PATH_PATTERN, $pid);
        if (!is_readable($statusFilePath)) {
            return 0;
        }

        $processStatus = (string)file_get_contents($statusFilePath);
        if (preg_match(static::PROC_STATUS_RSS_PATTERN, $processStatus, $matches) !== 1) {
            return 0;
        }

        return (int)$matches[1] * static::BYTES_PER_KILOBYTE;
    }

    protected function getWorkerName(): string
    {
        return sprintf('%s:%d', gethostname() ?: 'unknown', getmypid() ?: 0);
    }

    /**
     * @return array<int, (callable(int): mixed)|int|string>
     */
    protected function installSignalHandlers(Process $process): array
    {
        if (!function_exists('pcntl_async_signals') || !function_exists('pcntl_signal')) {
            return [];
        }

        pcntl_async_signals(true);

        $previousHandlers = [];
        foreach (static::HANDLED_SIGNALS as $signal) {
            $previousHandlers[$signal] = pcntl_signal_get_handler($signal);
        }

        foreach (static::HANDLED_SIGNALS as $signal) {
            $previousHandler = $previousHandlers[$signal];
            pcntl_signal($signal, function (int $receivedSignal) use ($process, $previousHandler): void {
                if ($process->isRunning()) {
                    $process->stop(static::STOP_TIMEOUT_SECONDS, SIGTERM);
                }

                if (is_callable($previousHandler)) {
                    // A restored signal handler receives the signal number; the pcntl stub types it as a 0-arg callable.
                    // @phpstan-ignore-next-line
                    $previousHandler($receivedSignal);
                }
            });
        }

        return $previousHandlers;
    }

    /**
     * @param array<int, (callable(int): mixed)|int|string> $previousHandlers
     */
    protected function restoreSignalHandlers(array $previousHandlers): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        foreach ($previousHandlers as $signal => $handler) {
            pcntl_signal($signal, $handler);
        }
    }

    protected function prepareResult(bool $isSuccessful): ?string
    {
        if ($isSuccessful) {
            return null;
        }

        return implode(PHP_EOL, $this->output);
    }
}
