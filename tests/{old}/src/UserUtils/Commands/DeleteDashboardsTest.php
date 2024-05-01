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
use Sugarcrm\Sugarcrm\UserUtils\Commands\DeleteDashboards;
use Sugarcrm\Sugarcrm\UserUtils\Managers\DashboardManager;
use SugarTestHelper;

class DeleteDashboardsTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\DeleteDashboards|mixed
     */
    public $deleteDashboards;

    protected function setUp(): void
    {
        $this->deleteDashboards = $this->getMockBuilder(DeleteDashboards::class)
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
            ->onlyMethods(['delete'])
            ->getMock();

        $this->deleteDashboards->expects($this->once())->method('execute');
        $this->deleteDashboards->execute();
        $this->deleteDashboards->expects($this->once())->method('getManager');
        $this->deleteDashboards->getManager();
        $manager->expects($this->once())->method('delete');
        $manager->delete();
    }
}
