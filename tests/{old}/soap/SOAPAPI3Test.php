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
 * This class is meant to test everything SOAP
 */
class SOAPAPI3Test extends SOAPTestCase
{
    private static $helperObject;

    /**
     * Create test user
     */
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v3/soap.php';
        parent::setUp();
        $this->login();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        self::$helperObject = new APIv3Helper();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        unset($GLOBALS['reload_vardefs']);
        parent::tearDown();
    }

    /**
     * Ensure we can create a session on the server.
     */
    public function testCanLogin()
    {
        $result = $this->login();
        $this->assertTrue(
            !empty($result->id) && $result->id != -1,
            'SOAP Session not created. Error (' . $this->soapClient->__getLastResponse() . ')'
        );
    }

    public function testSearchByModule()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest('1');

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
            '1'
        );

        $this->assertTrue(self::$helperObject->findBeanIdFromEntryList($results->entry_list, $seedData[0]['id'], 'Accounts'));
        $this->assertFalse(self::$helperObject->findBeanIdFromEntryList($results->entry_list, $seedData[1]['id'], 'Accounts'));
        $this->assertTrue(self::$helperObject->findBeanIdFromEntryList($results->entry_list, $seedData[2]['id'], 'Contacts'));
        $this->assertTrue(self::$helperObject->findBeanIdFromEntryList($results->entry_list, $seedData[3]['id'], 'Opportunities'));
        $this->assertFalse(self::$helperObject->findBeanIdFromEntryList($results->entry_list, $seedData[4]['id'], 'Opportunities'));
    }

    public function testSearchByModuleWithReturnFields()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest('1');

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
            '1',
            $returnFields
        );

        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results->entry_list, $seedData[0]['id'], 'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results->entry_list, $seedData[1]['id'], 'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results->entry_list, $seedData[2]['id'], 'Contacts', $seedData[2]['fieldName']));
        $this->assertEquals($seedData[3]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results->entry_list, $seedData[3]['id'], 'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results->entry_list, $seedData[4]['id'], 'Opportunities', $seedData[4]['fieldName']));
    }

    public function testGetVardefsMD5()
    {
        $GLOBALS['reload_vardefs'] = true;
        //Test a regular module
        $result = $this->getVardefsMD5('Accounts');
        $a = new Account();
        $soapHelper = new SugarWebServiceUtilv3();
        $actualVardef = $soapHelper->get_return_module_fields($a, 'Accounts', '');
        $actualMD5 = md5(serialize($actualVardef));
        $this->assertEquals($actualMD5, $result[0], 'Unable to retrieve vardef md5.');
    }

    public function testGetUpcomingActivities()
    {
        $expected = $this->createUpcomingActivities(); //Seed the data.
        $results = $this->soapClient->get_upcoming_activities($this->sessionId);
        $this->removeUpcomingActivities();

        $this->assertEquals(
            $expected[0],
            $results[1]->id,
            'Unable to get upcoming activities Error (' . $this->soapClient->__getLastResponse() . ')'
        );
        $this->assertEquals(
            $expected[1],
            $results[2]->id,
            'Unable to get upcoming activities Error (' . $this->soapClient->__getLastResponse() . ')'
        );
    }

    public function testSetEntriesForAccount()
    {
        $result = $this->setEntriesForAccount();
        $this->assertTrue(
            !empty($result['ids']) && $result['ids'][0] != -1,
            'Can not create new account using testSetEntriesForAccount. Error (' . $this->soapClient->__getLastResponse() . ')'
        );
    } // fn

    /**
     * @depends testSetEntriesForAccount
     */
    public function testGetLastViewed()
    {
        $testModule = 'Accounts';
        $testModuleID = create_guid();

        $this->createTrackerEntry($testModule, $testModuleID);

        $this->login();
        $results = $this->soapClient->get_last_viewed($this->sessionId, [$testModule]);

        $found = false;
        foreach ($results as $entry) {
            if ($entry->item_id == $testModuleID) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Unable to get last viewed modules');
    }

    private function createTrackerEntry($module, $id, $summaryText = 'UNIT TEST SUMMARY')
    {
        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unPause();

        $timeStamp = TimeDate::getInstance()->nowDb();
        $monitor = $trackerManager->getMonitor('tracker');
        $monitor->setValue('team_id', self::$user->getPrivateTeamID());
        $monitor->setValue('action', 'detail');
        $monitor->setValue('user_id', self::$user->id);
        $monitor->setValue('module_name', $module);
        $monitor->setValue('date_modified', $timeStamp);
        $monitor->setValue('visible', true);
        $monitor->setValue('item_id', $id);
        $monitor->setValue('item_summary', $summaryText);
        $trackerManager->saveMonitor($monitor, true, true);
        $GLOBALS['db']->commit();
    }

    /**
     * Get Module Layout functions not exposed to soap service, make sure they are not available.
     */
    public function testGetModuleLayoutMD5()
    {
        $result = $this->getModuleLayoutMD5();
        $this->assertStringContainsString('Server', $result);
    }

    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    private function removeUpcomingActivities()
    {
        $GLOBALS['db']->query("DELETE FROM calls where name = 'UNIT TEST'");
        $GLOBALS['db']->query("DELETE FROM tasks where name = 'UNIT TEST'");
    }

    private function createUpcomingActivities()
    {
        $GLOBALS['current_user']->setPreference('datef', 'Y-m-d');
        $GLOBALS['current_user']->setPreference('timef', 'H:i');
        global $timedate;
        $date1 = $timedate->asUser($timedate->getNow()->modify('+2 days'));
        $date2 = $timedate->asUser($timedate->getNow()->modify('+4 days'));

        $callID = uniqid();
        $c = new Call();
        $c->id = $callID;
        $c->new_with_id = true;
        $c->status = 'Planned';
        $c->date_start = $date1;
        $c->name = 'UNIT TEST';
        $c->assigned_user_id = '1';
        $c->save(false);

        $callID = uniqid();
        $c = new Call();
        $c->id = $callID;
        $c->new_with_id = true;
        $c->status = 'Planned';
        $c->date_start = $date1;
        $c->name = 'UNIT TEST';
        $c->assigned_user_id = '1';
        $c->save(false);

        $taskID = uniqid();
        $t = new Task();
        $t->id = $taskID;
        $t->new_with_id = true;
        $t->status = 'Not Started';
        $t->date_due = $date2;
        $t->name = 'UNIT TEST';
        $t->assigned_user_id = '1';
        $t->save(false);
        $GLOBALS['db']->commit();

        return [$callID, $taskID];
    }

    private function getVardefsMD5($module)
    {
        $result = $this->soapClient->get_module_fields_md5($this->sessionId, [$module]);
        return $result;
    }

    private function getModuleLayoutMD5()
    {
        $result = null;
        try {
            // request without WSDL to make sure that it is rejected by server, not clientside schema validation
            $this->soapClientNoWsdl->get_module_layout_md5($this->sessionId, ['Accounts'], ['default'], ['list']);
        } catch (SoapFault $exception) {
            $result = $exception->detail;
        }
        return $result;
    }

    private function setEntriesForAccount()
    {
        $time = random_int(0, mt_getrandmax());
        $name = 'SugarAccount' . $time;
        $email1 = 'account@' . $time . 'sugar.com';
        $result = get_object_vars($this->soapClient->set_entries($this->sessionId, 'Accounts', [[['name' => 'name', 'value' => $name], ['name' => 'email1', 'value' => $email1]]]));
        $soap_version_test_accountId = $result['ids'][0];
        SugarTestAccountUtilities::setCreatedAccount([$soap_version_test_accountId]);
        return $result;
    } // fn
}
