<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

use Generated\Shared\Transfer\SchedulerJobStatusTransfer;

interface JobStatusReaderInterface
{
    /**
     * @return array<string, \Generated\Shared\Transfer\SchedulerJobStatusTransfer>
     */
    public function getJobStatuses(): array;

    public function findJobStatusByName(string $name): ?SchedulerJobStatusTransfer;
}
