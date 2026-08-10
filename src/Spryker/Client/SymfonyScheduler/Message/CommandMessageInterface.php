<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Message;

interface CommandMessageInterface
{
    public function getCommand(): string;

    /**
     * @return $this
     */
    public function setCommand(string $command);

    public function getName(): ?string;

    /**
     * @return $this
     */
    public function setName(string $name);
}
