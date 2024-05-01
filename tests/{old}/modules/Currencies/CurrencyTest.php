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

require_once 'modules/Currencies/Currency.php';

class CurrencyTest extends TestCase
{
    /** @var Currency */
    private static $currency;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen', '¥', 'YEN', 78.87);
    }

    protected function setUp(): void
    {
        global $current_user;

        $current_user->setPreference('num_grp_sep', ',', 0, 'global');
        $current_user->setPreference('dec_sep', '.', 0, 'global');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
        get_number_seperators(true);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }

    /**
     * test retrieval of base currency
     *
     * @group currency
     */
    public function testCurrencyRetrieveBase()
    {
        $currency = BeanFactory::getBean('Currencies', '-99');
        $this->assertInstanceOf('Currency', $currency);
        $this->assertEquals(1.0, $currency->conversion_rate);
    }

    /**
     * test retrieval of base currency with null id
     *
     * @group currency
     */
    public function testConvertToDollar()
    {
        $this->assertEquals(1.267909, self::$currency->convertToDollar(100.00));
    }

    /**
     * test retrieval of base currency with null id
     *
     * @group currency
     */
    public function testConvertFromDollar()
    {
        $this->assertEquals(7887, self::$currency->convertFromDollar(100.00));
    }

    /**
     * test retrieval of base currency name
     *
     * @group currency
     */
    public function testGetBaseCurrencyName()
    {
        $this->assertEquals(
            $GLOBALS['sugar_config']['default_currency_name'],
            self::$currency->getDefaultCurrencyName()
        );
    }

    /**
     * test retrieval of base currency symbol
     *
     * @group currency
     */
    public function testGetBaseCurrencySymbol()
    {
        $this->assertEquals(
            $GLOBALS['sugar_config']['default_currency_symbol'],
            self::$currency->getDefaultCurrencySymbol()
        );
    }

    /**
     * test retrieval of base currency ISO code
     *
     * @group currency
     */
    public function testGetBaseCurrencyISO()
    {
        $this->assertEquals('USD', self::$currency->getDefaultISO4217());
    }

    /**
     * test retrieval of default currency by symbol
     *
     * @group currency
     */
    public function testRetrieveBaseCurrencyIdBySymbol()
    {
        $symbol = $GLOBALS['sugar_config']['default_currency_symbol'];
        $this->assertEquals('-99', self::$currency->retrieveIDBySymbol($symbol));
    }

    public function testRetrieveCustomCurrencyIdBySymbol()
    {
        $this->assertEquals(self::$currency->id, self::$currency->retrieveIDBySymbol('¥'));
    }

    /**
     * test retrieval of default currency by ISO
     *
     * @group currency
     */
    public function testRetrieveBaseCurrencyIdByIso()
    {
        $this->assertEquals('-99', self::$currency->retrieveIDByISO('USD'));
    }

    public function testRetrieveCustomCurrencyIdByIso()
    {
        $this->assertEquals(self::$currency->id, self::$currency->retrieveIDByISO('YEN'));
    }

    /**
     * test retrieval of default currency by name
     *
     * @group currency
     */
    public function testRetrieveBaseCurrencyIdByName()
    {
        $name = $GLOBALS['sugar_config']['default_currency_name'];
        $this->assertEquals('-99', self::$currency->retrieveIDByName($name));
    }

    public function testRetrieveCustomCurrencyIdByName()
    {
        $this->assertEquals(self::$currency->id, self::$currency->retrieveIDByName('Yen'));
    }

    /**
     * test unformatting currency
     *
     * @group currency
     */

    public function testUnformatNumber()
    {
        global $current_user;
        $testValue = '$100,000.50';

        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(100000.50, $unformattedValue, 'Assert that $100,000.50 becomes 100000.50. Unformatted value is: ' . $unformattedValue);

        //Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('dec_sep', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
        get_number_seperators(true);

        $testValue = '$100.000,50';
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(100000.50, $unformattedValue, 'Assert that $100.000,50 becomes 100000.50. Unformatted value is: ' . $unformattedValue);

        $testValue = '0.9';
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(9, $unformattedValue, 'Assert that 0.9 becomes 9. Unformatted value is: ' . $unformattedValue);

        $testValue = '-0.9';
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(-9, $unformattedValue, 'Assert that -0.9 becomes -9. Unformatted value is: ' . $unformattedValue);

        $testValue = '-3.000';
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(-3000, $unformattedValue, 'Assert that -3.000 becomes -3000. Unformatted value is: ' . $unformattedValue);

        $testValue = '3.000';
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(3000, $unformattedValue, 'Assert that 3.000 becomes 3000. Unformatted value is: ' . $unformattedValue);
    }

    /**
     * test formatting currency
     *
     * @group currency
     */

    public function testFormatNumber()
    {
        global $current_user;
        $testValue = '100000.50';

        $formattedValue = format_number($testValue);
        $this->assertEquals('100,000.50', $formattedValue, 'Assert that 100000.50 becomes 100,000.50. Formatted value is: ' . $formattedValue);

        //Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('dec_sep', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
        get_number_seperators(true);

        $testValue = '100000.50';
        $formattedValue = format_number($testValue);
        $this->assertEquals('100.000,50', $formattedValue, 'Assert that 100000.50 becomes 100.000,50. Formatted value is: ' . $formattedValue);
    }
}
