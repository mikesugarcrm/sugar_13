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

class TemplateEnumTest extends TestCase
{
    private $moduleName = 'Accounts';
    private $field;

    protected function setUp(): void
    {
        $this->field = get_widget('enum');
        $this->field->id = $this->moduleName . 'foofighter_c';
        $this->field->name = 'foofighter_c';
        $this->field->dependency = htmlentities('equal(strlen($name),5)', ENT_COMPAT);
        $this->field->ext4 = serialize(htmlentities('fred', ENT_COMPAT));
    }

    public function testPopulateDependencyFromDependencyField()
    {
        $fieldDef = $this->field->get_field_def();
        $this->assertEquals('equal(strlen($name),5)', $fieldDef['dependency'], 'The dependency was not populated correctly.');
    }

    public function testPopulateDependencyFromExt4()
    {
        unset($this->field->dependency);
        $fieldDef = $this->field->get_field_def();
        $this->assertEquals('fred', $fieldDef['dependency'], 'The dependency was not populated correctly.');
    }
}
