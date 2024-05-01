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

class ReassignUserRecordsTest extends TestCase
{
    private $user1;
    private $user2;
    private $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->user2 = $GLOBALS['current_user'];
        $this->user2->is_admin = 1;
        $this->user2->save();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', ['Users']);

        //Create another user for testing
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();

        //Create a notification bean
        $this->bean = SugarTestNotificationUtilities::createNotification();
        $this->bean->name = 'Notification Test';
        $this->bean->assigned_user_id = $this->user2->id;
        $this->bean->save();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestNotificationUtilities::removeAllCreatedNotifications();
        unset($_SESSION['reassignRecords']);
        unset($_POST['module']);
        unset($_POST['action']);
        unset($_POST['fromuser']);
        unset($_POST['touser']);
        unset($_POST['moudules']);
        unset($_POST['steponesubmit']);
    }

    /**
     * This method tests the reassignment notification code.  This particular test checks to ensure that the notification bean
     * does not cause problems when reassigning since we need code to filter out team specific fields.
     * @group user_reassignment
     */
    public function testReassignRecordForNotifications()
    {
        //simulate selecting notification module for reassignment
        $_SESSION['reassignRecords']['assignedModuleListCache'] = ['Notifications' => 'Notifications'];
        $_SESSION['reassignRecords']['assignedModuleListCacheDisp'] = ['Notifications' => 'Notifications'];

        $_POST['module'] = 'Users';
        $_POST['action'] = 'reassignUserRecords';
        $_POST['fromuser'] = $this->user1->id;
        $_POST['touser'] = $GLOBALS['current_user']->id;
        $_POST['modules'] = ['Notifications'];
        $_POST['steponesubmit'] = 'Next';

        global $app_list_strings, $beanFiles, $beanList, $current_user, $mod_strings, $app_strings;
        //Include the reassignUserRecords.php file to run it
        include 'modules/Users/reassignUserRecords.php';

        $notificationBean = BeanFactory::getBean('Notifications', $this->bean->id);
        $this->assertEquals($this->user2->id, $notificationBean->assigned_user_id);
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }
}
