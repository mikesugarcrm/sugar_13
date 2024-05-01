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
 * Tests emails subpanel query can fetch emails.
 *
 * @covers ArchivedEmailsBeanLink
 */
class ArchivedEmailsBeanLinkTest extends TestCase
{
    /**
     * @var Account
     */
    private $account;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        // turn on admin setting "Enable/Disable emails from related (or linked) contacts to show in Email Subpanel."
        $GLOBALS['sugar_config']['hide_history_contacts_emails']['Opportunities'] = false;

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function testRelatedEmails()
    {
        $emailAddress = 'testRelatedEmails@testRelatedEmails.com';

        // create contact and email related (via to_addr) to contact
        $contact = SugarTestContactUtilities::createContact(null, [
            'email' => $emailAddress,
        ]);

        $email = SugarTestEmailUtilities::createEmail(null, [
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
            'to_addrs' => $emailAddress,
        ]);

        $opportunity = SugarTestOpportunityUtilities::createOpportunity(null, $this->account);
        $opportunity->load_relationship('contacts');
        $opportunity->contacts->add($contact);

        $emails = $this->fetchEmails($opportunity);

        $this->assertArrayHasKey($email->id, $emails, 'Email is not displayed in the sub-panel');
    }

    public function testContactEmails()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->load_relationship('accounts');
        $contact->accounts->add($this->account);

        $email = SugarTestEmailUtilities::createEmail();
        $email->load_relationship('contacts');
        $email->contacts->add($contact);

        $emails = $this->fetchEmails($this->account);

        $this->assertArrayHasKey($email->id, $emails);
    }

    private function fetchEmails(SugarBean $bean)
    {
        $email = BeanFactory::newBean('Emails');

        $query = new SugarQuery();
        $query->select('id');
        $query->from($email);
        $query->joinSubpanel($bean, 'archived_emails');

        return $email->fetchFromQuery($query);
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailUtilities::removeAllCreatedEmails();

        SugarTestHelper::tearDown();
    }
}
