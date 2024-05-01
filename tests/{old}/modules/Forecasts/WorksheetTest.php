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

class WorksheetTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
    }

    /**
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     * @group forecasts
     * @group worksheet
     */
    public function testWorksheetRate()
    {
        $worksheet = SugarTestWorksheetUtilities::createWorksheet();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        $worksheet->currency_id = $currency->id;
        $worksheet->save();
        $this->assertEquals(2, $currency->conversion_rate, '');
    }
}
