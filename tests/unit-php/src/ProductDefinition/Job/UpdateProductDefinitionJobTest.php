<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\ProductDefinition\Job;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Sugarcrm\Sugarcrm\ProductDefinition\Config\Config;
use Sugarcrm\Sugarcrm\ProductDefinition\Job\UpdateProductDefinitionJob;
use SchedulersJob;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\ProductDefinition\Job\UpdateProductDefinitionJob
 */
class UpdateProductDefinitionJobTest extends TestCase
{
    /**
     * @var MockObject | Config
     */
    protected $config;

    /**
     * @var MockObject | SchedulersJob
     */
    protected $sugarJob;

    /**
     * @var UpdateProductDefinitionJob
     */
    protected $job;

    /**
     * @codeCoverageIgnore
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->sugarJob = $this->createMock(SchedulersJob::class);

        $this->job = new UpdateProductDefinitionJob();
        $this->job->setJob($this->sugarJob);
        $this->job->setProductDefinitionConfig($this->config);
    }

    /**
     * @covers ::run
     */
    public function testRunFail()
    {
        $this->config->expects($this->once())->method('updateProductDefinition')->willReturn(false);
        $this->sugarJob->expects($this->once())->method('failJob');
        $this->job->run('');
    }

    /**
     * @covers ::run
     */
    public function testRunSuccess()
    {
        $this->config->expects($this->once())->method('updateProductDefinition')->willReturn(true);
        $this->sugarJob->expects($this->once())->method('succeedJob');
        $this->job->run('');
    }
}
