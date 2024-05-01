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

class Bug45714Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        //$this->useOutputBuffering = true;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testViewAsAdminUser()
    {
        $GLOBALS['current_user']->is_admin = true;
        $output = $this->getEmployeeListViewOutput();
        $this->assertMatchesRegularExpression('/utilsLink/', $output);
        $output = $this->getEmployeeListViewOutput();
        $this->assertMatchesRegularExpression('/utilsLink/', $output);
    }

    public function testViewAsNonAdminUser()
    {
        $output = $this->getEmployeeListViewOutput();
        $this->assertDoesNotMatchRegularExpression('/utilsLink/', $output);
        $output = $this->getEmployeeDetailViewOutput();
        $this->assertDoesNotMatchRegularExpression('/utilsLink/', $output);
    }

    public function testViewAsModuleAdmin()
    {
        $GLOBALS['current_user'] = new Bug45714UserMock();
        $output = $this->getEmployeeListViewOutput();
        $this->assertMatchesRegularExpression('/utilsLink/', $output);
        $output = $this->getEmployeeDetailViewOutput();
        $this->assertMatchesRegularExpression('/utilsLink/', $output);
    }

    private function getEmployeeListViewOutput()
    {
        $employeeViewList = new EmployeesViewList();
        $employeeViewList->module = 'Employees';
        return $employeeViewList->getModuleTitle(true);
    }

    private function getEmployeeDetailViewOutput()
    {
        $employeeViewDetail = new EmployeesViewDetail();
        $employeeViewDetail->module = 'Employees';
        return $employeeViewDetail->getModuleTitle(true);
    }
}

class Bug45714UserMock extends User
{
    public function isDeveloperForModule($module)
    {
        return true;
    }
}
