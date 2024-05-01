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

class Bug40739Test extends TestCase
{
    protected $contact;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /*
     * @group bug40739
     */
    public function testCreatedByNameOverride()
    {
        $this->contact->created_by = '';
        $this->contact->created_by_name = 'admin';
        $this->contact->fill_in_additional_detail_fields();

        $this->assertTrue($this->contact->created_by_name == 'admin', "created_by_name shouldn't have been affected by a blank created_by value");
    }
}
