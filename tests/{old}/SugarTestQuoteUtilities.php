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

class SugarTestQuoteUtilities
{
    private static $createdQuotes = [];

    private function __construct()
    {
    }

    public static function createQuote($id = '')
    {
        $time = random_int(0, mt_getrandmax());
        $name = 'SugarQuote';
        $quote = new Quote();
        $quote->name = $name . $time;
        $quote->quote_stage = 'Draft';
        $quote->date_quote_expected_closed = $GLOBALS['timedate']->to_display_date(gmdate('Y-m-d'));
        if (!empty($id)) {
            $quote->new_with_id = true;
            $quote->id = $id;
        }
        $quote->save();
        self::$createdQuotes[] = $quote;
        return $quote;
    }

    public static function setCreatedQuote($quote_ids)
    {
        foreach ($quote_ids as $quote_id) {
            $quote = new Quote();
            $quote->id = $quote_id;
            self::$createdQuotes[] = $quote;
        } // foreach
    } // fn

    public static function removeAllCreatedQuotes()
    {
        $quote_ids = self::getCreatedQuoteIds();
        $GLOBALS['db']->query('DELETE FROM quotes WHERE id IN (\'' . implode("', '", $quote_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM quotes_contacts WHERE quote_id IN (\'' . implode("', '", $quote_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM quotes_accounts WHERE quote_id IN (\'' . implode("', '", $quote_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM quotes_opportunities WHERE quote_id IN (\'' . implode("', '", $quote_ids) . '\')');
        $GLOBALS['db']->query(
            'DELETE FROM product_bundle_quote WHERE quote_id IN (\'' . implode("', '", $quote_ids) . '\')'
        );
    }

    public static function getCreatedQuoteIds()
    {
        $quote_ids = [];
        foreach (self::$createdQuotes as $quote) {
            $quote_ids[] = $quote->id;
        }
        return $quote_ids;
    }

    public static function relateQuoteToOpportunity($quoteId, $oppId)
    {
        $db = DBManagerFactory::getInstance();
        $query = sprintf(
            "insert into quotes_opportunities(id,opportunity_id,quote_id,date_modified,deleted) values('%s','%s','%s',%s,0)",
            create_guid(),
            $oppId,
            $quoteId,
            $db->convert(null, 'today')
        );
        $db->query($query);
    }

    public static function relateQuoteToProductBundle($quoteId, $bundleId)
    {
        $db = DBManagerFactory::getInstance();
        $query = sprintf(
            'insert into product_bundle_quote(id,bundle_id,quote_id,date_modified,deleted,bundle_index) '
            . " values('%s','%s','%s',%s,0,0)",
            create_guid(),
            $bundleId,
            $quoteId,
            $db->convert(null, 'today')
        );
        $db->query($query);
    }
}
