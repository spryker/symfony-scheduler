<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Message;

use RuntimeException;

class CommandMessage implements CommandMessageInterface
{
    protected ?string $command;

    protected ?string $name = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }
}
