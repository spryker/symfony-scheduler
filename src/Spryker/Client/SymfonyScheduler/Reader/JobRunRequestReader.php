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

class JobRunRequestReader implements JobRunRequestReaderInterface
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_RUN_REQUEST_LOOKUP_FAILED = 'SymfonyScheduler: failed to read job run-request marker from Redis; treating job as not requested.';

    protected const string LOG_MESSAGE_RUN_REQUEST_CONSUME_FAILED = 'SymfonyScheduler: failed to consume job run-request marker in Redis; skipping the forced run.';

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    public function hasRunRequest(string $name): bool
    {
        $key = $this->buildKey($name);

        // Fail-open in the negative direction: a Redis outage must never spam a job with forced runs.
        try {
            return ($this->redisWrapper->getMulti([$key])[$key] ?? null) !== null;
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_RUN_REQUEST_LOOKUP_FAILED, ['exception' => $exception]);

            return false;
        }
    }

    public function consumeRunRequest(string $name): bool
    {
        // DELETE returns the number of removed keys, giving an atomic check-and-consume in a single round trip:
        // only the caller that actually removed the marker sees `true`, so a request triggers exactly one run.
        try {
            return $this->redisWrapper->delete([$this->buildKey($name)]) > 0;
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_RUN_REQUEST_CONSUME_FAILED, ['exception' => $exception]);

            return false;
        }
    }

    protected function buildKey(string $name): string
    {
        return $this->symfonySchedulerConfig->getJobRunRequestStorageKeyPrefix() . $name;
    }
}
