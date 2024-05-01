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
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerDashboardsPayload;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\CommandFactory
 */
class InvokerDashboardsPayloadTest extends TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::setDashboards
     */
    public function testSetAndGetDashboards()
    {
        $dashboardsPayload = new InvokerDashboardsPayload([]);
        $dashboardsPayload->setDashboards(['dashboard1']);
        $dashboards = $dashboardsPayload->getDashboards();
        $this->assertEquals(['dashboard1'], $dashboards);
    }

    /**
     * @covers ::setDashboards
     */
    public function testSetAndGetModules()
    {
        $dashboardsPayload = new InvokerDashboardsPayload([]);
        $dashboardsPayload->setModules(['Accounts', 'Contacts']);
        $modules = $dashboardsPayload->getModules();
        $this->assertEquals(['Accounts', 'Contacts'], $modules);
    }
}
