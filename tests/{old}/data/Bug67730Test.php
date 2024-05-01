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
 * @ticket 67730
 */
class Bug67730Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testUserBeanAclFields()
    {
        global $dictionary, $current_user;
        $acl_fields = (isset($dictionary['User']['acl_fields']) && $dictionary['User']['acl_fields'] === false) ? false : true;
        $this->assertEquals($acl_fields, $current_user->acl_fields, "current_user->acl_fileds should be $acl_fields");
        $bean = BeanFactory::getBean('Users', $current_user->id);
        $this->assertEquals($acl_fields, $bean->acl_fields, "acl_fileds of cached User bean should be $acl_fields");
    }
}
