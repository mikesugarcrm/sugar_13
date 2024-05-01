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

class Bug40434Test extends TestCase
{
    protected function setUp(): void
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = true;
        $user->save();
        $GLOBALS['current_user'] = $user;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group bug40434
     */
    public function testNameOfModifiedByNameField()
    {
        $contact = new Contact();
        $contact->create_new_list_query('', '');
        $this->assertEquals($contact->field_defs['modified_by_name']['name'], 'modified_by_name', "Name of modified by name field should be 'modified_by_name'");
    }
}
