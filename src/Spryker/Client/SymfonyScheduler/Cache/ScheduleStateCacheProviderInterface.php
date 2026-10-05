<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Cache;

use Symfony\Contracts\Cache\CacheInterface;

interface ScheduleStateCacheProviderInterface
{
    public function findCache(): ?CacheInterface;
}
