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
 * @ticket 33405
 */
class Bug33905Test extends TestCase
{
    private $user;
    private $team;
    private $ie;

    protected function setUp(): void
    {
        global $current_user;

        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->user->default_team = $this->team->id;
        $this->team->add_user_to_team($this->user->id);
        $this->user->save();
        $current_user = $this->user;
        $ieID = $this->createInboundAccount();
        $ie = new InboundEmail();
        $this->ie = $ie->retrieve($ieID);
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$this->user->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);

        $GLOBALS['db']->query("DELETE FROM inbound_email WHERE id='{$this->ie->id}'");
    }

    private function createInboundAccount()
    {
        $stored_options = [];
        $stored_options['from_name'] = 'UnitTest';
        $stored_options['from_addr'] = 'UT@sugarcrm.com';
        $stored_options['reply_to_name'] = 'UnitTest';
        $stored_options['reply_to_addr'] = 'UT@sugarcrm.com';
        $stored_options['only_since'] = false;
        $stored_options['filter_domains'] = '';
        $stored_options['trashFolder'] = 'INBOX.Trash';
        $stored_options['leaveMessagesOnMailServer'] = 1;

        $focus = new InboundEmail();
        $focus->name = 'Unittest';
        $focus->email_user = 'ajaysales@sugarcrm.com';
        $focus->email_password = 'f00f004';
        $focus->server_url = 'mail.sugarcrm.com';
        $focus->protocol = 'imap';
        $focus->mailbox = 'INBOX';
        $focus->port = '143';
        $focus->service = '0::0::1::IMAP';
        $focus->is_personal = 0;
        $focus->status = 'Active';
        $focus->mailbox_type = 'pick';
        $focus->group_id = create_guid();
        $focus->team_id = $this->team->id;
        $focus->team_set_id = $this->team->id;
        $focus->stored_options = base64_encode(serialize($stored_options));
        return $focus->save();
    }

    public function testCreateSubscriptions()
    {
        global $current_user;

        $this->assertInstanceOf('InboundEmail', $this->ie);

        $this->ie->createUserSubscriptionsForGroupAccount();

        $subs = unserialize(base64_decode($current_user->getPreference('showFolders', 'Emails')), ['allowed_classes' => false]);
        $this->assertEquals($this->ie->id, $subs[0], 'Unable to create subscriptions for IE Group Account (Import not enabled)');
    }
}
