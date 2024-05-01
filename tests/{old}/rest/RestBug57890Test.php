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

/**
 * Bug 57890 - Required values should be boolean
 */
class RestBug57890Test extends RestTestBase
{
    /**
     * @group rest
     * @group Bug57890
     */
    public function testMetadataModuleVardefRequiredFieldsAreBooleanType()
    {
        $reply = $this->restCall('metadata?module_filter=Leads&type_filter=modules');
        $this->assertTrue(isset($reply['reply']['modules']['Leads']['fields']), 'Fields were not returned in the metadata response');

        // Handle assertions for all defs
        foreach ($reply['reply']['modules']['Leads']['fields'] as $field => $def) {
            if (isset($def['required'])) {
                $this->assertIsBool($def['required'], "$field required property should of type boolean");
            }
        }
    }

    /**
     * @group 57890
     */
    public function testMetaDataManagerReturnsProperRequiredType()
    {
        $fielddef = [
            'test_field_c' => [
                'source' => 'custom_fields',
                'name' => 'test_field_c',
                'vname' => 'LBL_AAA_TEST',
                'type' => 'varchar',
                'len' => '30',
                'required' => 'true',
                'size' => '20',
                'id' => 'Leadstest_field_c',
                'custom_module' => 'Leads',
            ],
            'test_field1_c' => [
                'source' => 'custom_fields',
                'name' => 'test_field1_c',
                'vname' => 'LBL_AAA1_TEST',
                'type' => 'varchar',
                'len' => '100',
                'required' => 'off',
                'size' => '90',
                'id' => 'Leadstest_field1_c',
                'custom_module' => 'Leads',
            ],
            'test_field2_c' => [
                'source' => 'custom_fields',
                'name' => 'test_field2_c',
                'vname' => 'LBL_AAA1_TEST',
                'type' => 'varchar',
                'len' => '100',
                'required' => true,
                'size' => '90',
                'id' => 'Leadstest_field2_c',
                'custom_module' => 'Leads',
            ],
        ];

        $mm = new RestBug57890MetaDataHacks($this->user);
        $cleaned = $mm->getNormalizedFields($fielddef);

        foreach ($cleaned as $field => $def) {
            if (isset($def['required'])) {
                $this->assertIsBool($def['required'], "$field required property should of type boolean");
            }
        }
    }
}

/**
 * Accessor class to the protected metadata manager method needed for testing
 */
class RestBug57890MetaDataHacks extends MetaDataHacks
{
    public function getNormalizedFields($fielddef)
    {
        return $this->normalizeFielddefs($fielddef);
    }
}
