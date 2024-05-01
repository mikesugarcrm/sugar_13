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
 * Bug #65044
 * Inbound Email to Case creation does not automatically link up to the Account
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 65044
 */
class Bug65044Test extends TestCase
{
    private $ie;
    private $account;
    private $contact;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->name = 'Boro SugarTest 65044';
        $this->account->save();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->first_name = 'Boro';
        $this->contact->last_name = 'SugarTest 65044';
        $this->contact->email1 = 'bsitnikovskiBug65044Test@sugarcrm.com';
        $this->contact->save();

        $this->ie = new InboundEmail();
        $this->ie->name = $this->ie->casename = '[CASE:Bug65044] Bug65044 Test';
        $this->ie->description = 'This is a test for Bug65044';
        $this->ie->mailbox_type = 'createcase';
        $this->ie->groupfolder_id = 'non-empty';
        $this->ie->from_addr = $this->contact->email1;

        $teamId = $GLOBALS['current_user']->getPrivateTeam();
        $this->ie->team_id = $_REQUEST['team_id'] = $teamId;
        $this->ie->team_set_id = $_REQUEST['team_set_id'] = $this->ie->getTeamSetIdForTeams($teamId);
        $_REQUEST['acl_team_set_id'] = '';

        $this->ie->save();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM inbound_email WHERE id = '{$this->ie->id}'");
        $GLOBALS['db']->query("DELETE FROM cases WHERE name = '{$this->ie->casename}'");
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    private function getCase()
    {
        // Cache intentionally bypassed
        $case = BeanFactory::newBean('Cases');
        $case->retrieve($this->ie->parent_id);

        $this->assertTrue($case->load_relationship('accounts'));

        $this->assertTrue($case->load_relationship('contacts'));

        return $case;
    }

    public function testContactWithAccountLink()
    {
        // link contact to accounts
        $this->assertTrue($this->contact->load_relationship('accounts'));
        $this->contact->accounts->add($this->account->id);

        $this->ie->handleCreateCase($this->ie, $GLOBALS['current_user']->id);

        $case = $this->getCase();

        $this->assertContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }

    public function testContactWithoutAccountLink()
    {
        $this->ie->handleCreateCase($this->ie, $GLOBALS['current_user']->id);
        $case = $this->getCase();

        $this->assertNotContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }

    public function testContactAccountEmail()
    {
        // set same e-mail address
        $this->account->email1 = $this->contact->email1;
        $this->account->save();

        $this->ie->handleCreateCase($this->ie, $GLOBALS['current_user']->id);

        $case = $this->getCase();

        $this->assertContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }
}
