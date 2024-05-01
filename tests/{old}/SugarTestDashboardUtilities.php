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

use Doctrine\DBAL\Connection;

class SugarTestDashboardUtilities
{
    /**
     * @var array List of previously created dashboard records.
     */
    private static $createdDashboards = [];

    private function __construct()
    {
    }

    /**
     * Creates a dashboard record based on the supplied parameters.
     *
     * @param string $id Dashboard Id, if none supplied a new one is
     *  automatically generated.
     * @param array $properties Array of key-value pairs to be applied as
     *  dashboard properties. If <code>$properties['name']</code> isn't
     *  supplied, the dashboard name defaults to 'SugarDashboard <random
     *  number>', same thing for <code>$properties['dashboard_module']</code>
     *  which in turn defaults to 'Home' if none supplied.
     *
     * @return Dashboard New dashboard record.
     */
    public static function createDashboard($id = '', $properties = [])
    {
        $db = \DBManagerFactory::getInstance();

        $random = random_int(0, mt_getrandmax());
        $dashboard = BeanFactory::newBean('Dashboards');

        $properties = array_merge([
            'name' => 'SugarDashboard' . $random,
            'dashboard_module' => 'Home',
        ], $properties);

        foreach ($properties as $property => $value) {
            $dashboard->$property = $value;
        }

        if (!empty($id)) {
            $dashboard->new_with_id = true;
            $dashboard->id = $id;
        }

        $dashboard->save();

        $db->commit();

        self::$createdDashboards[] = $dashboard;
        return $dashboard;
    }

    /**
     * Remove all previously created dashboards.
     */
    public static function removeAllCreatedDashboards()
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        $dashboardIds = self::getCreatedDashboardIds();
        if (count($dashboardIds)) {
            $query = 'DELETE FROM dashboards WHERE id IN (?)';
            $conn->executeUpdate(
                $query,
                [$dashboardIds],
                [Connection::PARAM_STR_ARRAY]
            );
        }
    }

    /**
     * Returns a list of all the previously created dashboard ids.
     *
     * @return array List of ids.
     */
    public static function getCreatedDashboardIds()
    {
        $dashboardIds = [];
        foreach (self::$createdDashboards as $dashboard) {
            $dashboardIds[] = $dashboard->id;
        }
        return $dashboardIds;
    }

    /**
     * Remove one dashboard
     *
     * @param string $dashbordId
     * @return void
     */
    public static function removeDashboard(string $dashbordId): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$dashbordId) {
            return;
        }
        $query = 'DELETE FROM dashboards WHERE id = ?';
        $conn->executeStatement($query, [$dashbordId]);
    }

    /**
     * Remove dashboards with a certain name
     *
     * @param string $name dashboard name
     * @return void
     */
    public static function removeDashboardsByName(string $name): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$name) {
            return;
        }
        $query = 'DELETE FROM dashboards WHERE name = ?';
        $conn->executeStatement($query, [$name]);
    }
}
