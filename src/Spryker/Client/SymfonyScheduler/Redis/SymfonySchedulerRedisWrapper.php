<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Redis;

use Exception;
use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Spryker\Client\Redis\RedisClientInterface;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerConfig;
use Spryker\Shared\Log\LoggerTrait;

class SymfonySchedulerRedisWrapper implements SymfonySchedulerRedisWrapperInterface
{
    use LoggerTrait;

    protected const string LOG_MESSAGE_CONNECTION_SETUP_FAILED = 'SymfonyScheduler: failed to set up the Redis connection; scheduler storage operations will fail-open until Redis is reachable again.';

    protected string $connectionKey;

    public function __construct(
        protected RedisClientInterface $redisClient,
        protected SymfonySchedulerConfig $symfonySchedulerConfig
    ) {
        $this->connectionKey = $this->symfonySchedulerConfig->getRedisConnectionKey();
        $this->setupConnection($this->symfonySchedulerConfig->getRedisConnectionConfiguration());
    }

    public function set(string $key, string $value, ?int $ttl = null): bool
    {
        if ($ttl === null) {
            return $this->redisClient->set($this->connectionKey, $key, $value);
        }

        return $this->redisClient->setex($this->connectionKey, $key, $ttl, $value);
    }

    /**
     * @return array<string>
     */
    public function getKeys(string $pattern): array
    {
        $cursor = 0;
        $keys = [];

        do {
            [$cursor, $foundKeys] = $this->redisClient->scan(
                $this->connectionKey,
                (int)$cursor,
                [
                    'COUNT' => $this->symfonySchedulerConfig->getRedisScanChunkSize(),
                    'MATCH' => $pattern,
                ],
            );
            $keys = array_merge($keys, $foundKeys);
        } while ((int)$cursor > 0);

        return array_values(array_unique($keys));
    }

    /**
     * @param array<string> $keys
     *
     * @return array<string, string|null>
     */
    public function getMulti(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        return array_combine($keys, $this->redisClient->mget($this->connectionKey, $keys)) ?: [];
    }

    /**
     * @param array<string> $keys
     */
    public function delete(array $keys): int
    {
        if ($keys === []) {
            return 0;
        }

        return $this->redisClient->del($this->connectionKey, $keys);
    }

    protected function setupConnection(RedisConfigurationTransfer $configurationTransfer): void
    {
        // Fail-open on construction: an unreachable Redis must not blow up the wrapper (and everything that
        // depends on it). If the connection cannot be set up, each storage method degrades gracefully instead.
        try {
            $this->redisClient->setupConnection($this->connectionKey, $configurationTransfer);
        } catch (Exception $exception) {
            $this->getLogger()->warning(static::LOG_MESSAGE_CONNECTION_SETUP_FAILED, ['exception' => $exception]);
        }
    }
}
