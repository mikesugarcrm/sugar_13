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

namespace Sugarcrm\SugarcrmTests\UserUtils;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneDashboards;
use Sugarcrm\Sugarcrm\UserUtils\Managers\DashboardManager;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\\Commands\CloneDashboards
 */
class CloneDashboardsTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CloneDashboards|mixed
     */
    public $cloneDashboards;

    protected function setUp(): void
    {
        $this->cloneDashboards = $this->getMockBuilder(CloneDashboards::class)
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
        $manager = $this->getMockBuilder(DashboardManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['clone'])
            ->getMock();

        $this->cloneDashboards->expects($this->once())->method('execute');
        $this->cloneDashboards->execute();
        $this->cloneDashboards->expects($this->once())->method('getManager');
        $this->cloneDashboards->getManager();
        $manager->expects($this->once())->method('clone');
        $manager->clone();
    }
}
