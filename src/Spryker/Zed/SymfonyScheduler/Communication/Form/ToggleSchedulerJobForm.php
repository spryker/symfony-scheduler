<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Form;

use Spryker\Zed\Kernel\Communication\Form\AbstractType;

/**
 * This class is intentionally empty: it exists solely to provide CSRF protection for the
 * enable/disable toggle buttons. All button/form markup is defined by the table renderer.
 *
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 */
class ToggleSchedulerJobForm extends AbstractType
{
}
