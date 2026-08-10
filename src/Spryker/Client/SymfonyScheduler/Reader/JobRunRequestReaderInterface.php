<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Reader;

interface JobRunRequestReaderInterface
{
    /**
     * Returns whether an on-demand run request currently exists for the given job. Read-only, never consumes it.
     */
    public function hasRunRequest(string $name): bool;

    /**
     * Atomically consumes (deletes) the on-demand run request for the given job.
     * Returns true if a request existed and was consumed by this call, false otherwise.
     */
    public function consumeRunRequest(string $name): bool;
}
