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
 * Bug49898Test.php
 *
 * This test is for bug 49898.  Basically the plugin code is still dependent on the legacy versions of the SOAP api (soap.php).  As a result,
 * the search_by_module call is expecting a username and password combination.  However, the plugin code cannot supply a username and password, but
 * only has the session id information.  Therefore, as an alternative, it was proposed to have a workaround where if a username is empty, then the password
 * is assumed to be the session id.  This test replicates that check by searching on two modules (Accounts and Contacts) based on an email address
 * derived from the Contact.
 */
class SearchByModuleWithSessionIdTest extends SOAPTestCase
{
    public $contact;
    public $account;
    public $lead;

    /**
     * setUp
     * Override the setup from SOAPTestCase to also create the seed search data for Accounts and Contacts.
     */
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();
        $this->loginLegacy(); // Logging in just before the SOAP call as this will also commit any pending DB changes
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->contacts_users_id = $GLOBALS['current_user']->id;
        $this->contact->save();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->email1 = $this->contact->email1;
        $this->account->save();
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->lead->email1 = $this->contact->email1;
        $this->lead->save();
        $GLOBALS['db']->commit(); // Making sure these changes are committed to the database
    }

    public function testSearchByModuleWithSessionIdHack()
    {
        //Assert that the plugin fix to use a blank user_name and session id as password works
        $modules = ['Contacts', 'Accounts', 'Leads'];
        $result = get_object_vars(
            $this->soapClient->search_by_module(
                '',
                $this->sessionId,
                $this->contact->email1,
                $modules,
                0,
                10
            )
        );
        $this->assertTrue(
            !empty($result) && safeCount($result['entry_list']) == 3,
            'Incorrect number of results returned. HTTP Response: ' . htmlentities($this->soapClient->__getLastResponse(), ENT_COMPAT)
        );

        //Assert that the traditional method of using user_name and password also works
        $result = get_object_vars(
            $this->soapClient->search_by_module(
                'admin',
                md5('asdf'),
                $this->contact->email1,
                $modules,
                0,
                10
            )
        );
        $this->assertTrue(
            !empty($result) && safeCount($result['entry_list']) == 3,
            'Incorrect number of results returned. HTTP Response: ' . htmlentities($this->soapClient->__getLastResponse(), ENT_COMPAT)
        );
    }
}
