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

class RESTAPI3_1Test extends TestCase
{
    private $user;

    private $lastRawResponse;

    private static $helperObject;

    protected function setUp(): void
    {
        //Reload langauge strings
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
        //Create an anonymous user for login purposes/
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->status = 'Active';
        $this->user->is_admin = 1;
        $this->user->save();
        $GLOBALS['current_user'] = $this->user;

        self::$helperObject = new APIv3Helper();
    }

    protected function tearDown(): void
    {
        if (isset($GLOBALS['listViewDefs'])) {
            unset($GLOBALS['listViewDefs']);
        }
        if (isset($GLOBALS['viewdefs'])) {
            unset($GLOBALS['viewdefs']);
        }
        SugarTestHelper::tearDown();
    }

    private function makeRESTCall($method, $parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'] . '/service/v3_1/rest.php';
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

    private function login()
    {
        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
        return $this->makeRESTCall(
            'login',
            [
                'user_auth' => [
                    'user_name' => $this->user->user_name,
                    'password' => $this->user->user_hash,
                    'version' => '.01',
                ],
                'application_name' => 'mobile',
                'name_value_list' => [],
            ]
        );
    }

    public function testLogin()
    {
        $result = $this->login();
        $this->assertTrue(isset($result['name_value_list']['available_modules']));
        $this->assertTrue(isset($result['name_value_list']['vardefs_md5']));
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1, $this->returnLastRawResponse());
    }

    public function testGetSingleModuleLanguage()
    {
        $result = $this->login();
        $session = $result['id'];

        $results = $this->makeRESTCall(
            'get_language_definition',
            [
                'session' => $session,
                'modules' => 'Accounts',
                'md5' => false,
            ]
        );
        $this->assertTrue(isset($results['Accounts']['LBL_NAME']));
    }

    public function testGetSingleModuleLanguageMD5()
    {
        $result = $this->login();
        $session = $result['id'];

        $results = $this->makeRESTCall(
            'get_language_definition',
            [
                'session' => $session,
                'modules' => 'Accounts',
                'md5' => true,
            ]
        );

        $this->assertTrue(isset($results['Accounts']));
        $this->assertTrue(!empty($results['Accounts']));
    }

    public function testGetMultipleModuleLanguage()
    {
        $result = $this->login();
        $session = $result['id'];

        $results = $this->makeRESTCall(
            'get_language_definition',
            [
                'session' => $session,
                'modules' => ['Accounts', 'Contacts', 'Leads'],
                'md5' => false,
            ]
        );
        $this->assertTrue(isset($results['Accounts']['LBL_NAME']), 'Unable to get multiple module language for Accounts, result: ' . var_export($results['Accounts'], true));
        $this->assertTrue(isset($results['Contacts']['LBL_NAME']), 'Unable to get multiple module language for Contacts, result: ' . var_export($results['Contacts'], true));
        $this->assertTrue(isset($results['Leads']['LBL_LEAD_SOURCE']), 'Unable to get multiple module language for Leads, result: ' . var_export($results['Leads'], true));
    }

    public function testGetMultipleModuleLanguageAndAppStrings()
    {
        $result = $this->login();
        $session = $result['id'];

        $results = $this->makeRESTCall(
            'get_language_definition',
            [
                'session' => $session,
                'modules' => ['Accounts', 'Contacts', 'Leads', 'app_strings', 'app_list_strings'],
                'md5' => false,
            ]
        );

        $this->assertTrue(isset($results['app_strings']['LBL_NO_ACTION']));
        $this->assertTrue(isset($results['app_strings']['LBL_EMAIL_YES']));
        $this->assertTrue(isset($results['app_list_strings']['account_type_dom']));
        $this->assertTrue(isset($results['app_list_strings']['moduleList']));
        $this->assertTrue(isset($results['Contacts']['LBL_NAME']));
        $this->assertTrue(isset($results['Leads']['LBL_LEAD_SOURCE']));
    }

    public function testGetQuotesPDFContents()
    {
        $quote = new Quote();
        $quote->name = 'Test ' . uniqid();
        $quote->date_quote_expected_closed = TimeDate::getInstance()->getNow()->asDbDate();
        $quote->save(false);

        $result = $this->login(); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];

        $results = $this->makeRESTCall(
            'get_quotes_pdf',
            [
                'session' => $session,
                'quote_id' => $quote->id,
                'pdf_format' => 'Standard',
            ]
        );

        $this->assertTrue(!empty($results['file_contents']));
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
        $this->assertArrayHasKey('NAME', $result[$module][$type][$view], 'NAME not found in the REST call result');

        $legacyKeys = array_keys($legacy);
        sort($legacyKeys);

        $convertedKeys = array_keys($result[$module][$type][$view]);
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

    public function testGetEmployee()
    {
        // make sure the current_user isn't an admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->save();

        $whereClause = '';
        $module = 'Employees';
        $orderBy = 'first_name';
        $offset = 0;
        $returnFields = ['id', 'first_name'];

        $result = $this->login(); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];
        $result = $this->makeRESTCall('get_entry_list', [$session, $module, $whereClause, $orderBy, $offset, $returnFields]);
        $this->assertNotEmpty($result, 'Should have returned at least 1 record');

        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
    }
}
