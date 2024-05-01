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

require_once 'modules/Currencies/Currency.php';

/**
 * Test Opportunity retrieve with currency
 */
class GetEntryListOppTest extends SOAPTestCase
{
    public const CURRENCY_CODE = 'EUR';

    private $currency;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';

        parent::setUp();

        self::$user = SugarTestUserUtilities::createAnonymousUser();
        $this->currency = BeanFactory::newBean('Currencies');
        $GLOBALS['current_user'] = self::$user;

        $found = $this->currency->retrieve_by_string_fields([
            'iso4217' => self::CURRENCY_CODE,
        ]);

        if (!$found) {
            $this->markTestSkipped('Currency \'' . self::CURRENCY_CODE . '\' not found.');
        }
    }

    protected function tearDown(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
    }

    public function dataset()
    {
        return [
            // [create_currency, retrieve_currency, amount]
            ['US', 'US', 10000],
            ['EUR', 'EUR', 10000],
        ];
    }

    /**
     * @dataProvider dataset
     */
    public function testContactAccount($createCurrency, $retrieveCurrency, $amount)
    {
        global $current_user;

        if ($createCurrency == self::CURRENCY_CODE) {
            $createCurrencyId = $this->currency->id;
        } else {
            $createCurrencyId = '-99';
        }

        if ($retrieveCurrency == self::CURRENCY_CODE) {
            $retrieveCurrencyId = $this->currency->id;
            $retrieveCurrencyName = $this->currency->name;
            $retrieveCurrencySymbol = $this->currency->symbol;
        } else {
            $retrieveCurrencyId = '-99';
            $retrieveCurrencyName = 'US Dollars';
            $retrieveCurrencySymbol = '$';
        }

        $current_user->setPreference('currency', $createCurrencyId);
        $current_user->savePreferencesToDB();

        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->currency_id = $createCurrencyId;
        $opportunity->amount = $amount;
        $opportunity->save();

        $current_user->setPreference('currency', $retrieveCurrencyId);
        $current_user->savePreferencesToDB();

        $this->loginLegacy();

        $result = $this->soapClient->get_entry_list(
            $this->sessionId,
            'Opportunities',
            'opportunities.id = \'' . $opportunity->id . '\'',
            '',
            0,
            [],
            10,
            -1
        );

        $this->assertGreaterThan(0, $result->result_count, 'Empty result returned');

        $opportunityData = array_shift($result->entry_list);
        $dataIndex = [];
        $dataLength = safeCount($opportunityData->name_value_list);

        for ($i = 0; $i < $dataLength; $i++) {
            $piece = $opportunityData->name_value_list[$i];
            $dataIndex[$piece->name] = $piece->value;
        }

        $this->assertEquals($retrieveCurrencySymbol, $dataIndex['currency_symbol'], 'Currency symbol is not match.');
        $this->assertEquals($retrieveCurrencyName, $dataIndex['currency_name'], 'Currency name is not match.');
    }
}
