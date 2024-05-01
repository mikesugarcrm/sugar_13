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


class RestPortalCreateTest extends RestTestPortalBase
{
    /**
     * @var \Account|mixed
     */
    public $account;
    /**
     * @var \aCase|mixed
     */
    public $case;
    /**
     * @var \Bug|mixed
     */
    public $bug;
    public $bugId;

    /**
     * We need to associate an Account with the Contact in order to create Cases
     */
    protected function setUp(): void
    {
        parent::setUp();
        // create account
        $this->account = new Account();
        $this->account->name = 'UNIT TEST account - ' . create_guid();
        $this->account->billing_address_postalcode = sprintf('%08d', 1);
        $this->account->save();
        $this->accounts[] = $this->account;

        $this->contact->load_relationship('accounts');
        // relate
        $this->contact->accounts->add($this->account->id);
        $GLOBALS['db']->commit();
    }

    /**
     * Make sure the relationship is removed, parent should clean everything else
     */
    protected function tearDown(): void
    {
        if (isset($this->account->id)) {
            $this->contact->accounts->delete($this->account->id);
        }
        // Parent will remove account
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreate()
    {
        // we need to be an admin to get at the relationship data
        $GLOBALS['current_user']->is_admin = 1;
        $args = [
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        ];
        $GLOBALS['db']->commit();

        // create case
        $caseReply = $this->restCall(
            'Cases/',
            json_encode(['name' => 'UNIT TEST Case', 'portal_visible' => true]),
            'POST'
        );
        $this->assertEquals(200, $caseReply['info']['http_code'], 'HTTP Code was not a 200 - #1');
        $this->assertEquals($caseReply['reply']['account_id'], $this->account->id, 'Case create did not contain account id of creator');
        $GLOBALS['db']->commit();
        $this->case = new aCase();

        $this->case->retrieve($caseReply['reply']['id']);
        $relates = $this->case->get_linked_beans('contacts', 'Contact');
        $this->assertEquals($relates[0]->id, $this->contact->id, 'The contact id does not match the first related contact for the created case');
        // Make sure new case is cleaned up
        $this->cases[] = $this->case;
        // create bug
        $bugReply = $this->restCall(
            'Bugs/',
            json_encode(['name' => 'UNIT TEST Bug']),
            'POST'
        );
        $this->assertEquals(200, $bugReply['info']['http_code'], 'HTTP Code was not a 200 - #2');
        $GLOBALS['db']->commit();
        $this->bug = new Bug();
        $this->bug->retrieve($bugReply['reply']['id']);
        // Make sure new bug is cleaned up
        $this->bugs[] = $this->bug;
        $relates = $this->bug->get_linked_beans('contacts', 'Contact');
        $this->assertEquals($relates[0]->id, $this->contact->id, 'The contact id does not match the first related contact for the created case - #2');

        $relatesAccounts = $this->bug->get_linked_beans('accounts', 'Account');
        $this->assertEquals($relatesAccounts[0]->id, $this->account->id, 'The account id does not match the first related account for the created case');
    }

    /**
     * @group bug56143
     * @group rest
     */
    public function testCreateErrorBug56143()
    {
        // we need to be an admin to get at the relationship data
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['db']->commit();
        $this->restLogin($this->contact->portal_name, 'unittest');

        // Remove the Account from Contact so this Contact can no longer create Cases
        if (isset($this->account->id)) {
            $this->contact->accounts->delete($this->account->id);
        }

        // create case
        $GLOBALS['db']->commit();
        $caseReply = $this->restCall(
            'Cases/',
            json_encode(['name' => 'UNIT TEST Case']),
            'POST'
        );

        $this->assertEquals('not_authorized', $caseReply['reply']['error']);
        $this->assertEquals(403, $caseReply['info']['http_code'], 'HTTP Status');
        // Error message should mention the module name
        $this->assertContains('Cases', $caseReply['reply']['error_message'], 'The error message should mention the module name.');
    }


    /**
     * @group bug57775
     * @group rest
     */
    public function testAddingNoteToBugAsSupportPortal()
    {
        $bugReply = $this->restCall(
            'Bugs/',
            json_encode([
                'name' => 'UNIT TEST CREATE BUG PORTAL USER',
                'portal_viewable' => '1',
                'team_id' => '1',
            ]),
            'POST'
        );

        $this->bugId = $bugReply['reply']['id'];

        // Create a note on the bug without an attachment
        $bugNoteReply = $this->restCall(
            "Bugs/{$this->bugId}/link/notes",
            json_encode([
                'name' => 'UNIT TEST BUG NOTE PORTAL USER',
                'portal_flag' => '1',
            ]),
            'POST'
        );

        $contactTeamId = $this->contact->team_id;
        $contactTeamSetId = $this->contact->team_set_id;
        $this->assertEquals($contactTeamId, $bugNoteReply['reply']['related_record']['team_id']);
        $this->assertEquals($contactTeamSetId, $bugNoteReply['reply']['related_record']['team_set_id']);

        $this->assertEquals($this->account->id, $bugNoteReply['reply']['related_record']['account_id'], 'account id should have been set in Note');
        $this->assertEquals($this->contact->id, $bugNoteReply['reply']['related_record']['contact_id'], 'contact id should have been set in Note');
        $this->assertEquals($this->bugId, $bugNoteReply['reply']['related_record']['parent_id'], 'Note should have been attached to Bug');
    }
}
