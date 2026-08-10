<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Writer;

use Exception;
use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use Spryker\Client\SymfonyScheduler\Mapper\JobStatusMapperInterface;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Shared\Log\LoggerTrait;

class JobStatusWriter implements JobStatusWriterInterface
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_STATUS_SAVE_FAILED = 'SymfonyScheduler: failed to persist job status to Redis; status tracking skipped for this run.';

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected SymfonySchedulerConfig $symfonySchedulerConfig,
        protected JobStatusMapperInterface $jobStatusMapper
    ) {
    }

    public function saveJobStatus(SchedulerJobStatusTransfer $schedulerJobStatusTransfer): void
    {
        $name = $schedulerJobStatusTransfer->getName();

        if ($name === null) {
            return;
        }

        // Status tracking is best-effort: a Redis failure must never break the scheduled job itself.
        try {
            $this->redisWrapper->set(
                $this->symfonySchedulerConfig->getJobStatusStorageKeyPrefix() . $name,
                $this->jobStatusMapper->mapJobStatusTransferToJson($schedulerJobStatusTransfer),
                $this->symfonySchedulerConfig->getJobStatusTtl(),
            );
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_STATUS_SAVE_FAILED, ['exception' => $exception]);
        }
    }
}
