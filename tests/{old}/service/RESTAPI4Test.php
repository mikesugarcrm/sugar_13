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

class RESTAPI4Test extends TestCase
{
    private $user;
    private $adminUser;
    private $lastRawResponse;

    private static $helperObject;

    protected $aclRole;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        //Create an anonymous user for login purposes/
        $this->user = SugarTestHelper::setUp('current_user');

        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');


        $this->adminUser = SugarTestUserUtilities::createAnonymousUser();
        $this->adminUser->status = 'Active';
        $this->adminUser->is_admin = 1;
        $this->adminUser->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        self::$helperObject = new APIv3Helper();

        //Disable access to the website field.
        $this->aclRole = new ACLRole();
        $this->aclRole->name = 'Unit Test';
        $this->aclRole->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        $this->aclRole->set_relationship('acl_roles_users', ['role_id' => $this->aclRole->id, 'user_id' => $this->user->id], false);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
        ACLField::setAccessControl('Accounts', $this->aclRole->id, 'website', -99);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
        ACLField::loadUserFields('Accounts', 'Account', $this->user->id, true);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id IN ( SELECT id FROM acl_roles WHERE id IN ( SELECT role_id FROM acl_roles_users WHERE user_id = '{$GLOBALS['current_user']->id}' ) )");
        $GLOBALS['db']->query("DELETE FROM acl_roles WHERE id IN ( SELECT role_id FROM acl_roles_users WHERE user_id = '{$GLOBALS['current_user']->id}' )");
        $GLOBALS['db']->query("DELETE FROM acl_roles_users WHERE user_id = '{$GLOBALS['current_user']->id}'");

        if (isset($GLOBALS['listViewDefs'])) {
            unset($GLOBALS['listViewDefs']);
        }
        if (isset($GLOBALS['viewdefs'])) {
            unset($GLOBALS['viewdefs']);
        }
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['mod_strings']);
        SugarTestHelper::tearDown();
    }

    private function makeRESTCall($method, $parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'] . '/service/v4/rest.php';
        // Open a curl session for making the call
        $curl = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        // build the request URL
        $json = json_encode($parameters);
        $postArgs = "method=$method&input_type=JSON&response_type=JSON&rest_data=$json";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        // Make the REST call, returning the result
        $response = curl_exec($curl);
        // Close the connection
        curl_close($curl);

        $this->lastRawResponse = $response;

        // Convert the result from JSON format to a PHP array
        return json_decode($response, true);
    }

    private function returnLastRawResponse()
    {
        return "Error in web services call. Response was: {$this->lastRawResponse}";
    }

    private function login($user = null)
    {
        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
        if ($user == null) {
            $user = $this->user;
        }
        return $this->makeRESTCall(
            'login',
            [
                'user_auth' => [
                    'user_name' => $user->user_name,
                    'password' => $user->user_hash,
                    'version' => '.01',
                ],
                'application_name' => 'mobile',
                'name_value_list' => [],
            ]
        );
    }

    /**
     * Test the get_entry_list call with Export access disabled to ensure results are returned.
     */
    public function testGetEntryListWithExportRole()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();

        //Set the Export Role to no access for user.
        $aclRole = new ACLRole();
        $aclRole->name = 'Unit Test Export';
        $aclRole->save();
        $aclRole->set_relationship('acl_roles_users', ['role_id' => $aclRole->id, 'user_id' => $this->user->id], false);
        $role_actions = $aclRole->getRoleActions($aclRole->id);
        $action_id = $role_actions['Accounts']['module']['export']['id'];
        $aclRole->setAction($aclRole->id, $action_id, -99);

        $result = $this->login($this->user);
        $session = $result['id'];

        $module = 'Accounts';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = ['name'];
        $linkNameFields = '';
        $maxResults = 2;
        $deleted = false;
        $favorites = false;
        $result = $this->makeRESTCall('get_entry_list', [$session, $module, '', $orderBy, $offset, $returnFields, $linkNameFields, $maxResults, $deleted, $favorites]);

        $this->assertFalse(isset($result['name']));
        if (isset($result['name'])) {
            $this->assertNotEquals('Access Denied', $result['name']);
        }
    }

    /**
     * Test the ability to retrieve quote PDFs
     */
    public function testGetQuotesPDF()
    {
        $log_result = $this->login($this->adminUser);
        $session = $log_result['id'];

        //Retrieve a list of quote ids to work with
        $whereClause = '';
        $module = 'Quotes';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = ['id'];
        $linkNameFields = '';
        $maxResults = 2;
        $deleted = false;
        $favorites = false;
        $list_result = $this->makeRESTCall('get_entry_list', [$session, $module, $whereClause, $orderBy, $offset, $returnFields, $linkNameFields, $maxResults, $deleted, $favorites]);

        //Test for standard oob layouts
        foreach ($list_result['entry_list'] as $entry) {
            $quote_id = $entry['id'];
            $result = $this->makeRESTCall('get_quotes_pdf', [$session, $quote_id, 'Standard']);
            $this->assertTrue(!empty($result['file_contents']));
        }

        //Test for a fake pdf type.
        if (safeCount($list_result['entry_list']) > 0) {
            $quote_id = $list_result['entry_list'][0]['id'];
            $result = $this->makeRESTCall('get_quotes_pdf', [$session, $quote_id, 'Fake']);
            $this->assertTrue(!empty($result['file_contents']));
        }

        //Test for a fake bean.
        $result = $this->makeRESTCall('get_quotes_pdf', [$session, '-1', 'Standard']);
        $this->assertTrue(!empty($result['file_contents']));
    }

    /**
     * Ensure the ability to retrieve a module list of recrods that are favorites.
     */
    public function testGetModuleFavoriteList()
    {
        $account = new Account();
        $account->id = uniqid();
        $account->new_with_id = true;
        $account->name = 'Test ' . $account->id;
        $account->save();

        $result = $this->login($this->adminUser); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];

        $this->markBeanAsFavorite($session, 'Accounts', $account->id);

        $whereClause = "accounts.name='{$account->name}'";
        $module = 'Accounts';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = ['name'];
        $linkNameFields = '';
        $maxResults = 50;
        $deleted = false;
        $favorites = true;
        $result = $this->makeRESTCall('get_entry_list', [$session, $module, $whereClause, $orderBy, $offset, $returnFields, $linkNameFields, $maxResults, $deleted, $favorites]);

        $this->assertEquals($account->id, $result['entry_list'][0]['id'], 'Unable to retrieve account favorite list.');

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account->id}'");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account->id}'");
    }

    /**
     * Test set entries call with name value list format key=>value.
     */
    public function testSetEntriesCall()
    {
        $result = $this->login();
        $session = $result['id'];
        $module = 'Contacts';
        $c1_uuid = uniqid();
        $c2_uuid = uniqid();
        $contacts = [
            ['first_name' => 'Unit Test', 'last_name' => $c1_uuid],
            ['first_name' => 'Unit Test', 'last_name' => $c2_uuid],
        ];
        $results = $this->makeRESTCall(
            'set_entries',
            [
                'session' => $session,
                'module' => $module,
                'name_value_lists' => $contacts,
            ]
        );
        $this->assertTrue(isset($results['ids']) && safeCount($results['ids']) == 2);

        $actual_results = $this->makeRESTCall(
            'get_entries',
            [
                'session' => $session,
                'module' => $module,
                'ids' => $results['ids'],
                'select_fields' => ['first_name', 'last_name'],
            ]
        );

        $this->assertTrue(isset($actual_results['entry_list']) && safeCount($actual_results['entry_list']) == 2);
        $this->assertEquals($actual_results['entry_list'][0]['name_value_list']['last_name']['value'], $c1_uuid);
        $this->assertEquals($actual_results['entry_list'][1]['name_value_list']['last_name']['value'], $c2_uuid);
    }


    /**
     * Test search by module with favorites flag enabled.
     */
    public function testSearchByModuleWithFavorites()
    {
        $account = new Account();
        $account->id = uniqid();
        $account->assigned_user_id = $this->user->id;
        $account->team_id = 1;
        $account->new_with_id = true;
        $account->name = 'Unit Test Fav ' . $account->id;
        $account->save();

        //Negative test.
        $account2 = new Account();
        $account2->id = uniqid();
        $account2->new_with_id = true;
        $account2->name = 'Unit Test Fav ' . $account->id;
        $account->assigned_user_id = $this->user->id;
        $account2->save();

        $result = $this->login($this->adminUser); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];

        $this->markBeanAsFavorite($session, 'Accounts', $account->id);

        $searchModules = ['Accounts'];
        $searchString = 'Unit Test Fav ';
        $offSet = 0;
        $maxResults = 10;

        $results = $this->makeRESTCall(
            'search_by_module',
            [
                'session' => $session,
                'search_string' => $searchString,
                'modules' => $searchModules,
                'offset' => $offSet,
                'max_results' => $maxResults,
                'assigned_user_id' => $this->user->id,
                'select_fields' => [],
                'unified_search_only' => true,
                'favorites' => true,
            ]
        );

        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'Unit Test %' ");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account->id}'");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account2->id}'");

        $this->assertTrue(self::$helperObject->findBeanIdFromEntryList($results['entry_list'], $account->id, 'Accounts'), "Unable to find {$account->id} id in favorites search.");
        $this->assertFalse(self::$helperObject->findBeanIdFromEntryList($results['entry_list'], $account2->id, 'Accounts'), "Account {$account2->id} id in favorites search should not be there.");
    }

    public static function aclEditViewFieldProvider()
    {
        return [
            ['Accounts', 'wireless', 'edit', ['name' => 99, 'website' => -99, 'phone_office' => 99, 'email1' => 99, 'nofield' => null]],
            ['Contacts', 'wireless', 'edit', ['first_name' => 99, 'last_name' => 99]],
            ['Reports', 'wireless', 'edit', ['name' => 99]],

            ['Accounts', 'wireless', 'detail', ['name' => 99, 'website' => -99, 'phone_office' => 99, 'email1' => 99, 'nofield' => null]],
            ['Contacts', 'wireless', 'detail', ['first_name' => 99, 'last_name' => 99]],
            ['Reports', 'wireless', 'detail', ['name' => 99]],
        ];
    }

    /**
     * @dataProvider aclEditViewFieldProvider
     */
    public function testMetadataEditViewFieldLevelACLS($module, $view_type, $view, $expected_fields)
    {
        $result = $this->login();
        $session = $result['id'];

        $results = $this->makeRESTCall(
            'get_module_layout',
            [
                'session' => $session,
                'module' => [$module],
                'type' => [$view_type],
                'view' => [$view],
            ]
        );

        if ($view == 'list') {
            $fields = $results[$module][$view_type][$view];
        } else {
            $fields = $results[$module][$view_type][$view]['panels'];
        }

        foreach ($fields as $field_row) {
            foreach ($field_row as $field_def) {
                if (isset($expected_fields[$field_def['name']])) {
                    $this->assertEquals($expected_fields[$field_def['name']], $field_def['acl']);
                    break;
                }
            }
        }
    }

    public static function aclListViewFieldProvider()
    {
        return [
            ['Accounts', 'wireless', ['name' => 99, 'website' => -99, 'phone_office' => 99, 'email1' => 99]],
            ['Contacts', 'wireless', ['name' => 99, 'title' => 99]],
            ['Reports', 'wireless', ['name' => 99]],
        ];
    }

    /**
     * @dataProvider aclListViewFieldProvider
     */
    public function testMetadataListViewFieldLevelACLS($module, $view_type, $expected_fields)
    {
        $result = $this->login();
        $session = $result['id'];
        $results = $this->makeRESTCall(
            'get_module_layout',
            [
                'session' => $session,
                'module' => [$module],
                'type' => [$view_type],
                'view' => ['list'],
            ]
        );

        $fields = $results[$module][$view_type]['list'];

        foreach ($fields as $field_name => $field_row) {
            $tmpName = strtolower($field_name);
            if (isset($expected_fields[$tmpName])) {
                $this->assertEquals($expected_fields[$tmpName], $field_row['acl']);
            }
        }
    }

    /**
     * Private helper function to mark a bean as a favorite item.
     *
     * @param string $session
     * @param string $moduleName
     * @param string $recordID
     */
    private function markBeanAsFavorite($session, $moduleName, $recordID)
    {
        $this->makeRESTCall(
            'set_entry',
            [
                'session' => $session,
                'module' => 'SugarFavorites',
                'name_value_list' => [
                    ['name' => 'record_id', 'value' => $recordID],
                    ['name' => 'module', 'value' => $moduleName],
                ],
            ]
        );
    }


    public function testRelateAccountToTwoContacts()
    {
        $result = $this->login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1, $this->returnLastRawResponse());
        $session = $result['id'];

        $result = $this->makeRESTCall(
            'set_entry',
            [
                'session' => $session,
                'module' => 'Accounts',
                'name_value_list' => [
                    ['name' => 'name', 'value' => 'New Account'],
                    ['name' => 'description', 'value' => 'This is an account created from a REST web services call'],
                ],
            ]
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1, $this->returnLastRawResponse());

        $accountId = $result['id'];

        $result = $this->makeRESTCall(
            'set_entry',
            [
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => [
                    ['name' => 'last_name', 'value' => 'New Contact 1'],
                    ['name' => 'description', 'value' => 'This is a contact created from a REST web services call'],
                ],
            ]
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1, $this->returnLastRawResponse());

        $contactId1 = $result['id'];

        $result = $this->makeRESTCall(
            'set_entry',
            [
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => [
                    ['name' => 'last_name', 'value' => 'New Contact 2'],
                    ['name' => 'description', 'value' => 'This is a contact created from a REST web services call'],
                ],
            ]
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1, $this->returnLastRawResponse());

        $contactId2 = $result['id'];

        // now relate them together
        $result = $this->makeRESTCall(
            'set_relationship',
            [
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_ids' => [$contactId1, $contactId2],
            ]
        );

        $this->assertEquals($result['created'], 1, $this->returnLastRawResponse());

        // check the relationship
        $result = $this->makeRESTCall(
            'get_relationships',
            [
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                'related_fields' => ['last_name', 'description'],
                'related_module_link_name_to_fields_array' => [],
                'deleted' => false,
            ]
        );

        $returnedValues = [];
        $returnedValues[] = $result['entry_list'][0]['name_value_list']['last_name']['value'];
        $returnedValues[] = $result['entry_list'][1]['name_value_list']['last_name']['value'];

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$accountId}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId1}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId2}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE account_id= '{$accountId}'");

        $this->assertContains('New Contact 1', $returnedValues, $this->returnLastRawResponse());
        $this->assertContains('New Contact 2', $returnedValues, $this->returnLastRawResponse());
    }

    /**
     * Test SQL injection bug in get_entries
     */
    public function testGetEntriesProspectFilter()
    {
        $result = $this->login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1, $this->returnLastRawResponse());
        $session = $result['id'];

        $result = $this->makeRESTCall(
            'get_entries',
            [
                'session' => $session,
                'module' => 'CampaignProspects',
                'ids' => ["' UNION SELECT id related_id, 'Users' related_type FROM users WHERE '1'='1"],
            ]
        );
        $this->assertNull($result);
    }

    public static function wirelessGridModuleLayoutProvider()
    {
        return [
            ['module' => 'Accounts', 'view' => 'edit', 'metadatafile' => 'modules/Accounts/clients/mobile/views/edit/edit.php',],
            ['module' => 'Accounts', 'view' => 'detail', 'metadatafile' => 'modules/Accounts/clients/mobile/views/detail/detail.php',],
        ];
    }

    /**
     * Leaving as a provider in the event we need to extend it in the future
     *
     * @static
     * @return array
     */
    public static function wirelessListModuleLayoutProvider()
    {
        return [
            ['module' => 'Cases'],
        ];
    }

    /**
     * @dataProvider wirelessListModuleLayoutProvider
     */
    public function testGetWirelessListModuleLayout($module)
    {
        $listViewDefs = [];
        $convertedKeys = [];
        $result = $this->login();
        $session = $result['id'];

        $type = 'wireless';
        $view = 'list';

        $result = $this->makeRESTCall(
            'get_module_layout',
            [
                'session' => $session,
                'module' => [$module],
                'type' => [$type],
                'view' => [$view],
            ]
        );

        // This is carried over metadata from pre-6.6 OOTB installations
        // This test if for backward compatibility with older API clients
        require 'tests/{old}/service/metadata/' . $module . 'legacy' . $view . '.php';

        $legacy = $listViewDefs[$module];

        $this->assertTrue(isset($result[$module][$type][$view]), 'Result did not contain expected data');

        foreach ($result[$module][$type][$view] as $def) {
            $this->assertArrayHasKey('name', $def, 'Name key not found in result definitions');
        }


        $legacyKeys = array_keys($legacy);
        sort($legacyKeys);

        foreach ($result[$module][$type][$view] as $def) {
            $convertedKeys[] = $def['name'];
        }

        sort($convertedKeys);

        $this->assertEquals($legacyKeys, $convertedKeys, 'Converted list def keys not the same as known list def keys');
    }

    /**
     * @dataProvider wirelessGridModuleLayoutProvider
     */
    public function testGetWirelessGridModuleLayout($module, $view, $metadatafile)
    {
        $viewdefs = [];
        $result = $this->login();
        $session = $result['id'];

        $type = 'wireless';
        $result = $this->makeRESTCall(
            'get_module_layout',
            [
                'session' => $session,
                'module' => [$module],
                'type' => [$type],
                'view' => [$view],
            ]
        );
        require 'tests/{old}/service/metadata/' . $module . 'legacy' . $view . '.php';

        // This is carried over metadata from pre-6.6 OOTB installations
        $legacy = $viewdefs[$module][ucfirst($view) . 'View'];
        unset($viewdefs); // Prevent clash with current viewdefs

        // Get our current OOTB metadata
        require $metadatafile;
        $current = $viewdefs[$module]['mobile']['view'][$view];

        $legacyFields = $legacy['panels'];
        $currentFields = $current['panels'][0]['fields'];

        $this->assertArrayHasKey('panels', $result[$module][$type][$view], 'REST call result does not have a panels array');

        $panels = $result[$module][$type][$view]['panels'];
        $this->assertTrue(isset($panels[0][0]['name']), 'No name index in the first row array of panel fields');
        $this->assertSameSize($legacyFields, $currentFields);
    }
}
