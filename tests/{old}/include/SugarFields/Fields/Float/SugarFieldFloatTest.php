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

class SugarFieldFloatTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    protected function setUp(): void
    {
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();
        //force static var reset
        get_number_seperators(true);
    }

    protected function tearDown(): void
    {
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();
        //force static var reset
        get_number_seperators(true);
    }

    /**
     * @dataProvider unformatFieldProvider
     * @param $value
     * @param $expectedValue
     */
    public function testUnformatField($value, $expectedValue)
    {
        $field = SugarFieldHandler::getSugarField('float');
        $this->assertEquals($expectedValue, $field->unformatField($value, null));
    }

    /**
     * testUnformatField data provider
     *
     * @group currency
     * @access public
     */
    public static function unformatFieldProvider()
    {
        return [
            ['1000', '1000'],
            ['1.000', '1.000'],
            ['1,000', '1000'],
            ['1,000.00', '1000.00'],
        ];
    }

    /**
     * @dataProvider unformatFieldProviderCommaDotFlip
     * @param $value
     * @param $expectedValue
     */
    public function testUnformatFieldCommaDotFlip($value, $expectedValue)
    {
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', ',');
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();

        //force static var reset
        get_number_seperators(true);

        $field = SugarFieldHandler::getSugarField('float');
        $this->assertEquals($expectedValue, $field->unformatField($value, null));
    }

    /**
     * testUnformatFieldCommaDotFlip data provider
     *
     * @group currency
     * @access public
     */
    public static function unformatFieldProviderCommaDotFlip()
    {
        return [
            ['1,000', '1'],
            ['1000,00', '1000'],
            ['1.000,65', '1000.65'],
            ['1.065', '1065'],
        ];
    }

    /**
     * @dataProvider apiUnformatFieldProvider
     * @param $value
     * @param $expectedValue
     */
    public function testApiUnformatField($value, $expectedValue)
    {
        $field = SugarFieldHandler::getSugarField('float');
        $this->assertEquals($expectedValue, $field->apiUnformatField($value));
    }

    /**
     * testApiUnformatField data provider
     *
     * @group currency
     * @access public
     */
    public static function apiUnformatFieldProvider()
    {
        return [
            ['1000', '1000'],
            ['1.000', '1.000'],
            ['1,000', '1,000'],
            ['1,000.00', '1,000.00'],
        ];
    }

    public function dataProviderFixForForFloats()
    {
        return [
            ['$equals', 10.69, '='],
            ['$not_equals', 10.69, '!='],
            ['$between', [10.69, 100.69], 'BETWEEN'],
            ['$lt', 10.69, '<'],
            ['$lte', 10.69, '<='],
            ['$gt', 10.69, '>'],
            ['$gte', 10.69, '>='],
        ];
    }

    /**
     * @dataProvider dataProviderFixForForFloats
     * @param String $op The Filer Operation
     * @param Number $value The Value we are looking for
     * @param String $query_op The value of $op in the query
     */
    public function testFixForFilterForFloats($op, $value, $query_op)
    {
        $bean = BeanFactory::newBean('RevenueLineItems');

        /* @var $where SugarQuery_Builder_Where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->getMock();

        /* @var $bean RevenueLineItem */
        $q = new SugarQuery();
        $q->from($bean);

        $field = new SugarFieldFloat('float');

        $ret = $field->fixForFilter($value, 'unit_test', $bean, $q, $where, $op);

        $this->assertFalse($ret);

        if (is_array($value)) {
            $expected = '(ROUND(unit_test, 2) ' . $query_op . ' ' . $value[0] . ' AND ' . $value[1] . ')';
        } else {
            $expected = '(ROUND(unit_test, 2) ' . $query_op . ' ' . $value . ')';
        }

        $this->assertStringContainsString($expected, $q->compile()->getSQL());
    }
}
