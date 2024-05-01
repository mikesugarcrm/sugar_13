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
 * @group api
 * @group activities
 */
class ActivitiesApiTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user'];
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ActivitiesApi::getQueryObject
     */
    public function testGetQueryObject_ForHomePage_ShowsOnlyHomePostsAndActivitiesUserLinkedTo()
    {
        $query = SugarTestReflection::callProtectedMethod(
            'ActivitiesApi',
            'getQueryObject',
            [
                $this->createMock(SugarBean::class),
                ['offset' => 0, 'limit' => 5],
                $this->api,
                true,
            ]
        );
        $sql = $query->compile()->getSQL();
        $this->assertStringContainsString('activities.parent_type IS NULL', $sql);
        $this->assertStringContainsString('activities_users.parent_type = ?', $sql);
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_HomePage_MultipleModuleTypes_UserHasMixedFieldAccess_AppropriateFieldChangesReturned()
    {
        $records = [
            [
                'activities__date_modified' => '2015-07-09 15:09:57',
                'id' => 'ba0105b3-a975-9ea9-f1ed-559e8e1699c9',
                'date_entered' => '2015-07-09 15:09:57',
                'date_modified' => '2015-07-09 15:09:57',
                'modified_user_id' => '1',
                'created_by' => '1',
                'deleted' => '0',
                'parent_id' => '',
                'parent_type' => '',
                'activity_type' => 'update',
                'data' => json_encode(
                    [
                        'object' => [
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'Davey Crockett',
                        ],
                        'changes' => [
                            'lead_source' => [
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ],
                        ],
                    ]
                ),
                'comment_count' => '0',
                'last_comment' => '{"name":"","deleted":false,"data":[]}',
                'first_name' => 'Davey',
                'last_name' => 'Crockett',
                'picture' => '1d74bee8-a666-72ea-ed32-559bd81b44ec',
                'fields' => json_encode(['first_name', 'last_name', 'lead_source', 'city']),
            ],
            [
                'activities__date_modified' => '2015-07-09 15:09:55',
                'id' => '12037c89-f75f-a8fb-1284-559e8e347e76',
                'date_entered' => '2015-07-09 15:09:55',
                'date_modified' => '2015-07-09 15:09:55',
                'modified_user_id' => '1',
                'created_by' => '1',
                'deleted' => '0',
                'parent_id' => '',
                'parent_type' => '',
                'activity_type' => 'update',
                'data' => json_encode(
                    [
                        'object' => [
                            'type' => 'Contact',
                            'module' => 'Contacts',
                            'name' => 'Jim Bowie',
                        ],
                        'changes' => [
                            'opt_out' => [
                                'field_name' => 'opt_out',
                                'before' => false,
                                'after' => true,
                            ],
                        ],
                    ]
                ),
                'comment_count' => '0',
                'last_comment' => '{"name":"","deleted":false,"data":[]}',
                'first_name' => 'Jim',
                'last_name' => 'Bowie',
                'picture' => '1d74bee8-a666-72ea-ed32-559bd81b44ec',
                'fields' => json_encode(['opt_out']),
            ],
        ];
        $records[] = []; // Need One Bogus Record that Formatter will POP

        $expectedLeadDataChanges = [
            'object' => [
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'Davey Crockett',
            ],
            'changes' => [              // User Has Access to lead_source field - Change Data Expected
                'lead_source' => [
                    'field_name' => 'lead_source',
                    'before' => 'xxx',
                    'after' => 'yyy',
                ],
            ],
        ];

        $expectedContactDataChanges = [
            'object' => [
                'type' => 'Contact',
                'module' => 'Contacts',
                'name' => 'Jim Bowie',
            ],
            'changes' => [],
        ];

        $sugarQueryMock = $this->createPartialMock('SugarQuery', ['execute']);
        $sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $aclLead = new TestSugarACLStatic();
        $aclLead->return_value = ['lead_source' => true];  //User Has Field Level Access to Leads::lead_source field
        $aclContact = new TestSugarACLStatic();
        $aclContact->return_value = ['opt_out' => false];     //User Does Not Have Field Level Access to Contacts::opt_out field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = [$aclLead];
        SugarACL::$acls['Contacts'] = [$aclContact];

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, [], $sugarQueryMock, null);

        $this->assertEquals($expectedLeadDataChanges, $actual['records'][0]['data'], 'Expected Activities Records with Field Access Applied correctly across Modules');
        $this->assertEquals($expectedContactDataChanges, $actual['records'][1]['data'], 'Expected Activities Records with Field Access Applied correctly across Modules');
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_ListView_UserHasFieldAccess_FieldChangesReturned()
    {
        $records = [
            [
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment' => json_encode([]),
                'date_modified' => '2013-12-25 13:00:00',
                'date_entered' => '2013-12-25 13:00:00',
                'activity_type' => 'update',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'fields' => json_encode(['first_name', 'last_name', 'lead_source', 'city']),
                'data' => json_encode(
                    [
                        'object' => [
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'John Doe',
                        ],
                        'changes' => [
                            'lead_source' => [
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ],
                        ],
                    ]
                ),
            ],
        ];
        $records[] = []; // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = [
            'object' => [
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'John Doe',
            ],
            'changes' => [
                'lead_source' => [
                    'field_name' => 'lead_source',
                    'before' => 'xxx',
                    'after' => 'yyy',
                ],
            ],
        ];

        $sugarQueryMock = $this->createPartialMock('SugarQuery', ['execute']);
        $sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        $acl->return_value = ['lead_source' => true];  // User Has Field Level Access to Leads::lead_source field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = [$acl];

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, [], $sugarQueryMock, null);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], 'Expected Activities Records with Changed Fields Listed');
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_ListView_UserDoesNotHaveFieldAccess_FieldChangesNotReturned()
    {
        $records = [
            [
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment' => json_encode([]),
                'date_modified' => '2013-12-25 13:00:00',
                'date_entered' => '2013-12-25 13:00:00',
                'activity_type' => 'update',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'fields' => json_encode(['first_name', 'last_name', 'lead_source', 'city']),
                'data' => json_encode(
                    [
                        'object' => [
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'John Doe',
                        ],
                        'changes' => [
                            'lead_source' => [
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ],
                        ],
                    ]
                ),
            ],
        ];
        $records[] = []; // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = [
            'object' => [
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'John Doe',
            ],
            'changes' => [],
        ];

        $sugarQueryMock = $this->createPartialMock('SugarQuery', ['execute']);
        $sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        $acl->return_value = ['lead_source' => false];  //User Has No Field Level Access to lead_source field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = [$acl];

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, [], $sugarQueryMock, null);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], 'Expected Activities Records without data for Changed Fields');
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_RecordView_UserDoesNotHaveFieldAccess_FieldChangesNotReturned()
    {
        $records = [
            [
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment' => json_encode([]),
                'date_modified' => '2013-12-25 13:00:00',
                'date_entered' => '2013-12-25 13:00:00',
                'activity_type' => 'update',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'fields' => json_encode(['first_name', 'last_name', 'lead_source']),
                'data' => json_encode(
                    [
                        'object' => [
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'John Doe',
                        ],
                        'changes' => [
                            'lead_source' => [
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ],
                            'first_name' => [
                                'field_name' => 'first_name',
                                'before' => 'Johnathan',
                                'after' => 'John',
                            ],
                            'last_name' => [
                                'field_name' => 'last_name',
                                'before' => 'Dough',
                                'after' => 'Doe',
                            ],
                        ],
                    ]
                ),
            ],
        ];
        $records[] = []; // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = [
            'object' => [
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'John Doe',
            ],
            'changes' => [],
        ];

        $sugarQueryMock = $this->createPartialMock('SugarQuery', ['execute']);
        $sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        //User Has Field Level Access to lead_source, first_name and last_name fields
        $acl->return_value = [
            'lead_source' => false,
            'first_name' => false,
            'last_name' => false,
        ];
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = [$acl];

        $lead = SugarTestLeadUtilities::createLead();

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, [], $sugarQueryMock, $lead);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], 'Expected Activities Records without data for Changed Fields');
    }

    /**
     * @covers ActivitiesApi::formatResult
     */
    public function testListActivities_RecordView_UserHasFieldAccess_FieldChangesReturned()
    {
        $records = [
            [
                'display_parent_type' => '',
                'display_parent_id' => '',
                'comment_count' => 0,
                'last_comment' => json_encode([]),
                'date_modified' => '2013-12-25 13:00:00',
                'date_entered' => '2013-12-25 13:00:00',
                'activity_type' => 'update',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'fields' => json_encode(['first_name', 'last_name', 'lead_source']),
                'data' => json_encode(
                    [
                        'object' => [
                            'type' => 'Lead',
                            'module' => 'Leads',
                            'name' => 'John Doe',
                        ],
                        'changes' => [
                            'lead_source' => [
                                'field_name' => 'lead_source',
                                'before' => 'xxx',
                                'after' => 'yyy',
                            ],
                            'first_name' => [
                                'field_name' => 'first_name',
                                'before' => 'Johnathan',
                                'after' => 'John',
                            ],
                            'last_name' => [
                                'field_name' => 'last_name',
                                'before' => 'Dough',
                                'after' => 'Doe',
                            ],
                        ],
                    ]
                ),
            ],
        ];
        $records[] = []; // Need One Bogus Record that Formatter will POP

        $expectedDataChanges = [
            'object' => [
                'type' => 'Lead',
                'module' => 'Leads',
                'name' => 'John Doe',
            ],
            'changes' => [
                'lead_source' => [
                    'field_name' => 'lead_source',
                    'before' => 'xxx',
                    'after' => 'yyy',
                ],
                'first_name' => [
                    'field_name' => 'first_name',
                    'before' => 'Johnathan',
                    'after' => 'John',
                ],
                'last_name' => [
                    'field_name' => 'last_name',
                    'before' => 'Dough',
                    'after' => 'Doe',
                ],
            ],
        ];

        $sugarQueryMock = $this->createPartialMock('SugarQuery', ['execute']);
        $sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        //User Has Field Level Access to lead_source, first_name and last_name fields
        $acl->return_value = [
            'lead_source' => true,
            'first_name' => true,
            'last_name' => true,
        ];
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = [$acl];

        $lead = SugarTestLeadUtilities::createLead();

        $activitiesApi = new TestActivitiesApi();
        $actual = $activitiesApi->exec_formatResult($this->api, [], $sugarQueryMock, $lead);

        $this->assertEquals($expectedDataChanges, $actual['records'][0]['data'], 'Expected Activities Records with all data for Changed Fields');
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_CheckAlreadyPerformedForRecord_ReturnCachedResults()
    {
        $activitiesApi = new TestActivitiesApi();
        $cachedResults = [
            'Foo.123' => [
                'preview_enabled' => false,
                'preview_disabled_reason' => 'Bar!!',
            ],
        ];
        $activitiesApi->setPreviewCheckResults($cachedResults);

        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Foo', '123');

        $this->assertEquals($cachedResults['Foo.123'], $actualResult, 'Expected result to be pulled from the cached results');
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_UserHasAccess_ReturnPreviewEnabledAndEmptyReason()
    {
        $activitiesApi = new TestActivitiesApi();
        $cachedResults = [
            'Foo.123' => [
                'preview_enabled' => false,
                'preview_disabled_reason' => 'Bar!!',
            ],
        ];
        $activitiesApi->setPreviewCheckResults($cachedResults);
        $beanList = [
            'Foo' => new TestCheckAccessBean(),
        ];
        $activitiesApi->setBeanList($beanList);

        $expectedResult = [
            'preview_enabled' => true,
            'preview_disabled_reason' => '',
        ];

        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Foo', '456');

        $this->assertEquals($expectedResult, $actualResult, 'Expected result to be preview enabled with empty reason');
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_UserNoAccess_ReturnPreviewEnabledAndEmptyReason()
    {
        $activitiesApi = new TestActivitiesApi();
        $cachedResults = [
            'Foo.123' => [
                'preview_enabled' => false,
                'preview_disabled_reason' => 'Bar!!',
            ],
        ];
        $activitiesApi->setPreviewCheckResults($cachedResults);
        $mockBean = new TestCheckAccessBean();
        $mockBean->checkUserAccessResult = false;
        $beanList = [
            'Foo' => $mockBean,
        ];
        $activitiesApi->setBeanList($beanList);

        $expectedResult = [
            'preview_enabled' => false,
            'preview_disabled_reason' => 'LBL_PREVIEW_DISABLED_DELETED_OR_NO_ACCESS',
        ];

        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Foo', '789');

        $this->assertEquals($expectedResult, $actualResult, 'Expected result to not be preview enabled with correct reason');
    }

    /**
     * @covers ActivitiesApi::checkParentPreviewEnabled
     */
    public function testCheckParentPreviewEnabled_RecordDeleted_ReturnPreviewEnabledAndEmptyReason()
    {
        //full functional test for this to ensure that checkUserAccess returns false for deleted records
        $lead = SugarTestLeadUtilities::createLead();
        $lead->mark_deleted($lead->id);

        $expectedResult = [
            'preview_enabled' => false,
            'preview_disabled_reason' => 'LBL_PREVIEW_DISABLED_DELETED_OR_NO_ACCESS',
        ];

        $activitiesApi = new TestActivitiesApi();
        $actualResult = $activitiesApi->exec_checkParentPreviewEnabled($this->api->user, 'Leads', $lead->id);

        $this->assertEquals($expectedResult, $actualResult, 'Expected result to not be preview enabled with correct reason');
    }
}

class TestActivitiesApi extends ActivitiesApi
{
    public function exec_formatResult(ServiceBase $api, array $args, SugarQuery $query, SugarBean $bean = null)
    {
        return $this->formatResult($api, $args, $query, $bean);
    }

    public function exec_checkParentPreviewEnabled($user, $module, $id)
    {
        return $this->checkParentPreviewEnabled($user, $module, $id);
    }

    public function setBeanList($beanList)
    {
        self::$beanList = $beanList;
    }

    public function setPreviewCheckResults($previewCheckResults)
    {
        self::$previewCheckResults = $previewCheckResults;
    }
}

class TestSugarACLStatic extends SugarACLStatic
{
    public $return_value = null;

    public function checkFieldList($module, $field_list, $action, $context)
    {
        return $this->return_value;
    }
}

class TestCheckAccessBean
{
    public $checkUserAccessResult = true;

    public function checkUserAccess($user)
    {
        return $this->checkUserAccessResult;
    }
}
