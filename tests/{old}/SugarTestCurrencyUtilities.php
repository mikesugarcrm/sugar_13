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

/**
 * SugarTestCurrencyUtilities
 *
 * utility class for currencies
 *
 * @author Monte Ohrt <mohrt@sugarcrm.com>
 */
class SugarTestCurrencyUtilities
{
    private static $createdCurrencies = [];

    private function __construct()
    {
    }

    /**
     * createCurrency
     *
     * This creates and returns a new currency object
     *
     * @param string $name the name of the currency
     * @param string $symbol the symbol for the currency
     * @param string $iso4217 the 3-letter ISO for the currency
     * @param number $conversion_rate the conversion rate from the US dollar
     * @param string $id the id for the currency record
     * @return Currency
     */
    public static function createCurrency($name, $symbol, $iso4217, $conversion_rate, $id = null)
    {
        $currency = BeanFactory::newBean('Currencies');
        $currency->name = $name;
        $currency->symbol = $symbol;
        $currency->iso4217 = $iso4217;
        $currency->conversion_rate = $conversion_rate;
        $currency->status = 'Active';
        if (!empty($id)) {
            $currency->new_with_id = true;
            $currency->id = $id;
        } else {
            $currency->created_by = $GLOBALS['current_user']->id;
        }
        $currency->save();
        self::$createdCurrencies[] = $currency;
        return $currency;
    }

    /**
     * getCurrencyByISO
     *
     * get an existing currency by its ISO
     *
     * @param string $iso4217 the 3-letter ISO for the currency
     * @return Currency
     */
    public static function getCurrencyByISO($iso4217)
    {
        $currency = BeanFactory::newBean('Currencies');
        $currency->retrieve($currency->retrieveIDByISO($iso4217));
        return $currency;
    }

    /**
     * removeAllCreatedCurrencies
     *
     * remove currencies created by this test utility
     *
     * @return boolean true on successful removal
     */
    public static function removeAllCreatedCurrencies()
    {
        if (empty(self::$createdCurrencies)) {
            return true;
        }
        $currency_ids = self::getCreatedCurrencyIds();
        $GLOBALS['db']->query(
            sprintf(
                "DELETE FROM currencies WHERE id IN ('%s')",
                implode("','", $currency_ids)
            )
        );
        self::$createdCurrencies = [];
        return true;
    }

    /**
     * getCreatedCurrencyIds
     *
     * get array of currency_ids created by this utility
     *
     * @return array list of currency_id's
     */
    public static function getCreatedCurrencyIds()
    {
        $currency_ids = [];
        foreach (self::$createdCurrencies as $currency) {
            $currency_ids[] = $currency->id;
        }
        return $currency_ids;
    }
}
