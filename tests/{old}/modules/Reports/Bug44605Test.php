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
 * @ticket 44605
 */
class Bug44605Test extends TestCase
{
    protected function setUp(): void
    {
        $beanList = null;
        $beanFiles = null;
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    /**
     * Test that each "count" column in report is represented by it's own field
     */
    public function testEachCountColumnIsRepresented()
    {
        $primaryModule = 'PrimaryModule';
        $primaryModuleTable = 'primary_module';
        $relatedModule = 'RelatedModule';

        // any value greater than 1 would be valid
        $relatedModulesCount = 3;

        // create report definition
        $definition = [
            'module' => $primaryModule,
            'group_defs' => [
                [
                    'name' => 'id',
                    'table_key' => 'self',
                ],
            ],
            'summary_columns' => [
                [
                    'name' => 'count',
                    'table_key' => 'self',
                ],
            ],
            'full_table_list' => [
                'self' => [
                    'params' => [
                        'join_table_alias' => $primaryModuleTable,
                    ],
                ],
            ],
            'filters_def' => [],
            'display_columns' => [],
        ];

        // add "count" field for each related module
        for ($i = 0; $i < $relatedModulesCount; $i++) {
            $tableKey = $primaryModule . ':' . $relatedModule . $i;

            $definition['summary_columns'][] = [
                'name' => 'count',
                'table_key' => $tableKey,
            ];

            $definition['full_table_list'][$tableKey] = [
                'link_def' => [],
            ];
        }

        $report = new Report(json_encode($definition));
        $report->create_summary_select();

        $countFields = 0;
        $primaryModuleTableUsages = 0;
        foreach ($report->summary_select_fields as $field) {
            // calculate number of "count" fields in request
            if (0 === strpos(strtolower($field), 'count(')) {
                $countFields++;

                if (false !== strpos($field, $primaryModuleTable)) {
                    $primaryModuleTableUsages++;
                }
            }
        }

        // ensure that number of "count" fields in request equals to
        // related modules count (one field for each module) + 1 (primary module)
        $this->assertEquals(
            $relatedModulesCount + 1,
            $countFields
        );

        // ensure that primary module table is mentioned by it's name, not alias
        $this->assertEquals(1, $primaryModuleTableUsages);
    }
}
