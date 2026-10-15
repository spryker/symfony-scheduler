<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

interface CronJobConfigReaderInterface
{
    public function isLockDisabled(string $name): bool;

    /**
     * Tells whether the job runs inside the polling loop instead of being handed to the execution queue.
     */
    public function isSyncExecutionEnabled(string $name): bool;
}
