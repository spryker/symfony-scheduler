<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Mapper;

use Generated\Shared\Transfer\SchedulerJobStatusTransfer;

interface JobStatusMapperInterface
{
    public function mapJobStatusTransferToJson(SchedulerJobStatusTransfer $schedulerJobStatusTransfer): string;

    public function mapJsonToJobStatusTransfer(string $json): SchedulerJobStatusTransfer;
}
