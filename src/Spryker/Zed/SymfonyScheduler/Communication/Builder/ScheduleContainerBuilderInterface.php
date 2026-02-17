<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Builder;

use Psr\Container\ContainerInterface;

interface ScheduleContainerBuilderInterface
{
    public function build(): ContainerInterface;
}
