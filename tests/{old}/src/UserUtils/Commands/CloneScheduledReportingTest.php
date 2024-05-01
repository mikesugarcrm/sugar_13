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

namespace Sugarcrm\SugarcrmTests\UserUtils\Commands;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneScheduledReporting;
use Sugarcrm\Sugarcrm\UserUtils\Managers\ScheduledReportingManager;
use SugarTestHelper;

class CloneScheduledReportingTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CloneScheduledReporting|mixed
     */
    public $cloneScheduledReporting;

    protected function setUp(): void
    {
        $this->cloneScheduledReporting = $this->getMockBuilder(CloneScheduledReporting::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getManager', 'execute'])
            ->getMock();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::testExecute
     */
    public function testExecute()
    {
        $manager = $this->getMockBuilder(ScheduledReportingManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['cloneScheduledReporting'])
            ->getMock();

        $this->cloneScheduledReporting->expects($this->once())->method('execute');
        $this->cloneScheduledReporting->execute();
        $this->cloneScheduledReporting->expects($this->once())->method('getManager');
        $this->cloneScheduledReporting->getManager();
        $manager->expects($this->once())->method('cloneScheduledReporting');
        $manager->cloneScheduledReporting();
    }
}
