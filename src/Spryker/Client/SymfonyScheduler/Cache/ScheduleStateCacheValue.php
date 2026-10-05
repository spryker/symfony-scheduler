<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Cache;

/**
 * Carries a value read back from storage, so that "nothing was stored" stays distinguishable from
 * "null was stored" without resorting to a sentinel.
 */
class ScheduleStateCacheValue
{
    public function __construct(public readonly mixed $value)
    {
    }
}
