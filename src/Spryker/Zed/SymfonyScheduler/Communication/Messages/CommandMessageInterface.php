<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Messages;

interface CommandMessageInterface
{
    public function getCommand(): string;

    /**
     * @return $this
     */
    public function setCommand(string $command);
}
