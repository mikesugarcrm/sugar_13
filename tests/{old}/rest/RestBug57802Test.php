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
 * Bug 57802 - REST API Metadata: vardef len property must be number, not string
 */
class RestBug57802Test extends RestTestBase
{
    /**
     * @group rest
     * @group Bug57802
     */
    public function testMetadataModuleVardefLenFieldsAreNumericType()
    {
        $reply = $this->restCall('metadata?module_filter=Accounts&type_filter=modules');
        $this->assertTrue(isset($reply['reply']['modules']['Accounts']['fields']), 'Fields were not returned in the metadata response');

        // Handle assertions for all defs
        foreach ($reply['reply']['modules']['Accounts']['fields'] as $field => $def) {
            if (isset($def['len'])) {
                $this->assertIsInt($def['len'], "$field len property should of type int");
            }

            if (isset($def['size'])) {
                $this->assertIsInt($def['size'], "$field size property should of type int");
            }
        }
    }

    /**
     * @group 57802
     */
    public function testMetaDataManagerReturnsProperLenType()
    {
        $fielddef = [
            'test_field_c' => [
                'source' => 'custom_fields',
                'name' => 'test_field_c',
                'vname' => 'LBL_AAA_TEST',
                'type' => 'varchar',
                'len' => '30', // Force string to test as int
                'size' => '20', // Same here
                'id' => 'Accountstest_field_c',
                'custom_module' => 'Accounts',
            ],
            'test_field1_c' => [
                'source' => 'custom_fields',
                'name' => 'test_field1_c',
                'vname' => 'LBL_AAA1_TEST',
                'type' => 'varchar',
                'len' => '100', // Force string to test as int
                'size' => '90', // Same here
                'id' => 'Accountstest_field1_c',
                'custom_module' => 'Accounts',
            ],
        ];

        $mm = new RestBug57802MetaDataHacks();
        $cleaned = $mm->getNormalizedFields($fielddef);

        foreach ($cleaned as $field => $def) {
            if (isset($def['len'])) {
                $this->assertIsInt($def['len'], "$field len property should of type int");
            }

            if (isset($def['size'])) {
                $this->assertIsInt($def['size'], "$field size property should of type int");
            }
        }
    }
}

/**
 * Accessor class to the protected metadata manager method needed for testing
 */
class RestBug57802MetaDataHacks extends MetaDataHacks
{
    public function getNormalizedFields($fielddef)
    {
        return $this->normalizeFielddefs($fielddef);
    }
}
