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

class BaseTest extends TestCase
{
    /**
     * EvaluatorInterface object
     * @var EvaluatorInterface
     */
    protected $eval;

    protected function setUp(): void
    {
        $this->eval = new Evaluator\Base();
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

    /**
     * Tests whether a value on a bean is empty
     * @dataProvider isEmptyProvider
     * @param SugarBean $bean SugarBean to test with
     * @param string $name Name of the field to test
     * @param boolean $expect Expectation
     */
    public function testIsEmpty($bean, $name, $expect)
    {
        $this->eval->init($bean, $name, []);
        $actual = $this->eval->isEmpty();
        $this->assertEquals($expect, $actual);
    }

    public function hasChangedProvider()
    {
        // Simple bean setup to cover all test cases
        $bean = \BeanFactory::newBean('Bugs');
        $bean->test1 = 'foo';
        $bean->test3 = 'bar';
        $bean->test4 = 'bar';
        $bean->test5 = 'zim';

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
                'name' => 'test2',
                'data' => ['test2' => 'bar'],
                'expect' => false,
            ],
            // Tests no change of data
            [
                'bean' => $bean,
                'name' => 'test3',
                'data' => ['test3' => 'bar'],
                'expect' => false,
            ],
            // Tests value change
            [
                'bean' => $bean,
                'name' => 'test4',
                'data' => ['test4' => 'baz'],
                'expect' => true,
            ],
            // Test case change
            [
                'bean' => $bean,
                'name' => 'test5',
                'data' => ['test5' => 'Zim'],
                'expect' => true,
            ],
        ];
    }

    public function isEmptyProvider()
    {
        // Simple bean setup to cover all test cases
        $bean = \BeanFactory::newBean('Bugs');
        $bean->test2 = null;
        $bean->test3 = '';
        $bean->test4 = ' ';
        $bean->test5 = "\n";
        $bean->test6 = "\n\t \n ";

        return [
            // Tests property not on the bean
            [
                'bean' => $bean,
                'name' => 'test1',
                'expect' => false,
            ],
            // Tests property is set to null
            [
                'bean' => $bean,
                'name' => 'test2',
                'expect' => true,
            ],
            // Tests property is set to an empty string
            [
                'bean' => $bean,
                'name' => 'test3',
                'expect' => true,
            ],
            // Tests property is set to a single space
            [
                'bean' => $bean,
                'name' => 'test4',
                'expect' => true,
            ],
            // Tests property is set to a new line
            [
                'bean' => $bean,
                'name' => 'test5',
                'expect' => true,
            ],
            // Tests property is set to a collection of whitespace chars
            [
                'bean' => $bean,
                'name' => 'test6',
                'expect' => true,
            ],
        ];
    }
}
