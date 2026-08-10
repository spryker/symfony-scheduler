<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Writer;

interface JobStateWriterInterface
{
    /**
     * @return bool True when the marker was persisted; false when Redis was unreachable.
     */
    public function disableJob(string $name): bool;

    /**
     * @return bool True when the marker was removed; false when Redis was unreachable.
     */
    public function enableJob(string $name): bool;
}
