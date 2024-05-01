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
 * @group activities
 * @group ActivityStream
 * @group activities_queue
 * @coversDefaultClass ActivityQueueManager
 */
class ActivityQueueManagerTest extends TestCase
{
    public const BOGUS_USER = '0';
    public const USER_ONE = '1';
    public const USER_TWO = '2';
    public const PORTAL_USER = '3';

    protected function setUp(): void
    {
        parent::setup();
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_TeamIDsChangedToNames_ChangesOccurredNormally()
    {
        $contact = SugarTestContactUtilities::createContact();
        $teamBefore = SugarTestTeamUtilities::createAnonymousTeam();
        $teamAfter = SugarTestTeamUtilities::createAnonymousTeam();

        $activityData = [
            'object' => [
                'name' => $contact->full_name,
                'type' => 'Contact',
                'module' => 'Contacts',
                'id' => $contact->id,
            ],
            'changes' => [
                'team_id' => [
                    'field_name' => 'team_id',
                    'data_type' => 'id',
                    'before' => $teamBefore->id,
                    'after' => $teamAfter->id,
                ],
            ],
        ];

        $expectedData = [
            'object' => [
                'name' => $contact->full_name,
                'type' => 'Contact',
                'module' => 'Contacts',
                'id' => $contact->id,
            ],
            'changes' => [
                'team_id' => [
                    'field_name' => 'team_id',
                    'data_type' => 'id',
                    'before' => $teamBefore->name,
                    'after' => $teamAfter->name,
                ],
            ],
        ];

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', [$contact, &$activityData]);

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_AssignedUserIDsChangedToNames_ChangesOccurredNormally()
    {
        $lead = SugarTestLeadUtilities::createLead();
        $assignedUser = SugarTestUserUtilities::createAnonymousUser();

        $activityData = [
            'object' => [
                'name' => $lead->full_name,
                'type' => 'Lead',
                'module' => 'Leads',
                'id' => $lead->id,
            ],
            'changes' => [
                'assigned_user_id' => [
                    'field_name' => 'assigned_user_id',
                    'data_type' => 'id',
                    'before' => '',
                    'after' => $assignedUser->id,
                ],
            ],
        ];

        $expectedData = [
            'object' => [
                'name' => $lead->full_name,
                'type' => 'Lead',
                'module' => 'Leads',
                'id' => $lead->id,
            ],
            'changes' => [
                'assigned_user_id' => [
                    'field_name' => 'assigned_user_id',
                    'data_type' => 'id',
                    'before' => '',
                    'after' => $assignedUser->name,
                ],
            ],
        ];

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', [$lead, &$activityData]);

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_AccountParentIdNoParentType_ChangesOccurredNormally()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $activityData = [
            'object' => [
                'name' => $account1->name,
                'type' => 'Account',
                'module' => 'Accounts',
                'id' => $account1->id,
            ],
            'changes' => [
                'parent_id' => [
                    'field_name' => 'parent_id',
                    'data_type' => 'id',
                    'before' => '',
                    'after' => $account2->id,
                ],
            ],
        ];

        $expectedData = [
            'object' => [
                'name' => $account1->name,
                'type' => 'Account',
                'module' => 'Accounts',
                'id' => $account1->id,
            ],
            'changes' => [
                'parent_id' => [
                    'field_name' => 'parent_id',
                    'data_type' => 'id',
                    'before' => '',
                    'after' => $account2->name,
                ],
            ],
        ];

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', [$account1, &$activityData]);

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testChangeFields_BeanParentIdIncludesParentType_ChangesOccurredNormally()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $contact = SugarTestContactUtilities::createContact();

        $contact->parent_type = 'Accounts';
        $contact->parent_id = $account1;
        $contact->save();

        $activityData = [
            'object' => [
                'name' => $contact->full_name,
                'type' => 'Account',
                'module' => 'Accounts',
                'id' => $contact->id,
            ],
            'changes' => [
                'parent_id' => [
                    'field_name' => 'parent_id',
                    'data_type' => 'id',
                    'before' => $account1->id,
                    'after' => $account2->id,
                ],
            ],
        ];

        $expectedData = [
            'object' => [
                'name' => $contact->full_name,
                'type' => 'Account',
                'module' => 'Accounts',
                'id' => $contact->id,
            ],
            'changes' => [
                'parent_id' => [
                    'field_name' => 'parent_id',
                    'data_type' => 'id',
                    'before' => $account1->name,
                    'after' => $account2->name,
                ],
            ],
        ];

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', [$account1, &$activityData]);

        $this->assertEquals($expectedData, $activityData);
    }

    /**
     * @covers ActivityQueueManager::prepareChanges
     */
    public function testPrepareChanges_FieldChangesIncludeActivityDisabledField_OnlyNonDisabledFieldsReturned()
    {
        $contact = BeanFactory::newBean('Contacts');

        //mock out field defs
        $originalFieldDefs = $contact->field_defs;
        $contact->field_defs = [
            'foo' => [
                'name' => 'foo',
                'activity_enabled' => false,
            ],
            'bar' => [
                'name' => 'bar',
                'audited' => true,
                'activity_enabled' => true,
            ],
            'baz' => [
                'name' => 'baz',
            ],
            'qux' => [
                'name' => 'qux',
                'audited' => false,
            ],
            'quux' => [
                'name' => 'quux',
                'audited' => true,
            ],
            'qir' => [
                'name' => 'qir',
                'audited' => false,
                'activity_enabled' => true,
            ],
            'qiir' => [
                'name' => 'qiir',
                'audited' => true,
                'activity_enabled' => false,
            ],
            'biiru' => [
                'name' => 'biiru',
                'activity_enabled' => true,
            ],
        ];

        $activityData = [
            'changes' => [
                'foo' => [
                    'field_name' => 'foo',
                    'data_type' => 'varchar',
                    'before' => 'fooval1',
                    'after' => 'fooval2',
                ],
                'bar' => [
                    'field_name' => 'bar',
                    'data_type' => 'varchar',
                    'before' => 'barval1',
                    'after' => 'barval2',
                ],
                'baz' => [
                    'field_name' => 'baz',
                    'data_type' => 'varchar',
                    'before' => 'bazval1',
                    'after' => 'bazval2',
                ],
                'qux' => [
                    'field_name' => 'qux',
                    'data_type' => 'varchar',
                    'before' => 'qux1',
                    'after' => 'qux2',
                ],
                'quux' => [
                    'field_name' => 'quux',
                    'data_type' => 'varchar',
                    'before' => 'quux1',
                    'after' => 'quux2',
                ],
                'qir' => [
                    'field_name' => 'qir',
                    'data_type' => 'varchar',
                    'before' => 'qirval1',
                    'after' => 'qirval2',
                ],
                'qiir' => [
                    'field_name' => 'qiir',
                    'data_type' => 'varchar',
                    'before' => 'qiirval1',
                    'after' => 'qiirval2',
                ],
                'biiru' => [
                    'field_name' => 'biiru',
                    'data_type' => 'varchar',
                    'before' => 'biiryval1',
                    'after' => 'biiruval2',
                ],
            ],
        ];

        $expectedData = [
            'changes' => [
                'bar' => [
                    'field_name' => 'bar',
                    'data_type' => 'varchar',
                    'before' => 'barval1',
                    'after' => 'barval2',
                ],
                'quux' => [
                    'field_name' => 'quux',
                    'data_type' => 'varchar',
                    'before' => 'quux1',
                    'after' => 'quux2',
                ],
            ],
        ];

        $actManager = new ActivityQueueManager();
        SugarTestReflection::callProtectedMethod($actManager, 'prepareChanges', [$contact, &$activityData]);

        //restore contact field defs
        $contact->field_defs = $originalFieldDefs;

        $this->assertEquals($expectedData, $activityData);
    }

    public function dataProviderForAddSubscriptions()
    {
        return [
            /*  1 */ [self::USER_ONE, self::USER_ONE, false, 1],
            /*  2 */ [self::USER_ONE, self::USER_TWO, false, 2],
            /*  3 */ [self::USER_ONE, self::USER_TWO, true, 1],
            /*  4 */ [self::USER_ONE, self::PORTAL_USER, false, 1],
            /*  5 */ [self::USER_ONE, self::BOGUS_USER, false, 1],
            /*  6 */ [self::PORTAL_USER, self::USER_TWO, false, 1],
            /*  7 */ [self::PORTAL_USER, self::USER_TWO, true, 0],
            /*  8 */ [self::BOGUS_USER, self::USER_TWO, false, 1],
            /*  9 */ [self::BOGUS_USER, self::USER_TWO, true, 0],
            /* 10 */ [self::BOGUS_USER, self::BOGUS_USER, false, 0],
            /* 11 */ [self::PORTAL_USER, self::PORTAL_USER, false, 0],
        ];
    }

    /**
     * @dataProvider dataProviderForAddSubscriptions
     * @covers       ActivityQueueManager::createOrUpdate
     * @covers       ActivityQueueManager::addRecordSubscriptions
     */
    public function testAddSubscribers(
        $arg_assigned_user,
        $arg_createdby_user,
        $isUpdate,
        $subscriptions
    ) {

        $bean = SugarTestContactUtilities::createContact();

        if ($arg_assigned_user == self::BOGUS_USER) {
            $assignedUser = new User();
            $assignedUser->id = '000A';
        } else {
            $assignedUser = SugarTestUserUtilities::createAnonymousUser();
            if ($arg_assigned_user == self::PORTAL_USER) {
                $assignedUser->portal_only = true;
                $assignedUser->save();
            }
        }

        if ($arg_createdby_user == $arg_assigned_user) {
            $createdByUser = $assignedUser;
        } elseif ($arg_createdby_user == self::BOGUS_USER) {
            $createdByUser = new User();
            $createdByUser->id = '000B';
        } else {
            $createdByUser = SugarTestUserUtilities::createAnonymousUser();
            if ($arg_createdby_user == self::PORTAL_USER) {
                $createdByUser->portal_only = true;
                $createdByUser->save();
            }
        }

        $bean->assigned_user_id = $assignedUser->id;
        $bean->created_by = $createdByUser->id;

        $args = [
            'isUpdate' => $isUpdate,
            'dataChanges' => ['assigned_user_id' => []],
        ];

        $mockActivity = self::getMockBuilder('Activity')->setMethods(['save', 'processRecord'])->getMock();
        $mockActivity->expects($this->once())
            ->method('save');
        $mockActivity->expects($this->once())
            ->method('processRecord');

        $actManager = $this->createPartialMock(
            'ActivityQueueManager',
            [
                'subscribeUserToRecord',
                'prepareChanges',
            ]
        );
        $actManager->expects($this->exactly($subscriptions))
            ->method('subscribeUserToRecord');

        Activity::enable();

        SugarTestReflection::callProtectedMethod($actManager, 'createOrUpdate', [$bean, $args, $mockActivity]);

        Activity::restoreToPreviousState();
    }

    public function dataProviderForActivityMessageCreation()
    {
        return [
            [true, 'after_save', 'createOrUpdate'],
            [false, 'after_save', null],
            [true, 'before_save', null],
            [true, 'after_relationship_add', 'link'],
            [true, 'after_relationship_delete', 'unlink'],
        ];
    }

    /**
     * @covers       ActivityQueueManager::eventDispatcher
     * @dataProvider dataProviderForActivityMessageCreation
     */
    public function testEventDispatcher_ActivityMessageCreation($activityEnabled, $event, $expectedAction)
    {
        $actions = [
            'createOrUpdate',
            'link',
            'unlink',
        ];
        $contact = BeanFactory::newBean('Contacts');
        $contact->id = create_guid();


        if ($activityEnabled) {
            Activity::enable();
        }

        $actManager = self::createPartialMock(
            'ActivityQueueManager',
            ['isValidLink', 'createOrUpdate', 'link', 'unlink']
        );
        $actManager->expects($this->any())->method('isValidLink')->will($this->returnValue(true));
        foreach ($actions as $action) {
            if ($action === $expectedAction) {
                $actManager->expects($this->once())->method($action)->will($this->returnValue(false));
            } else {
                $actManager->expects($this->never())->method($action);
            }
        }
        $actManager->eventDispatcher($contact, $event, []);

        if ($activityEnabled) {
            Activity::restoreToPreviousState();
        }
    }

    /**
     * @covers       ActivityQueueManager::isEnabledForModule
     * @dataProvider dataProviderModuleBlackListWhiteListEnabled
     */
    public function testIsEnabledForModule_WithModuleBlackListAndWhiteList_ActivityStreamEnabled($moduleName, $expected, $assertMessage)
    {
        $aqm = new ActivityQueueManager();
        Activity::enable();
        $isEnabledForModule = $aqm->isEnabledForModule($moduleName);
        Activity::restoreToPreviousState();
        $this->assertEquals($expected, $isEnabledForModule, $assertMessage);
    }

    public static function dataProviderModuleBlackListWhiteListEnabled()
    {
        return [
            ['Forecasts', false, 'expected blacklist module to be disabled'],
            ['Notes', true, 'expected whitelist module to be enabled'],
            ['Foo', false, 'expected nonexistent module to be disabled'],
        ];
    }

    /**
     * @covers       ActivityQueueManager::isEnabledForModule
     * @dataProvider dataProviderModuleBlackListWhiteListDisabled
     */
    public function testIsEnabledForModule_WithModuleBlackListAndWhiteList_ActivityStreamDisabled($moduleName, $expected, $assertMessage)
    {
        $aqm = new ActivityQueueManager();
        $this->assertEquals($expected, $aqm->isEnabledForModule($moduleName), $assertMessage);
    }

    public static function dataProviderModuleBlackListWhiteListDisabled()
    {
        return [
            ['Forecasts', false, 'expected blacklist module to be disabled'],
            ['Notes', false, 'expected whitelist module to be disabled'],
            ['Foo', false, 'expected nonexistent module to be disabled'],
        ];
    }

    /**
     * @covers       ActivityQueueManager::isEnabledForModule
     * @dataProvider dataProviderDifferentActivityAndAuditFlags
     */
    public function testIsEnabledForModule_DifferentActivityAndAuditFlags($auditFlag, $activityFlag, $expected, $assertMessage)
    {
        global $dictionary;
        $moduleNameSingular = 'Contact';
        $moduleNamePlural = 'Contacts';
        $auditBefore = $dictionary[$moduleNameSingular]['audited'];
        $activityBefore = $dictionary[$moduleNameSingular]['activity_enabled'];

        $dictionary[$moduleNameSingular]['audited'] = $auditFlag;
        $dictionary[$moduleNameSingular]['activity_enabled'] = $activityFlag;
        $aqm = new ActivityQueueManager();

        Activity::enable();
        $isEnabledForModule = $aqm->isEnabledForModule($moduleNamePlural);
        Activity::restoreToPreviousState();

        $this->assertEquals($expected, $isEnabledForModule, $assertMessage);

        //cleanup
        $dictionary[$moduleNameSingular]['audited'] = $auditBefore;
        $dictionary[$moduleNameSingular]['activity_enabled'] = $activityBefore;
    }

    public static function dataProviderDifferentActivityAndAuditFlags()
    {
        return [
            [true, true, true, 'expected module with activity and audit enabled to return true'],
            [false, false, false, 'expected module with activity and audit disabled to return false'],
            [true, false, false, 'expected module with activity disabled to return false'],
            [false, true, false, 'expected module with audit disabled to return false'],
        ];
    }

    /**
     * @covers       ActivityQueueManager::assignmentChanged
     * @dataProvider dataProviderAssignedUserChanged
     */
    public function testAssignmentChanged($auditedChanges, $allDataChanges, $expected, $assertMessage)
    {
        $bean = BeanFactory::newBean('Contacts');

        //mock out db manager
        $dbManagerClass = get_class($bean->db);
        $dbManager = self::createPartialMock($dbManagerClass, ['getDataChanges']);
        $dbManager->expects($this->any())->method('getDataChanges')->will($this->returnValue($allDataChanges));
        $bean->db = $dbManager;

        $auditedDataChanges = [
            'dataChanges' => $auditedChanges,
        ];

        $actManager = new ActivityQueueManager();
        $actual = SugarTestReflection::callProtectedMethod($actManager, 'assignmentChanged', [$bean, $auditedDataChanges]);
        $this->assertEquals($expected, $actual, $assertMessage);
    }

    public static function dataProviderAssignedUserChanged()
    {
        return [
            [
                ['assigned_user_id' => []],
                ['assigned_user_id' => [], 'foo' => []],
                true,
                'Assignment changed when assigned_user_id is in audited changes',
            ],
            [
                [],
                ['assigned_user_id' => [], 'foo' => []],
                true,
                'Assignment changed when assigned_user_id is not audited, but still changed',
            ],
            [
                [],
                ['foo' => []],
                false,
                'Assignment did not change when assigned_user_id is not changed at all (audited or otherwise)',
            ],
        ];
    }

    /**
     * @covers       ActivityQueueManager::isLinkDupe
     * @dataProvider dataProviderIsLinkDupe
     */
    public function testIsLinkDupeReturnsValidResult(array $link1, array $link2, $expected)
    {
        $aqm = new ActivityQueueManager();
        $this->assertEquals($expected, SugarTestReflection::callProtectedMethod($aqm, 'isLinkDupe', [$link1, $link2]));
    }

    public static function dataProviderIsLinkDupe()
    {
        $link1 = [
            'id' => 'foo',
            'module' => 'Accounts',
            'related_module' => 'Leads',
            'related_id' => 'bar',
            'link' => 'leads',
            'relationship' => 'account_leads',
        ];

        $link2 = [
            'related_id' => 'foo',
            'related_module' => 'Accounts',
            'module' => 'Leads',
            'id' => 'bar',
            'link' => 'account',
            'relationship' => 'account_leads',
        ];

        $link3 = [
            'id' => 'baz',
            'module' => 'Leads',
            'related_id' => 'bar',
            'related_module' => 'Accounts',
            'link' => 'account',
            'relationship' => 'account_leads',
        ];

        $link4 = [
            'id' => 'a1',
            'module' => 'Accounts',
            'related_id' => 'c1',
            'related_module' => 'Contacts',
            'link' => 'account',
            'relationship' => 'account_contacts',
        ];

        $link5 = [
            'id' => 'a1',
            'module' => 'Accounts',
            'related_id' => 'c2',
            'related_module' => 'Contacts',
            'link' => 'account',
            'relationship' => 'account_contacts',
        ];

        return [
            [$link1, $link2, true],
            [$link1, $link3, false],
            [$link2, $link3, false],
            [$link4, $link5, false],
        ];
    }

    public function getBeanAttributesDataProvider()
    {
        return [
            'Non-Person Bean Has Name' => [
                'SugarTestAccountUtilities::createAccount',
                [],
                [
                    'name' => 'John Doe',
                ],
                'John Doe',
            ],
            'Non-Person Bean Name Empty' => [
                'SugarTestAccountUtilities::createAccount',
                [],
                [
                    'name' => '',
                ],
                '',
            ],
            'Person Bean Has Name' => [
                'SugarTestLeadUtilities::createLead',
                [],
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
                'John Doe',
            ],
            'Person Bean Name Empty, First Name Erased' => [
                'SugarTestContactUtilities::createContact',
                [
                    'first_name',
                ],
                [
                    'first_name' => '',
                    'last_name' => 'Doe',
                ],
                'Doe',
            ],
            'Person Bean Name Empty, Last Name Erased' => [
                'SugarTestContactUtilities::createContact',
                [
                    'last_name',
                ],
                [
                    'first_name' => 'John',
                    'last_name' => '',
                ],
                'John',
            ],
            'Person Bean Name Empty, First Name and Last Name Erased' => [
                'SugarTestContactUtilities::createContact',
                [
                    'first_name',
                    'last_name',
                ],
                [
                    'first_name' => '',
                    'last_name' => '',
                ],
                'LBL_VALUE_ERASED',
            ],
            'Person Bean Name Empty, Not Erased' => [
                'SugarTestContactUtilities::createContact',
                [],
                [
                    'first_name' => '',
                    'last_name' => '',
                ],
                '',
            ],
        ];
    }

    /**
     * @dataProvider getBeanAttributesDataProvider
     * @covers ::getBeanAttributes
     * @covers ::getDisplayName
     */
    public function testGetBeanAttributes($testUtilCreateFunction, array $erasedFields, array $data, $expectedName)
    {
        $bean = call_user_func_array($testUtilCreateFunction, ['', $data]);

        // Seed the erased fields.
        if (!empty($erasedFields)) {
            DBManagerFactory::getConnection()->executeStatement(
                'INSERT INTO erased_fields (bean_id, table_name, data) VALUES (?,?,?)',
                [
                    $bean->id,
                    $bean->getTableName(),
                    json_encode($erasedFields),
                ]
            );
        }

        $result = ActivityQueueManager::getBeanAttributes($bean);

        $this->assertSame($expectedName, $result['name']);
    }
}
