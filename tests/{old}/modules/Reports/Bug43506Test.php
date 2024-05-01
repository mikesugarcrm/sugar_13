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

require_once 'include/generic/SugarWidgets/SugarWidgetReportField.php';

/**
 * Bug #43506
 * desc
 *
 * @ticket 43506
 */
class Bug43506Test extends TestCase
{
    protected function setUp(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
        unset($GLOBALS['app_strings'], $GLOBALS['app_list_strings']);
    }

    private function createContent($module, $def)
    {
        $content = '{"display_columns":[],"module":"' . $module . '",' .
            '"group_defs":[{"name":"' . $def['name'] . '","label":"Parent Type","table_key":"self","type":"' . $def['type'] . '"}],' .
            '"summary_columns":[' .
            '{"name":"' . $def['name'] . '","label":"Parent Type","table_key":"self"},' .
            '{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],' .
            '"report_name":"Report #1","chart_type":"none","do_round":1,"chart_description":"",' .
            '"numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1",' .
            '"report_type":"summary",' .
            '"full_table_list":{"self":{"value":"' . $module . '","module":"' . $module . '","label":"' . $module . '"}},' .
            '"filters_def":{"Filter_1":{"operator":"AND"}}}';
        return $content;
    }

    private function createDef($objBean, $def)
    {
        $defs = [
            'name' => $def['name'],
            'label' => 'Parent Type',
            'table_key' => 'self',
            'type' => $def['type'],
            'table_alias' => $objBean->table_name,
            'column_key' => 'self:' . $def['name'],
        ];
        return $defs;
    }

    public function providerData()
    {
        $data = [];

        /**
         * find beans that have field with type 'parent_type'
         */
        foreach ($GLOBALS['beanList'] as $module => $bean_name) {
            if (isset($GLOBALS['beanFiles'][$bean_name])) {
                require_once $GLOBALS['beanFiles'][$bean_name];
                $objBean = new $bean_name();
                $found = false;

                if (!isset($objBean->field_defs) || empty($objBean->field_defs)) {
                    continue;
                }

                foreach ($objBean->field_defs as $field_name => $defs) {
                    if ($defs['type'] == 'parent_type') {
                        $found = $field_name;
                    }
                }
                if ($found !== false) {
                    $data[] = [
                        $objBean,
                        $found,
                        $this->createContent($module, $objBean->field_defs[$found]),
                        $this->createDef($objBean, $objBean->field_defs[$found]),
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @group 43506
     * @params SugarBean $objBean
     * @params string $field_name name of field with type 'parent_type'
     * @params string $content generated json for report
     * @params array $def generated defs for report
     * @dataProvider providerData
     */
    public function testQueryOrderBy($objBean, $field_name, $content, $def)
    {
        if (!isset($objBean->field_defs[$field_name]['options']) || empty($objBean->field_defs[$field_name]['options'])) {
            $this->fail('Field with type = "parent_type" must have options params.');
        }

        $report = new Report($content);
        $report->db = DBManagerFactory::getInstance('reports');
        $report->layout_manager = new LayoutManager();
        $report->layout_manager->default_widget_name = 'ReportField';
        $report->layout_manager->setAttributePtr('reporter', $report);

        $widget = new SugarWidgetFieldparent_type($report->layout_manager);
        $query = $widget->queryOrderBy($def);

        // check is main query valid SQL (returned order by is valid)
        $query = 'SELECT * FROM ' . $objBean->table_name . ' ORDER BY ' . $query;
        $result = $GLOBALS['db']->query($query);
        $this->assertNotEmpty($result);
    }
}
