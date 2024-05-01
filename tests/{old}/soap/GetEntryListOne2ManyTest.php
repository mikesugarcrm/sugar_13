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
 * @group bug43196
 */
class GetEntryListOne2ManyTest extends SOAPTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function tearDown(): void
    {
        foreach (SugarTestContactUtilities::getCreatedContactIds() as $id) {
            $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id = '{$id}'");
        }
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testGetEntryWhenAccountHasMultipleContactsRelationshipsWorks()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $account->load_relationship('contacts');
        $account->contacts->add($contact1->id);
        $account->contacts->add($contact2->id);

        $this->login();

        $resultObj = $this->soapClient->get_entry_list(
            $this->sessionId,
            'Accounts',
            "accounts.id = '{$account->id}'",
            '',
            0,
            ['id', 'contact_id', 'contact_name'],
            [['name' => 'contacts', 'value' => ['id', 'name']]],
            250,
            0
        );

        $contact_names = [$contact1->name, $contact2->name];
        $contact_ids = [$contact1->id, $contact2->id];

        $actualContact1Name = $resultObj->relationship_list[0]->link_list[0]->records[0]->link_value[1]->value;
        $actualContact2Name = $resultObj->relationship_list[0]->link_list[0]->records[1]->link_value[1]->value;
        $actualContact1Id = $resultObj->relationship_list[0]->link_list[0]->records[0]->link_value[0]->value;
        $actualContact2Id = $resultObj->relationship_list[0]->link_list[0]->records[1]->link_value[0]->value;

        $this->assertTrue(in_array($actualContact1Name, $contact_names), 'Contact1s name not returned.');
        $this->assertTrue(in_array($actualContact2Name, $contact_names), 'Contact2s name not returned.');
        $this->assertTrue(in_array($actualContact1Id, $contact_ids), 'Contact1s id not returned.');
        $this->assertTrue(in_array($actualContact2Id, $contact_ids), 'Contact2s id not returned.');
    }
}
