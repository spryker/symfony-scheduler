<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Cache;

use Spryker\Client\SymfonyScheduler\Redis\SymfonySchedulerRedisWrapperInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

/**
 * Stores the schedule checkpoint through the module's own Redis wrapper.
 *
 * Deliberately not one of the framework's Redis cache adapters: those open a connection of their own
 * from a DSN and write values straight, so the checkpoint would bypass the connection handling and
 * the compression the Spryker Redis client applies, and land in the same database in a different
 * encoding from every other key this module writes.
 */
class ScheduleStateCache implements CacheInterface
{
    protected const string SERIALIZED_FALSE = 'b:0;';

    public function __construct(
        protected SymfonySchedulerRedisWrapperInterface $redisWrapper,
        protected string $keyPrefix
    ) {
    }

    /**
     * @param (callable(\Psr\Cache\CacheItemInterface, bool): mixed) $callback Receives $save by reference.
     * @param array<string, mixed>|null $metadata
     */
    public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null): mixed
    {
        $prefixedKey = $this->buildKey($key);

        // The scheduler forces a write by passing an infinite beta, which means "do not trust what is
        // stored, recompute it". Any other value is a plain read-through.
        if ($beta === null || !is_infinite($beta)) {
            $storedValue = $this->redisWrapper->getMulti([$prefixedKey])[$prefixedKey] ?? null;

            if ($storedValue !== null && $storedValue !== '') {
                $unserializedValue = $this->findUnserializedValue($storedValue);

                if ($unserializedValue !== null) {
                    return $unserializedValue->value;
                }
            }
        }

        $cacheItem = new ScheduleStateCacheItem($prefixedKey);
        $save = true;
        $value = $callback($cacheItem, $save);

        // The contract passes $save by reference so a callback can decline persistence. PHPStan has no
        // syntax for a by-reference parameter in a callable type, so it reads the flag as never reassigned.
        /** @phpstan-ignore if.alwaysTrue */
        if ($save) {
            $this->redisWrapper->set($prefixedKey, serialize($value), $cacheItem->findTtlSeconds());
        }

        return $value;
    }

    public function delete(string $key): bool
    {
        return $this->redisWrapper->delete([$this->buildKey($key)]) > 0;
    }

    protected function buildKey(string $key): string
    {
        return $this->keyPrefix . $key;
    }

    /**
     * A value that cannot be read back is reported as absent rather than handed on, because the caller
     * would otherwise receive false where it expects its own shape. Both ways a failure can present
     * are covered: an error handler that turns the warning into an exception, and a plain false return.
     */
    protected function findUnserializedValue(string $storedValue): ?ScheduleStateCacheValue
    {
        try {
            $unserializedValue = unserialize($storedValue);
        } catch (Throwable $exception) {
            return null;
        }

        if ($unserializedValue === false && $storedValue !== static::SERIALIZED_FALSE) {
            return null;
        }

        return new ScheduleStateCacheValue($unserializedValue);
    }
}
