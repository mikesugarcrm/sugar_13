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

class Bug42378Test extends TestCase
{
    public $saved_search_id;

    protected function setUp(): void
    {
        $this->saved_search_id = md5(time());
        //Safety cleanup
        $GLOBALS['db']->query("DELETE FROM saved_search where name = 'Bug42378Test'");

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $datetime_prefs = $GLOBALS['current_user']->getUserDateTimePreferences();
        $GLOBALS['current_user']->setPreference('datef', 'm/d/Y', 0, 'global');
        $GLOBALS['current_user']->save();

        $GLOBALS['db']->query(
            "INSERT INTO saved_search (team_id, team_set_id, id, name, search_module, deleted, date_entered, date_modified, assigned_user_id, contents) VALUES ('1', '1', '" . $this->saved_search_id . "', 'Bug42378Test', 'Opportunities', 0, '2011-03-10 17:05:27', '2011-03-10 17:05:27', '" . $GLOBALS['current_user']->id . "', 'YTo0OTp7czoxMzoic2VhcmNoRm9ybVRhYiI7czoxNToiYWR2YW5jZWRfc2VhcmNoIjtzOjU6InF1ZXJ5IjtzOjQ6InRydWUiO3M6MTM6Im5hbWVfYWR2YW5jZWQiO3M6MDoiIjtzOjIxOiJhY2NvdW50X25hbWVfYWR2YW5jZWQiO3M6MDoiIjtzOjM0OiJjdXN0b21kYXRlX2NfYWR2YW5jZWRfcmFuZ2VfY2hvaWNlIjtzOjE6Ij0iO3M6Mjc6InJhbmdlX2N1c3RvbWRhdGVfY19hZHZhbmNlZCI7czoxMDoiMDMvMDEvMjAxMSI7czozMzoic3RhcnRfcmFuZ2VfY3VzdG9tZGF0ZV9jX2FkdmFuY2VkIjtzOjA6IiI7czozMToiZW5kX3JhbmdlX2N1c3RvbWRhdGVfY19hZHZhbmNlZCI7czowOiIiO3M6Mzg6ImN1c3RvbWRhdGV0aW1lX2NfYWR2YW5jZWRfcmFuZ2VfY2hvaWNlIjtzOjE6Ij0iO3M6MzE6InJhbmdlX2N1c3RvbWRhdGV0aW1lX2NfYWR2YW5jZWQiO3M6MTA6IjAzLzAyLzIwMTEiO3M6Mzc6InN0YXJ0X3JhbmdlX2N1c3RvbWRhdGV0aW1lX2NfYWR2YW5jZWQiO3M6MDoiIjtzOjM1OiJlbmRfcmFuZ2VfY3VzdG9tZGF0ZXRpbWVfY19hZHZhbmNlZCI7czowOiIiO3M6Mjg6ImFtb3VudF9hZHZhbmNlZF9yYW5nZV9jaG9pY2UiO3M6MToiPSI7czoyMToicmFuZ2VfYW1vdW50X2FkdmFuY2VkIjtzOjA6IiI7czoyNzoic3RhcnRfcmFuZ2VfYW1vdW50X2FkdmFuY2VkIjtzOjA6IiI7czoyNToiZW5kX3JhbmdlX2Ftb3VudF9hZHZhbmNlZCI7czowOiIiO3M6MzQ6ImRhdGVfZW50ZXJlZF9hZHZhbmNlZF9yYW5nZV9jaG9pY2UiO3M6NzoiYmV0d2VlbiI7czoyNzoicmFuZ2VfZGF0ZV9lbnRlcmVkX2FkdmFuY2VkIjtzOjA6IiI7czozMzoic3RhcnRfcmFuZ2VfZGF0ZV9lbnRlcmVkX2FkdmFuY2VkIjtzOjEwOiIwMy8wMS8yMDExIjtzOjMxOiJlbmRfcmFuZ2VfZGF0ZV9lbnRlcmVkX2FkdmFuY2VkIjtzOjEwOiIwMy8wNS8yMDExIjtzOjM1OiJkYXRlX21vZGlmaWVkX2FkdmFuY2VkX3JhbmdlX2Nob2ljZSI7czoxMjoiZ3JlYXRlcl90aGFuIjtzOjI4OiJyYW5nZV9kYXRlX21vZGlmaWVkX2FkdmFuY2VkIjtzOjEwOiIwMy8wMS8yMDExIjtzOjM0OiJzdGFydF9yYW5nZV9kYXRlX21vZGlmaWVkX2FkdmFuY2VkIjtzOjA6IiI7czozMjoiZW5kX3JhbmdlX2RhdGVfbW9kaWZpZWRfYWR2YW5jZWQiO3M6MDoiIjtzOjMzOiJkYXRlX2Nsb3NlZF9hZHZhbmNlZF9yYW5nZV9jaG9pY2UiO3M6MTE6Imxhc3RfN19kYXlzIjtzOjI2OiJyYW5nZV9kYXRlX2Nsb3NlZF9hZHZhbmNlZCI7czoxMzoiW2xhc3RfN19kYXlzXSI7czozMjoic3RhcnRfcmFuZ2VfZGF0ZV9jbG9zZWRfYWR2YW5jZWQiO3M6MDoiIjtzOjMwOiJlbmRfcmFuZ2VfZGF0ZV9jbG9zZWRfYWR2YW5jZWQiO3M6MDoiIjtzOjQzOiJ1cGRhdGVfZmllbGRzX3RlYW1fbmFtZV9hZHZhbmNlZF9jb2xsZWN0aW9uIjtzOjA6IiI7czozMjoidGVhbV9uYW1lX2FkdmFuY2VkX25ld19vbl91cGRhdGUiO3M6NToiZmFsc2UiO3M6MzE6InRlYW1fbmFtZV9hZHZhbmNlZF9hbGxvd191cGRhdGUiO3M6MDoiIjtzOjM1OiJ0ZWFtX25hbWVfYWR2YW5jZWRfYWxsb3dlZF90b19jaGVjayI7czo1OiJmYWxzZSI7czozMToidGVhbV9uYW1lX2FkdmFuY2VkX2NvbGxlY3Rpb25fMCI7czowOiIiO3M6MzQ6ImlkX3RlYW1fbmFtZV9hZHZhbmNlZF9jb2xsZWN0aW9uXzAiO3M6MDoiIjtzOjIzOiJ0ZWFtX25hbWVfYWR2YW5jZWRfdHlwZSI7czozOiJhbnkiO3M6MjM6ImZhdm9yaXRlc19vbmx5X2FkdmFuY2VkIjtzOjE6IjAiO3M6OToic2hvd1NTRElWIjtzOjI6Im5vIjtzOjEzOiJzZWFyY2hfbW9kdWxlIjtzOjEzOiJPcHBvcnR1bml0aWVzIjtzOjE5OiJzYXZlZF9zZWFyY2hfYWN0aW9uIjtzOjQ6InNhdmUiO3M6MTQ6ImRpc3BsYXlDb2x1bW5zIjtzOjg5OiJOQU1FfEFDQ09VTlRfTkFNRXxTQUxFU19TVEFHRXxBTU9VTlRfVVNET0xMQVJ8REFURV9DTE9TRUR8QVNTSUdORURfVVNFUl9OQU1FfERBVEVfRU5URVJFRCI7czo4OiJoaWRlVGFicyI7czo5MzoiT1BQT1JUVU5JVFlfVFlQRXxMRUFEX1NPVVJDRXxORVhUX1NURVB8UFJPQkFCSUxJVFl8Q1JFQVRFRF9CWV9OQU1FfFRFQU1fTkFNRXxNT0RJRklFRF9CWV9OQU1FIjtzOjc6Im9yZGVyQnkiO3M6NDoiTkFNRSI7czo5OiJzb3J0T3JkZXIiO3M6MzoiQVNDIjtzOjE2OiJzdWdhcl91c2VyX3RoZW1lIjtzOjU6IlN1Z2FyIjtzOjEzOiJDb250YWN0c19kaXZzIjtzOjE3OiJvcHBvcnR1bml0aWVzX3Y9IyI7czoxMzoiTW9kdWxlQnVpbGRlciI7czoxNToiaGVscEhpZGRlbj10cnVlIjtzOjIyOiJzdWdhcl90aGVtZV9nbV9jdXJyZW50IjtzOjM6IkFsbCI7czoxNToiZ2xvYmFsTGlua3NPcGVuIjtzOjQ6InRydWUiO3M6ODoiYWR2YW5jZWQiO2I6MTt9')"
        );
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM saved_search where id = '{$this->saved_search_id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testStoreQuerySaveAndPopulate()
    {
        global $current_user, $timedate;

        $storeQuery = new StoreQuery();
        //Simulate a search request here
        $_REQUEST =
            [
                'module' => 'Opportunities',
                'action' => 'index',
                'searchFormTab' => 'advanced_search',
                'query' => true,
                'name_advanced' => '',
                'account_name_advanced' => '',
                'amount_advanced_range_choice' => '=',
                'range_amount_advanced' => '',
                'start_range_amount_advanced' => '',
                'end_range_amount_advanced' => '',
                'date_closed_advanced_range_choice' => '=',
                'range_date_closed_advanced' => '09/01/2008',
                'start_range_date_closed_advanced' => '',
                'end_range_date_closed_advanced' => '',
                'next_step_advanced' => '',
                'update_fields_team_name_advanced_collection' => '',
                'team_name_advanced_new_on_update' => false,
                'team_name_advanced_allow_update' => '',
                'team_name_advanced_allowed_to_check' => false,
                'team_name_advanced_collection_0' => '',
                'id_team_name_advanced_collection_0' => '',
                'team_name_advanced_type' => 'any',
                'favorites_only_advanced' => 0,
                'showSSDIV' => 'no',
                'saved_search_name' => '',
                'search_module' => '',
                'saved_search_action' => '',
                'displayColumns' => 'NAME|ACCOUNT_NAME|SALES_STAGE|AMOUNT_USDOLLAR|DATE_CLOSED|ASSIGNED_USER_NAME|DATE_ENTERED',
                'hideTabs' => 'OPPORTUNITY_TYPE|LEAD_SOURCE|NEXT_STEP|PROBABILITY|CREATED_BY_NAME|TEAM_NAME|MODIFIED_BY_NAME',
                'orderBy' => 'NAME',
                'sortOrder' => 'ASC',
                'button' => 'Search',
                'saved_search_select' => '_none',
                'sugar_user_theme' => 'Sugar',
                'ModuleBuilder' => 'helpHidden=true',
                'Contacts_divs' => 'quotes_v=#',
                'sugar_theme_gm_current' => 'All',
                'globalLinksOpen' => 'true',
                'SQLiteManager_currentLangue' => '2',
                'PHPSESSID' => 'b8e4b4b955ef3c4b29291779751b5fca',
            ];

        $storeQuery->saveFromRequest('Opportunities');

        $storedSearch = StoreQuery::getStoredQueryForUser('Opportunities');
        $this->assertEquals(
            $storedSearch['range_date_closed_advanced'],
            '2008-09-01',
            'Assert that search date 09/02/2008 was saved in db format 2008-09-01'
        );

        //Test that value is converted to user date preferences when retrieved
        unset($_REQUEST['range_date_closed_advanced']);
        $storeQuery->loadQuery('Opportunities');
        $storeQuery->populateRequest();
        $this->assertTrue(
            isset($_REQUEST['range_date_closed_advanced']),
            'Assert that the field was correctly populated'
        );
        $this->assertEquals(
            $_REQUEST['range_date_closed_advanced'],
            '09/01/2008',
            'Assert that search date in db_format 2008-09-01 was converted to user date preference 09/01/2008'
        );

        //Now say the user changes his date preferences and switches back to this StoredQuery
        $current_user->setPreference('datef', 'Y.m.d', 0, 'global');
        $current_user->save();

        //Now when we reload this store query, the $_REQUEST array should be populated with new user date preference
        unset($_REQUEST['range_date_closed_advanced']);
        $storeQuery->loadQuery('Opportunities');
        $storeQuery->populateRequest();
        $this->assertTrue(
            isset($_REQUEST['range_date_closed_advanced']),
            'Assert that the field was correctly populated'
        );
        $this->assertEquals(
            $_REQUEST['range_date_closed_advanced'],
            '2008.09.01',
            'Assert that search date in db_format 2008-09-01 was converted to user date preference 2008.09.01'
        );
    }
}
