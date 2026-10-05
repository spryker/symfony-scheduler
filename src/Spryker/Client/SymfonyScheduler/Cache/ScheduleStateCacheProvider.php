<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Cache;

use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Shared\Log\LoggerTrait;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

class ScheduleStateCacheProvider implements ScheduleStateCacheProviderInterface
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_UNAVAILABLE = 'SymfonyScheduler: schedule state cache is unavailable; the schedule will restart from the current time on every consumer restart and occurrences due during a restart will be skipped.';

    protected const string LOG_KEY_EXCEPTION = 'exception';

    protected ?CacheInterface $cache = null;

    protected bool $isResolved = false;

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
    }

    public function findCache(): ?CacheInterface
    {
        if ($this->isResolved) {
            return $this->cache;
        }

        $this->isResolved = true;

        try {
            $this->cache = new ScheduleStateCache(
                $this->redisWrapper,
                $this->symfonySchedulerConfig->getScheduleStateStorageKeyPrefix(),
            );
        } catch (Throwable $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_UNAVAILABLE, [static::LOG_KEY_EXCEPTION => $exception]);
        }

        return $this->cache;
    }
}
