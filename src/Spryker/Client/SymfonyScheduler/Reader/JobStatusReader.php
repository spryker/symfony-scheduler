<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

use Exception;
use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use Spryker\Client\SymfonyScheduler\Mapper\JobStatusMapperInterface;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Shared\Log\LoggerTrait;

class JobStatusReader implements JobStatusReaderInterface
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_STATUS_LOOKUP_FAILED = 'SymfonyScheduler: failed to read job statuses from Redis; returning no statuses.';

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected SymfonySchedulerConfig $symfonySchedulerConfig,
        protected JobStatusMapperInterface $jobStatusMapper
    ) {
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\SchedulerJobStatusTransfer>
     */
    public function getJobStatuses(): array
    {
        // Fail-open: a Redis outage must not turn the Back Office scheduler overview into an error page.
        try {
            $keys = $this->redisWrapper->getKeys(
                $this->symfonySchedulerConfig->getJobStatusStorageKeyPrefix() . '*',
            );

            return $this->mapKeysToJobStatusTransfers($keys);
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_STATUS_LOOKUP_FAILED, ['exception' => $exception]);

            return [];
        }
    }

    public function findJobStatusByName(string $name): ?SchedulerJobStatusTransfer
    {
        return $this->getJobStatuses()[$name] ?? null;
    }

    /**
     * @param array<string> $keys
     *
     * @return array<string, \Generated\Shared\Transfer\SchedulerJobStatusTransfer>
     */
    protected function mapKeysToJobStatusTransfers(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $schedulerJobStatusTransfers = [];
        foreach ($this->redisWrapper->getMulti($keys) as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $schedulerJobStatusTransfer = $this->jobStatusMapper->mapJsonToJobStatusTransfer((string)$value);
            $schedulerJobStatusTransfers[(string)$schedulerJobStatusTransfer->getName()] = $schedulerJobStatusTransfer;
        }

        return $schedulerJobStatusTransfers;
    }
}
