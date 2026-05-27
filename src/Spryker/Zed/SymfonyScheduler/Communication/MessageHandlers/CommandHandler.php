<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\MessageHandlers;

use Spryker\Zed\SymfonyScheduler\Communication\Messages\CommandMessageInterface;
use Symfony\Component\Process\Process;

class CommandHandler
{
    /**
     * @var array<int>
     */
    protected const array HANDLED_SIGNALS = [SIGTERM, SIGINT];

    protected const int PROCESS_TIMEOUT_SECONDS = 300;

    protected const int STOP_TIMEOUT_SECONDS = 10;

    protected const int POLL_INTERVAL_MICROSECONDS = 100000;

    /**
     * @var array<string>
     */
    protected array $output = [];

    public function __invoke(CommandMessageInterface $commandMessage): ?string
    {
        $process = Process::fromShellCommandline($commandMessage->getCommand());
        $process->setTimeout(static::PROCESS_TIMEOUT_SECONDS);

        $previousHandlers = $this->installSignalHandlers($process);

        try {
            $process->start(function ($type, $buffer): void {
                $this->output[] = $buffer;
            });

            while ($process->isRunning()) {
                $process->checkTimeout();
                usleep(static::POLL_INTERVAL_MICROSECONDS);
            }
        } finally {
            $this->restoreSignalHandlers($previousHandlers);
        }

        $result = $this->prepareResult($process->isSuccessful());
        $this->output = [];

        return $commandMessage->getCommand() . ' ' . $result;
    }

    /**
     * @return array<int, callable|int|string>
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
                    $previousHandler($receivedSignal);
                }
            });
        }

        return $previousHandlers;
    }

    /**
     * @param array<int, callable|int|string> $previousHandlers
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
