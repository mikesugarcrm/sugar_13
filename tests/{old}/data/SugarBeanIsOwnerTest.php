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

class SugarBeanIsOwner extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testIsOwnerNew()
    {
        $bean = new SugarBean();

        $this->assertTrue($bean->isOwner('DONT-CARE'), 'SugarBean->isOwner() should return true if there is no id.');

        $bean->id = 'TEST-BEAN-PLEASE-IGNORE';
        $bean->new_with_id = true;

        $this->assertTrue($bean->isOwner('DONT-CARE'), 'SugarBean->isOwner() should return true if there is an id but new_with_id is true');

        $bean->new_with_id = false;
        $this->assertFalse($bean->isOwner('DONT-CARE'), 'SugarBean->isOwner() should return false if there is an id but new_with_id is false');
    }

    public function testIsOwnerAssignedUserId()
    {
        $bean = new SugarBean();
        $bean->id = 'TEST-BEAN-PLEASE-IGNORE';
        $bean->assigned_user_id = 'MY-ONE-AND-ONLY-USER';

        $this->assertTrue($bean->isOwner('MY-ONE-AND-ONLY-USER'), 'SugarBean->isOwner() should return true if the assigned user matches the passed in user');

        $this->assertFalse($bean->isOwner('NOT-ME'), "SugarBean->isOwner() should return false if the assigned user doesn't match the passed in user");

        $bean->assigned_user_id = 'OTHER-KIDS';
        $bean->fetched_row = ['assigned_user_id' => 'MY-ONE-AND-ONLY-USER'];

        $this->assertTrue($bean->isOwner('MY-ONE-AND-ONLY-USER'), 'SugarBean->isOwner() should return true if the passed in user matches the fetched row assigned user');

        $this->assertTrue($bean->isOwner('OTHER-KIDS'), 'SugarBean->isOwner() should return true if the passed in user matches the assigned user but not the fetched row');

        $this->assertFalse($bean->isOwner('NOT-ME'), "SugarBean->isOwner() should return false if the passed in user doesn't match the fetched row or normal assigned user ");


        unset($bean->fetched_row);
        unset($bean->assigned_user_id);

        $bean->created_by = 'MY-ONE-AND-ONLY-USER';

        $this->assertTrue($bean->isOwner('MY-ONE-AND-ONLY-USER'), 'SugarBean->isOwner() should return true if the created by user matches the passed in user and there is no assigned user');

        $this->assertFalse($bean->isOwner('NOT-ME'), "SugarBean->isOwner() should return false if the created by user doesn't match the passed in user and there is no assigned user");

        $bean->assigned_user_id = 'OTHER-KIDS';

        $this->assertFalse($bean->isOwner('MY-ONE-AND-ONLY-USER'), 'SugarBean->isOwner() should return false if the created by user matches the passed in user and there is an assigned user');
    }
}
