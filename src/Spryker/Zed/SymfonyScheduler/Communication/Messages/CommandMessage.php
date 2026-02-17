<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Messages;

use RuntimeException;

class CommandMessage implements CommandMessageInterface
{
    protected ?string $command;

    public function getCommand(): string
    {
        if (!$this->command) {
            throw new RuntimeException('Command is not set.');
        }

        return $this->command;
    }

    /**
     * @return $this
     */
    public function setCommand(string $command)
    {
        $this->command = $command;

        return $this;
    }
}
