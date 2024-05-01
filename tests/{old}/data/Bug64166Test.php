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
 * @ticket 64166
 */
class Bug64166Test extends TestCase
{
    /**
     * @var Contact
     */
    private $contact;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testEmptyRelateFieldIsRegistered()
    {
        $contact = BeanFactory::newBean('Contacts');
        $contact->retrieve($this->contact->id);
        $this->assertEmpty($contact->account_name);
        $this->assertArrayHasKey('account_name', $contact->fetched_rel_row);
    }
}
