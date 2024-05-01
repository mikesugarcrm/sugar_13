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

class PMSERoundRobinTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $roundRobin;

    public function testRun()
    {
        $this->roundRobin = $this->getMockBuilder('PMSERoundRobin')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'retrieveDefinitionData',
                    'retrieveTeamData',
                    'retrieveHistoryData',
                    'prepareResponse',
                ]
            )
            ->getMock();

        $definitionMock = [
            'id' => '12s1829s739sj8912123',
            'pro_id' => '8937298492jd823',
            'act_assign_team' => 'mi192sk09219s2',
            'act_assignment_method' => 'BALANCED',
            'act_update_record_owner' => 1,
        ];

        $this->roundRobin->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));

        $teamMock = $this->getMockBuilder('Users')
            ->getMock();
        $teamMock->id = 'mi192sk09219s2';

        $currentUser = new stdClass();
        $currentUser->id = '82jes9823jd8932';

        $this->roundRobin->expects($this->exactly(1))
            ->method('retrieveTeamData')
            ->will($this->returnValue($teamMock));

        $historyMock = $this->getMockBuilder('PMSEHistoryData')
            ->setMethods(['savePreData', 'savePostData', 'getLog'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->roundRobin->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyMock));

        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(['load_relationship', 'save'])
            ->getMock();

        $dbMock = $this->getMockBuilder('DBHandler')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $beanMock->db = $dbMock;
        $beanMock->team_id = '932ei0923dk0239ike023';
        $beanMock->team_set_id = 'ijdaoisdjaio892de';
        $beanMock->assigned_user_id = '278uw8912uw1';
        $beanMock->field_defs = [
            'team_id' => '932ei0923dk0239ike023',
        ];


        $flowData = [
            'bpmn_id' => 'act89u298dj2893j',
            'cas_sugar_module' => 'Leads',
            'cas_user_id' => 'sijd8923j98d2',
            'cas_id' => 1,
            'cas_index' => 2,

        ];

        $caseHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['saveFormAction'])
            ->getMock();

        $userHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getNextUserUsingRoundRobin'])
            ->getMock();

        $caseHandlerMock->expects($this->exactly(1))
            ->method('saveFormAction');

        $this->roundRobin->expects($this->exactly(1))
            ->method('prepareResponse');

        $this->roundRobin->setCurrentUser($currentUser);
        $this->roundRobin->setCaseFlowHandler($caseHandlerMock);
        $this->roundRobin->setUserAssignmentHandler($userHandlerMock);
        $this->roundRobin->run($flowData, $beanMock, '');
    }
}
