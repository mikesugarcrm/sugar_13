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

require_once 'include/export_utils.php';

/**
 * Test export_utils.php
 */
class ExportUtilsTest extends TestCase
{
    private $focus;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        global $app_strings;
        global $app_list_strings;

        $app_strings['LBL_MY_TEST_MULTIENUM'] = 'My Test Multienum';
        $app_strings['LBL_MY_TEST_ENUM'] = 'My Test Enum';
        $app_list_strings['test_enum_dom'] = [
            'v1' => 'Value 1',
            'v2' => 'Value 2',
            'v3' => 'Value 3',
        ];

        $this->focus = BeanFactory::newBean('Accounts');
        $this->focus->field_defs['my_test_multienum'] = [
            'name' => 'my_test_multienum',
            'type' => 'multienum',
            'options' => 'test_enum_dom',
            'label' => 'LBL_MY_TEST_MULTIENUM',
        ];
        $this->focus->field_defs['my_test_enum'] = [
            'name' => 'my_test_enum',
            'type' => 'enum',
            'options' => 'test_enum_dom',
            'label' => 'LBL_MY_TEST_ENUM',
        ];
    }

    /**
     * Ensure that get_field_order_mapping returns an array with lowercase keys
     * even if passed column names that are capitalized.
     */
    public function testGetFieldOrderMappingHasLowercaseKeys()
    {
        $fields = [
            'Uppercase Field' => 'Uppercase Field',
            'BLOCK_CAPS_FIELD' => 'Block Capital Field',
            'all lowercase field' => 'Lowercase Field',
        ];
        $result = get_field_order_mapping('contacts', $fields);
        $expectedResult = array_change_key_case($fields, CASE_LOWER);
        $this->assertEquals($expectedResult, $result, 'get_field_order_mapping did not convert keys to lowercase!');
    }

    /**
     * Test that extra header labels get added for enum and multienum display label columns
     */
    public function testGetExportHeaderLabels()
    {
        $fields_array = [
            'id' => 'id',
            'my_test_enum' => 'my_test_enum',
            'my_test_multienum' => 'my_test_multienum',
        ];

        $header_labels = getExportHeaderLabels($this->focus, $fields_array, true, []);

        $this->assertEquals(5, sizeof($header_labels));
        $this->assertEquals('My Test Enum', $header_labels['my_test_enum']);
        $this->assertEquals('My Test Enum Display Label', $header_labels['my_test_enum_export_label']);
        $this->assertEquals('My Test Multienum', $header_labels['my_test_multienum']);
        $this->assertEquals(
            'My Test Multienum Display Label',
            $header_labels['my_test_multienum_export_label'],
        );
    }

    /**
     * Test that enum and multienums get translated properly into their user-facing values
     * @param $fieldName
     * @param $dbValue
     * @param $expectedValue
     * @dataProvider dataProviderTestGetTranslatedFieldValue
     */
    public function testGetTranslatedFieldValue($fieldName, $dbValue, $expectedValue)
    {
        $translatedValue = getTranslatedFieldValue($this->focus, $fieldName, $dbValue, [
            $fieldName => $dbValue,
        ]);

        $this->assertEquals($expectedValue, $translatedValue);
    }

    public function dataProviderTestGetTranslatedFieldValue()
    {
        return [
            ['my_test_multienum', '^v2^', 'Value 2'],
            ['my_test_multienum', '^v1^,^v3^', 'Value 1,Value 3'],
            ['my_test_enum', 'v2', 'Value 2'],
            ['my_test_enum', '', ''],
            ['id', 'id-value', false],
        ];
    }
}
