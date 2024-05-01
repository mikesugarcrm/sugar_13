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


class SugarTestProductTypesUtilities
{
    private static $createdTypes = [];

    private function __construct()
    {
    }

    public static function createType($id = '', $name = '')
    {
        $type = new ProductType();
        $type->name = $name;
        if (!empty($id)) {
            $type->new_with_id = true;
            $type->id = $id;
        }
        $type->save();
        self::$createdTypes[] = $type;
        return $type;
    }

    public static function removeAllCreatedtypes()
    {
        $type_ids = self::getCreatedTypeIds();
        $GLOBALS['db']->query('DELETE FROM product_types WHERE id IN (\'' . implode("', '", $type_ids) . '\')');
    }

    public static function getCreatedTypeIds()
    {
        $type_ids = [];
        foreach (self::$createdTypes as $type) {
            $type_ids[] = $type->id;
        }
        return $type_ids;
    }
}
