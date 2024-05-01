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

/**
 * @ticket 43471
 */
class Bug43471Test extends TestCase
{
    public function testDynamicFieldsRepairCustomFields()
    {
        $bean = $this->createMock(Company::class);
        $bean->table_name = 'test' . date('YmdHis');
        $bean->field_defs = [
            'id' => [
                'name' => 'id',
                'vname' => 'LBL_ID',
                'type' => 'id',
                'required' => true,
                'reportable' => true,
                'comment' => 'Unique identifier',
            ],
            'name' => [
                'name' => 'name',
                'type' => 'name',
                'dbType' => 'varchar',
                'vname' => 'LBL_NAME',
                'len' => 150,
                'comment' => 'Name of the Company',
                'unified_search' => true,
                'audited' => true,
                'required' => true,
                'importable' => 'required',
                'merge_filter' => 'selected',
            ],
            'FooBar_c' => [
                'required' => false,
                'source' => 'custom_fields',
                'name' => 'FooBar_c',
                'vname' => 'LBL_FOOBAR',
                'type' => 'varchar',
                'massupdate' => '0',
                'default' => null,
                'comments' => 'LBL_FOOBAR_COMMENT',
                'help' => 'LBL_FOOBAR_HELP',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'calculated' => false,
                'len' => '255',
                'size' => '20',
                'id' => 'AccountsFooBar_c',
                'custom_module' => 'Accounts',
            ],
        ];

        $df = $this->createPartialMock(DynamicField::class, ['createCustomTable']);
        $df->setup($bean);
        $df->expects($this->any())
            ->method('createCustomTable')
            ->will($this->returnValue(null));

        $helper = $this->createPartialMock('MysqliManager', ['get_columns']);
        $helper->expects($this->any())
            ->method('get_columns')
            ->will($this->returnValue([
                'foobar_c' => [
                    'name' => 'FooBar_c',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ]));

        SugarTestHelper::setUp('mock_db', $helper);

        $repair = $df->repairCustomFields(false);
        $this->assertEquals('', $repair);
    }
}
