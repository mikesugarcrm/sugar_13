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

class SugarFieldIntTest extends TestCase
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
        $field = SugarFieldHandler::getSugarField('int');
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
            ['1.000', '1'],
            ['1,000', '1000'],
            ['1,000.00', '1000'],
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

        $field = SugarFieldHandler::getSugarField('int');
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
            ['1.000,65', '1000'],
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
        $field = SugarFieldHandler::getSugarField('int');
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

    /**
     * @dataProvider apiValidateProvider
     * @param $bean
     * @param $name
     * @param $value
     * @param $vardef
     * @param $valid
     */
    public function testApiValidate($bean, $name, $value, $vardef, $valid)
    {
        $field = SugarFieldHandler::getSugarField('int');
        $this->assertEquals($valid, $field->apiValidate($bean, [$name => $value], $name, $vardef));
    }

    /**
     * testApiValidate data provider
     * @access public
     */
    public function apiValidateProvider()
    {
        $bean = new SugarBean();
        $vardef = ['name' => 'test', 'type' => 'int'];
        $data = [
            'MySQL' => [
                [$bean, 'test', 0, $vardef, true],
                [$bean, 'test', -12345678901, $vardef, false],
                [$bean, 'test', 12345678901, $vardef, false],
            ],
            'Oracle' => [
                [$bean, 'test', 0, $vardef, true],
                [$bean, 'test', -12345678901, $vardef, false],
                [$bean, 'test', 12345678901, $vardef, false],
            ],
            'IBM_DB2' => [
                [$bean, 'test', 0, $vardef, true],
                [$bean, 'test', -12345678901, $vardef, false],
                [$bean, 'test', 12345678901, $vardef, false],
            ],
            'SQL Server' => [
                [$bean, 'test', 0, $vardef, true],
                [$bean, 'test', -12345678901, $vardef, false],
                [$bean, 'test', 12345678901, $vardef, false],
            ],
        ];

        $data = $data[$bean->db->dbName] ?? [];

        $sugarMinInt = SugarConfig::getInstance()->get('sugar_min_int');
        if (!empty($sugarMinInt)) {
            $data[] = [$bean, 'test', $sugarMinInt - 1, $vardef, false];
            $data[] = [$bean, 'test', $sugarMinInt, $vardef, true];
        }
        $sugarMaxInt = SugarConfig::getInstance()->get('sugar_max_int');
        if (!empty($sugarMaxInt)) {
            $data[] = [$bean, 'test', $sugarMaxInt + 1, $vardef, false];
            $data[] = [$bean, 'test', $sugarMaxInt, $vardef, true];
        }

        $vardef['min'] = -100;
        $data[] = [$bean, 'test', $vardef['min'] - 1, $vardef, false];
        $data[] = [$bean, 'test', $vardef['min'], $vardef, true];
        $vardef['max'] = 100;
        $data[] = [$bean, 'test', $vardef['max'] + 1, $vardef, false];
        $data[] = [$bean, 'test', $vardef['max'], $vardef, true];

        return $data;
    }
}
