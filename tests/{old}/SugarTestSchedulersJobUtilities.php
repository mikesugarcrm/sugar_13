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


class SugarTestSchedulersJobUtilities
{
    private static $createdJobs = [];

    /**
     * Create a new job.
     *
     * @param string $id
     * @return SugarBean
     */
    public static function createJob($id = '')
    {
        $job = BeanFactory::newBean('SchedulersJobs');

        if (!empty($id)) {
            $job->new_with_id = true;
            $job->id = $id;
        }
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        DBManagerFactory::getInstance()->commit();
        self::$createdJobs[] = $job;
        return $job;
    }

    /**
     * Add a job to the internal storage.
     *
     * @param array $ids
     */
    public static function setCreatedJob($ids)
    {
        foreach ($ids as $jobId) {
            $job = BeanFactory::newBean('SchedulersJobs');
            $job->id = $jobId;
            self::$createdJobs[] = $job;
        }
    }

    /**
     * Remove jobs, created by this helper, from DB.
     */
    public static function removeAllCreatedJobs()
    {
        $jobIds = self::getCreatedJobIds();
        DBManagerFactory::getInstance()
            ->query('DELETE FROM job_queue WHERE id IN (\'' . implode("', '", $jobIds) . '\')');
    }

    /**
     * Return all IDs of all created records.
     *
     * @return array
     */
    public static function getCreatedJobIds()
    {
        $jobIds = [];
        foreach (self::$createdJobs as $job) {
            $jobIds[] = $job->id;
        }
        return $jobIds;
    }
}
