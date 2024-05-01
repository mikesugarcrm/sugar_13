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

use PHPUnit\Framework\TestCase;

/**
 * Unit test for SugarACLStatic
 */
class SugarACLStaticTest extends TestCase
{
    public $testUser;
    public static $modules = [];

    protected function setUp(): void
    {
        SugarTestHelper::setup('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * @group pat-851
     */
    public function testTrackerTypeForBeanACL()
    {
        // a role that can access Tracker
        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), ['Trackers'], ['access', 'edit', 'list', 'export', 'view'], [], 'Tracker');
        SugarTestACLUtilities::setupUser($role);

        // parameters needed to call beanACL
        $module = 'Trackers';
        $action = 'detailview';
        $bean = new Tracker();
        $context = ['bean' => $bean];

        $mockObj = new MockSugarACLStatic();
        $ret = $mockObj->mockBeanACL($module, $action, $context);

        $this->assertTrue($ret);
    }
}

class MockSugarACLStatic extends SugarACLStatic
{
    public function mockBeanACL($module, $action, $context)
    {
        return parent::beanACL($module, $action, $context);
    }
}
