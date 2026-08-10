<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Spryker\Client\SymfonyScheduler\SymfonySchedulerClientInterface;
use SprykerTest\Zed\SymfonyScheduler\SymfonySchedulerCommunicationTester;

/**
 * Exercises the enable/disable data path against the real scheduler Redis connection
 * via the real client facade, proving the round-trip end to end.
 *
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group JobStateRedisRoundTripTest
 * Add your own group annotations below this line
 */
class JobStateRedisRoundTripTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_JOB_NAME = 'phpunit-round-trip-job';

    protected SymfonySchedulerCommunicationTester $tester;

    protected function _after(): void
    {
        // Make sure the marker never leaks between runs regardless of assertion outcome.
        $this->createSymfonySchedulerClient()->enableJob(static::TEST_JOB_NAME);
    }

    public function testDisableThenEnableRoundTripThroughRealRedis(): void
    {
        // Arrange
        $symfonySchedulerClient = $this->createSymfonySchedulerClient();

        // Act
        $symfonySchedulerClient->disableJob(static::TEST_JOB_NAME);

        // Assert
        $this->assertTrue($symfonySchedulerClient->isJobDisabled(static::TEST_JOB_NAME));
        $this->assertContains(static::TEST_JOB_NAME, $symfonySchedulerClient->getDisabledJobNames());

        // Arrange
        // The marker is now present from the disable step above.

        // Act
        $symfonySchedulerClient->enableJob(static::TEST_JOB_NAME);

        // Assert
        $this->assertFalse($symfonySchedulerClient->isJobDisabled(static::TEST_JOB_NAME));
        $this->assertNotContains(static::TEST_JOB_NAME, $symfonySchedulerClient->getDisabledJobNames());
    }

    protected function createSymfonySchedulerClient(): SymfonySchedulerClientInterface
    {
        return $this->tester->getLocator()->symfonyScheduler()->client();
    }
}
