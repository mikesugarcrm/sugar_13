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

class SugarTestPurchasedLineItemUtilities
{
    protected static $createdPLIs = [];

    /**
     * @return PurchasedLineItem
     */
    public static function createPurchasedLineItem($id = ''): PurchasedLineItem
    {
        $time = random_int(0, mt_getrandmax());
        $name = 'SugarPurchasedLineItem';

        $pli = new PurchasedLineItem();
        $pli->currency_id = '-99';
        $pli->name = $name . $time;
        $pli->tax_class = 'Taxable';
        $pli->cost_price = '100.00';
        $pli->list_price = '100.00';
        $pli->discount_price = '100.00';
        $pli->quantity = '100';
        $pli->best_case = '100.00';
        $pli->likely_case = '80.00';
        $pli->worst_case = '50.00';

        if (!empty($id)) {
            $pli->new_with_id = true;
            $pli->id = $id;
        }
        $pli->save();
        self::$createdPLIs[] = $pli;
        return $pli;
    }

    public static function removeAllCreatedPurchasedLineItems(): void
    {
        $db = DBManagerFactory::getInstance();
        $conditions = implode(',', array_map([$db, 'quoted'], self::getCreatedPurchasedLineItemIds()));
        if (!empty($conditions)) {
            $db->query('DELETE FROM purchased_line_items WHERE id IN (' . $conditions . ')');
            $db->query('DELETE FROM purchased_line_items_audit WHERE parent_id IN (' . $conditions . ')');
        }

        self::$createdPLIs = [];
    }

    public static function getCreatedPurchasedLineItemIds(): array
    {
        $pli_ids = [];
        foreach (self::$createdPLIs as $pli) {
            $pli_ids[] = $pli->id;
        }
        return $pli_ids;
    }

    public static function removePurchasedLineItemsByID(array $ids): void
    {
        $db = DBManagerFactory::getInstance();
        $conditions = implode(',', array_map([$db, 'quoted'], $ids));
        if (!empty($conditions)) {
            $db->query('DELETE FROM purchased_line_items WHERE id IN (' . $conditions . ')');
            $db->query('DELETE FROM purchased_line_items_audit WHERE parent_id IN (' . $conditions . ')');
        }
    }
}
