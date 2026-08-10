<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SymfonyScheduler\Communication;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\SchedulerJobStatusTransfer;
use Spryker\Client\SymfonyScheduler\Mapper\JobStatusMapper;
use Spryker\Shared\SymfonyScheduler\SymfonySchedulerConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SymfonyScheduler
 * @group Communication
 * @group JobStatusMapperTest
 * Add your own group annotations below this line
 */
class JobStatusMapperTest extends Unit
{
    public function testMapsJobStatusTransferToJsonAndBack(): void
    {
        // Arrange
        $jobStatusMapper = new JobStatusMapper();

        $schedulerJobStatusTransfer = (new SchedulerJobStatusTransfer())
            ->setName('import-job-run')
            ->setStatus(SymfonySchedulerConfig::STATUS_RUNNING)
            ->setWorkerName('worker-host:1234')
            ->setCommand('vendor/bin/console import:job:run')
            ->setStartedAt('2026-01-01T00:00:00+00:00');

        // Act
        $json = $jobStatusMapper->mapJobStatusTransferToJson($schedulerJobStatusTransfer);
        $resultTransfer = $jobStatusMapper->mapJsonToJobStatusTransfer($json);

        // Assert
        $this->assertJson($json);
        $this->assertSame('import-job-run', $resultTransfer->getName());
        $this->assertSame(SymfonySchedulerConfig::STATUS_RUNNING, $resultTransfer->getStatus());
        $this->assertSame('worker-host:1234', $resultTransfer->getWorkerName());
        $this->assertSame('vendor/bin/console import:job:run', $resultTransfer->getCommand());
        $this->assertSame('2026-01-01T00:00:00+00:00', $resultTransfer->getStartedAt());
    }

    public function testMapsInvalidJsonToEmptyTransfer(): void
    {
        // Arrange
        $jobStatusMapper = new JobStatusMapper();

        // Act
        $resultTransfer = $jobStatusMapper->mapJsonToJobStatusTransfer('not-a-json');

        // Assert
        $this->assertNull($resultTransfer->getName());
        $this->assertNull($resultTransfer->getStatus());
    }
}
