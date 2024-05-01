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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine\PMSEElements;

use Sugarcrm\Sugarcrm\ProcessManager\Registry;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSEStartEvent
 */
class PMSEStartEventTest extends TestCase
{
    /**
     * @covers ::run
     */
    public function testRun()
    {
        $id = '1234567890';
        $flowData = ['bpmn_id' => $id];
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $bean->id = '0987654321';

        $registryEntry = ['1234567890' => ['0987654321' => true]];

        $registryMock = $this->createMock(Registry\Registry::class);

        $registryMock->method('get')
            ->willReturn($registryEntry);

        $loggerMock = $this->createMock('PMSELogger');

        $loggerMock->expects($this->once())
            ->method('alert');

        $startEventMock = $this->getMockBuilder('PMSEStartEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getRegistry', 'prepareResponse'])
            ->getMock();

        $startEventMock->method('getRegistry')
            ->willReturn($registryMock);

        $startEventMock->setLogger($loggerMock);

        $startEventMock->expects($this->once())
            ->method('prepareResponse')
            ->with([], '', '');

        $startEventMock->run($flowData, $bean);
    }
}
