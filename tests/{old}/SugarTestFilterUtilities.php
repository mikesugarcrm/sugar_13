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

/**
 * SugarTestFilterUtilities
 *
 * utility class for filters
 */
class SugarTestFilterUtilities
{
    private static $createdFilters = [];

    private function __construct()
    {
    }

    /**
     * Creates and returns a new user filter object
     *
     * @param string $assigned_user_id the name of user to own the filter
     * @param string $name the name of the filter
     * @param string $filter_definition the body of the filter (JSON)
     * @param string $id Optional the id for the currency record
     * @param string $module_name Optional
     * @return Filters
     */
    public static function createUserFilter($assigned_user_id, $name, $filter_definition, $id = null, $module_name = null)
    {
        $db = \DBManagerFactory::getInstance();

        $filter = new Filters();
        if (!empty($id)) {
            $filter->new_with_id = true;
            $filter->id = $id;
        }
        $filter->assigned_user_id = $assigned_user_id;
        $filter->set_created_by = true;
        $filter->created_by = $assigned_user_id;
        $filter->module_name = $module_name;
        $filter->name = $name;
        $filter->filter_definition = $filter_definition;
        $filter->save();
        $db->commit();
        self::$createdFilters[] = $filter;
        return $filter;
    }

    /**
     * remove all created filters from this utility
     */
    public static function removeAllCreatedFilters()
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        $filterIds = self::getCreatedFilterIds();

        $query = 'DELETE FROM filters WHERE id IN (?)';
        $conn->executeUpdate(
            $query,
            [$filterIds],
            [Connection::PARAM_STR_ARRAY]
        );
    }

    /**
     * get list of created filters by id
     *
     * @return array filter ids
     */
    public static function getCreatedFilterIds()
    {
        $filter_ids = [];
        foreach (self::$createdFilters as $filter) {
            $filter_ids[] = $filter->id;
        }
        return $filter_ids;
    }

    /**
     * Remove one filter
     *
     * @param string $dashbordId
     * @return void
     */
    public static function removeFilter(string $filterId): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$filterId) {
            return;
        }
        $query = 'DELETE FROM filters WHERE id = ?';
        $conn->executeStatement($query, [$filterId]);
    }

    /**
     * Remove filters with a certain name
     *
     * @param string $name filter name
     * @return void
     */
    public static function removeFiltersByName(string $name): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$name) {
            return;
        }

        $query = 'DELETE FROM filters WHERE name = ?';
        $conn->executeStatement($query, [$name]);
    }
}
