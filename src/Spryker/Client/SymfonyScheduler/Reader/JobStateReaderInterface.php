<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

interface JobStateReaderInterface
{
    public function isJobDisabled(string $name): bool;

    /**
     * @return array<string>
     */
    public function getDisabledJobNames(): array;
}
