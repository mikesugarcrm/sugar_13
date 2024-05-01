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
 * Class SugarTestExternalUserUtilities
 *
 * This is a test class to create objects needed to test ExternalUsers
 */
class SugarTestExternalUserUtilities
{
    private static $createdExternalUsers = [];

    /**
     * Create an instance of ExternalUser
     *
     * @param string $id id for new object. If none is provided, random ID will be generated
     * @param array $fields fields to override during new object creation
     * @return SugarBean|null Bean instance if creation is successful, else null
     */
    public static function createExternalUser(string $id = '', array $fields = [])
    {
        $time = random_int(0, mt_getrandmax());
        $bean = BeanFactory::newBean('ExternalUsers');

        $fields = array_merge([
            'name' => 'ExternalUser' . $time,
        ], $fields);

        foreach ($fields as $property => $value) {
            $bean->$property = $value;
        }

        if (!empty($id)) {
            $bean->id = $id;
        }
        $bean->save();
        $GLOBALS['db']->commit();
        self::$createdExternalUsers[] = $bean;

        return $bean;
    }

    /**
     * Destroy all DB records created by this test class
     */
    public static function removeAllCreatedExternalUsers()
    {
        $db = DBManagerFactory::getInstance();

        $conditions = implode(',', array_map([$db, 'quoted'], self::getCreatedObjectIds()));
        if (!empty($conditions)) {
            $db->query('DELETE FROM external_users WHERE id IN (' . $conditions . ')');
        }
        self::$createdExternalUsers = [];
    }

    /**
     * Util method to get IDs of objects to destroy
     *
     * @return array of created object ids
     */
    public static function getCreatedObjectIds()
    {
        $ids = [];
        foreach (self::$createdExternalUsers as $bean) {
            $ids[] = $bean->id;
        }
        return $ids;
    }
}
