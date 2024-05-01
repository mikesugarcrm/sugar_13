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


class SugarTestDataArchiverUtilities
{
    private static $createdArchivers = [];

    private function __construct()
    {
    }

    /**
     * Creates a new DataArchiver
     * @param string $id
     * @param array $archiverValues
     * @return DataArchiver
     */
    public static function createDataArchiver($id = '', $archiverValues = [])
    {
        $time = random_int(0, mt_getrandmax());
        $archiver = BeanFactory::newBean('DataArchiver');

        $archiverValues = array_merge([
            'name' => 'SugarDataArchiver' . $time,
        ], $archiverValues);

        foreach ($archiverValues as $property => $value) {
            $archiver->$property = $value;
        }

        if (!empty($id)) {
            $archiver->new_with_id = true;
            $archiver->id = $id;
        }
        $archiver->save();
        $GLOBALS['db']->commit();
        self::$createdArchivers[] = $archiver;
        return $archiver;
    }

    /**
     * Sets an archiver by id
     * @param $archiver_ids
     */
    public static function setCreatedArchiver($archiver_ids)
    {
        foreach ($archiver_ids as $archiver_id) {
            $archiver = BeanFactory::newBean('DataArchiver');
            $archiver->id = $archiver_id;
            self::$createdArchivers[] = $archiver;
        }
    }

    /**
     * Removes all created archivers
     */
    public static function removeAllCreatedArchivers()
    {
        $archiver_ids = self::getCreatedArchiverIds();
        $GLOBALS['db']->query('DELETE FROM data_archivers WHERE id IN (\'' . implode("', '", $archiver_ids) . '\')');
    }

    /**
     * Returns created archiver ids
     * @return array
     */
    public static function getCreatedArchiverIds()
    {
        $archiver_ids = [];
        foreach (self::$createdArchivers as $archiver) {
            $archiver_ids[] = $archiver->id;
        }
        return $archiver_ids;
    }
}
