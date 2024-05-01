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
 * VarDefHandler.php language tests
 */
class VarDefHandlerTest extends TestCase
{
    /**
     * Test tries to assert related field in array which was filtered by template_filter
     */
    public function testRelatedField()
    {
        $module = new SugarBean();
        $varDefHandler = new VarDefHandler($module, 'template_filter');

        $module->module_dir = 'Account';
        $module->field_defs = [
            'bug_field_c' => [
                'name' => 'bug_field_c',
                'source' => 'non-db',
                'type' => 'relate',
            ],
        ];

        $this->assertArrayHasKey('bug_field_c', $varDefHandler->get_vardef_array(true), 'Related field is not exist!');
    }
}
