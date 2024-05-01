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

class CallTest extends TestCase
{
    /**
     * @var Call our call object
     */
    private $callid;
    public $contact = null;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'CallTest';
        $contact->last_name = 'Contact';
        $contact->save();
        $this->contact = $contact;
    }

    protected function tearDown(): void
    {
        SugarTestCallUtilities::removeCallUsers();
        SugarTestCallUtilities::removeCallContacts();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (!empty($this->callid)) {
            $GLOBALS['db']->query("DELETE FROM calls WHERE id='{$this->callid}'");
            $GLOBALS['db']->query("DELETE FROM vcals WHERE user_id='{$GLOBALS['current_user']->id}'");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
        unset($this->contact);
    }

    /**
     * @group bug40999
     */
    public function testCallStatus()
    {
        global $current_user;
        $call = new Call();
        $this->callid = $call->id = create_guid();
        $call->new_with_id = 1;
        $call->status = 'Test';
        $call->assigned_user_id = $current_user->id;
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $call->save();
        // then retrieve
        $call = new Call();
        $call->retrieve($this->callid);
        $this->assertEquals('Test', $call->status);
    }

    /**
     * @group bug40999
     */
    public function testCallEmptyStatus()
    {
        global $current_user;
        $call = new Call();
        $this->callid = $call->id = create_guid();
        $call->new_with_id = 1;
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $call->assigned_user_id = $current_user->id;
        $call->save();
        // then retrieve
        $call = new Call();
        $call->retrieve($this->callid);
        $this->assertEquals('Planned', $call->status);
    }

    /**
     * @group bug40999
     * Check if empty status is handled correctly
     */
    public function testCallEmptyStatusLang()
    {
        global $current_user;
        $langpack = new SugarTestLangPackCreator();
        $langpack->setModString('LBL_DEFAULT_STATUS', 'FAILED!', 'Calls');
        $langpack->save();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Calls');

        $call = new Call();
        $this->callid = $call->id = create_guid();
        $call->new_with_id = 1;
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $call->assigned_user_id = $current_user->id;
        $call->save();
        // then retrieve
        $call = new Call();
        $call->retrieve($this->callid);
        $this->assertEquals('Planned', $call->status);
    }

    /**
     * @group bug40999
     * Check if empty status is handled correctly
     */
    public function testCallEmptyStatusLangConfig()
    {
        global $db;
        $langpack = new SugarTestLangPackCreator();
        $langpack->setModString('LBL_DEFAULT_STATUS', 'FAILED!', 'Calls');
        $langpack->save();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Calls');

        $call = new Call();
        $this->callid = $call->id = create_guid();
        $call->new_with_id = 1;
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $call->assigned_user_id = $GLOBALS['current_user']->id;
        $call->save();
        // then retrieve
        $call = new Call();
        $call->retrieve($this->callid);
        $this->assertEquals($call->field_defs['status']['default'], $call->status);

        $q = "SELECT cu.accept_status FROM calls_users cu WHERE cu.call_id = '{$this->callid}' AND user_id = '{$GLOBALS['current_user']->id}'";
        $r = $db->query($q);
        $a = $db->fetchByAssoc($r);
        $this->assertEquals('accept', $a['accept_status'], "Call wasn't accepted by the User");
    }

    public function testLoadFromRow()
    {
        /** @var Call $call */
        $call = BeanFactory::newBean('Calls');
        $this->assertEmpty($call->reminder_checked);
        $this->assertEmpty($call->email_reminder_checked);

        $call->loadFromRow([
            'reminder_time' => 30,
            'email_reminder_time' => 30,
        ]);

        $this->assertTrue($call->reminder_checked);
        $this->assertTrue($call->email_reminder_checked);
    }

    public function testGetNotificationRecipients_RecipientsAreAlreadyLoaded_ReturnsRecipients()
    {
        $contacts = [
            SugarTestContactUtilities::createContact(),
            SugarTestContactUtilities::createContact(),
        ];

        $call = BeanFactory::newBean('Calls');
        $call->users_arr = [$GLOBALS['current_user']->id];
        $call->contacts_arr = [$contacts[0]->id, $contacts[1]->id];

        $actual = $call->get_notification_recipients();
        $this->assertArrayHasKey($GLOBALS['current_user']->id, $actual, 'The current user should be in the list.');
        $this->assertArrayHasKey($contacts[0]->id, $actual, 'The first contact should be in the list.');
        $this->assertArrayHasKey($contacts[1]->id, $actual, 'The second contact should be in the list.');
    }

    public function testGetNotificationRecipients_RecipientsAreNotAlreadyLoaded_ReturnsEmptyRecipients()
    {
        $contacts = [
            SugarTestContactUtilities::createContact(),
            SugarTestContactUtilities::createContact(),
        ];

        $call = SugarTestCallUtilities::createCall();
        SugarTestCallUtilities::addCallUserRelation($call->id, $GLOBALS['current_user']->id);
        SugarTestCallUtilities::addCallContactRelation($call->id, $contacts[0]->id);
        SugarTestCallUtilities::addCallContactRelation($call->id, $contacts[1]->id);

        $actual = $call->get_notification_recipients();
        $this->assertEmpty($actual, 'No invitees should have been loaded for this call.');
    }

    /**
     * Test that when assigned user is not a current one re-saved Call will contain only assigned user.
     * @covers \Call::save
     */
    public function testCallIsExistingTheAssignedUserIsNotTheCurrentUserTheCurrentUserIsNotInvited()
    {
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $call = BeanFactory::newBean('Calls');
        $call->id = create_guid();
        $call->name = 'Test Call';
        $call->duration_hours = '0';
        $call->duration_minutes = '15';
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $call->assigned_user_id = $user2->id;
        $call->save();
        $this->callid = $call->id;


        $invitees = $call->users->get();
        $this->assertCount(1, $invitees, 'Should only contain the assigned user');
        $this->assertContains($call->assigned_user_id, $invitees, 'The assigned user was not found');
    }
}
