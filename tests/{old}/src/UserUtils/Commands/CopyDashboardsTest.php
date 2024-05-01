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
use Sugarcrm\Sugarcrm\UserUtils\Commands\CopyDashboards;
use Sugarcrm\Sugarcrm\UserUtils\Managers\DashboardManager;
use SugarTestHelper;

class CopyDashboardsTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CopyDashboards|mixed
     */
    public $copyDashboards;

    protected function setUp(): void
    {
        $this->copyDashboards = $this->getMockBuilder(CopyDashboards::class)
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
            ->onlyMethods(['copy'])
            ->getMock();

        $this->copyDashboards->expects($this->once())->method('execute');
        $this->copyDashboards->execute();
        $this->copyDashboards->expects($this->once())->method('getManager');
        $this->copyDashboards->getManager();
        $manager->expects($this->once())->method('copy');
        $manager->copy();
    }
}
