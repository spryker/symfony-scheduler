<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Message;

use RuntimeException;

/**
 * Holds the state every scheduled command message carries. Concrete subclasses exist to be told apart
 * by the handler map, which is keyed by message class, so each one selects its own execution tier.
 */
abstract class AbstractCommandMessage implements CommandMessageInterface
{
    protected ?string $command = null;

    protected ?string $name = null;

    public function getCommand(): string
    {
        if (!$this->command) {
            throw new RuntimeException(sprintf('The scheduler command is not set in %s message', static::class));
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
