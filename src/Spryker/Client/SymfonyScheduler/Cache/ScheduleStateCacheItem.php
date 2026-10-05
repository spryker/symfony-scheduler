<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Cache;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

/**
 * The item handed to the callback of {@see \Spryker\Client\SymfonyScheduler\Cache\ScheduleStateCache::get()}.
 *
 * It exists so the expiry the caller asks for can be captured and turned into the TTL the Redis
 * wrapper takes; the scheduler only ever calls expiresAfter() on it.
 */
class ScheduleStateCacheItem implements CacheItemInterface
{
    protected mixed $value = null;

    protected ?int $ttlSeconds = null;

    public function __construct(protected string $key, protected bool $isHit = false)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->ttlSeconds = $expiration === null
            ? null
            : max(0, $expiration->getTimestamp() - (new DateTimeImmutable())->getTimestamp());

        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if ($time instanceof DateInterval) {
            $now = new DateTimeImmutable();

            $time = $now->add($time)->getTimestamp() - $now->getTimestamp();
        }

        $this->ttlSeconds = $time;

        return $this;
    }

    public function findTtlSeconds(): ?int
    {
        return $this->ttlSeconds;
    }
}
