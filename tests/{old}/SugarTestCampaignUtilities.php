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


class SugarTestCampaignUtilities
{
    private static $createdCampaigns = [];
    private static $createdCampaignLogs = [];
    private static $createdCampaignTrackers = [];

    private function __construct()
    {
    }

    public static function createCampaign($id = '', $class = 'Campaign')
    {
        $time = random_int(0, mt_getrandmax());
        $name = 'SugarCampaign';
        $campaign = new $class();
        $campaign->name = $name . $time;
        $campaign->status = 'Active';
        $campaign->campaign_type = 'Email';
        $campaign->end_date = '2010-11-08';
        if (!empty($id)) {
            $campaign->new_with_id = true;
            $campaign->id = $id;
        }
        $campaign->save();
        self::$createdCampaigns[] = $campaign;
        return $campaign;
    }

    public static function removeAllCreatedCampaigns()
    {
        $campaignIds = static::getCreatedCampaignIds();
        $campaignIds = implode("', '", $campaignIds);
        $GLOBALS['db']->query("DELETE FROM campaigns WHERE id IN ('{$campaignIds}')");
        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE campaign_id IN ('{$campaignIds}')");
        $GLOBALS['db']->query("DELETE FROM campaign_trkrs WHERE campaign_id IN ('{$campaignIds}')");
    }

    public static function getCreatedCampaignIds()
    {
        $campaign_ids = [];
        foreach (self::$createdCampaigns as $campaign) {
            $campaign_ids[] = $campaign->id;
        }
        return $campaign_ids;
    }

    public static function setCreatedCampaign($ids)
    {
        $ids = is_array($ids) ? $ids : [$ids];
        foreach ($ids as $id) {
            $campaign = new Campaign();
            $campaign->id = $id;
            self::$createdCampaigns[] = $campaign;
        }
    }

    public static function createCampaignLog($campaignId, $activityType, $relatedBean, $extraData = [])
    {
        $campaignLog = BeanFactory::newBean('CampaignLog');
        $campaignLog->campaign_id = $campaignId;
        $campaignLog->related_id = $relatedBean->id;
        $campaignLog->related_type = $relatedBean->module_dir;
        $campaignLog->activity_type = $activityType;
        $campaignLog->target_type = $relatedBean->module_dir;
        $campaignLog->target_id = $relatedBean->id;

        foreach ($extraData as $k => $v) {
            $campaignLog->$k = $v;
        }

        $campaignLog->save();
        $GLOBALS['db']->commit();
        self::$createdCampaignLogs[] = $campaignLog;

        return $campaignLog;
    }

    public static function removeAllCreatedCampaignLogs()
    {
        $campaignLogIds = self::getCreatedCampaignLogsIds();
        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id IN ('" . implode("', '", $campaignLogIds) . "')");
    }

    public static function getCreatedCampaignLogsIds()
    {
        $campaignLogIds = [];

        foreach (self::$createdCampaignLogs as $campaignLog) {
            $campaignLogIds[] = $campaignLog->id;
        }

        return $campaignLogIds;
    }

    public static function createCampaignTracker($campaignId, $name = '', $url = '')
    {
        $time = random_int(0, mt_getrandmax());
        if ($name == '') {
            $name = 'SugarCampaignTracker' . $time;
        }
        if ($url == '') {
            $url = 'http://www.foo.com/' . $time;
        }
        $campaignTracker = BeanFactory::newBean('CampaignTrackers');
        $campaignTracker->campaign_id = $campaignId;
        $campaignTracker->tracker_name = $name;
        $campaignTracker->tracker_url = $url;

        $campaignTracker->save();
        $GLOBALS['db']->commit();
        self::$createdCampaignTrackers[] = $campaignTracker;

        return $campaignTracker;
    }

    public static function removeAllCreatedCampaignTrackers()
    {
        $campaignTrackerIds = self::getCreatedCampaignTrackerIds();
        $GLOBALS['db']->query("DELETE FROM campaign_trkrs WHERE id IN ('" . implode("', '", $campaignTrackerIds) . "')");
    }

    public static function getCreatedCampaignTrackerIds()
    {
        $campaignTrackerIds = [];

        foreach (self::$createdCampaignTrackers as $campaignTracker) {
            $campaignTrackerIds[] = $campaignTracker->id;
        }

        return $campaignTrackerIds;
    }
}

class CampaignMock extends Campaign
{
    public function getNotificationEmailTemplate($test = false)
    {
        $templateName = null;
        if ($test) {
            $templateName = $this->getTemplateNameForNotificationEmail();
            return $this->createNotificationEmailTemplate($templateName);
        }

        return $this->createNotificationEmailTemplate($templateName);
    }
}
