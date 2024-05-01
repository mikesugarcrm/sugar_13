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

class PMSEAssignUserTest extends TestCase
{
    /**
     * @var type
     */
    protected $loggerMock;

    /**
     * @var PMSEElement
     */
    protected $assignUser;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug'])
            ->getMock();
    }

    public function testRun()
    {
        $this->assignUser = $this->getMockBuilder('PMSEAssignUser')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'retrieveDefinitionData',
                    'retrieveUserData',
                    'retrieveHistoryData',
                    'prepareResponse',
                ]
            )
            ->getMock();

        $this->assignUser->setLogger($this->loggerMock);

        $definitionMock = [
            'id' => '12s1829s739sj8912123',
            'pro_id' => '8937298492jd823',
            'act_assign_user' => 'dui3j89d8923jd3',
            'act_update_record_owner' => true,
        ];

        $this->assignUser->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));

        $userMock = $this->getMockBuilder('Users')
            ->getMock();
        $userMock->id = 'dui3j89d8923jd3';
        $userMock->status = 'Active';

        $currentUser = new stdClass();
        $currentUser->id = '82jes9823jd8932';

        $this->assignUser->expects($this->exactly(1))
            ->method('retrieveUserData')
            ->will($this->returnValue($userMock));

        $historyMock = $this->getMockBuilder('PMSEHistoryData')
            ->setMethods(['savePreData', 'savePostData', 'getLog'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->assignUser->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyMock));

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $beanMock->field_defs = [
            'assigned_user_id' => '2389uj29eu8932ue93',
        ];

        $flowData = [
            'bpmn_id' => 'act89u298dj2893j',
            'cas_sugar_module' => 'Leads',
            'cas_user_id' => 'sijd8923j98d2',
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $caseHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(['saveFormAction'])
            ->getMock();

        $caseHandlerMock->expects($this->exactly(1))
            ->method('saveFormAction');

        $this->assignUser->expects($this->exactly(1))
            ->method('prepareResponse');

        $this->assignUser->setCurrentUser($currentUser);
        $this->assignUser->setCaseFlowHandler($caseHandlerMock);
        $this->assignUser->run($flowData, $beanMock, '');
    }
}
