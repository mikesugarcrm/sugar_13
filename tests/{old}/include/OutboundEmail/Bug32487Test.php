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
 * @ticket 32487
 */
class Bug32487Test extends TestCase
{
    public $ib = null;
    public $outbound_id = null;

    protected function setUp(): void
    {
        $stored_options = [];
        global $current_user, $currentModule;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Contacts');
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->outbound_id = uniqid();
        $time = date('Y-m-d H:i:s');

        $ib = new InboundEmail();
        $ib->is_personal = 1;
        $ib->name = 'Test';
        $ib->port = 3309;
        $ib->mailbox = 'empty';
        $ib->created_by = $current_user->id;
        $ib->email_password = 'pass';
        $ib->protocol = 'IMAP';
        $stored_options['outbound_email'] = $this->outbound_id;
        $ib->stored_options = base64_encode(serialize($stored_options));
        $ib->save();
        $this->ib = $ib;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        $GLOBALS['db']->query("DELETE FROM inbound_email WHERE id= '{$this->ib->id}'");
        unset($GLOBALS['mod_strings']);
        unset($this->ib);
    }

    public function testGetAssoicatedInboundAccountForOutboundAccounts()
    {
        global $current_user;
        $ob = new OutboundEmail();
        $ob->id = $this->outbound_id;

        $results = $ob->getAssociatedInboundAccounts($current_user);
        $this->assertEquals($this->ib->id, $results[0], 'Could not retrieve the inbound mail accounts for an outbound account');

        $obEmpty = new OutboundEmail();
        $obEmpty->id = uniqid();

        $empty_results = $obEmpty->getAssociatedInboundAccounts($current_user);
        $this->assertEquals(0, count($empty_results), 'Outbound email account returned for unspecified/empty inbound mail account.');
    }
}
