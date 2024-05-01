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

namespace Sugarcrm\SugarcrmTests\ProcessManager\Field\Evaluator;

use Sugarcrm\Sugarcrm\ProcessManager\Field\Evaluator;
use PHPUnit\Framework\TestCase;

class RelateTest extends TestCase
{
    /**
     * EvaluatorInterface object
     * @var EvaluatorInterface
     */
    protected $eval;

    protected function setUp(): void
    {
        $this->eval = new Evaluator\Relate();
    }

    /**
     * Tests whether a value on a bean has changed
     * @dataProvider hasChangedProvider
     * @param SugarBean $bean SugarBean to test with
     * @param string $name Name of the field to test
     * @param array $data Data array to test
     * @param boolean $expect Expectation
     */
    public function testHasChanged($bean, $name, $data, $expect)
    {
        $this->eval->init($bean, $name, $data);
        $actual = $this->eval->hasChanged();
        $this->assertEquals($expect, $actual);
    }

    protected function getPreparedBean()
    {
        // Simple bean setup to cover all test cases
        $bean = \BeanFactory::newBean('Bugs');

        // Create a mock set of vardefs for this bean
        $defs = [
            'test1' => [
                'id_name' => 'test1_foo',
            ],
            'test2' => [
                'id_name' => 'test2_foo',
            ],
            'test3' => [
                'id_name' => 'test3_foo',
            ],
        ];
        $bean->field_defs = array_merge($bean->field_defs, $defs);

        $bean->test1_foo = 'test_value_1';
        $bean->test2_foo = 'test_value_2';
        $bean->test3_foo = 'test_value_3';

        return $bean;
    }

    public function hasChangedProvider()
    {
        // Simple bean setup to cover all test cases
        $bean = $this->getPreparedBean();

        return [
            // Tests no data value given
            [
                'bean' => $bean,
                'name' => 'test1',
                'data' => [],
                'expect' => false,
            ],
            // Tests no bean property set
            [
                'bean' => $bean,
                'name' => 'test7',
                'data' => ['test7_foo' => 'test_value_7'],
                'expect' => false,
            ],
            // Tests no change of data
            [
                'bean' => $bean,
                'name' => 'test2',
                'data' => ['test2_foo' => 'test_value_2'],
                'expect' => false,
            ],
            // Tests value change
            [
                'bean' => $bean,
                'name' => 'test3',
                'data' => ['test3_foo' => 'test_value_3_foo'],
                'expect' => true,
            ],
        ];
    }
}
