<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Redis;

interface SymfonySchedulerRedisWrapperInterface
{
    public function set(string $key, string $value, ?int $ttl = null): bool;

    /**
     * @return array<string>
     */
    public function getKeys(string $pattern): array;

    /**
     * @param array<string> $keys
     *
     * @return array<string, string|null>
     */
    public function getMulti(array $keys): array;

    /**
     * @param array<string> $keys
     */
    public function delete(array $keys): int;
}
