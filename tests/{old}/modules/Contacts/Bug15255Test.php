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

class Bug15255Test extends TestCase
{
    public $c = null;
    public $a = null;
    public $ac_id = null;

    protected function setUp(): void
    {
        global $current_user, $currentModule;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Contacts');
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $unid = uniqid();
        $time = date('Y-m-d H:i:s');

        $contact = new Contact();
        $contact->id = 'c_' . $unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $this->c = $contact;

        $account = new Account();
        $account->id = 'a_' . $unid;
        $account->first_name = 'testfirst';
        $account->last_name = 'testlast';
        $account->assigned_user_id = 'SugarUser';
        $account->new_with_id = true;
        $account->disable_custom_fields = true;
        $account->save();
        $this->a = $account;

        $ac_id = 'ac_' . $unid;
        $this->ac_id = $ac_id;
        $GLOBALS['db']->query("insert into accounts_contacts (id , contact_id, account_id, date_modified, deleted) values ('{$ac_id}', '{$contact->id}', '{$account->id}', '$time', 0)");
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->a->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE id = '{$this->ac_id}'");

        unset($this->a);
        unset($this->c);
        unset($this->ac_id);
    }

    public function testFill_in_additional_detail_fields()
    {
        $locale = Localization::getObject();
        $this->c->fill_in_additional_detail_fields();
        $localName = $locale->getLocaleFormattedName('testfirst', 'testlast');
        $this->assertEquals($this->c->name, $localName);
        //$this->assertEquals($this->c->name, 'testfirst testlast');
    }
}
