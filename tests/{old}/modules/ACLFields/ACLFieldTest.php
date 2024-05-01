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
 * @covers ACLField
 */
class ACLFieldTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setup('dictionary');
        SugarTestHelper::setup('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestACLUtilities::tearDown();
        ACLField::clearACLCache();
        SugarTestHelper::tearDown();
    }

    public function testFieldAclIsRespected()
    {
        global $current_user;

        $this->setUpReadOnlyField('Accounts', 'Account', 'name');
        $this->assertEquals(1, ACLField::hasAccess('name', 'Accounts', $current_user));
    }

    public function testFieldAclIsIgnored()
    {
        global $current_user;
        global $dictionary;

        $dictionary['Account']['acl_fields'] = false;

        $this->setUpReadOnlyField('Accounts', 'Account', 'name');
        $this->assertEquals(4, ACLField::hasAccess('name', 'Accounts', $current_user));
    }

    public function testCollectionFieldsCanHaveAcls()
    {
        global $current_user;

        $this->setUpReadOnlyField('Accounts', 'Account', 'commentlog');
        $this->assertEquals(1, ACLField::hasAccess('commentlog', 'Accounts', $current_user));

        // ensure changes to collection field ACL's are propagated to links
        $this->assertEquals(1, ACLField::hasAccess('commentlog_link', 'Accounts', $current_user));
    }

    private function setUpReadOnlyField($module, $object, $field)
    {
        global $current_user;

        $role = SugarTestACLUtilities::createRole(create_guid(), [$module], []);
        SugarTestACLUtilities::createField($role->id, $module, $field, ACL_READ_ONLY);
        SugarTestACLUtilities::setupUser($role);
        ACLField::loadUserFields($module, $object, $current_user->id, true);
    }
}
