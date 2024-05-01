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

namespace Sugarcrm\SugarcrmTestsUnit\Dbal\Query;

use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder
 */
class QueryBuilderTest extends TestCase
{
    /**
     * @covers ::importSubQuery
     */
    public function testImportSubQuery()
    {
        /** @var \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder|MockObject $q1 */
        $q1 = $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSQL'])
            ->getMock();
        $q1->expects($this->once())
            ->method('getSQL')
            ->willReturn('SELECT 1 FROM DUAL');
        $q1->createPositionalParameter('x', ParameterType::INTEGER);

        /** @var \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder|MockObject $q2 */
        $q2 = $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $sql = $q2->importSubQuery($q1);
        $this->assertEquals('SELECT 1 FROM DUAL', $sql);

        $q2->createPositionalParameter('y', ParameterType::BOOLEAN);

        $this->assertSame(['x', 'y'], $q2->getParameters());

        $this->assertSame([ParameterType::INTEGER, ParameterType::BOOLEAN], $q2->getParameterTypes());
    }
}
