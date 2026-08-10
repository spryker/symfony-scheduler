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

class JobStateWriter implements JobStateWriterInterface
{
    use LoggerTrait;

    protected const string DISABLED_MARKER_VALUE = '1';

    protected const string LOG_MESSAGE_DISABLE_FAILED = 'SymfonyScheduler: failed to persist job-disabled marker to Redis; the job was not disabled.';

    protected const string LOG_MESSAGE_ENABLE_FAILED = 'SymfonyScheduler: failed to remove job-disabled marker from Redis; the job was not enabled.';

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    public function disableJob(string $name): bool
    {
        // No TTL: the disabled marker must persist until the job is explicitly re-enabled.
        try {
            $this->redisWrapper->set(
                $this->symfonySchedulerConfig->getJobDisabledStorageKeyPrefix() . $name,
                static::DISABLED_MARKER_VALUE,
            );
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_DISABLE_FAILED, ['exception' => $exception]);

            return false;
        }

        return true;
    }

    public function enableJob(string $name): bool
    {
        try {
            $this->redisWrapper->delete([
                $this->symfonySchedulerConfig->getJobDisabledStorageKeyPrefix() . $name,
            ]);
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_ENABLE_FAILED, ['exception' => $exception]);

            return false;
        }

        return true;
    }
}
