<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Writer;

interface JobRunRequestWriterInterface
{
    /**
     * @return bool True when the run request was persisted; false when Redis was unreachable.
     */
    public function requestRun(string $name): bool;
}
