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
 * @group leadconvert
 */
class LeadConvertTest extends TestCase
{
    private $lead;
    private $leadId;
    private $contactsDef;
    private $accountsDef;
    private $opportunitiesDef;
    private $tasksDef;
    private $rliDef;
    private $modulesDef;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $this->lead = SugarTestLeadUtilities::createLead();
        $this->leadId = $this->lead->id;

        $this->contactsDef = [
            'module' => 'Contacts',
            'required' => true,
            'duplicateCheck' => true,
            'fieldMapping' => [],
        ];

        $this->accountsDef = [
            'module' => 'Accounts',
            'required' => true,
            'duplicateCheck' => true,
            'contactRelateField' => 'account_name',
            'fieldMapping' => [],
        ];

        $this->opportunitiesDef = [
            'module' => 'Opportunities',
            'required' => false,
            'duplicateCheck' => true,
            'fieldMapping' => [],
            'dependentModules' => [
                'Contacts',
                'Accounts',
            ],
        ];

        $this->tasksDef = [
            'module' => 'Tasks',
            'required' => false,
            'duplicateCheck' => true,
            'fieldMapping' => [],
        ];

        $this->rliDef = [
            'module' => 'RevenueLineItems',
            'required' => false,
            'duplicateCheck' => true,
            'dependentModules' => [
                'Opportunities' => [
                    'fieldMapping' => [
                        'opportunity_id' => 'id',
                    ],
                ],
            ],
        ];

        $this->modulesDef = [
            $this->contactsDef,
            $this->accountsDef,
            $this->opportunitiesDef,
            $this->tasksDef,
            $this->rliDef,
        ];

        SugarTestHelper::setUp('dictionary');
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();

        unset($this->lead);
        unset($this->modulesDef);
    }

    public function testInitialize_Successful()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);

        $lead = $leadConvert->getLead();
        $this->assertEquals($this->leadId, $lead->id);
    }

    public function testInitialize_InvalidLeadId_ThrowsException()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();
        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $this->expectException(Exception::class);

        $leadConvert->initialize('abcd');
    }

    /**
     * An entry in campaign_log table should be created for the new contact during lead conversion
     * @group bug44522
     * @dataProvider providerDataAddLogForContactInCampaign
     */
    public function testAddLogForContactInCampaign_LogsProperlyWhenCorrectDataSet($hasCampaign, $hasContact, $expected)
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs', 'addCampaignLog'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);

        if ($hasCampaign) {
            $campaign = SugarTestCampaignUtilities::createCampaign();
            $leadConvert->getLead()->campaign_id = $campaign->id;
        }

        if ($hasContact) {
            $leadConvert->setContact(SugarTestContactUtilities::createContact());
        }
        $leadConvert->expects($this->exactly($expected))
            ->method('addCampaignLog');


        $leadConvert->AddLogForContactInCampaign();
    }

    /**
     * @return array
     */
    public function providerDataAddLogForContactInCampaign()
    {
        return [
            [true, true, 1],
            [true, false, 0],
            [false, false, 0],
            [false, true, 0],
        ];
    }

    public function testSetRelationshipForModulesToLeads_OneToManyRelationship_RelationshipIsStoredOnlead()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);

        $account = SugarTestAccountUtilities::createAccount();
        $leadConvert->setModules(['Accounts' => $account]);

        $lead = $leadConvert->getLead();
        $this->assertNull($lead->account_id);
        $leadConvert->setRelationshipForModulesToLeads($this->accountsDef);

        $lead = $leadConvert->getLead();
        $this->assertNotNull($lead->account_id);
        $this->assertEquals($account->id, $lead->account_id);
        $this->assertTrue($lead->load_relationship('accounts'));
        $this->assertInstanceOf('Link2', $lead->accounts);
        $this->assertTrue($lead->accounts->loadedSuccesfully());
    }

    public function testSetRelationshipForModulesToLeads_NotOneToManyRelationship_RelationshipIsAddedToModule_NotLead()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);

        $meetingDef = [
            'module' => 'Meetings',
            'required' => true,
            'duplicateCheck' => true,
            'fieldMapping' => [],
        ];
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $leadConvert->setModules(['Meetings' => $meeting]);

        $leadConvert->setRelationshipForModulesToLeads($meetingDef);

        $lead = $leadConvert->getLead();

        $this->assertTrue($lead->load_relationship('meetings'));
        $this->assertInstanceOf('Link2', $lead->meetings);
        $this->assertTrue($lead->meetings->loadedSuccesfully());

        $related = $lead->meetings->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$meeting->id]);
    }

    public function testSetRelationshipsForModulesToContacts_ContactRelatedFieldInVarDef_FieldOnContactSet()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);

        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();

        $leadConvert->setModules(
            [
                'Accounts' => $account,
                'Contacts' => $contact,
            ]
        );
        $leadConvert->setContact($contact);

        $leadConvert->setRelationshipsForModulesToContacts($this->accountsDef);

        $contact = $leadConvert->getContact();

        $this->assertTrue($contact->load_relationship('accounts'));

        $this->assertInstanceOf('Link2', $contact->accounts);
        $this->assertTrue($contact->accounts->loadedSuccesfully());

        $related = $contact->accounts->getBeans();
        $this->assertEmpty($related);

        $relatedField = 'account_name';
        $this->assertEquals($account->name, $contact->$relatedField);
    }

    public function testSetRelationshipsForModulesToContacts_ManyToManyRelationship_RelationshipIsAddToContact()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $contact = SugarTestContactUtilities::createContact();

        $leadConvert->setModules(
            [
                'Opportunities' => $opp,
                'Contacts' => $contact,
            ]
        );
        $leadConvert->setContact($contact);

        $leadConvert->setRelationshipsForModulesToContacts($this->opportunitiesDef);

        $contact = $leadConvert->getContact();

        $this->assertTrue($contact->load_relationship('opportunities'));

        $this->assertInstanceOf('Link2', $contact->opportunities);
        $this->assertTrue($contact->opportunities->loadedSuccesfully());

        $related = $contact->opportunities->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$opp->id]);
    }

    public function testSetRelationshipsForModulesToContacts_OneToManyRelationship_RelationshipAdded_FieldOnContactSet()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);

        $task = SugarTestTaskUtilities::createTask();
        $contact = SugarTestContactUtilities::createContact();

        $leadConvert->setModules(
            [
                'Tasks' => $task,
                'Contacts' => $contact,
            ]
        );
        $leadConvert->setContact($contact);

        $leadConvert->setRelationshipsForModulesToContacts($this->tasksDef);

        $contact = $leadConvert->getContact();

        $this->assertTrue($contact->load_relationship('tasks'));

        $this->assertInstanceOf('Link2', $contact->tasks);
        $this->assertTrue($contact->tasks->loadedSuccesfully());

        $related = $contact->tasks->getBeans();
        $this->assertEmpty($related);

        $modules = $leadConvert->getModules();
        $this->assertEquals($contact->id, $modules['Tasks']->contact_id);
    }

    public function testConvertLead_NoOpportunity_LeadIsConverted()
    {
        $task = SugarTestTaskUtilities::createTask();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $modules = [
            'Tasks' => $task,
            'Contacts' => $contact,
            'Accounts' => $account,
        ];

        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods([
                'getVarDefs',
                'setRelationshipsForModulesToContacts',
                'setAssignedForModulesToLeads',
                'setRelationshipForModulesToLeads',
                'addLogForContactInCampaign',
                'updateOpportunityWithAccountInformation',
            ])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));
        $leadConvert->expects($this->exactly(2))
            ->method('setRelationshipsForModulesToContacts');
        $leadConvert->expects($this->exactly(3))
            ->method('setAssignedForModulesToLeads');
        $leadConvert->expects($this->exactly(3))
            ->method('setRelationshipForModulesToLeads');
        $leadConvert->expects($this->once())
            ->method('addLogForContactInCampaign');
        $leadConvert->expects($this->never())
            ->method('updateOpportunityWithAccountInformation');

        $leadConvert->initialize($this->leadId);
        $leadConvert->convertLead($modules);

        $lead = BeanFactory::getBean('Leads', $this->leadId);

        $this->assertEquals(
            LeadConvert::STATUS_CONVERTED,
            $lead->status,
            'Lead status field was not changed properly.'
        );
        $this->assertEquals(true, $lead->converted, 'Lead converted field not set properly');
        $this->assertEquals(true, $lead->in_workflow, 'Lead workflow field not set properly');
    }

    /**
     * @covers LeadConvert::copyDependentData
     */
    public function testCopyDependentData()
    {
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->opportunity_id = null;
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $leadConvert = $this->createPartialMock('LeadConvert', ['initialize']);
        $leadConvert->setModules([
            'RevenueLineItems' => $rli,
            'Opportunities' => $opp,
        ]);
        SugarTestReflection::callProtectedMethod($leadConvert, 'copyDependentData', [$this->rliDef]);
        $this->assertEquals($opp->id, $rli->opportunity_id, 'Opp id should be copied to opportunity_id in rli');
    }

    /**
     * Account related to leads record are copied over to contact record during conversion
     * @group bug40209
     */
    public function testConvertLead_AccountIsCopiedFromLeadToContact()
    {
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $this->lead = SugarTestLeadUtilities::createLead();
        $this->leadId = $this->lead->id;

        $modules = [
            'Contacts' => $contact,
            'Accounts' => $account,
            'Opportunities' => $opp,
        ];

        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);
        $leadConvert->convertLead($modules);

        $lead = BeanFactory::getBean('Leads', $this->leadId);
        $this->assertEquals(
            LeadConvert::STATUS_CONVERTED,
            $lead->status,
            'Lead status field was not changed properly.'
        );

        $contact_id = $lead->contact_id;
        $this->assertNotEmpty(
            $contact_id,
            'contact id was not created during conversion process.  An error has ocurred'
        );

        $contact = BeanFactory::getBean('Contacts', $contact_id);
        $this->assertEquals(
            $lead->account_id,
            $contact->account_id,
            'Account id from converted lead does not match the new contact account id, there was an error during conversion.'
        );

        $this->assertSame($opp->name, $lead->opportunity_name, 'Opportunity name did not match.');
        $this->assertSame($account->name, $lead->account_name, 'Account name did not match.');
    }

    public function testCopyActivities_CopyTaskToAccountAndContact_SuccessFullyCopied()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();
        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->initialize($this->leadId);
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();
        $task = SugarTestTaskUtilities::createTask();
        $modules = [
            'Accounts' => $account,
            'Contacts' => $contact,
            'Tasks' => $task,
        ];

        $transferActivitiesModules = [
            'Contacts',
            'Accounts',
        ];

        $leadConvert->setModules($modules);
        $lead = $leadConvert->getLead();
        $leadConvert->setRelationshipForModulesToLeads($this->tasksDef);
        $linkedTasks = $lead->tasks->get();

        /*--- We Should Have the Task Linked to this Lead ---*/
        $this->assertEquals(1, safeCount($linkedTasks), 'Expected 1 Task to Be Linked to Lead');

        /*--- Copy Activities to Contact and Account ---*/
        $leadConvert->copyActivities(
            $lead,
            $modules,
            $transferActivitiesModules
        );

        unset($lead->tasks);
        /*-- Verify Task Remains Linked to Lead --*/
        $this->assertTrue($lead->load_relationship('tasks'), 'Failed Loading Task Relationship on Lead');
        $leadTasks = $lead->tasks->get();
        $this->assertEquals(1, safeCount($leadTasks), 'Expected Task to remain linked to Lead on Copy');

        /*-- Verify copy of Task has been linked to Contact --*/
        $this->assertTrue($contact->load_relationship('tasks'), 'Failed Loading Task Relationship on Contact');
        $contactTasks = $contact->tasks->get();
        $this->assertEquals(1, safeCount($contactTasks), 'Expected 1 Task to be linked to Contact');

        /*-- Verify copy of Task has been linked to Account --*/
        $this->assertTrue($account->load_relationship('tasks'), 'Failed Loading Task Relationship on Account');
        $accountTasks = $account->tasks->get();
        $this->assertEquals(1, safeCount($accountTasks), 'Expected 1 Task to be linked to Account');
    }

    public function testMoveActivitiesToContact_MovedSuccessFully()
    {
        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods(['getVarDefs', 'getActivitySetting'])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();
        $leadConvert->expects($this->any())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->expects($this->once())
            ->method('getActivitySetting')
            ->will($this->returnValue('move'));

        $leadConvert->initialize($this->leadId);
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();
        $task = SugarTestTaskUtilities::createTask();
        $modules = [
            'Accounts' => $account,
            'Contacts' => $contact,
            'Tasks' => $task,
        ];

        $leadConvert->setModules($modules);
        $leadConvert->setContact($contact);
        $lead = $leadConvert->getLead();
        $leadConvert->setRelationshipForModulesToLeads($this->tasksDef);
        $linkedTasks = $lead->tasks->get();

        /*--- We Should Have the Task Linked to this Lead ---*/
        $this->assertEquals(1, safeCount($linkedTasks), 'Expected 1 Task to Be Linked to Lead');

        /*--- Move the Activities to the Contact ---*/
        $leadConvert->performLeadActivitiesTransfer('move', []);

        unset($lead->tasks);
        /*-- Verify Task Has been Unlinked from Lead --*/
        $this->assertTrue($lead->load_relationship('tasks'), 'Failed Loading Task Relationship on Lead');
        $leadTasks = $lead->tasks->get();
        $this->assertEquals(0, safeCount($leadTasks), 'Expected Task to be unlinked from Lead on Move');

        /*-- Verify move of Task has been linked to Contact --*/
        $this->assertTrue($contact->load_relationship('tasks'), 'Failed Loading Task Relationship on Contact');
        $contactTasks = $contact->tasks->get();
        $this->assertEquals(1, safeCount($contactTasks), 'Expected 1 Task to be linked to Contact');
    }

    public function testHandleActivities_TransferAction_DoNothing_NoActivitiesTransferred()
    {
        $task = SugarTestTaskUtilities::createTask();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $modules = [
            'Tasks' => $task,
            'Contacts' => $contact,
            'Accounts' => $account,
        ];

        $leadConvert = $this->getMockBuilder('LeadConvert')
            ->setMethods([
                'getVarDefs',
                'copyActivities',
                'getActivitySetting',
            ])
            ->setConstructorArgs([$this->leadId])
            ->disableOriginalConstructor()
            ->getMock();

        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modulesDef));

        $leadConvert->expects($this->never())
            ->method('copyActivities');

        $leadConvert->expects($this->once())
            ->method('getActivitySetting')
            ->will($this->returnValue('donothing'));

        $leadConvert->initialize($this->leadId);
        $leadConvert->convertLead($modules, 'donothing', ['Accounts', 'Contacts']);

        $lead = BeanFactory::getBean('Leads', $this->leadId);

        $this->assertEquals(
            LeadConvert::STATUS_CONVERTED,
            $lead->status,
            'Lead status field was not changed properly.'
        );
        $this->assertEquals(true, $lead->converted, 'Lead converted field not set properly');
    }
}
