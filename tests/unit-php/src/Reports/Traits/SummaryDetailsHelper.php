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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\Traits;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Traits\SummaryDetailsHelper
 */
class SummaryDetailsHelper extends TestCase
{
    /**
     * @covers ::getGroupByKey
     * @dataProvider providerGetGroupByKey
     */
    public function testGetGroupByKey($data, $result)
    {
        $trait = $this->getTraitMock();

        $getGroupByKeyResult = TestReflection::callProtectedMethod($trait, 'getGroupByKey', [$data]);

        $this->assertSame($getGroupByKeyResult, $result);
    }

    public function providerGetGroupByKey()
    {
        return [
            [
                'data' => [
                    'name' => 'test',
                    'table_key' => 'table_key',
                    'label' => 'LBL_TEST',
                ],
                'result' => 'test#table_key#LBL_TEST',
            ],
        ];
    }

    /**
     * @covers ::createColumnsForHeaderWithoutGroupBy
     * @dataProvider providerCreateColumnsForHeaderWithoutGroupBy
     */
    public function testCreateColumnsForHeaderWithoutGroupBy(
        $headerRow,
        $row,
        $groupByIndexInHeaderRow,
        $detaliedHeader,
        $result
    ) {

        $trait = $this->getTraitMock();

        $data = TestReflection::callProtectedMethod(
            $trait,
            'createColumnsForHeaderWithoutGroupBy',
            [$headerRow, $row, $groupByIndexInHeaderRow, $detaliedHeader],
        );

        $this->assertSame($data, $result);
    }

    public function providerCreateColumnsForHeaderWithoutGroupBy()
    {
        return [
            [
                'headerRow' => [
                    0 => 'Primary Team Name',
                    1 => 'Count',
                ],
                'row' => [
                    'cells' => [
                        0 => 'Administrator',
                        1 => '1',
                    ],
                    'count' => 1,
                ],
                'groupByIndexInHeaderRow' => [
                    0 => 0,
                ],
                'detaliedHeader' => false,
                'result' => 'Count = 1',
            ],
        ];
    }

    /**
     * @param null|array $methods
     * @return \SummaryDetailsHelper
     */
    protected function getTraitMock($methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Reports\Traits\SummaryDetailsHelper::class)
            ->setMethods(null)
            ->getMockForTrait();
    }
}
