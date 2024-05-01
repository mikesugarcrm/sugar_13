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
use Sugarcrm\Sugarcrm\Util\Uuid;

class SugarTestReportUtilities
{
    /**
     * @var array List of previously created report records.
     */
    private static $createdReports = [];

    private function __construct()
    {
    }

    /**
     * Creates a report record based on the supplied parameters.
     *
     * @param string $id Report Id, if none supplied a new one is
     *  automatically generated.
     * @param array $properties Array of key-value pairs to be applied as
     *  report properties.
     *
     * @return Report New report record.
     */
    public static function createReport($id = '', $properties = [])
    {
        $random = random_int(0, mt_getrandmax());
        $report = BeanFactory::newBean('Reports');

        $properties = array_merge([
            'name' => 'SugarReport' . $random,
        ], $properties);

        foreach ($properties as $property => $value) {
            $report->$property = $value;
        }

        if (!empty($id)) {
            $report->new_with_id = true;
            $report->id = $id;
        }

        $report->save();

        $GLOBALS['db']->commit();

        self::$createdReports[] = $report;
        return $report;
    }

    /**
     * Remove all previously created reports.
     */
    public static function removeAllCreatedReports()
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        $reportIds = self::getCreatedReportIds();
        if (count($reportIds)) {
            $query = 'DELETE FROM saved_reports WHERE id IN (?)';
            $conn->executeUpdate(
                $query,
                [$reportIds],
                [Connection::PARAM_STR_ARRAY]
            );
        }
    }

    /**
     * Returns a list of all the previously created report ids.
     *
     * @return array List of ids.
     */
    public static function getCreatedReportIds()
    {
        $reportIds = [];
        foreach (self::$createdReports as $report) {
            $reportIds[] = $report->id;
        }
        return $reportIds;
    }

    /**
     * Remove one report
     *
     * @param string $reportId
     * @return void
     */
    public static function removeReport(string $reportId): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$reportId) {
            return;
        }

        $query = 'DELETE FROM saved_reports WHERE id = ?';
        $conn->executeStatement($query, [$reportId]);
    }

    /**
     * Remove reports with a certain name
     *
     * @param string $name report name
     * @return void
     */
    public static function removeReportsByName(string $name): void
    {
        $db = \DBManagerFactory::getInstance();
        $conn = $db->getConnection();

        if (!$name) {
            return;
        }

        $query = 'DELETE FROM saved_reports WHERE name = ?';
        $conn->executeStatement($query, [$name]);
    }

    /**
     * Create report panel for user
     *
     * @param string $userId
     * @param string $reportId
     * @return array
     */
    public static function createReportPanelForUser(
        string $userId,
        string $reportId,
        string $reportType = 'summary'
    ): array {

        global $timedate;

        $reportPanelId = Uuid::uuid4();
        $now = $timedate->asDb($timedate->getNow());

        $qb = DBManagerFactory::getConnection()->createQueryBuilder();
        $qb->insert('reports_panels');

        $contents = [
            'panels' => [
                [
                    'layout' => [
                        'type' => 'report-chart',
                        'label' => 'CHART',
                    ],
                    'width' => 5,
                    'height' => 10,
                    'x' => 0,
                    'y' => 0,
                ],
                [
                    'layout' => [
                        'type' => 'report-table',
                        'label' => 'LIST',
                    ],
                    'width' => 5,
                    'height' => 10,
                    'x' => 5,
                    'y' => 0,
                ],
                [
                    'layout' => [
                        'type' => 'report-filters',
                        'label' => 'FILTERS',
                    ],
                    'width' => 2,
                    'height' => 10,
                    'x' => 10,
                    'y' => 0,
                ],
            ],
        ];

        $values = [
            'id' => $qb->createPositionalParameter($reportPanelId),
            'user_id' => $qb->createPositionalParameter($userId),
            'report_id' => $qb->createPositionalParameter($reportId),
            'contents' => $qb->createPositionalParameter(json_encode($contents)),
            'date_entered' => $qb->createPositionalParameter($now),
            'date_modified' => $qb->createPositionalParameter($now),
            'default_panel' => $qb->createPositionalParameter(0),
            'report_type' => $qb->createPositionalParameter($reportType),
        ];
        $qb->values($values);
        $qb->execute();

        return [
            'id' => $reportPanelId,
            'contents' => $contents,
            'date_entered' => $now,
            'date_modified' => $now,
            'default_panel' => 0,
            'report_type' => $reportType,
        ];
    }

    /**
     * Get report panel for user
     *
     * @param string $userId
     * @param string $reportId
     * @return array
     */
    public static function getReportPanelForUser(string $userId, string $reportId): array
    {
        $qb = DBManagerFactory::getConnection()->createQueryBuilder();
        $qb->select('contents');
        $qb->from('reports_panels');
        $qb->where($qb->expr()->eq('report_id', $qb->createPositionalParameter($reportId)));
        $qb->andWhere($qb->expr()->eq('user_id', $qb->createPositionalParameter($userId)));
        $qb->andWhere($qb->expr()->eq('deleted', $qb->createPositionalParameter(0)));
        $res = $qb->execute();
        $row = $res->fetchAssociative();

        return $row;
    }

    /**
     * Get report panel for user
     *
     * @param string $reportPanelId
     */
    public static function removeReportPanel(string $reportPanelId)
    {
        $qb = DBManagerFactory::getConnection()->createQueryBuilder();
        $qb->delete('reports_panels');
        $qb->where($qb->expr()->eq('id', $qb->createPositionalParameter($reportPanelId)));
        $qb->execute();
    }
}
