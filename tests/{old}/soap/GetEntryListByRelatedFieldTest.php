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

/**
 * @group SoapTests
 */
class GetEntryListByRelatedFieldTest extends SOAPTestCase
{
    /** @var Account */
    private $account;

    /** @var Contact */
    private $contact;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->account = SugarTestAccountUtilities::createAccount();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('accounts');
        $this->contact->accounts->add($this->account);
        $GLOBALS['db']->commit();

        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();

        self::$user = $GLOBALS['current_user'];
        $this->loginLegacy();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testGetEntryList()
    {
        $result = $this->soapClient->get_entry_list(
            $this->sessionId,
            'Contacts',
            'accounts.name=' . $GLOBALS['db']->quoted($this->account->name),
            '',
            0,
            ['id', 'account_name'],
            -1,
            -1
        );

        $this->assertTrue(property_exists($result, 'entry_list'));
        $this->assertCount(1, $result->entry_list, 'Entry list should contain exactly one entry');
        $entry = array_shift($result->entry_list);
        $this->assertEquals($this->contact->id, $entry->id, 'Wrong contact is retrieved');
    }
}
