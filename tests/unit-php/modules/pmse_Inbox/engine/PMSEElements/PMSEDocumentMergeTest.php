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

use Document;
use DocumentMerge;
use Sugarcrm\Sugarcrm\ProcessManager\Registry;
use PHPUnit\Framework\TestCase;
use PMSEDocumentMerge;

/**
 * @coversDefaultClass \PMSEDocumentMerge
 */
class PMSEDocumentMergeTest extends TestCase
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

        $documentMergeMock = $this->getMockBuilder(\PMSEDocumentMerge::class)
            ->disableOriginalConstructor()
            ->setMethods(['isRunnable', 'prepareResponse', 'getDefinitionBean',
                'merge', 'retrieveDefinitionData', 'buildEvnDefBean', 'updateEvnDefBean',])
            ->getMock();

        // The way licensing is set up, in order for this to be a unit test, this
        // method was added then stubbed
        $documentMergeMock->method('isRunnable')
            ->willReturn(true);

        $documentMergeMock->method('retrieveDefinitionData')
            ->willReturn(['id' => $id]);

        $documentMergeMock->method('getDefinitionBean')
            ->willReturn((object)['act_fields' => json_encode([
                'recordId' => '123',
                'recordModule' => 'Accounts',
                'useRevision' => true,
                'templateId' => '123',
                'mergeType' => 'merge',]),
            ]);

        $documentMergeMock->method('buildEvnDefBean')
            ->willReturn(true);

        $documentMergeMock->method('updateEvnDefBean')
            ->willReturn(true);

        $documentMergeMock->expects($this->once())->method('merge')->with((object)[
            'recordId' => '123',
            'recordModule' => 'Accounts',
            'useRevision' => true,
            'templateId' => '123',
            'mergeType' => 'merge',
        ], $bean);

        $documentMergeMock->expects($this->once())
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'CREATE');

        $documentMergeMock->run($flowData, $bean);
    }
}
