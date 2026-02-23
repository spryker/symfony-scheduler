<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Business\Messages;

class TestScheduledMessage
{
    /**
     * @var string
     */
    public string $data = 'test data';

    public function getCommand(): string
    {
        return 'test:command';
    }
}
