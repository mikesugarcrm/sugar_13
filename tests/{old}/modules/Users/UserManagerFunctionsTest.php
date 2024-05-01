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

class UserManagerFunctionsTest extends TestCase
{
    private $employee1;
    private $employee2;
    private $employee3;
    private $employee4;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->user_name = 'employee0';
        $current_user->save();

        $this->employee1 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee1->reports_to_id = $current_user->id;
        $this->employee1->user_name = 'employee1';
        $this->employee1->save();

        $this->employee2 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee2->reports_to_id = $current_user->id;
        $this->employee2->user_name = 'employee2';
        $this->employee2->save();

        $this->employee3 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee3->reports_to_id = $this->employee2->id;
        $this->employee3->user_name = 'employee3';
        $this->employee3->save();

        $this->employee4 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee4->reports_to_id = $this->employee3->id;
        $this->employee4->deleted = 1;
        $this->employee4->user_name = 'employee4';
        $this->employee4->save();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testUserManagementFunctions()
    {
        global $current_user;
        $this->assertTrue(User::isTopLevelManager($current_user->id), 'current_user is top level manager');
        $this->assertFalse(User::isManager($this->employee1->id), 'employee1 does not report to anyone');
        $this->assertFalse(User::isTopLevelManager($this->employee3->id), 'employee3 is not a top level manager');
        $this->assertFalse(User::isManager($this->employee3->id), 'employee3 is not a manager if we exclude deleted users');
        $this->assertTrue(User::isManager($this->employee3->id, true), 'employee3 is a manager if we include deleted users');
    }
}
