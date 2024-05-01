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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;

class Bug46923Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testUserListView()
    {
        global $mod_strings;
        // new employee
        $last_name = 'Test_46923_' . time();
        $user = new User();
        $user->last_name = $last_name;
        $user->default_team = 1;
        $user->status = 'Active';
        $user->employee_status = 'Active';
        $user->user_name = 'test_user_name';
        $user->save();
        $user_id = $user->id;
        $this->assertNotNull($user_id, 'User id should not be null.');

        // list view
        $view = new EmployeesViewList();
        $GLOBALS['action'] = 'index';
        $GLOBALS['module'] = 'Employees';

        $request = InputValidation::create([
            'module' => 'Employees',
        ], []);
        SugarTestReflection::setProtectedValue($view, 'request', $request);

        $view->init($user);
        $view->lv = new ListViewSmarty($request);
        $view->display();

        // ensure the new user shows up in the employees list view
        $this->expectOutputRegex('/.*' . $last_name . '.*/');

        // cleanup
        unset($GLOBALS['action']);
        unset($GLOBALS['module']);
        unset($_REQUEST['module']);
        $GLOBALS['db']->query("delete from users where id='{$user_id}'");
    }
}
