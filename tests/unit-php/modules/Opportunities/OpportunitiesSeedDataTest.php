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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Opportunities;

use OpportunitiesSeedData;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpportunitiesSeedData
 */
class OpportunitiesSeedDataTest extends TestCase
{
    private $indices;

    protected function setUp(): void
    {
        $this->indices = [
            'sync_key' => [
                'name' => 'idx_opportunities_skey',
                'type' => 'unique',
                'fields' => ['sync_key'],
            ],
            'date_entered' => [
                'name' => 'idx_opportunities_del_d_e',
                'type' => 'index',
                'fields' => ['deleted', 'date_entered', 'id'],
            ],
            'deleted' => [
                'name' => 'idx_opportunities_id_del',
                'type' => 'index',
                'fields' => ['id', 'deleted'],
            ],
            0 => [
                'name' => 'idx_assigned_user_id',
                'type' => 'index',
                'fields' => ['id', 'assigned_user_id'],
            ],
        ];
    }

    /**
     * @covers ::evaluateIndexedValue()
     * @param string $name field name
     * @param $value field value
     * @param $expect expected result
     * @dataProvider evaluateIndexedValueProvider
     */
    public function testEvaluateIndexedValue(string $name, $value, $expect)
    {
        $value = OpportunitiesSeedData::evaluateIndexedValue($name, $value, $this->indices);
        $this->assertEquals($expect, $value);
    }

    /**
     * Indexed field values provider
     * @return array
     */
    public function evaluateIndexedValueProvider()
    {
        return [
            [
                'name' => 'sync_key',
                'value' => '',
                'expect' => null,
            ],
            [
                'name' => 'date_entered',
                'value' => '2020-08-26',
                'expect' => '2020-08-26',
            ],
            [
                'name' => 'deleted',
                'value' => 0,
                'expect' => 0,
            ],
            [
                'name' => 'assigned_user_id',
                'value' => '1234-5678',
                'expect' => '1234-5678',
            ],
        ];
    }
}
