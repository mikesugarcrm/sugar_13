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

require_once 'modules/DynamicFields/FieldCases.php';


/**
 *  Bug #51427: Setting a "name" field to calculated removes it from Global Search
 *
 *  StandardField class didn't have functionality for checking `unified_search`
 *  option. `unified_search` was always `false` for non-custom fields.
 */
class Bug51427Test extends TestCase
{
    private $old_dictionary;
    private $old_bean_list;
    private $bean_name;
    private $test_field;
    private $test_standart_field;

    protected function setUp(): void
    {
        global $dictionary, $bean_list;

        $this->old_dictionary = $dictionary;
        $this->old_bean_list = $bean_list;
        $this->test_standart_field = $this->getMockBuilder('StandardField')
            ->setMethods(['loadCustomDef', 'writeVardefExtension'])
            ->getMock();
        $this->test_standart_field->module = 'Accounts';
        loadBean($this->test_standart_field->module);
        $this->test_field = get_widget('varchar');
        $this->test_field->name = 'name';
        $this->bean_name = get_valid_bean_name($this->test_standart_field->module);
    }

    protected function tearDown(): void
    {
        global $dictionary, $bean_list;

        $dictionary = $this->old_dictionary;
        $bean_list = $this->old_bean_list;
        VardefManager::clearVardef('Accounts', 'Account');
        VardefManager::refreshVardefs('Accounts', 'Account');
    }

    /**
     * Test different combinations of the options
     *
     * @dataProvider providerUnifiedSearchOptions
     */
    public function testIfUnifiedSearchEnabledByDefault(array $opt_common, array $opt_field, $assert)
    {
        $this->test_field->unified_search = false;
        $this->setOptions($opt_common, $opt_field);
        $this->test_standart_field->addFieldObject($this->test_field);
        $this->assertEquals(
            $assert,
            (bool)$this->test_field->unified_search,
            "\r\n"
            . 'Assertion error: field->unified_search should be `' . ($assert ? 'true' : 'false') . '` with the following vardef options:'

            . "\r\nvardef[<module>][unified_search_default_enabled] "
            . ($opt_common['unified_search_default_enabled'] === -1 ? 'is undefined' : ($opt_common['unified_search_default_enabled'] ? '= true' : '= false'))

            . "\r\nvardef[<module>][unified_search] "
            . ($opt_common['unified_search'] === -1 ? 'is undefined' : ($opt_common['unified_search'] ? '= true' : '= false'))

            . "\r\nvardef[<module>][fields][<field_name>][unified_search] "
            . ($opt_field['unified_search'] === -1 ? 'is undefined' : ($opt_field['unified_search'] ? '= true' : '= false'))
            . "\r\n"
        );
    }

    public function providerUnifiedSearchOptions()
    {
        return [
            [
                'options_common' => [
                    'unified_search_default_enabled' => 1,
                    'unified_search' => 1,
                ],
                'options_field' => [
                    'unified_search' => 1,
                ],
                'assert' => true,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => 0,
                    'unified_search' => 1,
                ],
                'options_field' => [
                    'unified_search' => 1,
                ],
                'assert' => false,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => 0,
                    'unified_search' => 0,
                ],
                'options_field' => [
                    'unified_search' => 1,
                ],
                'assert' => false,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => 0,
                    'unified_search' => 0,
                ],
                'options_field' => [
                    'unified_search' => 0,
                ],
                'assert' => false,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => 0,
                    'unified_search' => 1,
                ],
                'options_field' => [
                    'unified_search' => 0,
                ],
                'assert' => false,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => 0,
                    'unified_search' => 0,
                ],
                'options_field' => [
                    'unified_search' => 1,
                ],
                'assert' => false,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => 1,
                    'unified_search' => 1,
                ],
                'options_field' => [
                    'unified_search' => -1,
                ],
                'assert' => true,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => 1,
                    'unified_search' => -1,
                ],
                'options_field' => [
                    'unified_search' => 1,
                ],
                'assert' => false,
            ],
            [
                'options_common' => [
                    'unified_search_default_enabled' => -1,
                    'unified_search' => 1,
                ],
                'options_field' => [
                    'unified_search' => 1,
                ],
                'assert' => false,
            ],
        ];
    }

    private function setOptions(array $opt_common, array $opt_field)
    {
        global $dictionary;
        $dictionary = $this->old_dictionary;
        if (!empty($opt_common)) {
            foreach ($opt_common as $k => $v) {
                if ($v === -1) {
                    unset($dictionary[$this->bean_name][$k]);
                } else {
                    $dictionary[$this->bean_name][$k] = $v;
                }
            }
        }
        if (!empty($opt_field)) {
            foreach ($opt_field as $k => $v) {
                if ($v === -1) {
                    unset($dictionary[$this->bean_name]['fields'][$this->test_field->name][$k]);
                } else {
                    $dictionary[$this->bean_name]['fields'][$this->test_field->name][$k] = $v;
                }
            }
        }
    }
}
