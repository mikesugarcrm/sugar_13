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
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;

/**
 * @coversDefaultClass SugarQuery_Builder_Field_Select
 */
class SelectTest extends TestCase
{
    public function expandFieldRelateOwnerDataProvider()
    {
        return [
            [
                [
                    'name' => 'account_name',
                    'type' => 'relate',
                    'source' => 'non-db',
                    'rname' => 'name',
                    'module' => 'Accounts',
                ],
            ],
            [
                [
                    'name' => 'account_id',
                    'type' => 'relate',
                    'source' => 'non-db',
                    'rname' => 'id',
                    'module' => 'Accounts',
                ],
            ],
        ];
    }

    /**
     * @dataProvider expandFieldRelateOwnerDataProvider
     * @covers ::expandField()
     */
    public function testExpandFieldRelateOwner($def)
    {
        $select = $this->getMockBuilder('SugarQuery_Builder_Field_Select')
            ->disableOriginalConstructor()
            ->setMethods(['checkCustomField', 'addToSelect'])
            ->getMock();
        $select->def = $def;
        $select->jta = 'join_table_alias';
        $select->query = $this->createMock('SugarQuery');
        $select->query->select = $this->getMockBuilder('SugarQuery_Builder_Select')
            ->disableOriginalConstructor()
            ->setMethods(['addField'])
            ->getMock();
        $select->query->select->expects($this->once())
            ->method('addField')
            ->with($this->equalTo('join_table_alias.assigned_user_id'));
        $select->expandField();
    }

    public function expandFieldRetrieveDataProvider()
    {
        return [
            [
                'verifyDBfields' => true,
                'get_columns_calls' => 1,
                'addToSelectCalls' => 1,
                'dbColumns' => [
                    'field1',
                ],
                'def' => [
                    [
                        'type' => 'id',
                        'name' => 'field1',
                    ],
                    [
                        'type' => 'id',
                        'name' => 'field2',
                    ],
                ],
            ],
            [
                'verifyDBfields' => false,
                'get_columns_calls' => 0,
                'addToSelectCalls' => 2,
                'dbColumns' => [],
                'def' => [
                    [
                        'type' => 'id',
                        'name' => 'field1',
                    ],
                    [
                        'type' => 'id',
                        'name' => 'field2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider expandFieldRetrieveDataProvider
     * @covers ::expandField()
     */
    public function testExpandFieldRetrieve($verifyDBfields, $get_columns_calls, $addToSelectCalls, $dbColumns, $def)
    {
        $select = $this->getMockBuilder(SugarQuery_Builder_Field_Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkCustomField', 'addToSelect'])
            ->getMock();
        $select->query = $this->createPartialMock(
            SugarQuery::class,
            [
                'getDBManager',
                'getFromBean',
            ]
        );
        $select->query->verifyDBfields = $verifyDBfields;
        $select->field = '*';

        $dbMock = TestMockHelper::getMockForAbstractClass(
            $this,
            DBManager::class,
            ['get_columns']
        );
        $dbMock->method('get_columns')->willReturn($dbColumns);
        $select->query->method('getDBManager')->willReturn($dbMock);

        $beanMock = $this->createPartialMock(
            SugarBean::class,
            [
                'getTableName',
            ]
        );
        $beanMock->field_defs = $def;
        $beanMock->module_name = 'test';
        $beanMock->method('getTableName')->willReturn('test');
        $select->query->method('getFromBean')->willReturn($beanMock);

        $dbMock->expects($this->exactly($get_columns_calls))->method('get_columns');
        $select->expects($this->exactly($addToSelectCalls))->method('addToSelect');

        $select->expandField();
    }
}
