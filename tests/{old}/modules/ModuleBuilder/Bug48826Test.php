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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;

/**
 * Bug #48826
 * Module Builder - Dependent multiselect fields are always displayed
 * Bug #49774
 * [IBM RTC 3020] XSS - Administration, Studio, Edit Fields, formula
 *
 * @ticket
 */

require_once 'modules/DynamicFields/FieldCases.php';

class Bug48826Test extends TestCase
{
    public function provider()
    {
        $types = [
            'char', 'varchar', 'varchar2', 'text', 'textarea', 'double', 'float', 'decimal', 'int', 'date', 'bool', 'relate',
            'enum', 'multienum', 'radioenum', 'email', 'url', 'iframe', 'html', 'phone', 'currency', 'parent', 'parent_type',
            'currency_id', 'address', 'encrypt', 'id', 'datetimecombo', 'datetime', 'image', '_other_',
        ];
        $provider_array = [];
        foreach ($types as $type) {
            // Bug #48826
            $provider_array[] = [$type, ['name' => 'equal($dd1_c,"Analyst")'], 'equal($dd1_c,"Analyst")'];
            $provider_array[] = [$type, ['dependency' => 'equal($dd1_c,"Analyst")'], 'equal($dd1_c,"Analyst")'];
            $provider_array[] = [$type, ['dependency' => 'equal($dd1_c,"Analyst")'], 'equal($dd1_c,"Analyst")'];
            $provider_array[] = [$type, ['formula' => 'equal($dd1_c,"Analyst")'], 'equal($dd1_c,"Analyst")'];
            $provider_array[] = [$type, ['formula' => 'equal($dd1_c,"Analyst")'], 'equal($dd1_c,"Analyst")'];
            // Bug #49775
            $provider_array[] = [$type, ['formula' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'], 'concat("alert(1623651453416)", "alert(1623651453416)")'];
            $provider_array[] = [$type, ['formula' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'], 'concat("alert(1623651453416)", "alert(1623651453416)")'];
            $provider_array[] = [$type, ['formula' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'], 'concat("alert(1623651453416)", "alert(1623651453416)")'];
            $provider_array[] = [$type, ['dependency' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'], 'concat("alert(1623651453416)", "alert(1623651453416)")'];
            $provider_array[] = [$type, ['dependency' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'], 'concat("alert(1623651453416)", "alert(1623651453416)")'];
            $provider_array[] = [$type, ['dependency' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'], 'concat("alert(1623651453416)", "alert(1623651453416)")'];
        }

        return $provider_array;
    }

    /**
     * @group 48826, 49774
     * @dataProvider provider
     */
    public function testPopulateFromPost($type, $request_data, $expected)
    {
        $this->assertCount(1, $request_data);
        $tested_key = key($request_data);

        $request = InputValidation::create($request_data, []);
        $field = get_widget($type);
        $field->populateFromPost($request);

        $this->assertEquals($expected, $field->$tested_key);
    }
}
