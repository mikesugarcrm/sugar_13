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

//Uses the latest version of SugarWebServiceUtil implementation

/**
 * RestTestCase
 *
 * Abstract class to separate out the common setup, tearDown and utility methods for testing REST API calls
 * @author Collin Lee
 *
 */
abstract class RestTestCase extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
    }

    /**
     * tearDown
     *
     * This function helps clean up global variables that may have been set and removes the anonymous user created
     */
    protected function tearDown(): void
    {
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

    /**
     * This function helps wrap the REST call using the CURL libraries
     *
     * @param $method String name of the method to call
     * @param $parameters Mixed array of arguments depending on the method call
     *
     * @return mixed JSON decoded response made from REST call
     */
    protected function makeRESTCall($method, $parameters)
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

        // Convert the result from JSON format to a PHP array
        return json_decode($response, true);
    }

    /**
     * This function helps make the login call
     *
     * @return mixed The REST response from the login operation
     */
    protected function login()
    {
        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
        // for now hard coded admin login as reg user login does not work
        // because of password hash issue
        return $this->makeRESTCall(
            'login',
            [
                'user_auth' => [
                    'user_name' => 'admin',
                    'password' => md5('asdf'),
                    'version' => '.01',
                ],
                'application_name' => 'mobile',
                'name_value_list' => [],
            ]
        );
    }
}
