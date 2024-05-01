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
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerFiltersPayload;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\CommandFactory
 */
class InvokerFiltersPayloadTest extends TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::setFilters
     */
    public function testSetAndGetDashboards()
    {
        $dashboardsPayload = new InvokerFiltersPayload([]);
        $dashboardsPayload->setFilters(['filter1']);
        $filters = $dashboardsPayload->getFilters();
        $this->assertEquals(['filter1'], $filters);
    }

    /**
     * @covers ::setModules
     */
    public function testSetAndGetModules()
    {
        $dashboardsPayload = new InvokerFiltersPayload([]);
        $dashboardsPayload->setModules(['Accounts', 'Contacts']);
        $modules = $dashboardsPayload->getModules();
        $this->assertEquals(['Accounts', 'Contacts'], $modules);
    }
}
