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
 * Bug #56694
 * Integer fields preset to max of 100 after upgrade
 *
 * @author mgusev@sugarcrm.com
 * @ticked 56694
 */
class Bug56694v2Test extends TestCase
{
    /**
     * @var TemplateInt
     */
    protected $templateInt = null;

    /**
     * @var javascript56694
     */
    protected $javascript = null;

    /**
     * @var SugarBean
     */
    protected $bean = null;

    protected function setUp(): void
    {
        $this->templateInt = new TemplateInt();
        $this->templateInt->importable = 'true';
        $this->templateInt->label = 'LBL_TEST';
        $this->templateInt->name = 'bug_c';
        $this->templateInt->no_default = 1;
        $this->templateInt->reportable = '1';
        $this->templateInt->supports_unified_search = true;
        $this->templateInt->vname = $this->templateInt->label;

        $this->bean = new SugarBean();

        $this->javascript = new javascript56694();
        $this->javascript->setSugarBean($this->bean);
    }

    /**
     * Test asserts that after addField call validator is not added
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithoutValidator()
    {
        $this->bean->field_defs[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertEmpty($this->javascript->getData(), 'Validator is added');
    }

    /**
     * Test asserts that after addField call validator is added with empty values
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithoutRealValidator()
    {
        $this->templateInt->min = 5;
        $this->bean->field_defs[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->bean->field_defs[$this->templateInt->name]['validation']['min'] = null;
        $this->bean->field_defs[$this->templateInt->name]['validation']['max'] = null;
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame([false, false], $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added only for min value, max value should be false
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMinOnly()
    {
        $this->templateInt->min = 5;
        $this->bean->field_defs[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame([$this->templateInt->min, false], $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added only to max value, min value should be false
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMaxOnly()
    {
        $this->templateInt->max = 5;
        $this->bean->field_defs[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame([false, $this->templateInt->max], $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added with both values
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMaxMin()
    {
        $this->templateInt->min = 5;
        $this->templateInt->max = 6;
        $this->bean->field_defs[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');
        $actual = $this->javascript->getData();
        $this->assertEquals([$this->templateInt->min, $this->templateInt->max], $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator added to both values and has min value, because of min value more than max
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithInvertedMaxMin()
    {
        $this->templateInt->min = 6;
        $this->templateInt->max = 5;
        $this->bean->field_defs[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');
        $actual = $this->javascript->getData();
        $this->assertSame([$this->templateInt->min, $this->templateInt->min], $actual, 'Min value is incorrect');
    }
}

/**
 * Mock of javascript class
 */
class javascript56694 extends javascript
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function addFieldRange(string $field, string $type, string $displayName, $required, string $prefix, $min, $max): void
    {
        $this->data = [$min, $max];
    }
}
