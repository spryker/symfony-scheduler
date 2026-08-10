<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Writer;

use Exception;
use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Shared\Log\LoggerTrait;

class JobRunRequestWriter implements JobRunRequestWriterInterface
{
    use LoggerTrait;

    /**
     * Marker value stored under the run-request key; only its presence matters.
     */
    protected const string RUN_REQUEST_MARKER_VALUE = '1';

    protected const string LOG_MESSAGE_RUN_REQUEST_SAVE_FAILED = 'SymfonyScheduler: failed to persist job run-request marker to Redis; the on-demand run was not scheduled.';

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    public function requestRun(string $name): bool
    {
        // A TTL guards against a request lingering forever if it is never consumed (worker down, job disabled).
        try {
            $this->redisWrapper->set(
                $this->symfonySchedulerConfig->getJobRunRequestStorageKeyPrefix() . $name,
                static::RUN_REQUEST_MARKER_VALUE,
                $this->symfonySchedulerConfig->getJobRunRequestTtl(),
            );
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_RUN_REQUEST_SAVE_FAILED, ['exception' => $exception]);

            return false;
        }

        return true;
    }
}
