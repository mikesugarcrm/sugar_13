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

/**
 * Class ExtAPIGoToMeetingTest
 *
 * This test is for the ExtAPIGoToMeeting.php class and the related functionality towards the GoToMeeting REST API
 */
class ExtAPIGoToMeetingTest extends TestCase
{
    private $eapm;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->eapm = BeanFactory::newBean('EAPM');
        $this->eapm->validated = 1;
        $this->eapm->oauth_token = 'something';
        $this->eapm->save();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM {$this->eapm->table_name} WHERE id = '{$this->eapm->id}'");
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
    }

    /**
     * Test createMeeting
     *
     * @dataProvider createDataProvider
     */
    public function testCreateMeeting($code, $body, $expected, $message)
    {
        $goToMeetingAPI = $this->getAPI($code, $body);
        $goToMeetingAPI->eapmBean = $this->eapm;

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->date_start = '2012-01-05 13:13:13';
        $meeting->date_end = '2012-01-05 14:13:13';

        $result = $goToMeetingAPI->scheduleMeeting($meeting);

        $this->assertEquals($expected, $result['success'], $message);
    }

    public static function createDataProvider()
    {
        return [
            [
                200,
                [
                    'hostURL' => 'url',
                    0 => [
                        'meetingid' => 'meetingid',
                        'joinURL' => 'joinURL',
                        'uniqueMeetingId' => 'uniqueMeetingId',
                    ],
                ],
                true,
                'The POST call returned 200, test should pass',
            ],
            [
                404,
                [],
                false,
                'The POST call returned 404, test should fail',
            ],
        ];
    }

    /**
     * Test updateMeeting
     *
     * @dataProvider updateDataProvider
     */
    public function testUpdateMeeting($code, $expected, $message)
    {
        $goToMeetingAPI = $this->getAPI($code);
        $goToMeetingAPI->eapmBean = $this->eapm;

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->external_id = 'gotomeeting-test';
        $meeting->date_start = '2012-01-05 13:13:13';
        $meeting->date_end = '2012-01-05 14:13:13';

        $result = $goToMeetingAPI->scheduleMeeting($meeting);

        $this->assertEquals($expected, $result['success'], $message);
    }

    public static function updateDataProvider()
    {
        return [
            [
                204,
                true,
                'The PUT call returned 204, test should pass',
            ],
            [
                404,
                false,
                'The PUT call returned 404, test should fail',
            ],
        ];
    }

    /**
     * Test unscheduleMeeting
     *
     * @dataProvider deleteDataProvider
     */
    public function testDeleteMeeting($code, $expected, $message)
    {
        $goToMeetingAPI = $this->getAPI($code);
        $goToMeetingAPI->eapmBean = $this->eapm;

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->external_id = 'gotomeeting-test';
        $meeting->deleted = 1;

        $result = $goToMeetingAPI->unscheduleMeeting($meeting);

        $this->assertEquals($expected, $result['success'], $message);
    }

    public static function deleteDataProvider()
    {
        return [
            [204, true, 'The DELETE call returned 204, test should pass'],
            [404, false, 'The DELETE call returned 404, test should fail'],
        ];
    }

    /**
     * Return a Mock of the API, with overridden makeRequest
     * that returns a fixed Zend_Http_Response
     *
     * @param string $code - HTTP Response Status code
     * @param array $headers - HTTP Response headers
     * @param array $body - PHP Array to be JSON encoded
     *
     * @return MockObject
     */
    private function getAPI($code, $body = [])
    {
        $headers = [];

        $this->assertTrue(is_array($body), '$body should be an array()');
        $goToMeetingAPI = $this->createPartialMock('ExtAPIGoToMeeting', ['makeRequest']);
        $goToMeetingAPI->expects($this->any())
            ->method('makeRequest')
            ->will(
                $this->returnValue(
                    new Zend_Http_Response($code, $headers, json_encode($body))
                )
            );

        return $goToMeetingAPI;
    }
}
