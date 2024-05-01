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
 * Class SugarTestBpmUtilities
 *
 * This is a test class to create objects needed to test the Bpm
 */
class SugarTestBpmUtilities
{
    private static $createdBpmObjects = [];
    private static $bpmTables = [
        'pmse_project', 'pmse_inbox', 'pmse_bpm_related_dependency', 'pmse_bpm_flow', 'pmse_bpmn_process',
    ];

    /**
     * Create an instance of the provided module name
     *
     * @param string $moduleName name of module to be created
     * @param string $id id for new object. If none is provided, random ID will be generated
     * @param string $fields fields to override during new object creation
     * @return SugarBean|null Bean instance if creation is successful, else null
     */
    public static function createBpmObject($moduleName, $id = '', $fields = '')
    {
        $time = random_int(0, mt_getrandmax());
        $bean = BeanFactory::newBean('pmse_' . $moduleName);

        $fields = array_merge([
            'name' => $moduleName . $time,
        ], $fields);

        foreach ($fields as $property => $value) {
            $bean->$property = $value;
        }

        if (!empty($id)) {
            $bean->id = $id;
        }
        $bean->save();
        $GLOBALS['db']->commit();

        if (!array_key_exists($moduleName, self::$createdBpmObjects)) {
            self::$createdBpmObjects[$moduleName] = [];
        }
        self::$createdBpmObjects[$moduleName][] = $bean;
        return $bean;
    }

    /**
     * Destroy all DB records created by this test class
     */
    public static function removeAllCreatedBpmObjects()
    {
        $createdObjectIds = self::getCreatedObjectIds();
        foreach (self::$bpmTables as $tablename) {
            $query = 'DELETE FROM ' . $tablename . ' WHERE id IN (\'' . implode("', '", $createdObjectIds) . '\')';
            $GLOBALS['db']->query(
                $query
            );
        }
        $GLOBALS['db']->commit();
        self::$createdBpmObjects = [];
    }

    /**
     * Util method to get IDs of objects to destroy
     *
     * @return array of created object ids
     */
    public static function getCreatedObjectIds()
    {
        $ids = [];
        foreach (self::$createdBpmObjects as $objectArray) {
            foreach ($objectArray as $bean) {
                $ids[] = $bean->id;
            }
        }
        return $ids;
    }
}
