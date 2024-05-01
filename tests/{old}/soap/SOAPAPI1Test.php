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
class SOAPAPI1Test extends SOAPTestCase
{
    private $contact;
    private $meeting;

    /**
     * Create test user
     */
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();
        $this->loginLegacy(); // Logging in just before the SOAP call as this will also commit any pending DB changes
        $this->setupTestContact();
        $this->meeting = SugarTestMeetingUtilities::createMeeting();
    }

    /**
     * Remove anything that was used during this test
     */
    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->contact = null;
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingContacts();
        $this->meeting = null;
        SugarTestReportUtilities::removeAllCreatedReports();
        parent::tearDown();
    }

    /**
     * Ensure we can create a session on the server.
     */
    public function testCanLogin()
    {
        $result = $this->loginLegacy();
        $this->assertTrue(
            !empty($result->id) && $result->id != -1,
            'SOAP Session not created. Error (' . htmlentities($this->soapClient->__getLastResponse(), ENT_COMPAT) . ')'
        );
    }

    public function testSearchContactByEmail()
    {
        $result = $this->soapClient->contact_by_email('admin', md5('asdf'), $this->contact->email1);
        $this->assertTrue(
            !empty($result) && safeCount($result) > 0,
            'Incorrect number of results returned. HTTP Response: ' . htmlentities($this->soapClient->__getLastResponse(), ENT_COMPAT)
        );
        $this->assertEquals($result[0]->name1, $this->contact->first_name, 'Incorrect result found');
    }

    public function testSearchByModule()
    {
        $modules = ['Contacts'];
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
            !empty($result) && safeCount($result['entry_list']) > 0,
            'Incorrect number of results returned. HTTP Response: ' . htmlentities($this->soapClient->__getLastResponse(), ENT_COMPAT)
        );
        $this->assertEquals(
            'first_name',
            $result['entry_list'][0]->name_value_list[1]->name,
            'Incorrect field returned'
        );
        $this->assertEquals(
            $this->contact->first_name,
            $result['entry_list'][0]->name_value_list[1]->value,
            'Incorrect result returned'
        );
    }

    public function testGetModifiedEntries()
    {
        $ids = [$this->contact->id];
        $result = $this->soapClient->get_modified_entries($this->sessionId, 'Contacts', $ids, []);
        $decoded = base64_decode($result->result);
        $decoded = simplexml_load_string($decoded);
        $this->assertEquals($this->contact->id, $decoded->item->id, 'Incorrect entry returned.');
    }

    public function testGetAttendeeList()
    {
        $this->meeting->load_relationship('contacts');
        $this->meeting->contacts->add($this->contact->id);
        $GLOBALS['db']->commit();
        $result = $this->soapClient->get_attendee_list($this->sessionId, 'Meetings', $this->meeting->id);
        $decoded = base64_decode($result->result);
        $decoded = simplexml_load_string($decoded);
        $this->assertTrue(
            !empty($result->result),
            'Results not returned. HTTP Response: ' . htmlentities($this->soapClient->__getLastResponse(), ENT_COMPAT)
        );
        $this->assertEquals(urldecode($decoded->attendee->first_name), $this->contact->first_name, 'Incorrect Result returned expected: ' . $this->contact->first_name . ' Found: ' . urldecode($decoded->attendee->first_name));
    }

    public function testSyncGetModifiedRelationships()
    {
        $ids = [$this->contact->id];
        $yesterday = date('Y-m-d', strtotime('last year'));
        $tomorrow = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')));
        $result = $this->soapClient->sync_get_modified_relationships($this->sessionId, 'Users', 'Contacts', $yesterday, $tomorrow, 0, 10, 0, $GLOBALS['current_user']->id, [], $ids, 'contacts_users', $yesterday, 0);
        $this->assertTrue(
            !empty($result->entry_list),
            'Results not returned. HTTP Response: ' . htmlentities($this->soapClient->__getLastResponse(), ENT_COMPAT)
        );
        $decoded = base64_decode($result->entry_list);
        $decoded = simplexml_load_string($decoded);
        if (isset($decoded->item[0])) {
            $this->assertEquals(urlencode($decoded->item->name_value_list->name_value[1]->name), 'contact_id', 'testSyncGetModifiedRelationships - could not retrieve contact_id column name');
            $this->assertEquals(urlencode($decoded->item->name_value_list->name_value[1]->value), $this->contact->id, 'vlue of contact id is not same as returned via SOAP');
        }
    }

    public function testGetReportEntry()
    {
        /**
         * Report defs for generating the report
         */
        $reportDef = [
            'display_columns' => [
                0 => [
                    'name' => 'id',
                    'label' => 'ID',
                    'table_key' => 'self',
                ],
                1 => [
                    'name' => 'name',
                    'label' => 'Name',
                    'table_key' => 'self',
                ],
            ],
            'module' => 'Users',
            'group_defs' => [],
            'summary_columns' => [],
            'report_name' => 'BR-8874 test report',
            'chart_type' => 'none',
            'do_round' => 1,
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Users',
                    'module' => 'Users',
                    'label' => 'Users',
                    'dependents' => [],
                ],
            ],
            'filters_def' => [],
        ];
        $properties = [
            'module' => 'Users',
            'report_type' => 'tabular',
            'content' => htmlentities(\JSON::encode($reportDef), ENT_COMPAT),
        ];
        $report = SugarTestReportUtilities::createReport('', $properties);

        $result = $this->soapClient->get_entry($this->sessionId, 'Reports', $report->id);
        $this->assertNotEmpty($result);
    }

    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    private function setupTestContact()
    {
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->contacts_users_id = $GLOBALS['current_user']->id;
        $this->contact->save();
        $GLOBALS['db']->commit(); // Making sure these changes are committed to the database
    }
}
