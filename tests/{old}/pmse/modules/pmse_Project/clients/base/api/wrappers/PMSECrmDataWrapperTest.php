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

class PMSECrmDataWrapperTest extends TestCase
{
    /**
     * List of items to delete at the end of the test
     * @var array
     */
    protected $deleteAssets = [];

    /**
     * The index for the number of records
     * @var integer
     */
    protected $recordIndex = 0;

    /**
     * Mock project ID
     * @var string
     */
    protected $prjId = 'test-project-1';

    /**
     * Module used for testing
     * @var string
     */
    protected $testModule = 'Accounts';

    /**
     * @var PMSECrmDataWrapper
     */
    protected $object;
    protected $beanFactory;
    protected $adamBeanFactory;

    protected $processDefinitionBean;
    protected $activityDefinitionBean;
    protected $dynaformBean;
    protected $projectBean;
    protected $processBean;
    protected $activityBean;
    protected $ruleSetBean;
    protected $teamsBean;
    protected $usersBean;
    protected $emailTemplateBean;
    protected $inboxBean;

    protected $sugarQueryMock;

    protected $teams;
    protected $users;

    protected $beanList;
    protected $db;

    protected $originals = [];

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', ['save' => false, 'is_admin' => 1]);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $this->originals['current_user'] = $GLOBALS['current_user'];
        $this->originals['db'] = $GLOBALS['db'];

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->beanList = [
            'ACLRoles' => 'ACLRole',
            'ACLActions' => 'ACLAction',
            'ACLFields' => 'ACLField',
            'Leads' => 'Lead',
            'Cases' => 'aCase',
            'Bugs' => 'Bug',
            'ProspectLists' => 'ProspectList',
            'Prospects' => 'Prospect',
            'Project' => 'Project',
            'ProjectTask' => 'ProjectTask',
            'Campaigns' => 'Campaign',
            'EmailMarketing' => 'EmailMarketing',
            'CampaignLog' => 'CampaignLog',
            'CampaignTrackers' => 'CampaignTracker',
            'Releases' => 'Release',
            'EmailMan' => 'EmailMan',
            'Schedulers' => 'Scheduler',
            'SchedulersJobs' => 'SchedulersJob',
            'Contacts' => 'Contact',
            'Accounts' => 'Account',
            'DynamicFields' => 'DynamicField',
            'EditCustomFields' => 'FieldsMetaData',
            'Opportunities' => 'Opportunity',
            'EmailTemplates' => 'EmailTemplate',
            'Notes' => 'Note',
            'Calls' => 'Call',
            'Emails' => 'Email',
            'Meetings' => 'Meeting',
            'Tasks' => 'Task',
            'Users' => 'User',
            'Currencies' => 'Currency',
            'Trackers' => 'Tracker',
            'Connectors' => 'Connectors',
            'TrackerSessions' => 'TrackerSession',
            'TrackerPerfs' => 'TrackerPerf',
            'TrackerQueries' => 'TrackerQuery',
            'Import_1' => 'ImportMap',
            'Import_2' => 'UsersLastImport',
            'Versions' => 'Version',
            'Administration' => 'Administration',
            'vCals' => 'vCal',
            'CustomFields' => 'CustomFields',
            'Documents' => 'Document',
            'DocumentRevisions' => 'DocumentRevision',
            'Audit' => 'Audit',
            'InboundEmail' => 'InboundEmail',
            'SavedSearch' => 'SavedSearch',
            'UserPreferences' => 'UserPreference',
            'EmailAddresses' => 'EmailAddress',
            'EmailText' => 'EmailText',
            'Relationships' => 'Relationship',
            'Employees' => 'Employee',
            'Reports' => 'SavedReport',
            'Teams' => 'Team',
            'TeamMemberships' => 'TeamMembership',
            'TeamSets' => 'TeamSet',
            'TeamSetModules' => 'TeamSetModule',
            'Quotes' => 'Quote',
            'Products' => 'Product',
            'ProductBundles' => 'ProductBundle',
            'ProductBundleNotes' => 'ProductBundleNote',
            'ProductTemplates' => 'ProductTemplate',
            'ProductTypes' => 'ProductType',
            'ProductCategories' => 'ProductCategory',
            'Manufacturers' => 'Manufacturer',
            'Shippers' => 'Shipper',
            'TaxRates' => 'TaxRate',
            'TeamNotices' => 'TeamNotice',
            'TimePeriods' => 'TimePeriod',
            'AnnualTimePeriods' => 'AnnualTimePeriod',
            'QuarterTimePeriods' => 'QuarterTimePeriod',
            'Quarter544TimePeriods' => 'Quarter544TimePeriod',
            'Quarter445TimePeriods' => 'Quarter445TimePeriod',
            'Quarter454TimePeriods' => 'Quarter454TimePeriod',
            'MonthTimePeriods' => 'MonthTimePeriod',
            'Forecasts' => 'Forecast',
            'ForecastWorksheets' => 'ForecastWorksheet',
            'ForecastManagerWorksheets' => 'ForecastManagerWorksheet',
            'ForecastSchedule' => 'ForecastSchedule',
            'Worksheet' => 'Worksheet',
            'ForecastOpportunities' => 'ForecastOpportunities',
            'Quotas' => 'Quota',
            'WorkFlow' => 'WorkFlow',
            'WorkFlowTriggerShells' => 'WorkFlowTriggerShell',
            'WorkFlowAlertShells' => 'WorkFlowAlertShell',
            'WorkFlowAlerts' => 'WorkFlowAlert',
            'WorkFlowActionShells' => 'WorkFlowActionShell',
            'WorkFlowActions' => 'WorkFlowAction',
            'Expressions' => 'Expression',
            'Contracts' => 'Contract',
            'KBDocuments' => 'KBDocument',
            'KBDocumentRevisions' => 'KBDocumentRevision',
            'KBTags' => 'KBTag',
            'KBDocumentKBTags' => 'KBDocumentKBTag',
            'KBContents' => 'KBContent',
            'ContractTypes' => 'ContractType',
            'Holidays' => 'Holiday',
            'CustomQueries' => 'CustomQuery',
            'DataSets' => 'DataSet',
            'ReportMaker' => 'ReportMaker',
            'SugarFeed' => 'SugarFeed',
            'Notifications' => 'Notifications',
            'EAPM' => 'EAPM',
            'OAuthKeys' => 'OAuthKey',
            'OAuthTokens' => 'OAuthToken',
            'SugarFavorites' => 'SugarFavorites',
            'PdfManager' => 'PdfManager',
            'ProcessMaker' => 'BpmnProject',
        ];

        $this->beanFactory = $this->getMockBuilder('BeanFactory')
            ->setMethods(['getBean'])
            ->getMock();
        $this->teams = $this->getMockBuilder('Teams')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['teamsAreSupported', 'getList', 'getDisplayName'])
            ->getMock();
        $this->users = $this->getMockBuilder('Users')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['get_full_list'])
            ->getMock();
        $this->processDefinitionBean = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows', 'retrieve'])
            ->getMock();
        $this->activityDefinitionBean = $this->getMockBuilder('SugarBean')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows'])
            ->getMock();
        $this->dynaformBean = $this->getMockBuilder('pmse_BpmDynaForm')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows', 'get_full_list'])
            ->getMock();
        $this->projectBean = $this->getMockBuilder('pmse_BpmnProject')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows', 'retrieve'])
            ->getMock();
        $this->processBean = $this->getMockBuilder('pmse_BpmnProcess')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows'])
            ->getMock();
        $this->ruleSetBean = $this->getMockBuilder('pmse_BpmRuleSet')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows', 'get_full_list'])
            ->getMock();
        $this->emailTemplateBean = $this->getMockBuilder('pmse_BpmEmailTemplate')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows', 'get_full_list'])
            ->getMock();
        $this->inboxBean = $this->getMockBuilder('pmse_BpmInbox')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'getSelectRows'])
            ->getMock();
        $this->teamsBean = $this->getMockBuilder('Teams')
            ->disableAutoload()
            ->disableORiginalConstructor()
            ->setMethods(['get_full_list'])
            ->getMock();
        $this->usersBean = $this->getMockBuilder('Users')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['get_full_list'])
            ->getMock();
        $this->sugarQueryMock = $this->getMockBuilder('SugarQuery')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(
                ['select', 'from', 'joinTable', 'on', 'equalsField', 'where', 'execute', 'equals']
            )
            ->getMock();
        $this->sugarQueryMock->expects($this->any())
            ->method('joinTable')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->any())
            ->method('on')
            ->willReturnSelf();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $allIds = [];
        $db = DBManagerFactory::getInstance();

        // Delete harness data
        foreach ($this->deleteAssets as $table => $ids) {
            // For use in clearing out tag bean rel
            $allIds = array_merge($allIds, $ids);

            // Handle deletes
            $sql = "DELETE FROM $table WHERE id IN ('" . implode("','", $ids) . "')";
            $db->query($sql);
        }

        // Delete tag relationships
        $ids = "'" . implode("','", $allIds) . "'";
        $sql = "DELETE FROM tag_bean_rel WHERE tag_id IN ($ids) OR bean_id IN ($ids)";
        $db->query($sql);

        // Reset variables
        foreach ($this->originals as $varname => $value) {
            $GLOBALS[$varname] = $value;
        }
    }

    /**
     * @covers PMSECrmDataWrapper::invalidRequest
     * @todo   Implement testInvalidRequest().
     */
    public function testInvalidRequest()
    {
        //        $this->object = new PMSECrmDataWrapper();

        $expected = ['success' => false, 'message' => 'Invalid Request'];
        $result = $this->object->invalidRequest();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveEmails
     * @todo   Implement testAddRelatedRecord().
     */
    public function testRetrieveEmailsWithEmptyFilter()
    {
        //        $this->object = new PMSECrmDataWrapper();

        $expectedResult = ['success' => false, 'message' => 'Invalid Request'];

        $result = $this->object->retrieveEmails('');
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveDynaforms
     */
    public function testRetrieveDynaforms()
    {
        $this->projectBean->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue(true));

        $this->processBean->expects($this->any())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $this->dynaformBean->expects($this->any())
            ->method('get_full_list')
//            ->with($this->equalTo('bpm_dynamic_forms.pro_id=1'));
            ->will($this->returnValue([
                (object)['dyn_uid' => 'abcdeff', 'name' => 'Test DynaForm'],
                (object)['dyn_uid' => 'abcdefg', 'name' => 'Test DynaForm 01'],
                (object)['dyn_uid' => 'abcdefh', 'name' => 'Test DynaForm 02'],
                (object)['dyn_uid' => 'abcdefi', 'name' => 'Test DynaForm 03'],
                (object)['dyn_uid' => 'abcdefj', 'name' => 'Test DynaForm 04'],
            ]));

        $expectedResult = [
            ['value' => 'abcdeff', 'text' => 'Test DynaForm'],
            ['value' => 'abcdefg', 'text' => 'Test DynaForm 01'],
            ['value' => 'abcdefh', 'text' => 'Test DynaForm 02'],
            ['value' => 'abcdefi', 'text' => 'Test DynaForm 03'],
            ['value' => 'abcdefj', 'text' => 'Test DynaForm 04'],
        ];

        $this->projectBean->id = '1';
        $this->processBean->id = '1';

        $this->object->setDynaformBean($this->dynaformBean);
        $this->object->setProjectBean($this->projectBean);
        $this->object->setProcessBean($this->processBean);
        $someFilter = 'filter';
        $result = $this->object->retrieveDynaforms($someFilter);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveActivities
     */
    public function testRetrieveActivitiesEmptyFilter()
    {
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $whereMock = $this->createMock(SugarQuery_Builder_Where::class);
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->willReturn($whereMock);

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue([
                ['act_uid' => 'abcdeff', 'name' => 'Test Activity'],
                ['act_uid' => 'abcdefg', 'name' => 'Test Activity 01'],
                ['act_uid' => 'abcdefh', 'name' => 'Test Activity 02'],
                ['act_uid' => 'abcdefi', 'name' => 'Test Activity 03'],
                ['act_uid' => 'abcdefj', 'name' => 'Test Activity 04'],
            ]));

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock));

        $expectedResult = [
            ['value' => 'abcdeff', 'text' => 'Test Activity'],
            ['value' => 'abcdefg', 'text' => 'Test Activity 01'],
            ['value' => 'abcdefh', 'text' => 'Test Activity 02'],
            ['value' => 'abcdefi', 'text' => 'Test Activity 03'],
            ['value' => 'abcdefj', 'text' => 'Test Activity 04'],
        ];
        $activityBeanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->object->setActivityBean($activityBeanMock);
        $result = $this->object->retrieveActivities();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Gets a tag record
     * @return Tag
     */
    protected function getNewTagRecord()
    {
        // Create the record
        $tag = BeanFactory::getBean('Tags');
        $tag->name = 'BPM Test Tag ' . $this->getNextRecordIndex();
        $tag->verifiedUnique = true;
        $tag->save();

        // Add it to the delete list
        $this->addBeanToDeleteAssets($tag);

        return $tag;
    }

    /**
     * Gets a new business rule record
     * @return pmse_Business_Rule
     */
    protected function getNewBusinessRuleRecord()
    {
        $bean = BeanFactory::getBean('pmse_Business_Rules');
        $bean->name = 'BPM Test BR ' . $this->getNextRecordIndex();
        $bean->rst_module = $this->testModule;
        $bean->save();

        // Add it to the delete list
        $this->addBeanToDeleteAssets($bean);

        return $bean;
    }

    /**
     * Gets a new email template record
     * @return pmse_Email_Template
     */
    protected function getNewEmailTemplateRecord()
    {
        $bean = BeanFactory::getBean('pmse_Emails_Templates');
        $bean->name = 'BPM Test ET ' . $this->getNextRecordIndex();
        $bean->base_module = $this->testModule;
        $bean->save();

        // Add it to the delete list
        $this->addBeanToDeleteAssets($bean);

        return $bean;
    }

    /**
     * Gets a new project record
     * @return pmse_Project
     */
    protected function getNewProjectRecord()
    {
        $bean = BeanFactory::getBean('pmse_Project');
        $bean->name = 'BPM Test Project ' . $this->getNextRecordIndex();
        $bean->prj_module = $this->testModule;
        $bean->save();

        // Add it to the delete list
        $this->addBeanToDeleteAssets($bean);

        return $bean;
    }

    /**
     * Increments the record index and returns the incremented value
     * @return int
     */
    protected function getNextRecordIndex()
    {
        return ++$this->recordIndex;
    }

    /**
     * Tracks which beans were added so that they can be deleted later
     * @param SugarBean $bean
     */
    protected function addBeanToDeleteAssets(SugarBean $bean)
    {
        $this->deleteAssets[$bean->getTableName()][] = $bean->id;
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveRuleSets
     */
    public function testRetrieveRuleSets()
    {
        // Tag records needed for tagging business rules
        $tag1 = $this->getNewTagRecord();
        $tag2 = $this->getNewTagRecord();
        $tag3 = $this->getNewTagRecord();

        // Business Rule records needed for testing
        $br1 = $this->getNewBusinessRuleRecord();
        $br2 = $this->getNewBusinessRuleRecord();

        // Tagging of ONE business rule record
        $br1->load_relationship('tag_link');
        $br1->tag_link->add($tag1);
        $br1->tag_link->add($tag2);
        $br1->tag_link->add($tag3);

        // Project record needed for processing the query
        $prj = $this->getNewProjectRecord();

        $wrapper = $this->getMockBuilder('PMSECrmDataWrapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $data = $wrapper->retrieveRuleSets($prj->id);

        // Test that we have two rows in the result, and that they are in the
        // right order
        $this->assertCount(2, $data);
        $this->assertSame($data[0]['value'], $br1->id);
        $this->assertSame($data[0]['text'], $br1->name);
        $this->assertSame($data[1]['value'], $br2->id);
        $this->assertSame($data[1]['text'], $br2->name);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveBusinessRules
     */
    public function testRetrieveBusinessRules()
    {
        $filter = '';
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $whereMock = $this->createMock(SugarQuery_Builder_Where::class);
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->willReturn($whereMock);

        $selectMock = $this->createMock(SugarQuery_Builder_Select::class);

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue([
                ['id' => 'abcdeff', 'name' => 'Test Activity'],
                ['id' => 'abcdefg', 'name' => 'Test Activity 01'],
                ['id' => 'abcdefh', 'name' => 'Test Activity 02'],
                ['id' => 'abcdefi', 'name' => 'Test Activity 03'],
                ['id' => 'abcdefj', 'name' => 'Test Activity 04'],
            ]));

        $this->sugarQueryMock->select = $selectMock;

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock));

        $expected = [
            ['value' => 'abcdeff', 'text' => 'Test Activity'],
            ['value' => 'abcdefg', 'text' => 'Test Activity 01'],
            ['value' => 'abcdefh', 'text' => 'Test Activity 02'],
            ['value' => 'abcdefi', 'text' => 'Test Activity 03'],
            ['value' => 'abcdefj', 'text' => 'Test Activity 04'],
        ];

        $this->projectBean->expects($this->any())
            ->method('retrieve')
            ->with($filter)
            ->will($this->returnValue(true));

        $this->projectBean->id = '1';

        $this->object->setProcessDefinition($this->processDefinitionBean);
        $this->object->setActivityDefinitionBean($this->activityDefinitionBean);
        $this->object->setProjectBean($this->projectBean);
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $result = $this->object->retrieveBusinessRules($filter);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveEmailTemplates
     */
    public function testRetrieveEmailTemplates()
    {
        // Tag records needed for tagging business rules
        $tag1 = $this->getNewTagRecord();
        $tag2 = $this->getNewTagRecord();
        $tag3 = $this->getNewTagRecord();

        // Email Template records needed for testing
        $et1 = $this->getNewEmailTemplateRecord();
        $et2 = $this->getNewEmailTemplateRecord();
        $et3 = $this->getNewEmailTemplateRecord();
        $et4 = $this->getNewEmailTemplateRecord();

        // Tagging of TWO email template records
        $et1->load_relationship('tag_link');
        $et1->tag_link->add($tag1);
        $et1->tag_link->add($tag2);

        $et2->load_relationship('tag_link');
        $et2->tag_link->add($tag2);
        $et2->tag_link->add($tag3);

        $wrapper = $this->getMockBuilder('PMSECrmDataWrapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $data = $wrapper->retrieveEmailTemplates($this->testModule);

        // Test that we have four rows in the result, and that they are in the
        // right order
        $this->assertCount(4, $data);
        $this->assertSame($data[0]['value'], $et1->id);
        $this->assertSame($data[0]['text'], $et1->name);
        $this->assertSame($data[1]['value'], $et2->id);
        $this->assertSame($data[1]['text'], $et2->name);
        $this->assertSame($data[2]['value'], $et3->id);
        $this->assertSame($data[2]['text'], $et3->name);
        $this->assertSame($data[3]['value'], $et4->id);
        $this->assertSame($data[3]['text'], $et4->name);
    }

    public function testRetrieveEmailTemplatesWithoutModule()
    {
        // Email Template records needed for testing
        $et1 = $this->getNewEmailTemplateRecord();
        $et2 = $this->getNewEmailTemplateRecord();

        $wrapper = $this->getMockBuilder('PMSECrmDataWrapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $data = $wrapper->retrieveEmailTemplates(null);

        $this->assertEquals([], $data);
    }

    /**
     * @covers PMSECrmDataWrapper::getBeanModuleName
     */
    public function testGetBeanModuleName()
    {
        $this->object->setBeanList($this->beanList);
        $result = $this->object->getBeanModuleName('Account');
        //        var_dump($result);
        $this->assertEquals('Accounts', $result);

        $this->beanList = ['Leads' => 'Leads'];
        $this->object->setBeanList($this->beanList);
        $result = $this->object->getBeanModuleName('Leads');
        //        var_dump($result);
        $this->assertEquals('Leads', $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateProjectName
     */
    public function testValidateProjectNameIfNull()
    {
        $this->projectBean->expects($this->any())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(null));

        $this->object->setProjectBean($this->projectBean);

        $expected = ['result' => true, 'success' => true];
        $result = $this->object->validateProjectName('Test Lead');

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateProjectName
     */
    public function testValidateProjectName()
    {
        $someObject = new stdClass();

        $this->projectBean->expects($this->any())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue($someObject));

        $this->object->setProjectBean($this->projectBean);

        $expected = ['result' => false, 'success' => true, 'message' => 'LBL_PMSE_MESSAGE_THEPROCESSNAMEALREADYEXISTS'];
        $result = $this->object->validateProjectName('Test Lead');

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateEmailTemplateName
     */
    public function testValidateEmailTemplateName()
    {
        $res = new stdClass();
        $res->success = true;
        $res->result = true;

        $this->object->setEmailTemplateBean($this->emailTemplateBean);

        $result = $this->object->validateEmailTemplateName('Test Lead', '1');
        //        var_dump($result);
        $this->assertEquals($res, $result);

        $this->emailTemplateBean->expects($this->any())
            ->method('get_full_list')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        ['pro_id' => 'abcdeff', 'pro_name' => 'Test Project Name'],
                    ],
                    'totalRows' => 1,
                    'currentOffset' => 0,
                ]
            ));
        $res->message = 'LBL_PMSE_MESSAGE_THEEMAILTEMPLATENAMEALREADYEXISTS';
        $res->result = false;
        $result = $this->object->validateEmailTemplateName('Test Lead', '1');
        //        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateBusinessRuleName
     */
    public function testValidateBusinessRuleName()
    {
        $res = [];
        $res['success'] = true;

        $this->ruleSetBean->expects($this->any())
            ->method('get_full_list')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        ['pro_id' => 'abcdeff', 'pro_name' => 'Test Project Name'],
                    ],
                    'totalRows' => 1,
                    'currentOffset' => 0,
                ]
            ));

        $this->object->setRuleSetBean($this->ruleSetBean);

        $res['message'] = 'LBL_PMSE_MESSAGE_BUSINESSRULENAMEALREADYEXISTS';
        $res['result'] = false;

        $result = $this->object->validateBusinessRuleName('Test Lead', '1');
        //        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::defaultUsersList
     */
    public function testDefaultUsersList()
    {
        $expected = [
            ['value' => 'current_user', 'text' => 'Current User'],
            ['value' => 'supervisor', 'text' => 'Supervisor'],
            ['value' => 'owner', 'text' => 'Record Owner'],
        ];

        $result = $this->object->defaultUsersList();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::rolesList
     */
    public function testRolesList()
    {
        $this->assertContains([
            'value' => 'is_admin',
            'text' => translate('LBL_PMSE_FORM_OPTION_ADMINISTRATOR', 'pmse_Project'),
        ], $this->object->rolesList());
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveDateFields
     * @todo   Implement testRetrieveDateFields().
     */
    public function testRetrieveDateFields()
    {
        $res = [];
        $res['name'] = 'Leads';
        $res['search'] = 'Leads';
        $res['success'] = true;
        $res['result'] = [
            ['value' => 'current_date_time', 'text' => 'Current Date Time'],
            ['value' => 'field', 'text' => 'some_field'],
        ];

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getModuleFilter',
                    'getRelationshipData',
                ]
            )
            ->getMock();

        $moduleFilter = new stdClass();
        $moduleFilter->field_defs = [
            [
                'vname' => 'some_field',
                'name' => 'field',
                'type' => 'date',
            ],
        ];

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleFilter));

        $this->object->setBeanList($this->beanList);
        $result = $this->object->retrieveDateFields('Leads');
        //        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::retrieveDateFields
     * @todo   Implement testRetrieveDateFields().
     */
    public function testRetrieveDateFieldsRelatedBean()
    {
        $res = [];
        $res['name'] = 'Leads';
        $res['search'] = 'Leads';
        $res['success'] = true;
        $res['result'] = [
            ['value' => 'current_date_time', 'text' => 'Current Date Time'],
            ['value' => 'field', 'text' => 'some_field'],
        ];


        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getModuleFilter',
                    'getRelationshipData',
                ]
            )
            ->getMock();

        $this->object->setBeanList([
            'Meetings' => 'Meetings',
        ]);

        $moduleFilter = new stdClass();
        $moduleFilter->field_defs = [
            [
                'vname' => 'some_field',
                'name' => 'field',
                'type' => 'date',
            ],
        ];

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleFilter));

        $relatioship = ['rhs_module' => 'Leads'];

        $this->object->expects($this->once())
            ->method('getRelationshipData')
            ->will($this->returnValue($relatioship));

        $result = $this->object->retrieveDateFields('Leads');
        //        var_dump($result);
        $this->assertEquals($res, $result);
    }

    /**
     * @covers PMSECrmDataWrapper::validateReclaimCase
     */
    public function testValidateReclaimCase()
    {
        $this->object->setSugarQueryObject($this->sugarQueryMock);

        $whereMock = $this->createMock(SugarQuery_Builder_Where::class);
        $whereMock->expects($this->any())
            ->method('queryAnd')
            ->willReturn($whereMock);

        $whereMock->expects($this->any())
            ->method('equals')
            ->will($this->returnValue($whereMock));

        $this->sugarQueryMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue([
                ['cas_start_date' => ''],
            ]));

        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($whereMock));

        $casId = 1;
        $casIndex = 1;

        $this->object->setSugarQueryObject($this->sugarQueryMock);
        $inboxBeanMock = $this->getMockBuilder('SugarBean')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $this->object->setInboxBean($inboxBeanMock);
        $result = $this->object->validateReclaimCase($casId, $casIndex);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function retrieveTeamsDataProvider()
    {
        return [
            ['public'],
            ['private'],
        ];
    }

    /**
     * @dataProvider retrieveTeamsDataProvider
     */
    public function testRetrieveTeams($filter)
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->object->setSugarQueryObject($this->sugarQueryMock);
        $this->sugarQueryMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->sugarQueryMock->expects($this->any())
            ->method('equals')
            ->willReturnSelf();

        $teamMock = $this->getMockBuilder('Team')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([['id' => 'team01', 'name' => 'Team #1']]));

        $this->object->setTeamsBean($teamMock);
        $result = $this->object->retrieveTeams($filter);
        $this->assertCount(1, $result);
    }

    public function testGetTargetAndRelatedFieldsSuccess()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveFields', 'retrieveRelatedBeans'])
            ->getMock();

        $this->object->expects($this->once())
            ->method('retrieveRelatedBeans')
            ->will($this->returnValue(
                [
                    'success' => true,
                    'result' => [
                        [
                            'value' => 'some value',
                            'fields' => [],
                        ],
                    ],
                ]
            ));

        $this->object->method('retrieveFields')
            ->willReturn([
                'success' => true,
                'result' => ['fields'],
            ]);

        $filter = 'some filter';
        $result = $this->object->getTargetAndRelatedFields($filter);
        $this->assertIsArray($result);
    }

    public function testGetTargetAndRelatedFieldsFailure()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveFields', 'retrieveRelatedBeans'])
            ->getMock();

        $this->object->expects($this->once())
            ->method('retrieveFields')
            ->will($this->returnValue(
                [
                    'success' => false,
                ]
            ));

        $this->object->expects($this->once())
            ->method('retrieveRelatedBeans')
            ->will($this->returnValue(
                [
                    'success' => false,
                ]
            ));

        $this->object->expects($this->once())
            ->method('retrieveFields')
            ->will($this->returnValue(
                [
                    'success' => true,
                    'result' => ['fields'],
                ]
            ));

        $filter = 'some filter';
        $result = $this->object->getTargetAndRelatedFields($filter);
        $this->assertIsArray($result);
    }

    public function testGetAjaxRelationships()
    {
        $relationshipMock = $this->getMockBuilder('Relationship')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['getRelationshipList', 'get', 'getDefinition'])
            ->getMock();

        $relList = [
            'leads_notes',
            'leads_meetings',
            'leads_tasks',
            'leads_opportunities',
            'leads_cases',
        ];

        $relationshipMock->expects($this->once())
            ->method('getRelationshipList')
            ->will($this->returnValue($relList));

        $relationshipMock->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $def01 = [
            'lhs_module' => 'leads',
            'rhs_module' => 'notes',
            'relationship_type' => 'one-to-one',
            'relationship_type_render' => '',
            'is_custom' => false,
            'from_studio' => false,
        ];

        $def02 = [
            'lhs_module' => 'leads',
            'rhs_module' => 'meetings',
            'relationship_type' => 'one-to-many',
            'relationship_type_render' => '',
            'is_custom' => false,
            'from_studio' => false,
        ];

        $def03 = [
            'lhs_module' => 'leads',
            'rhs_module' => 'tasks',
            'relationship_type' => 'many-to-one',
            'relationship_type_render' => '',
            'is_custom' => false,
            'from_studio' => false,
        ];

        $def04 = [
            'lhs_module' => 'leads',
            'rhs_module' => 'opportunities',
            'relationship_type' => 'many-to-many',
            'relationship_type_render' => '',
            'is_custom' => true,
            'from_studio' => true,
        ];

        $def05 = [
            'lhs_module' => 'leads',
            'rhs_module' => 'cases',
            'relationship_type' => 'another-type',
            'relationship_type_render' => '',
            'is_custom' => true,
            'from_studio' => true,
        ];

        $relationshipMock->expects($this->exactly(5))
            ->method('getDefinition')
            ->willReturnOnConsecutiveCalls($def01, $def02, $def03, $def04, $def05);

        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveFields', 'retrieveRelatedBeans'])
            ->getMock();

        $this->object->getAjaxRelationships($relationshipMock);
    }


    public function testRetrieveRelatedModulesFilterFound()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveFields', 'getModuleFilter', 'getAjaxRelationships'])
            ->getMock();

        $moduleObj = new stdClass();

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleObj));

        $ajaxRelationships = [
            [
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Leads',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-many',
                'relationship_name' => 'project_project_tasks',
                'name' => 'some relationship',
            ],
            [
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Meetings',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'relationship_name' => 'meetings_project_tasks',
                'name' => 'some relationship',
            ],
            [
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Opportunities',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'relationship_name' => 'opportunities_project_tasks',
                'name' => 'some relationship',
            ],
        ];

        $this->object->expects($this->once())
            ->method('getAjaxRelationships')
            ->will($this->returnValue($ajaxRelationships));

        $filter = 'ProjectTask';

        $this->object->setBeanList([
            'Leads' => [],
            'ProjectTask' => [],
        ]);

        $result = $this->object->retrieveRelatedModules($filter);
        $this->assertCount(4, $result);
    }

    public function testRetrieveRelatedModulesFilterNotFound()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveFields', 'getModuleFilter', 'getAjaxRelationships'])
            ->getMock();

        $moduleObj = new stdClass();

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleObj));

        $ajaxRelationships = [
            [
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Leads',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-many',
                'name' => 'some relationship',
            ],
            [
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Meetings',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'name' => 'some relationship',
            ],
            [
                'lhs_module' => 'Project Tasks',
                'rhs_module' => 'Opportunities',
                'lhs_table' => 'project_tasks',
                'relationship_type' => 'one-to-one',
                'name' => 'some relationship',
            ],
        ];

        $this->object->expects($this->once())
            ->method('getAjaxRelationships')
            ->will($this->returnValue($ajaxRelationships));

        $filter = 'ProjectTask';

        $this->object->setBeanList([
            'Leads',
            'ProjectTask',
        ]);
        $result = $this->object->retrieveRelatedModules($filter);
        $this->assertCount(1, $result);
    }

    public function testUpdateProcessDefinitions()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getProcessDefinition', 'getProjectBean', 'getProcessBean'])
            ->getMock();

        $this->object->setObservers([]);

        $dynaformMock = $this->getMockBuilder('PMSEDynaForm')
            ->disableOriginalConstructor()
            ->setMethods(['generateDefaultDynaform'])
            ->getMock();

        $this->object->setDefaultDynaform($dynaformMock);

        $processDefMock = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $processDefMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $processDefMock->id = 'pro01';
        $processDefMock->pro_module = 'Opportunities';
        $processDefMock->name = 'some name';

        $this->object->expects($this->once())
            ->method('getProcessDefinition')
            ->will($this->returnValue($processDefMock));

        $processMock = $this->getMockBuilder('pmse_bpmProcess')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $processMock->id = 'pro01';
        $processMock->name = 'some name';

        $this->object->expects($this->once())
            ->method('getProcessBean')
            ->will($this->returnValue($processMock));

        $projectMock = $this->getMockBuilder('pmse_bpmProject')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve'])
            ->getMock();

        $projectMock->expects($this->once())
            ->method('retrieve')
            ->will($this->returnValue(true));

        $projectMock->id = 'prj01';
        $projectMock->name = 'some name';

        $this->object->expects($this->once())
            ->method('getProjectBean')
            ->will($this->returnValue($projectMock));

        $args = [
            'filter' => 'Leads',
            'name' => 'Some Name',
            'description' => 'Some Description',
            'pro_module' => 'Leads',
            'pro_locked_variables' => '[]',
            'pro_terminate_variables' => '[]',
        ];
        $this->object->updateProcessDefinitions($args);
    }

    public function testAddRelatedRecordWithoutFields()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRelationshipData',
                    'getModuleFilter',
                    'isValidStudioField',
                    'fieldTodo',
                    'returnArrayModules',
                    'dataFieldPersonalized',
                    'gatewayModulesMethod',
                ]
            )
            ->getMock();

        $this->object->expects($this->once())
            ->method('getRelationshipData')
            ->will($this->returnValue([
                'rhs_module' => 'All',
            ]));

        $this->object->setBeanList([]);
        $this->object->addRelatedRecord('Leads');
    }

    public function testAddRelatedRecordWithFields()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRelationshipData',
                    'getModuleFilter',
                    'isValidStudioField',
                    'fieldTodo',
                    'returnArrayModules',
                    'gatewayModulesMethod',
                ]
            )
            ->getMock();

        $moduleBeanMock = $this->getMockBuilder('ModuleBean')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $moduleBeanMock->field_defs = [
            [
                'name' => 'id',
                'vname' => 'id',
                'type' => 'string',
                'required' => true,
                'len' => 32,
            ],
            [
                'name' => 'name',
                'vname' => 'name',
                'type' => 'string',
                'required' => true,
                'len' => 32,
            ],
            [
                'name' => 'list',
                'vname' => 'list',
                'type' => 'enum',
                'options' => 'listOptions',
                'required' => true,
                'len' => 32,
            ],
            [
                'name' => 'list2',
                'vname' => 'list2',
                'type' => 'enum',
                'required' => true,
                'len' => 32,
            ],
        ];

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleBeanMock));

        $this->object->expects($this->any())
            ->method('isValidStudioField')
            ->will($this->returnValue(true));

        $this->object->expects($this->any())
            ->method('fieldTodo')
            ->will($this->returnValue(false));

        global $app_list_strings;

        $this->object->setBeanList([
            'Notes' => [],
        ]);

        $app_list_strings = [
            'listOptions' => [],
        ];

        $args = [
            'retrieveId' => true,
        ];

        $this->object->addRelatedRecord('Notes', $args);
    }

    public function testAddRelatedRecordWithFieldDatePersonalized()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRelationshipData',
                    'getModuleFilter',
                    'isValidStudioField',
                    'fieldTodo',
                    'returnArrayModules',
                    'dataFieldPersonalized',
                    'gatewayModulesMethod',
                ]
            )
            ->getMock();

        $moduleBeanMock = $this->getMockBuilder('ModuleBean')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $moduleBeanMock->field_defs = [
            [
                'name' => 'id',
                'vname' => 'id',
                'type' => 'string',
                'required' => true,
                'len' => 32,
            ],
            [
                'name' => 'name',
                'vname' => 'name',
                'type' => 'string',
                'required' => true,
                'len' => 32,
            ],
            [
                'name' => 'list',
                'vname' => 'list',
                'type' => 'enum',
                'options' => 'listOptions',
                'required' => true,
                'len' => 32,
            ],
            [
                'name' => 'list2',
                'vname' => 'list2',
                'type' => 'enum',
                'required' => true,
                'len' => 32,
            ],
        ];

        $this->object->expects($this->once())
            ->method('getModuleFilter')
            ->will($this->returnValue($moduleBeanMock));

        $this->object->expects($this->any())
            ->method('isValidStudioField')
            ->will($this->returnValue(true));

        $this->object->expects($this->any())
            ->method('fieldTodo')
            ->will($this->returnValue(false));

        global $app_list_strings;

        $this->object->setBeanList([
            'Notes' => [],
        ]);

        $app_list_strings = [
            'listOptions' => [],
        ];

        $args = [
            'retrieveId' => true,
        ];

        $this->object->addRelatedRecord('Notes', $args);
    }

    public function testGetRelatedSearchModules()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'retrieveModules',
                ]
            )
            ->getMock();

        $this->object->expects($this->once())
            ->method('retrieveModules')
            ->will($this->returnValue('SomeResponse'));

        $filter = 'modules';
        $args = [];

        $result = $this->object->getRelatedSearch($filter, $args);
        $this->assertEquals('SomeResponse', $result);
    }

    public function testGetRelatedSearchFields()
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'retrieveModules',
                ]
            )
            ->getMock();

        $filter = 'fields';
        $args = [];

        $result = $this->object->getRelatedSearch($filter, $args);
        $this->assertEquals([], $result);
    }

    /**
     * @covers       PMSECrmDataWrapper::retrieveFields
     * @dataProvider getRetrieveFieldsData
     * @param $action
     * @param $count
     * @param $containOrNot1
     * @param $containOrNot2
     */
    public function testRetrieveFields($action, $count, $containsOrNot1 = [], $containsOrNot2 = [])
    {
        $this->object = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $GLOBALS['app_list_strings']['moduleList'] = [];
        $this->object->setBeanList(['Emails' => 'Email']);

        $output = $this->object->retrieveFields('Emails', null, $action, 'Emails');
        $fields = $this->getOutputFields($output['result']);
        $this->assertEquals(count($fields), $count);
        $this->verifySupportedFields($containsOrNot1, $fields);
        $this->verifySupportedFields($containsOrNot2, $fields);
    }

    public function getRetrieveFieldsData()
    {
        return [
            [
                'action' => 'CF',
                'count' => 2,
                'containsOrNot1' => [
                    'containsOrNot' => true,
                    'field' => 'assigned_user_id',
                    'message' => 'assigned_user_id should be a supported field in CF.',
                ],
                'containsOrNot2' => [
                    'containsOrNot' => true,
                    'field' => 'teams',
                    'message' => 'teams should be a supported field in CF.',
                ],
            ],
            [
                'action' => 'BR',
                'count' => 1,
                'containsOrNot1' => [
                    'containsOrNot' => true,
                    'field' => 'assigned_user_id',
                    'message' => 'assigned_user_id should be a supported field in BR.',
                ],
            ],
            [
                'action' => 'AC',
                'count' => 0,
            ],
            [
                'action' => 'RR',
                'count' => 0,
            ],
            [
                'action' => 'PD',
                'count' => 9,
                'containsOrNot1' => [
                    'containsOrNot' => true,
                    'field' => 'direction',
                    'message' => 'direction should be a supported field in PD.',
                ],
                'containsOrNot2' => [
                    'containsOrNot' => false,
                    'field' => 'type',
                    'message' => 'type should not be a supported field in PD.',
                ],
            ],
            [
                'action' => 'BRR',
                'count' => 9,
                'containsOrNot1' => [
                    'containsOrNot' => true,
                    'field' => 'direction',
                    'message' => 'direction should be a supported field in BRR.',
                ],
                'containsOrNot2' => [
                    'containsOrNot' => false,
                    'field' => 'type',
                    'message' => 'type should not be a supported field in BRR.',
                ],
            ],
        ];
    }

    /**
     * Verify supported fields
     * @params array
     * @params array
     */
    protected function verifySupportedFields($containsOrNot, $fields)
    {
        if (!empty($containsOrNot)) {
            if ($containsOrNot['containsOrNot'] === true) {
                $this->assertContains($containsOrNot['field'], $fields, $containsOrNot['message']);
            } else {
                $this->assertNotContains($containsOrNot['field'], $fields, $containsOrNot['message']);
            }
        }
    }

    /**
     * Get output fields
     * @params array
     * @return array
     */
    protected function getOutputFields($result)
    {
        $fields = [];
        if (!empty($result)) {
            foreach ($result as $field) {
                $fields[] = $field['value'];
            }
        }
        return $fields;
    }
}
