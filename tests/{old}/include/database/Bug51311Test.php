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

class Bug51311Test extends TestCase
{
    public function providerBug51311()
    {
        return [
            [
                [
                    'name' => 'contents',
                    'dbType' => 'longtext',
                    'type' => 'nvarchar',
                    'vname' => 'LBL_DESCRIPTION',
                    'isnull' => true,
                ],
                'max',
            ],

            [
                [
                    'name' => 'contents',
                    'dbType' => 'text',
                    'type' => 'nvarchar',
                    'vname' => 'LBL_DESCRIPTION',
                    'isnull' => true,
                ],
                'max',
            ],

            [
                [
                    'name' => 'contents',
                    'dbType' => 'image',
                    'type' => 'image',
                    'vname' => 'LBL_DESCRIPTION',
                    'isnull' => true,
                ],
                '2147483647',
            ],

            [
                [
                    'name' => 'contents',
                    'dbType' => 'ntext',
                    'type' => 'image',
                    'vname' => 'LBL_DESCRIPTION',
                    'isnull' => true,
                ],
                '2147483646',
            ],

            [
                [
                    'name' => 'contents',
                    'dbType' => 'nvarchar',
                    'type' => 'nvarchar',
                    'vname' => 'LBL_DESCRIPTION',
                    'isnull' => true,
                ],
                '255',
            ],
        ];
    }

    /**
     * @dataProvider providerBug51311
     */
    public function testSqlSrvMassageFieldDef($fieldDef, $len)
    {
        $manager = new SqlsrvManager();
        $manager->massageFieldDef($fieldDef);
        $this->assertEquals($len, $fieldDef['len']);
    }
}
