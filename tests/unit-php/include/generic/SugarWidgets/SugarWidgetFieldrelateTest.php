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

namespace Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets;

use PHPUnit\Framework\TestCase;

/**
 * Class SugarWidgetFieldrelateTest
 *
 * @coversDefaultClass \SugarWidgetFieldRelate
 */
class SugarWidgetFieldrelateTest extends TestCase
{
    /**
     * @covers ::queryFilterEquals
     */
    public function testQueryFilterEquals()
    {
        $expected = "cases_cstm.user_id_c IN ('seed_sarah_id')";
        $layoutDef = [
            'adhoc' => 1,
            'name' => 'user_id_c',
            'table_key' => 'self',
            'qualifier_name' => 'equals',
            'input_name0' => 'Sarah Smith',
            'table_alias' => 'cases_cstm',
            'column_key' => 'self:owner_c',
            'type' => 'relate',
        ];
        $reporter = new \stdClass();
        $reporter->all_fields['self:owner_c'] = [
            'name' => 'owner_c',
            'vname' => 'LBL_OWNER',
            'type' => 'relate',
            'id_name' => 'user_id_c',
            'ext2' => 'Users',
            'module' => 'Cases',
            'rname' => 'name',
            'id' => 'Casesowner_c',
            'custom_module' => 'Cases',
            'real_table' => 'cases_cstm',
            'secondary_table' => 'users',
            'rep_rel_name' => 'owner_c_0',
        ];
        $lm = $this->createPartialMock('LayoutManager', []);
        $lm->setAttributePtr('reporter', $reporter);
        $widgetField = $this->getMockBuilder('SugarWidgetFieldRelate')
            ->setMethods(['getRelateIds'])
            ->setConstructorArgs([&$lm])
            ->getMock();
        $widgetField->expects($this->once())
            ->method('getRelateIds')
            ->will($this->returnValue(['seed_sarah_id']));
        $filter = $widgetField->queryFilterEquals($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::displayList
     */
    public function testDisplayList()
    {
        $reporter = new \stdClass();
        $reporter->embeddedData = true;

        $lm = $this->createPartialMock('LayoutManager', []);
        $lm->setAttributePtr('reporter', $reporter);

        $sugarWidgetFieldRelate = new \SugarWidgetFieldRelate($lm);

        $GLOBALS['bwcModules'] = [];

        $layout_def = [
            'name' => 'account_id_c',
            'label' => 'test contact',
            'table_key' => 'self',
            'table_alias' => 'accounts_cstm',
            'column_key' => 'self:test_contact_c',
            'type' => 'relate',
            'fields' =>
            [
                'PRIMARYID' => '55ae7c54-d0ab-11ee-aeb4-acde48001122',
                'ACCOUNTS_NAME' => 'scheduler',
                'ACCOUNTS_DESCRIPTION' => 'test',
                'ACCOUNTS_CSTM_ACCOUNT_ID_C' => 'b7dd8c24-b938-11ee-9481-acde48001122',
                'ACCOUNTS1_NAME' => 'Kringle Bell IncK.A. Tower & Co',
            ],
        ];

        $reporter->all_fields['self:test_contact_c'] = [
            'labelValue' => 'test contact',
            'dependency' => '',
            'required_formula' => '',
            'readonly_formula' => '',
            'required' => false,
            'readonly' => false,
            'source' => 'non-db',
            'name' => 'test_contact_c',
            'vname' => 'LBL_TEST_CONTACT',
            'type' => 'relate',
            'massupdate' => true,
            'hidemassupdate' => false,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => 1,
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'pii' => false,
            'calculated' => false,
            'len' => 255,
            'size' => '20',
            'id_name' => 'account_id_c',
            'ext2' => 'Accounts',
            'module' => 'Accounts',
            'rname' => 'name',
            'quicksearch' => 'enabled',
            'studio' => 'visible',
            'id' => '0aea46d0-d0ab-11ee-91a0-acde48001122',
            'custom_module' => 'Accounts',
            'real_table' => 'accounts_cstm',
            'secondary_table' => 'accounts',
            'rep_rel_name' => 'test_contact_c_0',
        ];

        $result = $sugarWidgetFieldRelate->displayList($layout_def);

        $this->assertStringNotContainsString('<a target="_blank"', $result);

        $reporter->embeddedData = false;
        $resultWithHTMLTags = $sugarWidgetFieldRelate->displayList($layout_def);

        $this->assertStringContainsString('<a target="_blank"', $resultWithHTMLTags);
    }
}
