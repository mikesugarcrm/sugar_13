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

class PMSEDivergingExclusiveGatewayTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $divergingExclusiveGateway;

    public function testRun()
    {
        $this->divergingExclusiveGateway = $this->getMockBuilder('PMSEDivergingExclusiveGateway')
            ->setMethods(['filterFlows', 'retrieveFollowingFlows', 'prepareResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingExclusiveGateway->expects($this->once())
            ->method('filterFlows')
            ->will($this->returnValue(['some_flow']));

        $flowData = [
            'id' => 'some_data',
        ];

        $this->divergingExclusiveGateway->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'CREATE', ['some_flow']);

        $this->divergingExclusiveGateway->run($flowData);
    }

    public function testRunWithoutFilters()
    {
        $this->divergingExclusiveGateway = $this->getMockBuilder('PMSEDivergingExclusiveGateway')
            ->setMethods(['filterFlows', 'retrieveFollowingFlows', 'prepareResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingExclusiveGateway->expects($this->once())
            ->method('filterFlows')
            ->will($this->returnValue([]));

        $flowData = [
            'id' => 'some_data',
        ];

        $this->expectException(PMSEElementException::class);
        $this->divergingExclusiveGateway->run($flowData);
    }
}
