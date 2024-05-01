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
 * Bug 56505 - Incorrect format for property "default" in multiselect field's vardef
 */
class Bug56505Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that multiselect default values are returned clean in the fields list
     * from the metadata manager
     *
     * @group Bug56505
     */
    public function testMultiselectDefaultFieldValueIsClean()
    {
        $defs = [];
        $defs['fields']['aaa_test_c'] = [
            'type' => 'multienum',
            'name' => 'aaa_test_c',
            'options' => 'aaa_list',
            'default' => '^bobby^,^billy^',
        ];

        $mm = new MetaDataHacksBug56505($GLOBALS['current_user']);
        $newdefs = $mm->getNormalizedFielddefs($defs);

        $this->assertArrayHasKey('aaa_test_c', $newdefs, 'New defs did not return custom test field');
        $this->assertArrayHasKey('default', $newdefs['aaa_test_c'], 'Test field def default value is missing');
        $this->assertIsArray($newdefs['aaa_test_c']['default'], 'Expected the default value to be an array');
        $this->assertTrue(in_array('bobby', $newdefs['aaa_test_c']['default']), "Expected the string 'bobby' to be in the default value array");
        $this->assertTrue(in_array('billy', $newdefs['aaa_test_c']['default']), "Expected the string 'billy' to be in the default value array");
    }
}

/**
 * Accessor class to the metadatamanager to allow access to protected methods
 */
class MetaDataHacksBug56505 extends MetaDataHacks
{
    public function getNormalizedFielddefs($defs)
    {
        return $this->normalizeFielddefs($defs);
    }
}
