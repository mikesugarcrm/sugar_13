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
 * Dependent Fields do not display in the Subpanel of a Related Module unless the Field(s) they Depend on are also in
 * the Subpanel Display
 *
 * @ticket 59047
 */
class Bug59047Test extends TestCase
{
    public function testDependentFieldsAreExpanded()
    {
        $bean = new SugarBean();
        $bean->field_defs = [
            'field_1' => [
                'dependency' => 'equals($field_2,"test")',
            ],
            'field_2' => [],
            'field_3' => [
                'dependency' => 'equals($non_existing_field,"test")',
            ],
            'field_4' => [
                'dependency' => 'equals($field_5,"test")',
            ],
            'field_5' => [],
        ];

        $definition = [
            'list_fields' => [
                'field_1' => [],
                'field_3' => [],
                'field_5' => [],
            ],
        ];

        $subPanel = new Bug59047Test_SubPanel();
        $subPanel->template_instance = $bean;
        $subPanel->set_panel_definition($definition);

        $list_fields = $subPanel->panel_definition['list_fields'];

        // ensure that "field_1" is marked as non-sortable
        $this->assertFalse($list_fields['field_1']['sortable']);

        // ensure that "field_2" is added to the definition and marked as "query only"
        $this->assertArrayHasKey('field_2', $list_fields);
        $this->assertEquals('query_only', $list_fields['field_2']['usage']);

        // ensure that "non_existing_field" is not added to the definition
        $this->assertArrayNotHasKey('non_existing_field', $list_fields);

        // ensure that "field_5" is not marked as "query only" since it's explicitly defined
        $this->assertArrayHasKey('field_5', $list_fields);
        $this->assertArrayNotHasKey('usage', $list_fields['field_5']);
    }
}

class Bug59047Test_SubPanel extends aSubPanel
{
    public function __construct()
    {
    }

    public function set_panel_definition(array $definition)
    {
        parent::set_panel_definition($definition);
    }
}
