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

use Sugarcrm\Sugarcrm\Security\Crypto\Blowfish;
use PHPUnit\Framework\TestCase;

/**
 * @ticket 23140
 */
class Bug23140Test extends TestCase
{
    private $user;
    private $ob;
    private $userOverrideAccount;

    protected function setUp(): void
    {
        global $current_user;
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $current_user = $this->user;
        OutboundEmailConfigurationTestHelper::setUp();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        //unset($GLOBALS['current_user']);
        $GLOBALS['db']->query("DELETE FROM outbound_email WHERE user_id= '{$this->user->id}'");
        if ($this->ob != null) {
            $GLOBALS['db']->query("DELETE FROM outbound_email WHERE id= '{$this->ob->id}'");
        }
        if ($this->userOverrideAccount != null) {
            $GLOBALS['db']->query("DELETE FROM outbound_email WHERE id= '{$this->userOverrideAccount->id}'");
        }
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    public function testSystemAccountMailSettingsChangedNoUserAccessToUsername()
    {
        //User not alloweed to access system email username/password
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");

        $newSystemPort = 864;
        $newSystemServer = 'system.imap.com';

        $userID = create_guid();
        $ob = new OutboundEmail();
        $ob->id = $userID;
        $ob->new_with_id = true;
        $ob->name = 'Sugar Test 2';
        $ob->type = 'system-override';
        $ob->user_id = $this->user->id;
        $ob->mail_sendtype = 'SMTP';
        $ob->mail_smtpuser = 'Test User';
        $ob->mail_smtppass = 'User Pass';
        $ob->save();
        $this->ob = $ob;

        $system = $ob->getSystemMailerSettings();
        $system->new_with_id = false;
        $system->mail_smtpport = $newSystemPort;
        $system->mail_smtpserver = $newSystemServer;
        $system->saveSystem();

        $obRetrieved = new OutboundEmail();
        $obRetrieved->retrieve($userID);

        $this->assertEquals($newSystemPort, $obRetrieved->mail_smtpport, 'Could not update users system-override accounts after system save.');
        $this->assertEquals($newSystemServer, $obRetrieved->mail_smtpserver, 'Could not update users system-override accounts after system save.');
        $this->assertEquals('Test User', $obRetrieved->mail_smtpuser, 'Could not update users system-override accounts after system save.');
        $this->assertEquals('User Pass', $obRetrieved->mail_smtppass, 'Could not update users system-override accounts after system save.');
    }


    public function testUserMailForSystemOverrideRetrieval()
    {
        $ob = new OutboundEmail();
        $ob->name = 'Sugar Test 3';
        $ob->type = 'system-override';
        $ob->user_id = $this->user->id;
        $ob->mail_sendtype = 'SMTP';
        $ob->mail_smtpuser = 'Test User';
        $ob->save();
        $this->ob = $ob;

        $retrievedOb = $ob->getUsersMailerForSystemOverride($this->user->id);
        $this->assertEquals($ob->name, $retrievedOb->name, 'Could not retrieve users system override outbound email account');
        $this->assertEquals($ob->type, $retrievedOb->type, 'Could not retrieve users system override outbound email account');
        $this->assertEquals($ob->user_id, $retrievedOb->user_id, 'Could not retrieve users system override outbound email account');
        $this->assertEquals($ob->mail_sendtype, $retrievedOb->mail_sendtype, 'Could not retrieve users system override outbound email account');
        $this->assertEquals('Test User', $retrievedOb->mail_smtpuser, 'Could not retrieve users system override outbound email account');
    }

    public function testDuplicateSystemAccountForUser()
    {
        $oe = new OutboundEmail();
        $userOverideAccont = $oe->createUserSystemOverrideAccount($this->user->id, 'TEST USER NAME', 'TEST PASSWORD');
        $this->userOverrideAccount = $userOverideAccont;
        $retrievedOb = $oe->getUsersMailerForSystemOverride($this->user->id);

        $this->assertEquals('TEST USER NAME', $retrievedOb->mail_smtpuser, 'Could not duplicate systems outbound account for user');
        $this->assertEquals($this->user->id, $retrievedOb->user_id, 'Could not duplicate systems outbound account for user');
        $this->assertEquals('TEST PASSWORD', $retrievedOb->mail_smtppass, 'Could not duplicate systems outbound account for user');
        $this->assertEquals('system-override', $userOverideAccont->type, 'Could not duplicate systems outbound account for user');
    }

    public function testIsUserAlloweedAccessToSystemOutboundEmail()
    {
        $oe = new OutboundEmail();
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $emptyTest = $oe->isAllowUserAccessToSystemDefaultOutbound();
        $this->assertFalse($emptyTest, 'User alloweed access to system outbound email account error');

        $GLOBALS['db']->query("INSERT INTO config (category,name,value) VALUES ('notify','allow_default_outbound','2') ");
        $allowTest = $oe->isAllowUserAccessToSystemDefaultOutbound();
        $this->assertTrue($allowTest, 'User alloweed access to system outbound email account error');

        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $emptyTest = $oe->isAllowUserAccessToSystemDefaultOutbound();
        $this->assertFalse($emptyTest, 'User alloweed access to system outbound email account error');
    }


    public function testIsUserAuthRequiredForOverrideAccount()
    {
        $oe = new OutboundEmail();

        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $oe->resetSystemMailerCache();
        $system = $oe->getSystemMailerSettings();

        //System does not require auth, no user overide account.
        $system->mail_smtpauth_req = 0;
        $system->save(false);

        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->user->id);
        $this->assertFalse($notRequired, 'Test failed for determining if user auth required.');

        //System does require auth, no user overide account.
        $system->mail_smtpauth_req = 1;
        $system->save(false);
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->user->id);
        $this->assertTrue($notRequired, 'Test failed for determining if user auth required.');

        //System requires auth and users alloweed to use sys defaults.
        $GLOBALS['db']->query("INSERT INTO config (category,name,value) VALUES ('notify','allow_default_outbound','2') ");
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->user->id);
        $this->assertFalse($notRequired, 'Test failed for determining if user auth required.');


        //System requires auth but user details are empty and users are not alloweed to use system details..
        $GLOBALS['db']->query("DELETE FROM config WHERE category='notify' AND name='allow_default_outbound' ");
        $userOverideAccont = $oe->createUserSystemOverrideAccount($this->user->id, '', '');
        $this->userOverrideAccount = $userOverideAccont;
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->user->id);
        $this->assertTrue($notRequired, 'Test failed for determining if user auth required.');

        //User has provided all credentials.
        $this->userOverrideAccount->mail_smtpuser = 'TEST USER NAME';
        $this->userOverrideAccount->mail_smtppass = 'TEST PASSWORD';
        $this->userOverrideAccount->new_with_id = false;
        $this->userOverrideAccount->save();
        $notRequired = $oe->doesUserOverrideAccountRequireCredentials($this->user->id);
        $this->assertFalse($notRequired, 'Test failed for determining if user auth required.');
    }
}
