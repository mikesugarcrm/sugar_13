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

class Bug39780Test extends TestCase
{
    /**
     * @var mixed
     */
    public $defs;
    protected $contact;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->defs = $this->contact->field_defs;
    }

    protected function tearDown(): void
    {
        $this->contact->field_defs = $this->defs;
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    // Test unPopulateDefaultValues to make sure it doesn't generate any notices
    /*
     * @group bug39780
     */
    public function testSugarBeanUnPopulateDefaultValues()
    {
        $this->contact->first_name = 'SadekDizzle';
        $this->contact->field_defs['first_name']['default'] = 'SadekSnizzle';
        try {
            $this->contact->unPopulateDefaultValues();
        } catch (Exception $e) {
            $this->assertTrue(false, 'SugarBean->unPopulateDefaultValues is generating a notice/warning/fatal: ' . $e->getMessage());
            return;
        }

        $this->assertTrue(true);
    }
}
