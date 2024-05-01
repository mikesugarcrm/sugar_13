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
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;

/**
 * @group api
 * @group calendar
 */
class CalendarApiTest extends TestCase
{
    private $api;
    private $calendarApi;
    private $dp;
    private $apiClass;

    public static function setUpBeforeClass(): void
    {
        \SugarTestHelper::setUp('log');
        \SugarTestHelper::setUp('beanList');
        \SugarTestHelper::setUp('beanFiles');
        \SugarTestHelper::setUp('moduleList');
    }

    public static function tearDownAfterClass(): void
    {
        \SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        \SugarAutoLoader::load('modules/Calendar/clients/base/api/CalendarApi.php');

        //Setup calendar definitions
        $mockedCalendar1 = $this->getMockBuilder(\Calendar::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $mockedCalendar1->id = 'calendar-def-id';
        $mockedCalendar1->name = 'c1';
        $mockedCalendar1->module_name = 'Calendar';
        \BeanFactory::registerBean($mockedCalendar1);

        $mockedCalendar2 = $this->getMockBuilder(\Calendar::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $mockedCalendar2->id = 'calendar-def-id2';
        $mockedCalendar2->name = 'c2';
        $mockedCalendar2->module_name = 'Calendar';
        \BeanFactory::registerBean($mockedCalendar2);

        $this->apiClass = $this->createPartialMock(
            \CalendarApi::class,
            []
        );

        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user']->getSystemUser();
        $GLOBALS['current_user'] = $this->api->user;

        $this->calendarApi = new CalendarApi();
        $this->dp = [];
    }

    protected function tearDown(): void
    {
        \BeanFactory::unregisterBean('Calendar', 'calendar-def-id');
        \BeanFactory::unregisterBean('Calendar', 'calendar-def-id2');

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestCalendartUtilities::removeAllCreatedCalendars();

        if (!empty($this->dp)) {
            $GLOBALS['db']->query('DELETE FROM data_privacy WHERE id IN (\'' . implode("', '", $this->dp) . '\')');
        }

        $this->dp = [];
    }

    public function testTransformInvitee_DPEnabled_NameIsErased()
    {
        $args = [
            'q' => 'bar',
            'fields' => 'first_name,last_name',
            'search_fields' => 'first_name,last_name',
            'erased_fields' => true,
        ];

        $contactValues = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
        ];
        $contact = SugarTestContactUtilities::createContact('', $contactValues);

        $searchResults = [
            'result' => [
                'list' => [
                    ['bean' => $contact],
                ],
            ],
        ];

        $this->createDpErasureRecord($contact, ['first_name', 'last_name']);
        $calendarApi = new \CalendarApi();

        $result = SugarTestReflection::callProtectedMethod(
            $calendarApi,
            'transformInvitees',
            [$this->api, $args, $searchResults]
        );

        $this->assertNotEmpty($result['records'], 'Api Result Contains No Records');
        $records = $result['records'];
        $this->assertCount(1, $records, 'Expecting 1 Contact Record to be returned as Invitee');
        $this->assertNotEmpty($records[0]['_erased_fields'], 'Erased Fields expected, not returned');
        $this->assertCount(2, $records[0]['_erased_fields'], 'Expected 2 erased fields');
        $this->assertSame(
            ['first_name', 'last_name'],
            $records[0]['_erased_fields'],
            'Unexpected Erased Fields were returned'
        );
    }

    public function testBuildSearchParams_ConvertsRestArgsToLegacyParams()
    {
        $args = [
            'q' => 'woo',
            'module_list' => 'Foo,Bar',
            'search_fields' => 'foo_search_field,bar_search_field',
            'fields' => 'foo_field,bar_field',
        ];

        $expectedParams = [
            [
                'modules' => ['Foo', 'Bar'],
                'group' => 'or',
                'field_list' => [
                    'foo_field',
                    'bar_field',
                    'foo_search_field',
                    'bar_search_field',
                ],
                'conditions' => [
                    [
                        'name' => 'foo_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ],
                    [
                        'name' => 'bar_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expectedParams,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'buildSearchParams',
                [$args]
            ),
            'Rest API args should be transformed correctly into legacy query params'
        );
    }

    public function testTransformInvitees_ConvertsLegacyResultsToUnifiedSearchForm()
    {
        $args = [
            'q' => 'bar',
            'fields' => 'first_name,last_name,email,account_name',
            'search_fields' => 'first_name,last_name,email,account_name',
        ];

        $bean = new SugarBean(); //dummy, mocking out formatBean anyway
        $formattedBean = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => [
                ['email_address' => 'foo@baz.com'],
                ['email_address' => 'bar@baz.com'],
            ],
        ];

        $this->calendarApi = $this->createPartialMock(
            'CalendarApi',
            ['formatBean']
        );
        $this->calendarApi->expects($this->once())
            ->method('formatBean')
            ->will($this->returnValue($formattedBean));

        $searchResults = [
            'result' => [
                'list' => [
                    ['bean' => $bean],
                ],
            ],
        ];

        $expectedInvitee = array_merge($formattedBean, [
            '_search' => [
                'highlighted' => [
                    'last_name' => [
                        'text' => 'Bar',
                        'module' => 'Contacts',
                        'label' => 'LBL_LAST_NAME',
                    ],
                ],
            ],
        ]);

        $expectedInvitees = [
            'next_offset' => -1,
            'records' => [
                $expectedInvitee,
            ],
        ];
        ;

        $this->assertEquals(
            $expectedInvitees,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'transformInvitees',
                [$this->api, $args, $searchResults]
            ),
            'Legacy search results should be transformed correctly into unified search format'
        );
    }

    public function testGetMatchedFields_MatchesRegularFieldsCorrectly()
    {
        $args = [
            'q' => 'foo',
            'search_fields' => 'first_name,last_name,email,account_name',
        ];

        $record = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => [
                ['email_address' => 'woo@baz.com'],
                ['email_address' => 'bar@baz.com'],
            ],
        ];

        $expectedMatchedFields = [
            'first_name' => [
                'text' => 'Foo',
                'module' => 'Contacts',
                'label' => 'LBL_FIRST_NAME',
            ],
        ];

        $this->assertEquals(
            $expectedMatchedFields,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'getMatchedFields',
                [$args, $record, 1]
            ),
            'Should match search query to field containing search text'
        );
    }

    public function testGetMatchedFields_MatchesEmailFieldCorrectly()
    {
        $args = [
            'q' => 'woo',
            'search_fields' => 'first_name,last_name,email,account_name',
        ];

        $record = [
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => [
                ['email_address' => 'woo@baz.com'],
                ['email_address' => 'bar@baz.com'],
            ],
        ];

        $expectedMatchedFields = [
            'email' => [
                'text' => 'woo@baz.com',
                'module' => 'Contacts',
                'label' => 'LBL_EMAIL_ADDRESS',
            ],
        ];

        $this->assertEquals(
            $expectedMatchedFields,
            SugarTestReflection::callProtectedMethod(
                $this->calendarApi,
                'getMatchedFields',
                [$args, $record, 1]
            ),
            'Should match search query to field containing search text'
        );
    }

    private function createDpErasureRecord($contact, $fields)
    {
        $dp = BeanFactory::newBean('DataPrivacy');
        $dp->name = 'Data Privacy Test';
        $dp->type = 'Request to Erase Information';
        $dp->status = 'Open';
        $dp->priority = 'Low';
        $dp->assigned_user_id = $GLOBALS['current_user']->id;
        $dp->date_opened = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->date_due = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->save();

        $module = 'Contacts';
        $linkName = strtolower($module);
        $dp->load_relationship($linkName);
        $dp->$linkName->add([$contact]);

        $options = ['use_cache' => false, 'encode' => false];
        $dp = BeanFactory::retrieveBean('DataPrivacy', $dp->id, $options);
        $dp->status = 'Closed';

        $fieldInfo = implode('","', $fields);
        $dp->fields_to_erase = '{"' . strtolower($module) . '":{"' . $contact->id . '":["' . $fieldInfo . '"]}}';

        $context = Container::getInstance()->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');

        $dp->save();
        $this->dp[] = $dp->id;
        return $dp;
    }

    public function apiParamProvider(): array
    {
        return [
            'validCalendar' => [
                [
                    [
                        'calendarId' => 'calendar-def-id',
                        'userId' => '1',
                        'teamId' => '',
                    ],
                ],
                [
                    [
                        'calendarId' => 'calendar-def-id',
                        'calendarBean' => \Calendar::class,
                        'userId' => '1',
                    ],
                ],
            ],
            'validMultipleCalendars' => [
                [
                    [
                        'calendarId' => 'calendar-def-id',
                        'userId' => '1',
                        'teamId' => '',
                    ],
                    [
                        'calendarId' => 'calendar-def-id2',
                        'userId' => '1',
                        'teamId' => '',
                    ],
                ],
                [
                    [
                        'calendarId' => 'calendar-def-id',
                        'calendarBean' => \Calendar::class,
                        'userId' => '1',
                    ],
                    [
                        'calendarId' => 'calendar-def-id2',
                        'calendarBean' => \Calendar::class,
                        'userId' => '1',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::prepareCalendars
     * @dataProvider apiParamProvider
     */
    public function testPrepareCalendars(array $input, array $expected): void
    {
        $this->apiClass->params = $input;
        $this->apiClass->logger = $GLOBALS['log'];

        $result = $this->apiClass->prepareCalendars($input);

        if (count($expected) >= 1) {
            foreach ($result as $idx => $res) {
                $this->assertEquals($expected[$idx]['calendarId'], $res['calendarId']);
                $this->assertInstanceOf($expected[$idx]['calendarBean'], $res['calendarBean']);
                $this->assertEquals($expected[$idx]['userId'], $res['userId']);
            }
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    public function getUsersAndTeamsProvider(): array
    {
        return [
            [
                'args' => [
                    'module_list' => 'all',
                ],
            ],
        ];
    }

    /**
     * @covers ::getUsersAndTeams
     * @dataProvider getUsersAndTeamsProvider
     */
    public function testGetUsersAndTeams(array $args): void
    {
        $result = $this->apiClass->getUsersAndTeams($this->api, $args);


        $this->assertArrayHasKey('next_offset', $result);
        $this->assertArrayHasKey('records', $result);
    }

    /**
     * @covers ::getCalendarDefs
     */
    public function testGetCalendarDefs(): void
    {
        $calendarApiClass = $this->createPartialMock(
            \CalendarApi::class,
            ['getCalendars']
        );

        $calendarApiClass->method('getCalendars')->willReturn([
            [
                'calendarId' => 'calendarId',
                'id' => 'id',
                'assigned_user_id' => 'Jim Smith',
                'name' => 'My Calls',
                'color' => '#c0edff',
                'module' => 'Calls',
                'start_field' => 'event_start',
                'end_field' => 'event_end',
                'dblclick_event' => 'detail:self:id',
                'calendar_type' => 'main',
                'allow_create' => 'true',
                'allow_update' => 'true',
                'allow_delete' => 'true',
                'objectName' => 'Call',
            ],
        ]);

        $result = $calendarApiClass->getCalendarDefs($this->api, [
            'calendars' => [
                [
                    'calendarId' => 'calendarId',
                    'userId' => '1',
                    'teamId' => '',
                ],
            ],
        ]);

        $this->assertArrayHasKey('calendarId', $result[0]);
        $this->assertEquals('calendarId', $result[0]['calendarId']);
    }

    /**
     * @covers ::listCalendars
     */
    public function testListCalendars(): void
    {
        $this->apiClass->logger = $GLOBALS['log'];

        $calendarApiClass = $this->createPartialMock(
            \CalendarApi::class,
            ['getCalendars']
        );

        $calendarApiClass->method('getCalendars')->willReturn([
            [
                'calendarId' => '63fcd5ce-fc1b-11eb-a7d8-0242ac140008',
                'id' => '63fcd5ce-fc1b-11eb-a7d8-0242ac140008',
                'assigned_user_id' => '1',
                'name' => 'Calls',
                'color' => '#c0edff',
                'module' => 'Calls',
                'start_field' => 'date_start',
                'end_field' => null,
                'dblclick_event' => 'detail:self:id',
                'calendar_type' => '^main^',
                'allow_create' => true,
                'allow_update' => true,
                'allow_delete' => true,
                'objName' => 'Call',
            ],
        ]);

        $result = $calendarApiClass->listCalendars($this->api, [
            'calendarFilter' => 'my_calendars',
            'calendars' => [
                [
                    'calendarId' => '63fcd5ce-fc1b-11eb-a7d8-0242ac140008',
                    'userId' => '1',
                    'teamId' => '',
                ],
            ],
        ]);

        $this->assertArrayHasKey('63fcd5ce-fc1b-11eb-a7d8-0242ac140008', $result['calendars']);
        $this->assertEquals(
            '63fcd5ce-fc1b-11eb-a7d8-0242ac140008',
            $result['calendars']['63fcd5ce-fc1b-11eb-a7d8-0242ac140008']['calendarId']
        );
    }

    /**
     * @covers ::updateRecord
     */
    public function testUpdateRecord(): void
    {
        //Setup call and calendar defition
        $newCall = SugarTestCallUtilities::createCall();
        $newCall->date_start = '2021-05-18 12:00:00';
        $newCall->duration_minutes = 30;
        $newCall->date_end = '2021-05-18 12:30:00';
        $newCall->save();

        $newCalendar = SugarTestCalendartUtilities::createCalendar('', [
            'calendar_module' => 'Calls',
            'event_start' => 'date_start',
            'event_end' => 'date_end',
            'duration_minutes' => 'duration_minutes',
            'duration_hours' => 'duration_hours',
        ]);

        $this->apiClass->logger = $GLOBALS['log'];

        $result = $this->apiClass->updateRecord($this->api, [
            'module' => 'Calls',
            'calendarId' => $newCalendar->id,
            'recordId' => $newCall->id,
            'start' => '2022-05-18T12:30:00+00:00',
            'end' => '2022-05-18T13:00:00+00:00',
        ]);

        $newCall->retrieve();

        $this->assertEquals('2022-05-18 12:30:00', $newCall->date_start);
        $this->assertEquals(true, $result);
    }
}
