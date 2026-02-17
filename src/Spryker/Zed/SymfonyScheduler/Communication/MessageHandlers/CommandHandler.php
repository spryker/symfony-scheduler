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
     * @var array<string>
     */
    protected array $output = [];

    public function __invoke(CommandMessageInterface $commandMessage): ?string
    {
        $process = Process::fromShellCommandline($commandMessage->getCommand());
        $process->setTimeout(300);

        $process->run(function ($type, $buffer) {
            $this->output[] = $buffer;

            return true;
        });

        $result = $this->prepareResult($process->isSuccessful());
        $this->output = [];

        return $commandMessage->getCommand() . ' ' . $result;
    }

    protected function prepareResult(bool $isSuccessful): ?string
    {
        if ($isSuccessful) {
            return null;
        }

        return implode(PHP_EOL, $this->output);
    }
}
