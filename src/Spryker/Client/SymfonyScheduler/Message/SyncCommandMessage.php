<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SymfonyScheduler\Message;

/**
 * Carries a cron job that runs inside the polling loop itself, which blocks that loop until the job
 * finishes. Jobs opt in through the `sync` key of their cron job configuration.
 *
 * This is a sibling of {@link \Spryker\Client\SymfonyScheduler\Message\CommandMessage} rather than a
 * subclass on purpose. The handler map is keyed by message class and Symfony's HandlersLocator resolves
 * parent classes and interfaces as well, so a subclass would match the asynchronous entry too and the
 * job would be executed here AND enqueued for a second execution.
 */
class SyncCommandMessage extends AbstractCommandMessage
{
}
