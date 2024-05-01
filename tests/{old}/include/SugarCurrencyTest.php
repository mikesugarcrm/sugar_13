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
 * SugarCurrencyTest
 *
 * unit tests for currencies
 *
 * @author Monte Ohrt <mohrt@sugarcrm.com>
 */
class SugarCurrencyTest extends TestCase
{
    /**
     * store $sugar_config for later revert
     * @var    array $sugar_config
     */
    private static $sugar_config;

    /**
     * @var object pointers to currency objects
     */
    private static $currencySGD;
    private static $currencyPHP;
    private static $currencyYEN;
    private static $currencyBase;

    /**
     * pre-class environment setup
     */
    public static function setUpBeforeClass(): void
    {
        // setup test user
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');

        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);

        // setup test currencies
        self::$currencySGD = SugarTestCurrencyUtilities::createCurrency('Singapore', '$', 'SGD', 1.246171, 'currency-sgd');
        self::$currencyPHP = SugarTestCurrencyUtilities::createCurrency('Philippines', '₱', 'PHP', 41.82982, 'currency-php');
        self::$currencyYEN = SugarTestCurrencyUtilities::createCurrency('Yen', '¥', 'YEN', 78.87, 'currency-yen');
        self::$currencyBase = BeanFactory::getBean('Currencies', '-99');
    }

    /**
     * post-object environment teardown
     */
    public static function tearDownAfterClass(): void
    {
        // remove test currencies
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
    }

    /**
     * test base currency retrieval
     *
     * @group currency
     */
    public function testBaseCurrency()
    {
        $currency = SugarCurrency::getBaseCurrency();
        $this->assertInstanceOf('Currency', $currency);
        // base currency is always a rate of 1.0
        $this->assertEquals(1.0, $currency->conversion_rate);
    }

    /**
     * test currency retrieval by currency_id
     *
     * @group currency
     */
    public function testCurrencyGetByID()
    {
        // get test currency id
        $currencyId = 'currency-php';
        // now fetch by currency id
        $currency = SugarCurrency::getCurrencyByID($currencyId);
        $this->assertInstanceOf('Currency', $currency);
        // test they are the same currency
        $this->assertEquals($currencyId, $currency->id);
    }

    /**
     * test currency retrieval by ISO code
     *
     * @group currency
     */
    public function testCurrencyGetByISO()
    {
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        $this->assertInstanceOf('Currency', $currency);
        $this->assertEquals('PHP', $currency->iso4217);
        $this->assertEquals(41.82982, $currency->conversion_rate);
    }

    /**
     * test currency retrieval by user preferences
     *
     * @group currency
     */
    public function testGetUserLocaleCurrency()
    {
        $currency = SugarCurrency::getUserLocaleCurrency();
        $this->assertInstanceOf('Currency', $currency);
    }

    /**
     * test converting amount to base currency
     *
     * @group currency
     */
    public function testConvertAmountToBase()
    {
        $amount = SugarCurrency::convertAmountToBase('1000.00', self::$currencySGD->id);
        $this->assertEquals('802.458090', $amount);
    }

    /**
     * test converting amount from base currency
     *
     * @group currency
     */
    public function testConvertAmountFromBase()
    {
        $amount = SugarCurrency::convertAmountFromBase('1000.00', self::$currencySGD->id);
        $this->assertEquals('1246.171000', $amount);
    }

    /**
     * test converting amount between currencies
     *
     * @group currency
     */
    public function testConvertAmount()
    {
        $amount = SugarCurrency::convertAmount('1000.00', self::$currencySGD->id, self::$currencyPHP->id);
        $this->assertEquals('33566.677446', $amount);
    }


    /**
     * test dollar amount conversions between currencies
     *
     * @dataProvider dataProviderConvertWithRateProvider
     * @param $amount
     * @param $rate
     * @param $result
     * @group currency
     */
    public function testConvertWithRate($amount, $rate, $result)
    {
        $this->assertEquals($result, SugarCurrency::convertWithRate($amount, $rate));
    }

    /**
     * convert with rate data provider
     *
     * @group math
     * @access public
     */
    public static function dataProviderConvertWithRateProvider()
    {
        return [
            [1000, 0.5, 2000],
            [1000, 2.0, 500],
            ['1000', '0.5', '2000.000000'],
            ['1000', '2.0', '500.000000'],
            ['', '2.0', '0'],
        ];
    }

    /**
     * test formatting of currency amount with user locale settings
     *
     * @dataProvider dataProviderFormatAmountUserLocaleProvider
     * @param $amount
     * @param $currencyId
     * @param $result
     * @group currency
     */
    public function testFormatAmountUserLocale($amount, $currencyId, $result)
    {
        $format = SugarCurrency::formatAmountUserLocale($amount, $currencyId);
        $this->assertEquals($result, $format);
    }

    /**
     * convert with rate data provider
     *
     * @group math
     * @access public
     */
    public static function dataProviderFormatAmountUserLocaleProvider()
    {
        $currencyId = 'currency-php';
        $currencySymbol = '₱';
        return [
            ['1000', $currencyId, $currencySymbol . '1,000.00'],
            ['1000.0', $currencyId, $currencySymbol . '1,000.00'],
            ['1000.00', $currencyId, $currencySymbol . '1,000.00'],
            ['1000.000', $currencyId, $currencySymbol . '1,000.00'],
        ];
    }

    /**
     * test formatting of currency amount manually
     *
     * @dataProvider dataProviderFormatAmountProvider
     * @param $amount
     * @param $currencyId
     * @param $precision
     * @param $decimal
     * @param $thousands
     * @param $showSymbol
     * @param $symbolSeparator
     * @param $result
     * @group currency
     */
    public function testFormatAmount($amount, $currencyId, $precision, $decimal, $thousands, $showSymbol, $symbolSeparator, $result)
    {
        $format = SugarCurrency::formatAmount($amount, $currencyId, $precision, $decimal, $thousands, $showSymbol, $symbolSeparator);
        $this->assertEquals($result, $format);
    }

    /**
     * format amount data provider
     *
     * @group math
     * @access public
     */
    public static function dataProviderFormatAmountProvider()
    {
        $currencyId = 'currency-php';
        $currencySymbol = '₱';
        return [
            ['1000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '1,000.00'],
            ['1000', $currencyId, 2, '.', ',', true, '&nbsp;', $currencySymbol . '&nbsp;1,000.00'],
            ['1000', $currencyId, 2, ',', '.', true, '', $currencySymbol . '1.000,00'],
            ['1000', $currencyId, 3, '.', ',', true, '', $currencySymbol . '1,000.000'],
            ['1000', $currencyId, 3, '.', '', true, '', $currencySymbol . '1000.000'],
            ['1000', $currencyId, 3, ',', '.', true, '', $currencySymbol . '1.000,000'],
            ['-1000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-1,000.00'],
            ['-1000', $currencyId, 2, '.', ',', true, '&nbsp;', $currencySymbol . '&nbsp;-1,000.00'],
            ['-1000', $currencyId, 2, ',', '.', true, '', $currencySymbol . '-1.000,00'],
            ['-1000', $currencyId, 3, '.', ',', true, '', $currencySymbol . '-1,000.000'],
            ['-1000', $currencyId, 3, '.', '', true, '', $currencySymbol . '-1000.000'],
            ['-1000', $currencyId, 3, ',', '.', true, '', $currencySymbol . '-1.000,000'],
            ['10000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '10,000.00'],
            ['100000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '100,000.00'],
            ['1000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '1,000,000.00'],
            ['10000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '10,000,000.00'],
            ['100000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '100,000,000.00'],
            ['1000000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '1,000,000,000.00'],
            ['-10000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-10,000.00'],
            ['-100000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-100,000.00'],
            ['-1000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-1,000,000.00'],
            ['-10000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-10,000,000.00'],
            ['-100000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-100,000,000.00'],
            ['-1000000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-1,000,000,000.00'],
            ['0.9', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.90'],
            ['0.09', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.09'],
            ['0.099', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.10'],
            ['0.094', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.09'],
            ['0.09499999', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.09'],
            ['0.09499999', $currencyId, 6, '.', ',', true, '', $currencySymbol . '0.095000'],
        ];
    }

    public function testFormatNotNumber()
    {
        $formatted = SugarCurrency::formatAmount('not-a-number', -99);
        $this->assertEquals('not-a-number', $formatted);
    }

    /**
     * test affects of changing base currency type
     *
     * @dataProvider dataProviderBaseCurrencyChangeProvider
     * @param $amount
     * @param $currencyId1
     * @param $currencyId2
     * @param $result
     * @group currency
     */
    public function testBaseCurrencyChange($amount, $currencyId1, $currencyId2, $result)
    {
        global $sugar_config;
        // save for resetting after test
        $orig_config = $sugar_config;
        $sugar_config['default_currency_iso4217'] = 'BTC';
        $sugar_config['default_currency_name'] = 'Bitcoin';
        $sugar_config['default_currency_symbol'] = '฿';

        $this->assertEquals($result, SugarCurrency::convertAmount($amount, $currencyId1, $currencyId2));

        // reset config values
        $sugar_config = $orig_config;
    }

    /**
     * base rate change provider
     *
     * @group math
     * @access public
     */
    public static function dataProviderBaseCurrencyChangeProvider()
    {
        return [
            ['1000.00', 'currency-sgd', 'currency-php', '33566.677446'],
            ['1000.00', 'currency-php', 'currency-yen', '1885.496997'],
            ['1000.00', 'currency-yen', '-99', '12.679092'],
            ['1000.00', '-99', 'currency-sgd', '1246.171000'],
        ];
    }

    public function testSetBaseRateWhenNotSet()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['save', 'getFieldDefinition'])
            ->disableOriginalConstructor()
            ->getMock();

        $bean->expects($this->exactly(2))
            ->method('getFieldDefinition')
            ->will($this->returnValue(true));

        /** @var Currency $currency */
        $currency = $this->getMockBuilder('Currency')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $currency->conversion_rate = '1.1';

        $sc = new MockSugarCurrency();
        $sc::$mockCurrency = $currency;

        /** @var SugarBean $bean */

        $bean->currency_id = 'test_1';

        /** @var SugarCurrency $sc */
        $sc::verifyCurrencyBaseRateSet($bean);

        $this->assertEquals('1.1', $bean->base_rate);
    }

    public function testBaseRatesChangeToCurrentBaseRate()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['save', 'getFieldDefinition'])
            ->disableOriginalConstructor()
            ->getMock();

        $bean->expects($this->exactly(2))
            ->method('getFieldDefinition')
            ->will($this->returnValue(true));

        /** @var Currency $currency */
        $currency = $this->getMockBuilder('Currency')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $currency->conversion_rate = '1.1';

        /** @var SugarBean $bean */
        $bean->currency_id = 'test_1';
        $bean->fetched_row['currency_id'] = 'test_1';
        $bean->base_rate = '1.2';

        $sc = new MockSugarCurrency();
        $sc::$mockCurrency = $currency;

        /** @var SugarCurrency $sc */
        $sc::verifyCurrencyBaseRateSet($bean);

        $this->assertEquals('1.1', $bean->base_rate);
    }

    public function testSaveChangesBaseRateIfCurrencyIdChanged()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['save', 'getFieldDefinition'])
            ->disableOriginalConstructor()
            ->getMock();

        $bean->expects($this->exactly(2))
            ->method('getFieldDefinition')
            ->will($this->returnValue(true));

        /** @var Currency $currency */
        $currency = $this->getMockBuilder('Currency')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $currency->conversion_rate = '1.1';

        /** @var SugarBean $bean */
        $bean->fetched_row['currency_id'] = 'test_2';
        $bean->currency_id = 'test_1';
        $bean->base_rate = '1.2';

        $sc = new MockSugarCurrency();
        $sc::$mockCurrency = $currency;

        /** @var SugarCurrency $sc */

        $sc::verifyCurrencyBaseRateSet($bean);

        $this->assertEquals('1.1', $bean->base_rate);
    }


    public static function dataProviderBeanMethod()
    {
        return [
            ['1.1', true],
            ['1.2', false],
        ];
    }

    /**
     * @dataProvider dataProviderBeanMethod
     */
    public function testSaveWithBeanUpdateCurrencyBaseRateMethod($expected, $method_return)
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['save', 'updateCurrencyBaseRate', 'getFieldDefinition'])
            ->disableOriginalConstructor()
            ->getMock();

        $bean->expects($this->exactly(2))
            ->method('getFieldDefinition')
            ->will($this->returnValue(true));

        /** @var Currency $currency */
        $currency = $this->getMockBuilder('Currency')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $currency->conversion_rate = '1.1';

        $bean->expects($this->once())
            ->method('updateCurrencyBaseRate')
            ->will($this->returnValue($method_return));

        /** @var SugarBean $bean */
        $bean->fetched_row['currency_id'] = 'test_1';
        $bean->currency_id = 'test_1';
        $bean->base_rate = '1.2';

        $sc = new MockSugarCurrency();
        $sc::$mockCurrency = $currency;

        /** @var SugarCurrency $sc */

        $sc::verifyCurrencyBaseRateSet($bean);

        $this->assertEquals($expected, $bean->base_rate);
    }

    public static function dataProviderHadCurrencyIdChanged()
    {
        return [
            ['test_1', 'test_1', false],
            ['test_1', '', true],
            ['', 'test_1', true],
        ];
    }

    /**
     * @dataProvider dataProviderHadCurrencyIdChanged
     */
    public function testHasCurrencyIdChanged($fetched_value, $bean_value, $expected)
    {
        $field = $this->getMockBuilder('SugarCurrency')
            ->setMethods(['getCurrency'])
            ->getMock();

        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $bean->currency_id = $bean_value;
        $bean->fetched_row['currency_id'] = $fetched_value;

        $this->assertEquals(
            $expected,
            SugarTestReflection::callProtectedMethod($field, 'hasCurrencyIdChanged', [$bean])
        );
    }
}

class MockSugarCurrency extends SugarCurrency
{
    /**
     * Which currency the getCurrency method should return
     *
     * @var SugarCurrency
     */
    public static $mockCurrency = null;

    /**
     * @return Currency|SugarCurrency
     */
    public static function getCurrency(SugarBean $bean)
    {
        return static::$mockCurrency;
    }
}
