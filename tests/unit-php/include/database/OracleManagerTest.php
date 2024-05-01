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

namespace Sugarcrm\SugarcrmTestsUnit\inc\database;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class OracleManagerTest
 *
 * @coversDefaultClass \OracleManager
 */
class OracleManagerTest extends TestCase
{
    /**
     * @covers ::massageIndexDefs
     * @covers ::generateCaseInsensitiveIndices
     */
    public function testMassageIndexDefs()
    {
        /** @var \DBManager|MockObject $db */
        $db = $this->getMockBuilder('OracleManager')
            ->disableOriginalConstructor()
            ->setMethods(['query'])
            ->getMock();

        $ciIndexConstraint = $this->logicalAnd(
            $this->matchesRegularExpression('/idx1_ci/'),
            $this->matchesRegularExpression('/UPPER\(field3\)/'),
            $this->logicalNot($this->matchesRegularExpression('/UPPER\(field[12]\)/'))
        );

        $db->expects($this->exactly(3))
            ->method('query')
            ->withConsecutive(
                [],
                [$this->matchesRegularExpression('/idx1/')],
                [$ciIndexConstraint]
            )
            ->willReturn(true);

        $indices = [
            ['name' => 'idx1', 'type' => 'index', 'fields' => ['field1', 'field2', 'field3']],
        ];

        $fieldDefs = [
            'field1' => ['name' => 'field1', 'type' => 'id'],
            'field2' => ['name' => 'field2', 'type' => 'enum'],
            'field3' => ['name' => 'field3', 'type' => 'varchar'],
        ];

        $db->createTableParams('table1', $fieldDefs, $indices);
    }

    /**
     * @covers ::getColumnFunctionalIndices
     */
    public function testGetColumnFunctionalIndices()
    {
        $db = $this->getMockBuilder('OracleManager')
            ->disableOriginalConstructor()
            ->onlyMethods(['get_indices'])
            ->getMock();
        $indices = [
            [
                'name' => 'idx1',
                'type' => 'index',
                'fields' => [
                    'field1',
                    'upper(field2)',
                ],
            ],
            [
                'name' => 'idx2',
                'type' => 'index',
                'fields' => [
                    'upper(field1)',
                    'field2',
                ],
            ],
        ];
        $db->expects($this->once())->method('get_indices')->willReturn($indices);

        $this->assertEquals($indices, $db->getColumnFunctionalIndices('table1', 'field1'));
    }
}
