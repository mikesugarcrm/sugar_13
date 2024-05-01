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
class Bug56694Test extends TestCase
{
    /**
     * @var TemplateInt
     */
    protected $templateInt = null;

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
    }

    /**
     * Test asserts that min & max properties more important than ext1 & ext2
     *
     * @group 53554
     */
    public function testMinMaxWinExt()
    {
        $this->templateInt->ext1 = 3;
        $this->templateInt->ext2 = 4;
        $vardef = $this->templateInt->get_field_def();

        $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
        $this->assertEquals(3, $vardef['validation']['min'], 'Ext won');
        $this->assertEquals(4, $vardef['validation']['max'], 'Ext won');

        $this->templateInt->min = 1;
        $this->templateInt->max = 2;
        $vardef = $this->templateInt->get_field_def();

        $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
        $this->assertEquals(1, $vardef['validation']['min'], 'Min won');
        $this->assertEquals(2, $vardef['validation']['max'], 'Max won');
    }

    /**
     * Method returns data for tests
     * min value
     * max value
     * should validator be present
     * should min value be present
     * should max value be present
     *
     * @return array
     */
    public function getMaxMin()
    {
        return [
            [null, null, false, false, false],
            [0, null, true, true, false],
            [0, 0, true, true, true],
            ['0', '0', true, true, true],
            [null, 0, true, false, true],
            [1, 5, true, true, true, true],
            ['1', '5', true, true, true, true],
            ['a', 'b', false, false, false],
            ['a', 5, true, false, true],
            [5, 'a', true, true, false],
        ];
    }

    /**
     * Test checks min & max range for validator for int field
     *
     * @param mixed $min value
     * @param mixed $max max
     * @param bool $isValidation is validation required
     * @param bool $isMin is min value present
     * @param bool $isMax is max value present
     *
     * @dataProvider getMaxMin
     * @group 56694
     */
    public function testGetFieldDefByMinMax($min, $max, $isValidation, $isMin, $isMax)
    {
        $this->templateInt->min = $min;
        $this->templateInt->max = $max;
        $vardef = $this->templateInt->get_field_def();

        if ($isValidation == false) {
            $this->assertArrayNotHasKey('validation', $vardef, 'Validation is required');
        } else {
            $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
            if ($isMin == true) {
                $this->assertEquals($min, $vardef['validation']['min'], 'Min value is incorrect');
            } else {
                $this->assertEquals(false, $vardef['validation']['min'], 'Min value is present');
            }
            if ($isMax == true) {
                $this->assertEquals($max, $vardef['validation']['max'], 'Max value is incorrect');
            } else {
                $this->assertEquals(false, $vardef['validation']['max'], 'Max value is present');
            }
        }
    }

    /**
     * Test checks min & max range for validator for int field
     *
     * @param mixed $min value
     * @param mixed $max max
     * @param bool $isValidation is validation required
     * @param bool $isMin is min value present
     * @param bool $isMax is max value present
     *
     * @dataProvider getMaxMin
     * @group 56694
     */
    public function testGetFieldDefByExt($min, $max, $isValidation, $isMin, $isMax)
    {
        $this->templateInt->ext1 = $min;
        $this->templateInt->ext2 = $max;
        $vardef = $this->templateInt->get_field_def();

        if ($isValidation == false) {
            $this->assertArrayNotHasKey('validation', $vardef, 'Validation is required');
        } else {
            $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
            if ($isMin == true) {
                $this->assertEquals($min, $vardef['validation']['min'], 'Min value is incorrect');
            } else {
                $this->assertEquals(false, $vardef['validation']['min'], 'Min value is present');
            }
            if ($isMax == true) {
                $this->assertEquals($max, $vardef['validation']['max'], 'Max value is incorrect');
            } else {
                $this->assertEquals(false, $vardef['validation']['max'], 'Max value is present');
            }
        }
    }
}
