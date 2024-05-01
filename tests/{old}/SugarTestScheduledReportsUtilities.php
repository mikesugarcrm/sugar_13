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

class SugarTestScheduledReportsUtilities
{
    /**
     * @var array List of previously created scheduled reports records.
     */
    private static $createdScheduledReports = [];

    private function __construct()
    {
    }

    /**
     * Creates a scheduled report record based on the supplied parameters.
     *
     * @param string $id scheduled report Id, if none supplied a new one is
     *  automatically generated.
     * @param array $properties Array of key-value pairs to be applied as
     *  scheduled reports properties. If <code>$properties['name']</code> isn't
     *  supplied, the scheduled report name defaults to 'ReportSchedules <random
     *  number>'
     *
     * @return ReportSchedules New scheduled report record.
     */
    public static function createScheduledReport($id = '', $properties = [])
    {
        $db = \DBManagerFactory::getInstance();

        $random = random_int(0, mt_getrandmax());
        $sreport = BeanFactory::newBean('ReportSchedules');

        $properties = array_merge([
            'name' => 'ReportSchedules' . $random,
        ], $properties);

        foreach ($properties as $property => $value) {
            $sreport->$property = $value;
        }

        if (!empty($id)) {
            $sreport->new_with_id = true;
            $sreport->id = $id;
        }

        $sreport->save();

        $db->commit();

        self::$createdScheduledReports[] = $sreport;
        return $sreport;
    }

    /**
     * Remove all previously created scheduled reports.
     */
    public static function removeAllCreatedScheduledReports()
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        $scheduledReportsIds = self::getCreatedScheduledReportsIds();
        if (count($scheduledReportsIds)) {
            $query = 'DELETE FROM report_schedules WHERE id IN (?)';
            $conn->executeUpdate(
                $query,
                [$scheduledReportsIds],
                [Connection::PARAM_STR_ARRAY]
            );
        }
    }

    /**
     * Returns a list of all the previously created report schedules ids.
     *
     * @return array List of ids.
     */
    public static function getCreatedScheduledReportsIds()
    {
        $reportScheduleIds = [];
        foreach (self::$createdScheduledReports as $reportSchedule) {
            $dashboardIds[] = $reportSchedule->id;
        }
        return $reportScheduleIds;
    }

    /**
     * Remove one reportSchedule
     *
     * @param string $reportScheduleId
     * @return void
     */
    public static function removeReportSchedule(string $reportScheduleId): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$reportScheduleId) {
            return;
        }

        $query = 'DELETE FROM report_schedules WHERE id = ?';
        $conn->executeStatement($query, [$reportScheduleId]);
    }

    /**
     * Remove report schedules with a certain name
     *
     * @param string $name report schedule name
     * @return void
     */
    public static function removeReportScheduleByName(string $name): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$name) {
            return;
        }

        $query = 'DELETE FROM report_schedules WHERE name = ?';
        $conn->executeStatement($query, [$name]);
    }
}
