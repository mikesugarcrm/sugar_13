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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Adapter;

use PHPUnit\Framework\TestCase;
use Elastica\Bulk\Action;
use Elastica\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Bulk;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\DeleteDocumentWithType;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\IndexDocumentWithType;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Bulk
 */
class BulkTest extends TestCase
{
    /**
     * @covers ::getAction
     * @param string $version
     * @param $expected
     *
     * @dataProvider getActionProvider
     */
    public function testGetAction(string $version, ?string $opType, string $expectedClass)
    {
        $bulkMock = $this->getMockBuilder(Bulk::class)
            ->disableOriginalConstructor()
            ->setMethods(['getServerVersion'])
            ->getMock();

        $bulkMock->expects($this->any())
            ->method('getServerVersion')
            ->willReturn($version);

        $actionMock = $this->getMockBuilder(Action\AbstractDocument::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument'])
            ->getMockForAbstractClass();

        $actionMock->expects($this->any())
            ->method('getDocument')
            ->willReturn(new Document('123456'));

        $result = TestReflection::callProtectedMethod($bulkMock, 'getAction', [$actionMock, $opType]);
        $this->assertTrue($result instanceof $expectedClass);
    }

    public function getActionProvider()
    {
        return [
            '5.6, null opt index doc with type' => ['5.6', null, IndexDocumentWithType::class],
            '6.2, index doc with type' => ['6.2', Action::OP_TYPE_INDEX, IndexDocumentWithType::class],
            '7.x, index doc with no type' => ['7.2', Action::OP_TYPE_INDEX, Action\AbstractDocument::class],
            '5.6, null delete doc with type' => ['5.6', Action::OP_TYPE_DELETE, DeleteDocumentWithType::class],
            '6.2, delete doc with type' => ['6.2', Action::OP_TYPE_DELETE, DeleteDocumentWithType::class],
            '7.x, delete doc no type' => ['7.2', Action::OP_TYPE_DELETE, Action\AbstractDocument::class],
        ];
    }
}
