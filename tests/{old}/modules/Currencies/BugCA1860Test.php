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

/**
 * Bug CA-1860
 * Report Group by currency generates unknown error in drill down
 */
class BugCA1860Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestCurrencyUtilities::createCurrency('Euro', '€', 'EUR', 0.9);
    }

    protected function setUp(): void
    {
        global $current_user;

        $current_user->setPreference('num_grp_sep', '-', 0, 'global');
        $current_user->setPreference('dec_sep', '*', 0, 'global');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values
        //are stored as static variables
        get_number_seperators(true);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }

    /**
     * test unformatting number
     *
     * @group currency
     */
    public function testUnformatNumber()
    {
        global $current_user;
        $testValue = '€100-000*50';

        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(100000.50, $unformattedValue, 'Assert that €100,000.50 becomes 100000.50. Unformatted value is: ' . $unformattedValue);

        //Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('dec_sep', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
        get_number_seperators(true);

        $testValue = '€100.000,50';
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(100000.50, $unformattedValue, 'Assert that €100.000,50 becomes 100000.50. Unformatted value is: ' . $unformattedValue);
    }
}
