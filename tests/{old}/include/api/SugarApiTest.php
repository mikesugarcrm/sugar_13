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

class SugarApiTest extends TestCase
{
    /**
     * @var \Contact|mixed
     */
    public $contact;
    protected $mock;

    public static $monitorList;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$monitorList = TrackerManager::getInstance()->getDisabledMonitors();

        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        ApiHelper::$moduleHelpers = [];
        TrackerManager::getInstance()->setDisabledMonitors(self::$monitorList);

        $_FILES = [];
        unset($_SERVER['CONTENT_LENGTH']);

        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->mock = new SugarApiMock();
        $this->contact = SugarTestContactUtilities::createContact();
        // We can override the module helpers with mocks.
        ApiHelper::$moduleHelpers = [];
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testLoadBeanById_BeanExists_Success()
    {
        $this->mock = new SugarApiMock();

        $args = [
            'module' => 'Contacts',
            'record' => $this->contact->id,
        ];

        $api = new SugarApiTestServiceMock();
        $bean = $this->mock->callLoadBean($api, $args);

        $this->assertTrue($bean instanceof Contact);
        $this->assertEquals($this->contact->id, $bean->id, 'Unexpected Contact Loaded');
    }

    public function testLoadBeanById_BeanNotExists_NotFound()
    {
        $this->mock = new SugarApiMock();

        $args = [
            'module' => 'Contacts',
            'record' => '12345',
        ];

        $api = new SugarApiTestServiceMock();
        $this->expectException(SugarApiExceptionNotFound::class);
        $bean = $this->mock->callLoadBean($api, $args);
    }

    public function testLoadBean_CreateTempBean_Success()
    {
        $this->mock = new SugarApiMock();

        $args = [ /* Note: No "record" element */
            'module' => 'Contacts',
        ];

        $api = new SugarApiTestServiceMock();
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $bean = $this->mock->callLoadBean($api, $args);
    }

    /**
     * @covers SugarApi::trackAction
     */
    public function testTrackAction()
    {
        $monitorMock = $this->getMockBuilder('Monitor')
            ->disableOriginalConstructor()->getMock();
        $monitorMock
            ->expects($this->any())
            ->method('setValue');

        $managerMock = $this->getMockBuilder('TrackerManager')
            ->disableOriginalConstructor()
            ->onlyMethods(['getMonitor', 'saveMonitor'])
            ->getMock();
        $managerMock
            ->expects($this->once())
            ->method('saveMonitor');

        $sugarApi = $this->createPartialMock('SugarApi', ['getTrackerManager']);
        $sugarApi
            ->expects($this->any())
            ->method('getTrackerManager')
            ->will($this->returnValue($managerMock));

        $sugarApi->api = $this->createMock('RestService');
        $sugarApi->api->user = $this->createPartialMock('User', ['getPrivateTeamID']);
        $sugarApi->api->user
            ->expects($this->any())
            ->method('getPrivateTeamID')
            ->will($this->returnValue('1'));
        $fakeBean = $this->createPartialMock('SugarBean', ['get_summary_text']);
        $fakeBean->id = 'abcd';
        $fakeBean->module_dir = 'fakeBean';
        $fakeBean->expects($this->any())
            ->method('get_summary_text')
            ->will($this->returnValue('Rickroll'));


        $sugarApi->action = 'unittest';

        // Emulate the tracker being disabled, then enabled
        $managerMock
            ->expects($this->any())
            ->method('getMonitor')
            ->will($this->onConsecutiveCalls(null, $monitorMock, $monitorMock, $monitorMock, $monitorMock));

        $sugarApi->trackAction($fakeBean);

        // This one should actually save
        $sugarApi->trackAction($fakeBean);

        // Try it again, but this time with a new bean with id
        $fakeBean->new_with_id = true;
        $sugarApi->trackAction($fakeBean);

        // And one last time but this time with an empty bean id
        unset($fakeBean->new_with_id);
        unset($fakeBean->id);
        $sugarApi->trackAction($fakeBean);

        // No asserts, handled through the saveMonitor ->once() expectation above
    }

    /**
     * @dataProvider lotsOData
     */
    public function testHtmlEntityDecode($array, $expected, $message)
    {
        $this->mock->htmlEntityDecodeStuff($array);
        $this->assertSame($array, $expected, $message);
    }

    public function lotsOData()
    {
        return [
            [['bool' => true], ['bool' => true], 'True came out wrong'],
            [['bool' => false], ['bool' => false], 'False came out wrong'],
            [['string' => 'Test'], ['string' => 'Test'], 'String came out wrong'],
            [['number' => 12345], ['number' => 12345], 'Number came out wrong'],
            [
                ['html' => htmlentities("I'll \"walk\" the <b>dog</b> now", ENT_COMPAT)],
                ['html' => "I'll \"walk\" the <b>dog</b> now"],
                'HTML came out wrong',
            ],
            [
                ['html' => ['nested_result' => ['data' => 'def &lt; abc &gt; xyz']]],
                ['html' => ['nested_result' => ['data' => 'def < abc > xyz']]],
                'HTML came out wrong',
            ],
        ];
    }

    /**
     * @dataProvider checkPostRequestBodyProvider
     */
    public function testCheckPostRequestBody($contentLength, $postMaxSize, $expectedException)
    {
        $api = $this->getMockBuilder('SugarApi')
            ->setMethods(['getPostMaxSize'])
            ->getMock();
        $api->expects($this->any())
            ->method('getPostMaxSize')
            ->will($this->returnValue($postMaxSize));

        $_FILES = [];
        $_SERVER['CONTENT_LENGTH'] = $contentLength;
        $this->expectException($expectedException);
        SugarTestReflection::callProtectedMethod($api, 'checkPostRequestBody');
    }

    public static function checkPostRequestBodyProvider()
    {
        return [
            [null, null, SugarApiExceptionMissingParameter::class],
            [1024, 1023, SugarApiExceptionRequestTooLarge::class],
        ];
    }

    /**
     * @dataProvider checkPutRequestBodyProvider
     */
    public function testCheckPutRequestBody($length, $contentLength, $expectedException)
    {
        $api = $this->getMockForAbstractClass('SugarApi');

        $_SERVER['CONTENT_LENGTH'] = $contentLength;
        $this->expectException($expectedException);
        SugarTestReflection::callProtectedMethod($api, 'checkPutRequestBody', [$length]);
    }

    public static function checkPutRequestBodyProvider()
    {
        return [
            [0, null, SugarApiExceptionMissingParameter::class],
            [1023, 1024, SugarApiExceptionRequestTooLarge::class],
        ];
    }

    /**
     * @dataProvider providerTestGetFieldsFromArgs
     * @covers       SugarApi::getFieldsFromArgs
     * @group unit
     */
    public function testGetFieldsFromArgs($module, $fieldDefs, $fieldList, $args, $view, $expected)
    {
        if ($module) {
            $seed = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->getMock();
            $seed->module_name = $module;
            $seed->field_defs = $fieldDefs;
        } else {
            $seed = null;
        }

        $service = new SugarApiTestServiceMock();

        $sugarApi = $this->getMockBuilder('SugarApiMock')
            ->setMethods(['getMetaDataManager'])
            ->getMock();

        $mm = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $mm->expects($this->any())
            ->method('getModuleViewFields')
            ->will($this->returnValue($fieldList));

        $sugarApi->expects($this->any())
            ->method('getMetaDataManager')
            ->will($this->returnValue($mm));

        $this->assertEquals(
            $expected,
            $sugarApi->getFieldsFromArgs($service, $args, $seed, $view)
        );
    }

    public function providerTestGetFieldsFromArgs()
    {
        return [

            // fields argument only
            [
                'Accounts',
                [], // field defs
                [], // view def
                [   // arguments
                    'fields' => 'name,website',
                ],
                'view',  // view
                [   // expected
                    'name',
                    'website',
                ],
            ],

            // view argument only
            [
                'Accounts',
                [], // field defs
                [ // view def
                    'name',
                    'website',
                ],
                [ // arguments
                    'xxx' => 'record',
                ],
                'xxx', // view
                [ // expected
                    'name',
                    'website',
                ],
            ],

            // fields/view argument merge
            [
                'Accounts',
                [], // field defs
                [ // view def
                    'phone',
                    'fax',
                ],
                [ // arguments
                    'fields' => 'name,website',
                    'view' => 'record',
                ],
                'view', // view
                [  // expected
                    'name',
                    'website',
                    'phone',
                    'fax',
                ],
            ],

            // nothing ...
            [
                'Accounts',
                [], // field defs
                [], // view def
                [], // arguments
                null,    // view
                [], // expected
            ],

            // fields/view with invalid module
            [
                null,
                [], // field defs
                [ // view def
                    'bogus',
                ],
                [   // arguments
                    'fields' => 'name,website',
                    'view' => 'record',
                ],
                'view', // view
                [  // expected
                    'name',
                    'website',
                ],
            ],

            // relate and parent field
            [
                'Accounts',
                [  // field defs
                    'case_name' => [
                        'name' => 'case_name',
                        'type' => 'relate',
                        'id_name' => 'case_id',
                    ],
                    'parent_name' => [
                        'name' => 'parent_name',
                        'type' => 'parent',
                        'id_name' => 'parent_id',
                        'type_name' => 'parent_type',
                    ],
                    'website' => [
                        'name' => 'website',
                        'type' => 'varchar',
                    ],
                ],
                [  // view def
                    'name',
                    'case_name',
                    'parent_name',
                    'website',
                ],
                [  // arguments
                    'view' => 'record',
                    'fields' => 'phone,fax',
                ],
                'view', // view
                [  // expected
                    'phone',
                    'fax',
                    'name',
                    'case_name',
                    'parent_name',
                    'website',
                    'case_id',
                    'parent_id',
                    'parent_type',
                ],
            ],
            // url field
            [
                'Leads',
                [  // field defs
                    'name' => [
                        'name' => 'name',
                        'type' => 'fullname',
                    ],
                    'url_c' => [
                        'name' => 'url_c',
                        'type' => 'url',
                        'default' => 'test/{name}',
                    ],
                ],
                [], // view def
                [ // arguments
                    'fields' => 'my_favorite,converted,url_c',
                    'view' => 'list',
                ],
                'view', // view
                [  // expected
                    'my_favorite',
                    'converted',
                    'url_c',
                    'name',
                ],
            ],

        ];
    }

    /**
     * @dataProvider getOrderByFromArgsSuccessProvider
     */
    public function testGetOrderByFromArgsSuccess(array $args, array $expected)
    {
        $actual = $this->getOrderByFromArgs($args);
        $this->assertEquals($expected, $actual);
    }

    public static function getOrderByFromArgsSuccessProvider()
    {
        return [
            'not-specified' => [
                [],
                [],
            ],
            'specified' => [
                [
                    'order_by' => 'a:asc,b:desc,c,d:whatever',
                ],
                [
                    'a' => true,
                    'b' => false,
                    'c' => true,
                    'd' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getOrderByFromArgsFailureProvider
     */
    public function testGetOrderByFromArgsFailure(array $args, $expectedException)
    {
        /** @var SugarBean|MockObject $bean */
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $bean->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(false));
        $bean->field_defs = ['name' => []];

        $this->expectException($expectedException);
        $this->getOrderByFromArgs($args, $bean);
    }

    public static function getOrderByFromArgsFailureProvider()
    {
        return [
            'field-not-found' => [
                [
                    'order_by' => 'not-existing-field',
                ],
                SugarApiExceptionInvalidParameter::class,
            ],
            'field-no-access' => [
                [
                    'order_by' => 'name',
                ],
                SugarApiExceptionNotAuthorized::class,
            ],
        ];
    }

    private function getOrderByFromArgs(array $args, SugarBean $bean = null)
    {
        $api = $this->getMockForAbstractClass('SugarApi');
        return SugarTestReflection::callProtectedMethod($api, 'getOrderByFromArgs', [$args, $bean]);
    }

    /**
     * @dataProvider normalizeFieldsSuccessProvider
     */
    public function testNormalizeFieldsSuccess($input, $expectedFields, $expectedDisplayParams)
    {
        $fields = $this->normalizeFields($input, $displayParams);
        $this->assertEquals($expectedFields, $fields);
        $this->assertEquals($expectedDisplayParams, $displayParams);
    }

    public static function normalizeFieldsSuccessProvider()
    {
        return [
            'from-string' => [
                'id,name',
                ['id', 'name'],
                [],
            ],
            'from-array' => [
                ['first_name', 'last_name'],
                ['first_name', 'last_name'],
                [],
            ],
            'from-string-with-display-params' => [
                'id,{"name":"opportunities","fields":["id","name","sales_status"],"order_by":"date_closed:desc"}',
                ['id', 'opportunities'],
                [
                    'opportunities' => [
                        'fields' => ['id', 'name', 'sales_status'],
                        'order_by' => 'date_closed:desc',
                    ],
                ],
            ],
            'from-array-with-display-params' => [
                [
                    'id', [
                    'name' => 'contacts',
                    'fields' => ['first_name', 'last_name'],
                    'order_by' => 'last_name',
                    ],
                ],
                ['id', 'contacts'],
                [
                    'contacts' => [
                        'fields' => ['first_name', 'last_name'],
                        'order_by' => 'last_name',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider normalizeFieldsFailureProvider
     */
    public function testNormalizeFieldsFailure($fields)
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->normalizeFields($fields, $displayParams);
    }

    public static function normalizeFieldsFailureProvider()
    {
        return [
            'non-array-or-string' => [false],
            'name-not-specified' => [
                [
                    ['order_by' => 'name'],
                ],
            ],
        ];
    }

    private function normalizeFields($fields, &$displayParams)
    {
        $api = $this->getMockForAbstractClass('SugarApi');
        return SugarTestReflection::callProtectedMethod($api, 'normalizeFields', [$fields, &$displayParams]);
    }

    /**
     * @dataProvider parseFieldsSuccessProvider
     */
    public function testParseFieldsSuccess($fields, array $expected)
    {
        $actual = $this->parseFields($fields);
        $this->assertEquals($expected, $actual);
    }

    public static function parseFieldsSuccessProvider()
    {
        return [
            'normal' => [
                'name,{"name":"opportunities","fields":["id","name","sales_status"]}',
                [
                    'name',
                    [
                        'name' => 'opportunities',
                        'fields' => ['id', 'name', 'sales_status'],
                    ],
                ],
            ],
            'whitespaces' => [
                'id , name',
                ['id', 'name'],
            ],
        ];
    }

    /**
     * @dataProvider parseFieldsFailureProvider
     *
     */
    public function testParseFieldsFailure($fields)
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->parseFields($fields);
    }

    public static function parseFieldsFailureProvider()
    {
        return [
            'invalid-json' => [
                '{"name":',
            ],
        ];
    }

    private function parseFields($fields)
    {
        $api = $this->getMockForAbstractClass('SugarApi');
        return SugarTestReflection::callProtectedMethod($api, 'parseFields', [$fields]);
    }
}


// need to make sure ServiceBase is included when extending it to avoid a fatal error

class SugarApiMock extends SugarApi
{
    public function htmlEntityDecodeStuff(&$data)
    {
        return parent::htmlDecodeReturn($data);
    }

    public function callLoadBean(ServiceBase $api, $args)
    {
        return parent::loadBean($api, $args);
    }

    public function getFieldsFromArgs(
        ServiceBase $api,
        array       $args,
        SugarBean   $bean = null,
        $viewName = 'view',
        &$displayParams = []
    ) {

        return parent::getFieldsFromArgs($api, $args, $bean, $viewName, $displayParams);
    }
}

class SugarApiTestServiceMock extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
