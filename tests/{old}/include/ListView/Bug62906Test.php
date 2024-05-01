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

require_once 'modules/ACLFields/actiondefs.php';

/**
 * Bug #62906 unit test
 *
 * @ticked 62906
 */
class Bug62906Test extends TestCase
{
    protected $lead = null;
    protected $task = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->lead = SugarTestLeadUtilities::createLead();
        $this->task = SugarTestTaskUtilities::createTask();
    }

    protected function tearDown(): void
    {
        ACLField::$acl_fields = [];

        SugarTestHelper::tearDown();

        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestTaskUtilities::removeAllCreatedTasks();
    }

    /**
     * data provider
     * @return array
     */
    public function permissionDataProvider()
    {
        // should be false if either one is read only
        return [
            [ACL_READ_WRITE, ACL_READ_WRITE, true],
            [ACL_READ_ONLY, ACL_READ_WRITE, false],
            [ACL_READ_WRITE, ACL_READ_ONLY, false],
        ];
    }

    /**
     * Test to check if the user has unlink permission
     *
     * @dataProvider permissionDataProvider
     *
     * @group 62906
     * @return void
     */
    public function testUnlinkPermission($parentIDPermission, $parentTypePermission, $expected)
    {
        global $current_user;

        $listview = new ListViewMock();

        // setting acl values
        ACLField::$acl_fields[$current_user->id]['Tasks']['parent_id'] = $parentIDPermission;
        ACLField::$acl_fields[$current_user->id]['Tasks']['parent_type'] = $parentTypePermission;

        $permission = $listview->checkUnlinkPermission('tasks', $this->task, $this->lead);

        $this->assertEquals($expected, $permission, 'Incorrect permission.');
    }
}

class ListViewMock extends ListView
{
    public function checkUnlinkPermission($linked_field, $aItem, $parentBean)
    {
        return parent::checkUnlinkPermission($linked_field, $aItem, $parentBean);
    }
}
