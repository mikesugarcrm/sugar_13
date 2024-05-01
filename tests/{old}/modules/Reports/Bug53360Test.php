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

require_once 'modules/Reports/templates/templates_list_view.php';

/**
 * Bug #53360
 * Matrix Report gets broken when data is ordered by one of summary columns
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 53360
 */
class Bug53360Test extends TestCase
{
    private $rowsAndColumnsData;
    private $report;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->report = new Report();
        $this->report->report_def = ['group_defs' => $this->getDummyGroupDefs()];
        $this->report->group_defs_Info = $this->getDummyGroupDefsInfo();
        $this->rowsAndColumnsData = $this->getData();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testGroupByFunctionZeroCount()
    {
        $ret = whereToStartGroupByRow($this->report, 0, $this->rowsAndColumnsData, null);

        $this->assertEquals(-1, $ret);
    }

    public function testGroupByFunctionUniqueRecord()
    {
        $row = $this->rowsAndColumnsData[0]; // this is chris, he is unique in our data set, should return -1
        $ret = whereToStartGroupByRow($this->report, 0, $this->rowsAndColumnsData, $row);

        $this->assertEquals(-1, $ret);
    }

    public function testGroupByFunctionNonUniqueRecord()
    {
        $row = $this->rowsAndColumnsData[1]; // this is first entry of sarah, she is not unique in our data set, should return index of first appearance
        $ret = whereToStartGroupByRow($this->report, 1, $this->rowsAndColumnsData, $row);
        $this->assertEquals(1, $ret); // first appearance of sarah is 1st index

        $row = $this->rowsAndColumnsData[2]; // this is second entry of sarah, she is not unique in our data set, should return index of first appearance
        $ret = whereToStartGroupByRow($this->report, 2, $this->rowsAndColumnsData, $row);
        $this->assertEquals(1, $ret); // first appearance of sarah is 1st index
    }

    private function getDummyGroupDefs()
    {
        return [
            0 => ['name' => 'user_name', 'label' => 'User Name', 'table_key' => 'Opportunities:assigned_user_link', 'type' => 'user_name'],
            1 => ['name' => 'sales_stage', 'label' => 'Sales Stage', 'table_key' => 'self', 'type' => 'enum'],
        ];
    }

    private function getDummyGroupDefsInfo()
    {
        $grpDefs = [
            [
                'name' => 'user_name', 'label' => 'User Name', 'table_key' => 'Opportunities:assigned_user_link',
                'type' => 'username', 'index' => 0,
            ],
            [
                'name' => 'sales_stage', 'label' => 'Sales Stage', 'table_key' => 'self', 'type' => 'enum',
                'index' => 1,
            ],
        ];
        $grpKeys = array_map('getGroupByKey', $grpDefs);
        return array_combine($grpKeys, $grpDefs);
    }

    private function getData()
    {
        return [
            ['cells' => ['chris', 'Value Proposition', '$10,000.00', '$10,000.00', '1'], 'count' => 1, 'User Name' => 'chris'],
            ['cells' => ['sarah', 'Value Proposition', '$10,000.00', '$20,000.00', '2'], 'count' => 2, 'User Name' => 'sarah'],
            ['cells' => ['sarah', 'Needs Analysis', '$10,000.00', '$10,000.00', '1'], 'count' => 1, 'User Name' => 'sarah'],
        ];
    }
}
