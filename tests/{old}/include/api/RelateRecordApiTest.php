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

use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use PHPUnit\Framework\TestCase;

class RelateRecordApiTest extends TestCase
{
    /**
     * @var RelateRecordApi
     */
    private $api;

    protected $createdBeans = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->api = new RelateRecordApi();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdBeans as $bean) {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProspectListsUtilities::removeAllCreatedProspectLists();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
    }

    public function testCreateRelatedRecord()
    {
        $relateApiArgs = ['param1' => 'value1'];
        $moduleApiArgs = ['param2' => 'value2'];
        $service = SugarTestRestUtilities::getRestServiceMock();

        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
        $link->expects($this->any())
            ->method('getRelatedModuleName')
            ->willReturn('TestModule');

        $primaryBean = new SugarBean();
        $primaryBean->testLink = $link;

        $primaryBeanClone = clone $primaryBean;

        $relatedBean = new SugarBean();
        $relatedBean->field_defs = [];
        $moduleApi = $this->getMockBuilder('ModuleApi')
            ->setMethods(['createBean'])
            ->getMock();
        $moduleApi->expects($this->once())
            ->method('createBean')
            ->with($service, $moduleApiArgs)
            ->willReturn($relatedBean);

        /** @var RelateRecordApi|MockObject $api */
        $api = $this->getMockBuilder('RelateRecordApi')
            ->setMethods([
                'loadBean',
                'reloadBean',
                'checkRelatedSecurity',
                'getModuleApi',
                'getModuleApiArgs',
                'formatNearAndFarRecords',
                'getRecordByRelation',
            ])
            ->getMock();
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($primaryBean);
        $api->expects($this->any())
            ->method('reloadBean')
            ->willReturn($primaryBeanClone);
        $api->expects($this->any())
            ->method('checkRelatedSecurity')
            ->willReturn(['testLink']);
        $api->expects($this->once())
            ->method('getModuleApi')
            ->with($service, 'TestModule')
            ->willReturn($moduleApi);
        $api->expects($this->once())
            ->method('getModuleApiArgs')
            ->with($relateApiArgs, 'TestModule')
            ->willReturn($moduleApiArgs);
        $api->expects($this->any())
            ->method('getRecordByRelation')
            ->willReturn([]);

        $api->createRelatedRecord($service, $relateApiArgs);
    }

    public function testLoadModuleApiSuccess()
    {
        $moduleApi = $this->loadModuleApi('Users');
        $this->assertInstanceOf('UsersApi', $moduleApi);
    }

    public function testLoadModuleApiFailure()
    {
        $moduleApi = $this->loadModuleApi('UnknownModule');
        $this->assertNull($moduleApi);
    }

    private function loadModuleApi($module)
    {
        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        return SugarTestReflection::callProtectedMethod($api, 'loadModuleApi', [$service, $module]);
    }

    /**
     * @dataProvider getModuleApiProvider
     */
    public function testGetModuleApi($loaded, $expected)
    {
        $service = SugarTestRestUtilities::getRestServiceMock();

        $api = $this->createPartialMock('RelateRecordApi', ['loadModuleApi']);
        $api->expects($this->once())
            ->method('loadModuleApi')
            ->with($service, 'TheModule')
            ->willReturn($loaded);

        $actual = SugarTestReflection::callProtectedMethod($api, 'getModuleApi', [$service, 'TheModule']);
        $this->assertInstanceOf($expected, $actual);
    }

    public static function getModuleApiProvider()
    {
        return [
            'module-specific' => [new UsersApi(), 'UsersApi'],
            'non-module' => [new stdClass(), 'ModuleApi'],
            'default' => [null, 'ModuleApi'],
        ];
    }

    /**
     * @dataProvider getModuleApiArgsProvider
     */
    public function testGetModuleApiArgs(array $args, $module, array $expected)
    {
        $api = new RelateRecordApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getModuleApiArgs', [$args, $module]);
        $this->assertEquals($expected, $actual);
    }

    public static function getModuleApiArgsProvider()
    {
        return [
            [
                [
                    'module' => 'PrimaryModule',
                    'record' => 'PrimaryRecord',
                    'link_name' => 'LinkName',
                    'property' => 'value',
                ],
                'RelateModule',
                [
                    'relate_module' => 'PrimaryModule',
                    'relate_record' => 'PrimaryRecord',
                    'module' => 'RelateModule',
                    'property' => 'value',
                ],
            ],
        ];
    }

    public function testCreateRelatedNote()
    {
        $contact = BeanFactory::newBean('Contacts');
        $contact->last_name = 'Related Record Unit Test Contact';
        $contact->save();
        // Get the real data that is in the system, not the partial data we have saved
        $contact->retrieve($contact->id);
        $this->createdBeans[] = $contact;
        $noteName = 'Related Record Unit Test Note';

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'notes',
            'name' => $noteName,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['related_record']['id']);
        $this->assertEquals($noteName, $result['related_record']['name']);

        $note = BeanFactory::getBean('Notes', $result['related_record']['id']);
        // Get the real data that is in the system, not the partial data we have saved
        $note->retrieve($note->id);
        $this->createdBeans[] = $note;

        $contact->load_relationship('notes');
        $relatedNoteIds = $contact->notes->get();
        $this->assertNotEmpty($relatedNoteIds);
        $this->assertEquals($note->id, $relatedNoteIds[0]);
    }

    /**
     * @group createRelatedLinksFromRecordList
     */
    public function testCreateRelatedLinksFromRecordList_AllRelationshipsAddedSuccessfully()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $records = [$account1->id, $account2->id];
        $recordListId = RecordListFactory::saveRecordList($records, 'Reports');

        $mockAPI = self::createPartialMock('RelateRecordApi', ['loadBean', 'requireArgs']);
        $mockAPI->expects(self::once())
            ->method('loadBean')
            ->will(self::returnValue($prospectList));

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'ProspectLists',
            'record' => $prospectList->id,
            'link_name' => 'accounts',
            'remote_id' => $recordListId,
        ];

        $result = $mockAPI->createRelatedLinksFromRecordList($api, $args);
        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['record']['id']);
        $this->assertEquals(2, safeCount($result['related_records']['success']));
        $this->assertEquals(0, safeCount($result['related_records']['error']));

        RecordListFactory::deleteRecordList($recordListId);
    }

    /**
     * @group createRelatedLinksFromRecordList
     */
    public function testCreateRelatedLinksFromRecordList_RelationshipsFailedToAdd()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $records = [$account1->id, $account2->id];
        $recordListId = RecordListFactory::saveRecordList($records, 'Reports');


        $relationshipStub = $this->getMockRelationship();
        $relationshipStub->expects($this->once())
            ->method('add')
            ->will($this->returnValue([$account1->id]));

        $stub = $this->createPartialMock(BeanFactory::getObjectName('ProspectLists'), ['getModuleName']);
        $stub->method('getModuleName')->willReturn('ProspectLists');
        $stub->accounts = $relationshipStub;

        $mockAPI = self::createPartialMock('RelateRecordApi', ['loadBean', 'requireArgs', 'checkRelatedSecurity']);
        $mockAPI->expects(self::once())
            ->method('loadBean')
            ->will(self::returnValue($stub));
        $mockAPI->expects(self::once())
            ->method('requireArgs')
            ->will(self::returnValue(true));
        $mockAPI->expects(self::once())
            ->method('checkRelatedSecurity')
            ->will(self::returnValue(['accounts']));

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'ProspectLists',
            'record' => $prospectList->id,
            'link_name' => 'accounts',
            'remote_id' => $recordListId,
        ];

        $result = $mockAPI->createRelatedLinksFromRecordList($api, $args);

        $this->assertNotEmpty($result['record']);
        $this->assertEquals(1, safeCount($result['related_records']['success']));
        $this->assertEquals(1, safeCount($result['related_records']['error']));

        RecordListFactory::deleteRecordList($recordListId);
    }

    /**
     * Helper to get a mock relationship
     * @return mixed
     */
    protected function getMockRelationship()
    {
        return $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetRelatedFieldsReturnsOnlyFieldsForPassedInLink()
    {
        $opp = $this->getMockBuilder('Opportunity')->setMethods(['save'])->getMock();
        $contact = $this->getMockBuilder('Contact')->setMethods(['save'])->getMock();

        $rr_api = new RelateRecordApi();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $fields = SugarTestReflection::callProtectedMethod(
            $rr_api,
            'getRelatedFields',
            [
                $api,
                // all of the below fields contain a rname_link.
                [
                    'accept_status_calls' => '',
                    'accept_status_meetings' => '',
                    'opportunity_role' => 'Unit Test',
                ],
                $opp,
                'contacts',
                $contact,
            ]
        );

        // this should only contain one field as opportunity_role is the only valid one for the contacts link
        $this->assertCount(1, $fields);
    }

    public function testDeleteRelatedLink()
    {
        $call = SugarTestCallUtilities::createCall();
        $contact = SugarTestContactUtilities::createContact();

        $this->assertTrue($call->load_relationship('contacts'), 'Relationship is not loaded');
        $call->contacts->add($contact);

        $call = BeanFactory::retrieveBean('Calls', $call->id, ['use_cache' => false]);
        $this->assertEquals($contact->id, $call->contact_id, 'Contact is not linked to call');

        // unregister bean in order to make sure API won't take it from cache
        // where the call is stored w/o linked contact
        BeanFactory::unregisterBean('Calls', $call->id);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->deleteRelatedLink($service, [
            'module' => 'Calls',
            'record' => $call->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
        ]);

        $this->assertArrayHasKey('record', $response);
        $this->assertEquals($call->id, $response['record']['id'], 'Call is not returned by API');
        $this->assertEmpty($response['record']['contact_id'], 'Contact is not unlinked from call');
    }

    /**
     * Before Save hook should be called only once.
     * @ticket PAT-769
     */
    public function testBeforeSaveOnCreateRelatedRecord()
    {
        LogicHook::refreshHooks();
        $hook = [
            'Notes',
            'before_save',
            [1, 'Notes::before_save', __FILE__, 'SugarBeanBeforeSaveTestHook', 'beforeSave'],
        ];
        call_user_func_array('check_logic_hook_file', $hook);

        $contact = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'notes',
            'name' => 'Test Note',
            'assigned_user_id' => $api->user->id,
        ];
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        call_user_func_array('remove_logic_hook', $hook);
        $this->createdBeans[] = BeanFactory::getBean('Notes', $result['related_record']['id']);
        $expectedCount = SugarBeanBeforeSaveTestHook::$callCounter;
        SugarBeanBeforeSaveTestHook::$callCounter = 0;

        $this->assertEquals(1, $expectedCount);
    }

    /**
     * opportunity_role should be saved when creating related contact
     * @ticket PAT-1281
     */
    public function testCreateRelatedRecordRelateFields()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'opportunity_role' => 'Technical Decision Maker',
            'module' => 'Opportunities',
            'record' => $opportunity->id,
            'link_name' => 'contacts',
            'assigned_user_id' => $api->user->id,
        ];

        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        $this->assertEquals($result['related_record']['opportunity_role'], 'Technical Decision Maker');
    }

    /**
     * @dataProvider normalizeLinkIdsSuccessProvider
     */
    public function testNormalizeLinkIdsSuccess($ids, array $expected)
    {
        $actual = $this->normalizeLinkIds($ids);
        $this->assertEquals($expected, $actual);
    }

    public static function normalizeLinkIdsSuccessProvider()
    {
        return [
            [
                ['id1', ['id' => 'id2', 'key' => 'value']],
                [
                    'id1' => [],
                    'id2' => ['key' => 'value'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider normalizeLinkIdsFailureProvider
     */
    public function testNormalizeLinkIdsFailure($ids)
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->normalizeLinkIds($ids);
    }

    public static function normalizeLinkIdsFailureProvider()
    {
        return [
            'non-array' => ['id'],
            'no-id' => [
                [
                    ['key' => 'value'],
                ],
            ],
        ];
    }

    private function normalizeLinkIds($ids)
    {
        $api = new RelateRecordApi();
        return SugarTestReflection::callProtectedMethod($api, 'normalizeLinkIds', [$ids]);
    }

    /**
     * @test
     */
    public function erasedFields()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->erase(FieldList::fromArray(['first_name']), false);

        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->load_relationship('contacts');
        $opportunity->contacts->add($contact);

        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $this->api->getRelatedRecord($service, [
            'module' => $opportunity->module_name,
            'record' => $opportunity->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
            'erased_fields' => true,
        ]);

        $this->assertSame(['first_name'], $response['_erased_fields']);
    }
}

class SugarBeanBeforeSaveTestHook
{
    public static $callCounter = 0;

    public function beforeSave($bean, $event, $arguments)
    {
        self::$callCounter++;
    }

    public function testCreateRecordACL()
    {
        $contact = SugarTestContactUtilities::createContact();
        $case = $this->getMockBuilder('SugarBean')
            ->setMethods(['ACLAccess', 'save'])
            ->getMock();
        $case->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));
        $case->field_defs = [];
        $case->module_dir = 'Cases';
        $case->module_name = 'Cases';
        $case->id = 'the-id';

        /** @var RelateRecordApi|MockObject $api */
        $api = $this->getMockBuilder('RelateRecordApi')
            ->setMethods(['loadBean'])
            ->getMock();
        $api->expects($this->any())
            ->method('loadBean')
            ->will($this->onConsecutiveCalls($contact, $case));

        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->createRelatedRecord($service, [
            'link_name' => 'cases',
        ]);

        $this->assertArrayHasKey('related_record', $response);
        $this->assertArrayHasKey('_acl', $response['related_record']);
    }
}
