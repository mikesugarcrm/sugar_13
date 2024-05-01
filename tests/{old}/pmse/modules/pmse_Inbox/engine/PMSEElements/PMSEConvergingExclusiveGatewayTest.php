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

class PMSEConvergingExclusiveGatewayTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $convergingExclusiveGateway;

    public function testRunWithNoPreviousFlows()
    {
        $this->convergingExclusiveGateway = $this->getMockBuilder('PMSEConvergingExclusiveGateway')
            ->setMethods(['retrievePreviousFlows', 'prepareResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->convergingExclusiveGateway->expects($this->exactly(1))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue([]));

        $flowData = ['bpmn_id' => '9872398ue23', 'cas_id' => 1];
        $bean = null;

        $this->convergingExclusiveGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'WAIT', 'NONE', []);

        $this->convergingExclusiveGateway->run($flowData, $bean);
    }

    public function testRun()
    {
        $this->convergingExclusiveGateway = $this->getMockBuilder('PMSEConvergingExclusiveGateway')
            ->setMethods(['retrievePreviousFlows', 'prepareResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $previousFlows = [
            ['id' => '1234567'],
        ];

        $flowData = ['bpmn_id' => '9872398ue23', 'cas_id' => 1];
        $bean = null;

        $this->convergingExclusiveGateway->expects($this->exactly(1))
            ->method('retrievePreviousFlows')
            ->will($this->returnValue($previousFlows));

        $this->convergingExclusiveGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'CREATE', []);

        $this->convergingExclusiveGateway->run($flowData, $bean);
    }
}
