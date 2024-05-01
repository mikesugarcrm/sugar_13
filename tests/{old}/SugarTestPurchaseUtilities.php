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

class SugarTestPurchaseUtilities
{
    protected static $createdPurchases = [];

    /**
     * @param string $id
     * @return Purchase
     */
    public static function createPurchase($id = ''): Purchase
    {
        $time = random_int(0, mt_getrandmax());
        $timedate = TimeDate::getInstance();
        $purchase = BeanFactory::newBean('Purchases');

        $purchase->name = 'SugarPurchase' . $time;
        $purchase->date_entered = $timedate->getNow()->asDbDate();

        if (!empty($id)) {
            $purchase->new_with_id = true;
            $purchase->id = $id;
        }

        $purchase->save();
        self::$createdPurchases[] = $purchase;
        $purchase->load_relationship('purchasedlineitems');
        return $purchase;
    }

    public static function removeAllCreatedPurchases(): void
    {
        $db = DBManagerFactory::getInstance();

        $conditions = implode(',', array_map([$db, 'quoted'], self::getCreatedPurchaseIds()));
        if (!empty($conditions)) {
            $db->query('DELETE FROM purchases_audit WHERE parent_id IN (' . $conditions . ')');
            $db->query('DELETE FROM purchases WHERE id IN (' . $conditions . ')');
        }
        self::$createdPurchases = [];
    }

    public static function removePurchasesByID(array $ids): void
    {
        $db = DBManagerFactory::getInstance();
        $conditions = implode(',', array_map([$db, 'quoted'], $ids));
        if (!empty($conditions)) {
            $db->query('DELETE FROM purchases WHERE id IN (' . $conditions . ')');
            $db->query('DELETE FROM purchases_audit WHERE parent_id IN (' . $conditions . ')');
        }
    }

    public static function getCreatedPurchaseIds(): array
    {
        $purchase_ids = [];
        foreach (self::$createdPurchases as $purchase) {
            $purchase_ids[] = $purchase->id;
        }
        return $purchase_ids;
    }
}
