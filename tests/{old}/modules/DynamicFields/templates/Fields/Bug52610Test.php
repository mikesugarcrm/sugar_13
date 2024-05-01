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
 * Bug #52610
 * [MSSQL]: Cannot add checkbox field in Studio
 *
 * @author mgusev@sugarcrm.com
 * @ticked 52610
 */
class Bug52610Test extends TestCase
{
    /**
     * @return array of default values for test
     */
    public function getDefaults()
    {
        return [
            [true],
            [''],
            ['string'],
            [123],
            [123.45],
        ];
    }

    /**
     * Test gets query for boolean field which should not contains default part
     * @dataProvider getDefaults
     * @group 52610
     * @return void
     */
    public function testDefaultInQuery($default)
    {
        $field = get_widget('bool');
        $field->name = 'bug52610_c';
        $field->type = 'bool';
        $field->default = $default;
        $field->default_value = '';
        $field->no_default = 1;
        $query = $field->get_db_add_alter_table('bug52610_cstm');
        $this->assertStringNotContainsString(' DEFAULT ', $query);
    }
}
