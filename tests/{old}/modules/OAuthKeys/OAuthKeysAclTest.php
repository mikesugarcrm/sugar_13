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

/* This unit test class covers the ACLs added for extra modules, this does not cover the Users/Employees modules, those are more intense. */

class OAuthKeysAclTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        // Need to be an admin to get to OAuthKeys
        $GLOBALS['current_user']->is_admin = 1;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testCreate()
    {
        $testBean = BeanFactory::newBean('OAuthKeys');

        $canCreate = $testBean->ACLAccess('create');
        $this->assertTrue($canCreate, 'Should be able to create a new record.');

        $canCreateType = $testBean->ACLFieldAccess('oauth_type', 'create');
        $this->assertTrue($canCreateType, 'Should be able to create oauth_type');

        $canCreateName = $testBean->ACLFieldAccess('name', 'create');
        $this->assertTrue($canCreateName, 'Should be able to create name');
    }

    public function testEdit()
    {
        $testBean = BeanFactory::newBean('OAuthKeys');
        $testBean->id = 'JUST_A_SMALL_TOWN_GIRL';
        $testBean->name = 'Living in a lonely world';
        $testBean->c_key = 'midnight_train';

        $canEdit = $testBean->ACLAccess('edit');
        $this->assertTrue($canEdit, 'Should be able to edit an existing record.');

        $canEditType = $testBean->ACLFieldAccess('oauth_type', 'edit');
        $this->assertFalse($canEditType, 'Should not be able to edit oauth_type');

        $canEditName = $testBean->ACLFieldAccess('name', 'edit');
        $this->assertTrue($canEditName, 'Should be able to edit name');
    }

    public function testSpecialCKey()
    {
        $testBean = BeanFactory::newBean('OAuthKeys');
        $testBean->id = 'STREETLIGHT_PEOPLE';
        $testBean->name = 'Up and down the boulevard';
        $testBean->c_key = 'sugar';

        $canEdit = $testBean->ACLAccess('edit');
        $this->assertFalse($canEdit, "Should not be able to edit a record with a c_key of 'sugar'");
    }
}
