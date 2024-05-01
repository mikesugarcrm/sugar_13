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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\Types;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use BeanFactory;

/**
 * @ticket CA-1507
 */
class BugCA1057Test extends TestCase
{
    /**
     *
     * Test with fix applyed.
     *
     * @param array $data
     * @param array $expectedData
     *
     * @dataProvider providerTestCount
     */
    public function testCountSuccess($data, $expectedData)
    {
        $ignoreFix = false;
        $this->countTest($data, $expectedData, $ignoreFix);
    }

    /**
     *
     * Test without the fix.
     *
     * @param array $data
     * @param array $expectedData
     *
     * @dataProvider providerTestCount
     */
    public function testCountFailed($data, $expectedData)
    {
        $ignoreFix = true;
        $this->countTest($data, $expectedData, $ignoreFix);
    }

    /**
     *
     * Test the logic for both cases, fixed/unfixed
     *
     * @param array $data
     * @param array $expectedData
     * @param bool $ignoreFix
     */
    protected function countTest($data, $expectedData, $ignoreFix)
    {
        $mockedSummaryMethods = ['getRecords', 'getOrderBy', 'getSummaryGrandTotal'];

        if ($ignoreFix) {
            $mockedSummaryMethods[] = 'setModuleByTableKey';
        }

        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\Summary::class, $mockedSummaryMethods);
        $mockReport = $this->getReporterMock('\Report');

        $accountBean = BeanFactory::newBean('Accounts');

        TestReflection::setProtectedValue($mockReport, 'report_def', $data['reportDef']);
        TestReflection::setProtectedValue($mockReport, 'focus', $accountBean);
        TestReflection::setProtectedValue($mockReport, 'full_table_list', $data['allFields']);

        if ($ignoreFix) {
            $mockSummary->expects($this->any())
                ->method('setModuleByTableKey')
                ->willReturn([]);
        }

        $mockSummary->expects($this->any())
            ->method('getRecords')
            ->willReturn([]);

        $mockSummary->expects($this->any())
            ->method('getOrderBy')
            ->willReturn([]);

        $mockSummary->expects($this->any())
            ->method('getSummaryGrandTotal')
            ->willReturn([]);

        $records = TestReflection::callProtectedMethod($mockSummary, 'generateData', [$mockReport]);

        $this->assertIsArray($records);

        $header = $records['header'];

        $this->assertIsArray($header);
        $this->assertEquals(count($header), $expectedData['count']);

        $countField = $header[1];

        if ($ignoreFix) {
            $this->assertFalse(array_key_exists($expectedData['keyName'], $countField));
        } else {
            $this->assertTrue(array_key_exists($expectedData['keyName'], $countField));
            $this->assertEquals($countField['module'], 'Accounts');
        }
    }

    /**
     * Data for testCountFailed/testCountFailed
     */
    public function providerTestCount()
    {
        return [
            [
                [
                    'reportDef' => [
                        'summary_columns' => [
                            [
                                'name' => 'billing_address_city',
                                'label' => 'Billing City',
                                'table_key' => 'self',
                            ],
                            [
                                'name' => 'count',
                                'label' => 'Count',
                                'field_type' => '',
                                'group_function' => 'count',
                                'table_key' => 'self',
                            ],
                        ],
                    ],
                    'allFields' => [
                        'self:billing_address_city' => [
                            'name' => 'billing_address_city',
                            'vname' => 'LBL_BILLING_ADDRESS_CITY',
                            'type' => 'varchar',
                            'len' => '100',
                            'comment' => 'The city used for billing address',
                            'group' => 'billing_address',
                            'merge_filter' => 'enabled',
                            'duplicate_on_record_copy' => 'always',
                            'module' => 'Accounts',
                            'real_table' => 'accounts',
                            'rep_rel_name' => 'billing_address_city_0',
                        ],
                        'self' => [
                            'value' => 'Accounts',
                            'module' => 'Accounts',
                            'bean_label' => 'Accounts',
                            'bean_module' => 'Accounts',
                        ],
                    ],
                ],
                [
                    'count' => 2,
                    'keyName' => 'module',
                    'moduleName' => 'Accounts',
                ],
            ],
        ];
    }

    /**
     * @param string $mockPath
     * @param null|array $methods
     * @return \Summary
     */
    protected function getReporterMock($mockPath, $methods = null)
    {
        return $this->getMockBuilder($mockPath)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
