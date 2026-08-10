<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

use Exception;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Shared\Log\LoggerTrait;

class JobStateReader implements JobStateReaderInterface
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_DISABLED_LOOKUP_FAILED = 'SymfonyScheduler: failed to read job-disabled marker from Redis; treating job as enabled.';

    protected const string LOG_MESSAGE_DISABLED_LIST_FAILED = 'SymfonyScheduler: failed to read job-disabled markers from Redis; returning no disabled jobs.';

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    public function isJobDisabled(string $name): bool
    {
        $key = $this->symfonySchedulerConfig->getJobDisabledStorageKeyPrefix() . $name;

        // Fail-open: a Redis outage must never pause every job, so treat an unreachable store as "enabled".
        try {
            return ($this->redisWrapper->getMulti([$key])[$key] ?? null) !== null;
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_DISABLED_LOOKUP_FAILED, ['exception' => $exception]);

            return false;
        }
    }

    /**
     * @return array<string>
     */
    public function getDisabledJobNames(): array
    {
        $prefix = $this->symfonySchedulerConfig->getJobDisabledStorageKeyPrefix();

        try {
            $keys = $this->redisWrapper->getKeys($prefix . '*');
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_DISABLED_LIST_FAILED, ['exception' => $exception]);

            return [];
        }

        $disabledJobNames = [];
        foreach ($keys as $key) {
            $disabledJobNames[] = substr((string)$key, strlen($prefix));
        }

        return $disabledJobNames;
    }
}
