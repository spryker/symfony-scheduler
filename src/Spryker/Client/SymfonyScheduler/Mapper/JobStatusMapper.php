<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Mapper;

use Generated\Shared\Transfer\SchedulerJobStatusTransfer;

class JobStatusMapper implements JobStatusMapperInterface
{
    public function mapJobStatusTransferToJson(SchedulerJobStatusTransfer $schedulerJobStatusTransfer): string
    {
        return (string)json_encode($schedulerJobStatusTransfer->toArray(true, true));
    }

    public function mapJsonToJobStatusTransfer(string $json): SchedulerJobStatusTransfer
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            $data = [];
        }

        return (new SchedulerJobStatusTransfer())->fromArray($data, true);
    }
}
