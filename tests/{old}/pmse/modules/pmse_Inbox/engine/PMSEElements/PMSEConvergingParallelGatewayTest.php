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

class PMSEConvergingParallelGatewayTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $convergingParallelGateway;

    public function testRunIncompleteFlows()
    {
        $this->convergingParallelGateway = $this->getMockBuilder('PMSEConvergingParallelGateway')
            ->setMethods(['retrievePreviousFlows', 'prepareResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $passedFlows = [
            ['id' => '1234567'],
        ];

        $allFlows = [
            ['id' => '1234567'],
            ['id' => '3982749'],
            ['id' => '0987654'],
        ];

        $this->convergingParallelGateway->expects($this->exactly(2))
            ->method('retrievePreviousFlows')
            ->willReturnOnConsecutiveCalls($passedFlows, $allFlows);

        $flowData = ['bpmn_id' => '9872398ue23', 'cas_id' => 1];
        $bean = null;

        $this->convergingParallelGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'WAIT', 'NONE', []);

        $this->convergingParallelGateway->run($flowData, $bean);
    }

    public function testRunCompletedFlows()
    {
        $this->convergingParallelGateway = $this->getMockBuilder('PMSEConvergingParallelGateway')
            ->setMethods(['retrievePreviousFlows', 'prepareResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $passedFlows = [
            ['id' => '1234567'],
            ['id' => '3982749'],
            ['id' => '0987654'],
        ];

        $allFlows = [
            ['id' => '1234567'],
            ['id' => '3982749'],
            ['id' => '0987654'],
        ];

        $this->convergingParallelGateway->expects($this->exactly(2))
            ->method('retrievePreviousFlows')
            ->willReturnOnConsecutiveCalls($passedFlows, $allFlows);

        $flowData = ['bpmn_id' => '9872398ue23', 'cas_id' => 1];
        $bean = null;

        $this->convergingParallelGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'CREATE', []);

        $this->convergingParallelGateway->run($flowData, $bean);
    }
}
