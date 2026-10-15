<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Message;

/**
 * Carries a cron job that is handed to the execution queue instead of being run by the polling loop.
 */
class CommandMessage extends AbstractCommandMessage
{
}
