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

require_once 'include/export_utils.php';

class Bug43478Test extends TestCase
{
    /**
     * Contains created prospect lists' ids
     * @var array
     */
    private static $createdProspectListsIds = [];

    /**
     * Instance of ProspectList
     * @var ProspectList
     */
    private $prospectList;

    /**
     * Contacts array
     * @var array
     */
    private $contacts = [];

    /**
     * Create contact instance (with account)
     */
    public static function createContact()
    {
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $contact->account_id = $account->id;
        $contact->primary_address_state = 'Test adress state';
        $contact->save();
        return $contact;
    }

    /**
     * Create ProspectList instance
     * @param Contact instance to attach to prospect list
     */
    public static function createProspectList($contact = null)
    {
        $prospectList = new ProspectList();
        $prospectList->name = 'test';
        $prospectList->save();
        self::$createdProspectListsIds[] = $prospectList->id;

        if ($contact instanceof Contact) {
            self::attachContactToProspectList($prospectList, $contact);
        }

        return $prospectList;
    }

    /**
     * Attach Contact to prospect list
     * @param ProspectList $prospectList prospect list instance
     * @param Contact $contact contact instance
     */
    public static function attachContactToProspectList($prospectList, $contact)
    {
        $prospectList->load_relationship('contacts');
        $prospectList->contacts->add($contact->id, []);
    }

    /**
     * Set up - create prospect list with 2 contacts
     */
    protected function setUp(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $contact = self::createContact();
        $contact->primary_address_state = 'Test adress state';
        $this->contacts[] = $contact;
        $this->contacts[] = self::createContact();
        $this->prospectList = self::createProspectList($this->contacts[0]);
        self::attachContactToProspectList($this->prospectList, $this->contacts[1]);
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->clearProspects();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * Test if email exists within report
     */
    public function testEmailExistsExportList()
    {
        $content = export('ProspectLists', [$this->prospectList->id], true);
        $this->assertStringContainsString('Test adress state', $content);
    }

    private function clearProspects()
    {
        $ids = implode("', '", self::$createdProspectListsIds);
        $GLOBALS['db']->query('DELETE FROM prospect_list_campaigns WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists_prospects WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . $ids . '\')');
    }
}
