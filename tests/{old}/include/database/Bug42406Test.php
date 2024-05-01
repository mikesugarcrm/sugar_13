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
 * Bug #42406
 * Bad Repair SQL Formed If Extended Vardef References a Field That Doesn't Exist
 *
 * @author mgusev@sugarcrm.com
 * @ticket 42406
 */
class Bug42406 extends TestCase
{
    public function getBrokenField()
    {
        return [
            [
                [
                    'name' => 'withouttype',
                    'type' => '',
                ],
                'TYPE',
            ],
            [
                [
                    'name' => '',
                    'type' => 'withoutname',
                ],
                'NAME',
            ],
        ];
    }

    /**
     * @dataProvider getBrokenField
     * @group 42406
     */
    public function testVardef($field, $error)
    {
        $fieldsdefs = [
            'broken_field' => $field,
            'test' => [
                'name' => 'test',
                'type' => 'varchar',
            ],
        ];
        $indices = [];

        $db = DBManagerFactory::getInstance();
        $result = $db->repairTableParams('contacts', $fieldsdefs, $indices, false);

        $this->assertMatchesRegularExpression('/\/\* ' . $error . ' IS MISSING IN VARDEF contacts::broken_field \*\//', $result, 'Broken vardef is passed to db');
    }
}
