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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass FieldApi
 * @group ApiTests
 */
class FieldApiTest extends TestCase
{
    protected $currentUser;
    protected $fieldApi;
    protected $serviceMock;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        // current user must be admin so a new field can be created
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->fieldApi = new FieldApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        $fieldsByModule = [
            'Opportunities' => [
                'new_decimal_field_c',
                'new_dropdown_field_c',
                'new_dropdown_field_with_new_dropdown_c',
                'new_checkbox_field_c',
                'new_datetime_field_c',
                'another_decimal_field_c',
            ],
            'Leads' => [
                'new_text_field_c',
                'new_multiselect_field_c',
                'new_multiselect_field_with_new_dropdown_c',
                'new_date_field_c',
                'ai_conv_score_classification_c',
            ],
        ];
        require_once 'modules/DynamicFields/DynamicField.php';

        // cleans up test fields in associated modules
        foreach ($fieldsByModule as $moduleName => $fields) {
            foreach ($fields as $field) {
                $dyField = new DynamicField();
                $dyField->bean = BeanFactory::getBean($moduleName);
                $dyField->module = $moduleName;
                $dyField->deleteField($field);
            }
        }

        $dropdownsToRemove = [
            'a_new_dropdown_dom',
            'a_new_multienum_dom',
            'ai_conv_score_classification_dropdown',
        ];
        $dirName = 'custom/Extension/application/Ext/Language';
        if (SugarAutoLoader::ensureDir($dirName)) {
            foreach ($dropdownsToRemove as $dropdown) {
                if (file_exists("$dirName/en_us.sugar_{$dropdown}.php")) {
                    unlink("$dirName/en_us.sugar_{$dropdown}.php");
                }
                if (!empty($GLOBALS['app_list_strings'][$dropdown])) {
                    unset($GLOBALS['app_list_strings'][$dropdown]);
                }
            }
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Checks the new custom field is added to the specified module
     *
     * @covers ::createCustomField
     * @param array $args argument that contains field attributes
     *
     * @dataProvider createCustomFieldProvider
     */
    public function testCreateCustomField(array $args)
    {
        $mod_strings = [];
        $result = $this->fieldApi->createCustomField($this->serviceMock, $args);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals($args['data']['name'] . '_c', $result['name']);
        $this->assertEquals($args['data']['type'], $result['type']);

        $basepath = "custom/Extension/modules/{$args['module']}/Ext/Language";
        $filename = "$basepath/en_us.lang.php";
        if (file_exists($basepath) && file_exists($filename)) {
            include $filename;
            $this->assertNotEmpty($mod_strings[$args['data']['label']]);
            $this->assertEquals(
                $args['localizations']['en_us'][$args['data']['label']],
                $mod_strings[$args['data']['label']]
            );
        }

        if (($args['data']['type'] === 'enum' ||
                $args['data']['type'] === 'multienum') &&
            is_array($args['data']['options'])) {
            // options is an array, create a new dropdown if dropdown doesn't exist
            require_once 'include/utils.php';
            $dd = translate($args['data']['options']['dropdownName']);
            $this->assertNotEmpty($dd);
            if (!empty($args['data']['options']['dropdownList']) &&
                !empty($args['localizations']['en_us'])) {
                $dropdownList = [];
                foreach ($args['data']['options']['dropdownList'] as $list) {
                    $value = $list['value'];
                    $label = $value !== '' ? $args['localizations']['en_us'][$list['label']] : '';
                    $dropdownList[$value] = $label;
                }
                $this->assertEquals($dropdownList, $dd);
            }
        }
    }

    /**
     * Provider for ::testCreateCustomField
     *
     * @return array
     */
    public function createCustomFieldProvider()
    {
        return [
            // Decimal field
            [
                'args' => [
                    'module' => 'Opportunities',
                    'localizations' => [
                        'en_us' => [
                            'LBL_DECIMAL_FIELD' => 'New decimal field',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_decimal_field',
                        'type' => 'decimal',
                        'label' => 'LBL_DECIMAL_FIELD',
                        'len' => '18',
                        'precision' => '8',
                    ],
                ],
            ],
            // Text field
            [
                'args' => [
                    'module' => 'Leads',
                    'localizations' => [
                        'en_us' => [
                            'LBL_TEXT_FIELD' => 'New text field',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_text_field',
                        'type' => 'varchar',
                        'label' => 'LBL_TEXT_FIELD',
                        'len' => '255',
                    ],
                ],
            ],
            // Dropdown field 1
            [
                'args' => [
                    'module' => 'Opportunities',
                    'localizations' => [
                        'en_us' => [
                            'LBL_DROPDOWN_FIELD' => 'New dropdown field',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_dropdown_field',
                        'type' => 'enum',
                        'label' => 'LBL_DROPDOWN_FIELD',
                        'options' => 'account_type_dom',
                        'default_value' => 'Analyst',
                    ],
                ],
            ],
            // Dropdown field 2
            [
                'args' => [
                    'module' => 'Opportunities',
                    'localizations' => [
                        'en_us' => [
                            'LBL_DROPDOWN_FIELD_WITH_NEW_DROPDOWN' => 'New dropdown field with new dropdown',
                            'LBL_DD_ITEM_ONE' => 'First Text',
                            'LBL_DD_ITEM_TWO' => 'Second Text',
                            'LBL_DD_ITEM_THREE' => 'Third Text',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_dropdown_field_with_new_dropdown',
                        'type' => 'enum',
                        'label' => 'LBL_DROPDOWN_FIELD_WITH_NEW_DROPDOWN',
                        'options' => [
                            'dropdownName' => 'a_new_dropdown_dom',
                            'dropdownList' => [
                                ['value' => 'First', 'label' => 'LBL_DD_ITEM_ONE'],
                                ['value' => 'Second', 'label' => 'LBL_DD_ITEM_TWO'],
                                ['value' => 'Third', 'label' => 'LBL_DD_ITEM_THREE'],
                            ],
                        ],
                        'default_value' => 'First',
                    ],
                ],
            ],
            // Dropdown field 3
            [
                'args' => [
                    'module' => 'Leads',
                    'localizations' => [
                        'en_us' => [
                            'LBL_AI_CONV_SCORE_CLASSIFICATION_TEST' => 'Ai Conv Score Classification',
                            'LBL_LEADS_CONV_NOT_LIKELY_TEST' => 'Not Likely',
                            'LBL_LEADS_CONV_LESS_LIKELY_TEST' => 'Less Likely',
                            'LBL_LEADS_CONV_SAME_TEST' => 'Same',
                        ],
                    ],
                    'data' => [
                        'name' => 'ai_conv_score_classification_test',
                        'type' => 'enum',
                        'label' => 'LBL_AI_CONV_SCORE_CLASSIFICATION_TEST',
                        'options' => [
                            'dropdownName' => 'ai_conv_score_classification_dropdown_test',
                            'dropdownList' => [
                                ['value' => '', 'label' => ''],
                                ['value' => 'not_likely', 'label' => 'LBL_LEADS_CONV_NOT_LIKELY_TEST'],
                                ['value' => 'less_likely', 'label' => 'LBL_LEADS_CONV_LESS_LIKELY_TEST'],
                                ['value' => 'same', 'label' => 'LBL_LEADS_CONV_SAME_TEST'],
                            ],
                        ],
                        'default_value' => '',
                    ],
                ],
            ],
            // MultiSelect field 1
            [
                'args' => [
                    'module' => 'Leads',
                    'localizations' => [
                        'en_us' => [
                            'LBL_MULTISELECT_FIELD' => 'New multiselect field',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_multiselect_field',
                        'type' => 'multienum',
                        'label' => 'LBL_MULTISELECT_FIELD',
                        'options' => 'account_type_dom',
                        'default_value' => 'Analyst',
                    ],
                ],
            ],
            // MultiSelect field 2
            [
                'args' => [
                    'module' => 'Leads',
                    'localizations' => [
                        'en_us' => [
                            'LBL_MULTISELECT_FIELD_WITH_NEW_DROPDOWN' => 'New multiselect field with new dropdown',
                            'LBL_DD_ITEM_ONE' => 'First Text',
                            'LBL_DD_ITEM_TWO' => 'Second Text',
                            'LBL_DD_ITEM_THREE' => 'Third Text',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_multiselect_field_with_new_dropdown',
                        'type' => 'multienum',
                        'label' => 'LBL_MULTISELECT_FIELD_WITH_NEW_DROPDOWN',
                        'options' => [
                            'dropdownName' => 'a_new_multienum_dom',
                            'dropdownList' => [
                                ['value' => 'First', 'label' => 'LBL_DD_ITEM_ONE'],
                                ['value' => 'Second', 'label' => 'LBL_DD_ITEM_TWO'],
                                ['value' => 'Third', 'label' => 'LBL_DD_ITEM_THREE'],
                            ],
                        ],
                        'default_value' => 'Second',
                    ],
                ],
            ],
            // Checkbox field
            [
                'args' => [
                    'module' => 'Opportunities',
                    'localizations' => [
                        'en_us' => [
                            'LBL_CHECKBOX_FIELD' => 'New checkbox field',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_checkbox_field',
                        'type' => 'bool',
                        'label' => 'LBL_CHECKBOX_FIELD',
                        'default_value' => true,
                    ],
                ],
            ],
            // Date field
            [
                'args' => [
                    'module' => 'Leads',
                    'localizations' => [
                        'en_us' => [
                            'LBL_DATE_FIELD' => 'New date field',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_date_field',
                        'type' => 'date',
                        'label' => 'LBL_DATE_FIELD',
                        'default_value' => '',
                    ],
                ],
            ],
            // Datetime field
            [
                'args' => [
                    'module' => 'Opportunities',
                    'localizations' => [
                        'en_us' => [
                            'LBL_DATETIME_FIELD' => 'New datetime field',
                        ],
                    ],
                    'data' => [
                        'name' => 'new_datetime_field',
                        'type' => 'datetime',
                        'label' => 'LBL_DATETIME_FIELD',
                        'default_value' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Checks the custom field is deleted from the specified module
     * @covers ::deleteCustomField
     */
    public function testDeleteCustomField()
    {
        $args = [
            'module' => 'Opportunities',
            'localizations' => [
                'en_us' => [
                    'LBL_ANOTHER_DECIMAL_FIELD' => 'Another decimal field',
                ],
            ],
            'data' => [
                'name' => 'another_decimal_field',
                'type' => 'decimal',
                'label' => 'LBL_ANOTHER_DECIMAL_FIELD',
                'len' => '18',
                'precision' => '8',
                'appendToViews' => [
                    'listview' => true,
                    'recordview' => true,
                    'searchview' => true,
                ],
            ],
        ];
        $result = $this->fieldApi->createCustomField($this->serviceMock, $args);
        $bean = BeanFactory::newBean('Opportunities');
        if (!empty($bean->field_defs)) {
            $this->assertArrayHasKey('another_decimal_field_c', $bean->field_defs);
            $this->assertTrue($this->isInListView('Opportunities', 'another_decimal_field_c'));
            $this->assertTrue($this->isInRecordView('Opportunities', 'another_decimal_field_c'));
            $this->assertTrue($this->isInSearchView('Opportunities', 'another_decimal_field_c'));
        }

        $args = [
            'module' => 'Opportunities',
            'field' => 'another_decimal_field_c',
        ];
        $result = $this->fieldApi->deleteCustomField($this->serviceMock, $args);
        $bean = BeanFactory::newBean('Opportunities');
        if (!empty($bean->field_defs)) {
            $this->assertArrayNotHasKey('another_decimal_field_c', $bean->field_defs);
            $this->assertFalse($this->isInListView('Opportunities', 'another_decimal_field_c'));
            $this->assertFalse($this->isInRecordView('Opportunities', 'another_decimal_field_c'));
            $this->assertFalse($this->isInSearchView('Opportunities', 'another_decimal_field_c'));
        }
    }

    /**
     * Checks whether the custom field is in the list view layout
     * @param string $module module name
     * @param string $fieldName custom field name
     *
     * @return boolean
     */
    protected function isInListView(string $module, string $fieldName)
    {
        $viewdefs = [];
        $fieldFound = false;
        $file = "custom/modules/{$module}/clients/base/views/list/list.php";
        if (file_exists($file)) {
            include $file;
            if (!empty($viewdefs[$module]['base']['view']['list']['panels'][0]['fields'])) {
                $fields = $viewdefs[$module]['base']['view']['list']['panels'][0]['fields'];
                foreach ($fields as $idx => $field) {
                    if ($field['name'] === $fieldName) {
                        $fieldFound = true;
                        break;
                    }
                }
            }
        }
        return $fieldFound;
    }

    /**
     * Checks whether the custom field is in the record view layout
     * @param string $module module name
     * @param string $fieldName custom field name
     *
     * @return boolean
     */
    protected function isInRecordView(string $module, string $fieldName)
    {
        $viewdefs = [];
        $fieldFound = false;
        $file = "custom/modules/{$module}/clients/base/views/record/record.php";
        if (file_exists($file)) {
            include $file;
            if (!empty($viewdefs[$module]['base']['view']['record']['panels'][1]['fields'])) {
                $fields = $viewdefs[$module]['base']['view']['record']['panels'][1]['fields'];
                foreach ($fields as $idx => $field) {
                    if (isset($field['name']) && $fieldName === $field['name']) {
                        $fieldFound = true;
                        break;
                    }
                }
            }
        }
        return $fieldFound;
    }

    /**
     * Checks whether the custom field is in the search view layout
     * @param string $module module name
     * @param string $fieldName custom field name
     *
     * @return boolean
     */
    protected function isInSearchView(string $module, string $fieldName)
    {
        $viewdefs = [];
        $fieldFound = false;
        $file = "custom/modules/{$module}/clients/base/filters/default/default.php";
        if (file_exists($file)) {
            include $file;
            if (!empty($viewdefs[$module]['base']['filter']['default']['fields']) &&
                isset($viewdefs[$module]['base']['filter']['default']['fields'][$fieldName])) {
                $fieldFound = true;
            }
        }
        return $fieldFound;
    }
}
