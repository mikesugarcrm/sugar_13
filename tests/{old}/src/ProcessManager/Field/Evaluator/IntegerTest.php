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

class IntegerTest extends TestCase
{
    /**
     * EvaluatorInterface object
     * @var EvaluatorInterface
     */
    protected $eval;

    protected function setUp(): void
    {
        $this->eval = new Evaluator\Integer();
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

    public function hasChangedProvider()
    {
        // Simple bean setup to cover all test cases
        $bean = \BeanFactory::newBean('Bugs');
        $bean->test1 = '1000';
        $bean->test3 = '12025';
        $bean->test4 = '955200595';
        $bean->test5 = '8361';

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
                'data' => ['test2' => 335],
                'expect' => false,
            ],
            // Tests no change
            [
                'bean' => $bean,
                'name' => 'test3',
                'data' => ['test3' => 12025],
                'expect' => false,
            ],
            // Tests value change
            [
                'bean' => $bean,
                'name' => 'test4',
                'data' => ['test4' => 955200495],
                'expect' => true,
            ],
            [
                'bean' => $bean,
                'name' => 'test5',
                'data' => ['test5' => 0],
                'expect' => true,
            ],
        ];
    }
}
