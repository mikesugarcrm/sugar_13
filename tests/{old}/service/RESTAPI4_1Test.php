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

class RESTAPI4_1Test extends TestCase
{
    /**
     * @var string
     */
    //@codingStandardsIgnoreStart
    public $_soapURL;
    //@codingStandardsIgnoreEnd
    public $meeting4;
    private $lastRawResponse;
    protected $contact1;
    protected $contact2;
    protected $another_user;
    protected $meeting1;
    protected $meeting2;
    protected $meeting3;
    protected $call1;
    protected $call2;

    protected function setUp(): void
    {
        global $current_user;
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->another_user = SugarTestUserUtilities::createAnonymousUser();
        $this->login();
        $this->another_user = SugarTestUserUtilities::createAnonymousUser();

        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact1->contacts_users_id = $current_user->id;
        $this->contact1->first_name = 'First1';
        $this->contact1->last_name = 'Last1';
        $this->contact1->save();

        $this->contact1->user_sync->add($current_user);
        $this->contact1->sync_contact = 1;
        $this->contact1->save();

        $this->contact2 = SugarTestContactUtilities::createContact();
        $this->contact2->contacts_users_id = $this->another_user->id;
        $this->contact2->first_name = 'First2';
        $this->contact2->last_name = 'Last2';
        $this->contact2->save();

        $this->contact2->user_sync->add($this->another_user);
        $this->contact2->sync_contact = 1;
        $this->contact2->save();

        $this->call1 = SugarTestCallUtilities::createCall();
        $this->call1->name = 'RESTAPI4_1Test1';
        $this->call1->load_relationship('users');
        $this->call1->users->add($current_user);
        $this->call1->save();

        $this->call2 = SugarTestCallUtilities::createCall();
        $this->call2->name = 'RESTAPI4_1Test2';
        $this->call2->load_relationship('users');
        $this->call2->users->add($current_user);
        $this->call2->save();

        $this->meeting1 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting1->name = 'RESTAPI4_1Test1';
        $this->meeting1->load_relationship('users');
        $this->meeting1->users->add($current_user);
        $this->meeting1->save();

        $this->meeting2 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting2->name = 'RESTAPI4_1Test2';
        $this->meeting2->load_relationship('users');
        $this->meeting2->users->add($this->another_user);
        $this->meeting2->save();

        $this->meeting3 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting3->name = 'RESTAPI4_1Test3';
        $this->meeting3->load_relationship('users');
        $this->meeting3->users->add($current_user);
        $this->meeting3->save();

        $this->meeting4 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting4->name = 'SOAPAPI4_1Test4';
        $this->meeting4->load_relationship('users');
        $this->meeting4->users->add($current_user);
        $this->meeting4->mark_deleted($this->meeting4->id);
        $this->meeting4->deleted = 1;
        $this->meeting4->save();
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestHelper::tearDown();
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['mod_strings']);
    }

    private function makeRESTCall($method, $parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/rest.php';
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
        global $current_user;
        return $this->makeRESTCall(
            'login',
            [
                'user_auth' => [
                    'user_name' => $current_user->user_name,
                    'password' => $current_user->user_hash,
                    'version' => '.01',
                ],
                'application_name' => 'mobile',
                'name_value_list' => [],
            ]
        );
    }

    public function testGetModifiedRelationships()
    {
        $result = $this->login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1, $this->returnLastRawResponse());
        $session = $result['id'];

        $callsAndMeetingsFields = ['id', 'date_modified', 'deleted', 'name', 'rt.deleted synced'];
        $contactsSelectFields = ['id', 'date_modified', 'deleted', 'first_name', 'last_name', 'rt.deleted synced'];

        global $timedate, $current_user;
        $one_hour_ago = $timedate->asDb($timedate->getNow()->get('-1 hours'));
        $one_hour_later = $timedate->asDb($timedate->getNow()->get('+1 hours'));

        $result = $this->makeRESTCall(
            'get_modified_relationships',
            [
                'session' => $session,
                'module_name' => 'Users',
                'related_module' => 'Contacts',
                'from_date' => $one_hour_ago,
                'to_date' => $one_hour_later,
                'offset' => 0,
                'max_results' => 10,
                'deleted' => 0,
                'user_id' => $current_user->id,
                'select_fields' => $contactsSelectFields,
                'relationship_name' => 'contacts_users',
                'deletion_date' => '',
            ]
        );

        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(1, $result['result_count']);
        $this->assertEquals(1, $result['next_offset']);


        $result = $this->makeRESTCall(
            'get_modified_relationships',
            [
                'session' => $session,
                'module_name' => 'Users',
                'related_module' => 'Meetings',
                'from_date' => $one_hour_ago,
                'to_date' => $one_hour_later,
                'offset' => 0,
                'max_results' => 10,
                'deleted' => 0,
                'user_id' => $current_user->id,
                'select_fields' => $callsAndMeetingsFields,
                'relationship_name' => 'meetings_users',
                'deletion_date' => '',
            ]
        );

        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(2, $result['result_count']);
        $this->assertEquals(2, $result['next_offset']);

        $result = $this->makeRESTCall(
            'get_modified_relationships',
            [
                'session' => $session,
                'module_name' => 'Users',
                'related_module' => 'Meetings',
                'from_date' => $one_hour_ago,
                'to_date' => $one_hour_later,
                'offset' => 0,
                'max_results' => 10,
                'deleted' => 1,
                'user_id' => $current_user->id,
                'select_fields' => $callsAndMeetingsFields,
                'relationship_name' => 'meetings_users',
                'deletion_date' => $one_hour_ago,
            ]
        );

        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(1, $result['result_count']);
        $this->assertEquals(1, $result['next_offset']);
    }
}
