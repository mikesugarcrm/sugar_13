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

class SugarTestConfigUtilities
{
    /**
     * @var array
     */
    protected static $orgConfig = [];

    /**
     * @var Administration
     */
    protected static $admin;

    /**
     * Set a config value
     *
     * @param $category
     * @param $key
     * @param $value
     * @param string $platform
     */
    public static function setConfig($category, $key, $value, $platform = 'base')
    {
        if (!(self::$admin instanceof Administration)) {
            self::$admin = BeanFactory::newBean('Administration');
        }
        if (empty(self::$orgConfig)) {
            self::$orgConfig = self::$admin->getAllSettings();
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        self::$admin->saveSetting($category, $key, $value, $platform);
    }

    /**
     * Reset Config back to original values
     */
    public static function resetConfig()
    {
        if (empty(self::$orgConfig)) {
            return;
        }
        // delete everything in the table
        $db = DBManagerFactory::getInstance();
        $db->commit(); // DB requires truncate table to be the first operation in a transaction
        $db->query($db->truncateTableSQL('config'));
        foreach (self::$orgConfig as $config) {
            if (is_array($config['value'])) {
                $config['value'] = json_encode($config['value']);
            }
            self::$admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
        self::$orgConfig = [];
        self::$admin = null;
    }
}
