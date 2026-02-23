<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Business;

use Codeception\Test\Unit;
use SprykerTest\Zed\SymfonyScheduler\SymfonySchedulerBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Business
 * @group Facade
 * @group RunScheduledTasksFacadeTest
 * Add your own group annotations below this line
 */
class RunScheduledTasksFacadeTest extends Unit
{
    /**
     * @var string
     */
    protected const string TEST_SCHEDULE_NAME = 'test_schedule';

    /**
     * @var string
     */
    protected const string TEST_CRON_EXPRESSION = '* * * * *';

    /**
     * @var \SprykerTest\Zed\SymfonyScheduler\SymfonySchedulerBusinessTester
     */
    protected SymfonySchedulerBusinessTester $tester;

    /**
     * @return void
     */
    public function testRunScheduledTasksExecutesSchedulerRun(): void
    {
        $this->markTestSkipped('This test will be refactored with the next Scheduler version, as the current implementation has side effects');
    }
}
