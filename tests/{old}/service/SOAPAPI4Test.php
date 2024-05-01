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

class SOAPAPI4Test extends SOAPTestCase
{
    private static $helperObject;
    private $cleanup;

    /**
     * Create test user
     */
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4/soap.php';
        parent::setUp();
        self::$helperObject = new APIv3Helper();
        $this->login();
        $this->cleanup = false;
    }

    protected function tearDown(): void
    {
        if (!empty($this->cleanup)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'UNIT TEST%' ");
            $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        }
        parent::tearDown();
    }

    public function testGetEntryList()
    {
        $contact = SugarTestContactUtilities::createContact();

        $result = $this->soapClient->get_entry_list(
            $this->sessionId,
            'Contacts',
            "contacts.id = '{$contact->id}'",
            '',
            0,
            ['last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'],
            [['name' => 'email_addresses', 'value' => ['id', 'email_address', 'opt_out', 'primary_address']]],
            1,
            0,
            false
        );
        $result = object_to_array_deep($result);

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
        );
    }


    public function testGetEntryListWithFavorites()
    {
        $contact = SugarTestContactUtilities::createContact();
        $sf = new SugarFavorites();
        $sf->id = SugarFavorites::generateGUID('Contacts', $contact->id);
        $sf->module = 'Contacts';
        $sf->record_id = $contact->id;
        $sf->save(false);
        $GLOBALS['db']->commit();
        $this->assertTrue(SugarFavorites::isUserFavorite('Contacts', $contact->id), "The contact wasn't correctly marked as a favorite.");

        $result = $this->soapClient->get_entry_list(
            $this->sessionId,
            'Contacts',
            "contacts.id = '{$contact->id}'",
            '',
            0,
            ['last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'],
            [['name' => 'email_addresses', 'value' => ['id', 'email_address', 'opt_out', 'primary_address']]],
            1,
            0,
            true
        );
        $result = object_to_array_deep($result);

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
        );
    }


    public function testSearchByModule()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);
        $this->cleanup = true;
        $returnFields = ['name', 'id', 'deleted'];
        $searchModules = ['Accounts', 'Contacts', 'Opportunities'];
        $searchString = 'UNIT TEST';
        $offSet = 0;
        $maxResults = 10;

        $results = $this->soapClient->search_by_module(
            $this->sessionId,
            $searchString,
            $searchModules,
            $offSet,
            $maxResults,
            $GLOBALS['current_user']->id,
            $returnFields,
            true,
            false
        );
        $results = object_to_array_deep($results);
        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[0]['id'], 'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[1]['id'], 'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[2]['id'], 'Contacts', $seedData[2]['fieldName']));
        $this->assertEquals($seedData[3]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[3]['id'], 'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[4]['id'], 'Opportunities', $seedData[4]['fieldName']));
    }

    public function testSearchByModuleWithFavorites()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);
        $this->cleanup = true;
        $sf = new SugarFavorites();
        $sf->module = 'Accounts';
        $sf->record_id = $seedData[0]['id'];
        $sf->save(false);

        $sf = new SugarFavorites();
        $sf->module = 'Contacts';
        $sf->record_id = $seedData[2]['id'];
        $sf->save(false);

        $GLOBALS['db']->commit();

        $returnFields = ['name', 'id', 'deleted'];
        $searchModules = ['Accounts', 'Contacts', 'Opportunities'];
        $searchString = 'UNIT TEST';
        $offSet = 0;
        $maxResults = 10;

        $results = $this->soapClient->search_by_module(
            $this->sessionId,
            $searchString,
            $searchModules,
            $offSet,
            $maxResults,
            $GLOBALS['current_user']->id,
            $returnFields,
            true,
            true
        );
        $results = object_to_array_deep($results);
        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[0]['id'], 'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[1]['id'], 'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[2]['id'], 'Contacts', $seedData[2]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[3]['id'], 'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'], $seedData[4]['id'], 'Opportunities', $seedData[4]['fieldName']));
    }


    public function testGetEntries()
    {
        $contact = SugarTestContactUtilities::createContact();

        $this->login();
        $result = $this->soapClient->get_entries(
            $this->sessionId,
            'Contacts',
            [$contact->id],
            ['last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'],
            [['name' => 'email_addresses', 'value' => ['id', 'email_address', 'opt_out', 'primary_address']]]
        );
        $result = object_to_array_deep($result);

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
        );
    }

    /**
     * Test get avaiable modules call
     */
    public function testGetAllAvailableModules()
    {
        $result = $this->soapClient->get_available_modules($this->sessionId);
        $result = object_to_array_deep($result);
        $actual = $result['modules'][0];
        $this->assertArrayHasKey('module_key', $actual);
        $this->assertArrayHasKey('module_label', $actual);
        $this->assertArrayHasKey('acls', $actual);
        $this->assertArrayHasKey('favorite_enabled', $actual);

        $result = $this->soapClient->get_available_modules($this->sessionId, 'all');
        $result = object_to_array_deep($result);
        $actual = $result['modules'][0];
        $this->assertArrayHasKey('module_key', $actual);
        $this->assertArrayHasKey('module_label', $actual);
        $this->assertArrayHasKey('acls', $actual);
        $this->assertArrayHasKey('favorite_enabled', $actual);
    }

    /**
     * Test get avaiable modules call
     */
    public function testGetAvailableModules()
    {
        $result = $this->soapClient->get_available_modules($this->sessionId, 'mobile');
        $result = object_to_array_deep($result);

        foreach ($result['modules'] as $tmpModEntry) {
            $tmpModEntry['module_key'];
            $this->assertTrue(isset($tmpModEntry['acls']));
            $this->assertTrue(isset($tmpModEntry['module_key']));


            $mod = BeanFactory::newBean($tmpModEntry['module_key']);
            $this->assertEquals($mod->isFavoritesEnabled(), $tmpModEntry['favorite_enabled']);
        }
    }
}
