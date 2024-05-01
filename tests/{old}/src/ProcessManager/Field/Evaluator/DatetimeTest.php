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

class DatetimeTest extends TestCase
{
    /**
     * EvaluatorInterface object
     * @var EvaluatorInterface
     */
    protected $eval;

    protected function setUp(): void
    {
        \SugarTestHelper::setUp('current_user');
        $this->eval = new Evaluator\Datetime();
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
                'type' => 'date',
            ],
            'test2' => [
                'type' => 'time',
            ],
            'test3' => [
                'type' => 'datetimecombo',
            ],
            'test4' => [
                'type' => 'datetime',
            ],
            'test5' => [
                'custom_type' => 'datetime',
            ],
        ];
        $bean->field_defs = array_merge($bean->field_defs, $defs);

        // Create a mock set of values on the bean for the mock fields
        // Considerations...
        //   2016-03-24T14:00:00-07:00 FROM CLIENT
        //   2016-03-24 14:27 ON BEAN

        // DB Date
        $bean->test1 = '2016-04-02';

        // DB Time, adjusted for offset
        $bean->test2 = '19:27:00';

        // DB Date time, adjusted for offset
        $bean->test3 = '2016-04-02 19:27:00';
        $bean->test4 = '2016-04-02 19:27:00';
        $bean->test5 = '2016-04-02 19:27:00';
        $bean->test6 = '2016-04-02 19:27:00';

        return $bean;
    }

    public function hasChangedProvider()
    {
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
                'data' => ['test7' => '2016-04-02 12:27'],
                'expect' => false,
            ],
            // Tests no change of data
            [
                'bean' => $bean,
                'name' => 'test1',
                'data' => ['test1' => '2016-04-02'],
                'expect' => false,
            ],
            [
                'bean' => $bean,
                'name' => 'test2',
                'data' => ['test2' => '12:27:00-0700'],
                'expect' => false,
            ],
            [
                'bean' => $bean,
                'name' => 'test3',
                'data' => ['test3' => '2016-04-02T12:27:00-0700'],
                'expect' => false,
            ],
            // Tests value change
            [
                'bean' => $bean,
                'name' => 'test4',
                'data' => ['test4' => '2016-04-03T12:27:00-0700'],
                'expect' => true,
            ],
            [
                'bean' => $bean,
                'name' => 'test5',
                'data' => ['test5' => '2016-04-02T15:27:00-0700'],
                'expect' => true,
            ],
        ];
    }
}
