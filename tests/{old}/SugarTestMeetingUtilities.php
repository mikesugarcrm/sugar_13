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
require_once 'modules/Meetings/Meeting.php';

class SugarTestMeetingUtilities
{
    private static $createdMeetings = [];

    private function __construct()
    {
    }

    public static function createRecurringMeeting($id = '', User $user = null, array $meetingData = [])
    {
        global $current_user;
        $time = random_int(0, mt_getrandmax());
        $name = 'Meeting';
        $meeting = new MeetingMock();
        if (!empty($id)) {
            $meeting->new_with_id = true;
            $meeting->id = $id;
        }
        if ($user instanceof User) {
            $meeting->assigned_user_id = $user->id;
        } else {
            $meeting->assigned_user_id = $current_user->id;
        }

        if ($meetingData) {
            foreach ($meetingData as $property => $value) {
                if ($property !== 'rset') {
                    $meeting->$property = $value;
                }
            }
        }

        if (!array_key_exists('rset', $meetingData) && $meeting->repeat_type) {
            $meeting->generateRset();
        } elseif (array_key_exists('rset', $meetingData)) {
            $meeting->generateRecurrencePattern($meetingData['rset']);
            $meeting->repeat_parent_id = '';
        }

        $meeting->save();

        self::$createdMeetings[] = $meeting;
        return $meeting;
    }

    public static function createMeeting($id = '', User $user = null, array $meetingData = [], $save = true)
    {
        global $current_user;
        $time = random_int(0, mt_getrandmax());
        $name = 'Meeting';
        $meeting = new MeetingMock();
        $meeting->name = $name . $time;
        $meeting->duration_hours = 0;
        $meeting->duration_minutes = 15;
        $meeting->date_start = TimeDate::getInstance()->getNow()->asDb();
        if (!empty($id)) {
            $meeting->new_with_id = true;
            $meeting->id = $id;
        }
        if ($user instanceof User) {
            $meeting->assigned_user_id = $user->id;
        } else {
            $meeting->assigned_user_id = $current_user->id;
        }
        if ($meetingData) {
            foreach ($meetingData as $property => $value) {
                if (property_exists($meeting, $property)) {
                    $meeting->$property = $value;
                }
            }
        }
        if ($save) {
            $meeting->save();
        }
        self::$createdMeetings[] = $meeting;
        return $meeting;
    }

    public static function removeAllCreatedMeetings()
    {
        $meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query(sprintf("DELETE FROM meetings WHERE id IN ('%s')", implode("', '", $meeting_ids)));
    }

    public static function removeAllCreatedMeetingsWithRecuringById($meeting_ids)
    {
        if ($meeting_ids) {
            $imploded_meeting_ids = implode("', '", $meeting_ids);
            $GLOBALS['db']->query(sprintf("DELETE FROM meetings WHERE id IN ('%s') OR repeat_parent_id IN ('%s')", $imploded_meeting_ids, $imploded_meeting_ids));
        }
    }

    public static function removeMeetingContacts()
    {
        $meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query(sprintf("DELETE FROM meetings_contacts WHERE meeting_id IN ('%s')", implode("', '", $meeting_ids)));
    }

    public static function addMeetingLeadRelation($meeting_id, $lead_id)
    {
        $id = create_guid();
        $GLOBALS['db']->query("INSERT INTO meetings_leads (id, meeting_id, lead_id) values ('{$id}', '{$meeting_id}', '{$lead_id}')");
        return $id;
    }

    public static function addMeetingUserRelation($meeting_id, $user_id)
    {
        $id = create_guid();
        $GLOBALS['db']->query("INSERT INTO meetings_users (id, meeting_id, user_id) values ('{$id}', '{$meeting_id}', '{$user_id}')");
        return $id;
    }

    public static function addMeetingContactRelation($meeting_id, $contact_id)
    {
        $result = $GLOBALS['db']->query("SELECT id FROM meetings_contacts WHERE meeting_id='{$meeting_id}' AND contact_id='{$contact_id}'");
        $result = $GLOBALS['db']->fetchByAssoc($result);
        if (empty($result)) {
            $id = create_guid();
            $GLOBALS['db']->query("INSERT INTO meetings_contacts (id, meeting_id, contact_id) values ('{$id}', '{$meeting_id}', '{$contact_id}')");
        } else {
            $id = $result['id'];
        }
        return $id;
    }

    public static function deleteMeetingLeadRelation($id)
    {
        $GLOBALS['db']->query("delete from meetings_leads where id='{$id}'");
    }

    public static function addMeetingParent($meeting_id, $lead_id)
    {
        $sql = "update meetings set parent_type='Leads', parent_id='{$lead_id}' where id='{$meeting_id}'";
        $GLOBALS['db']->query($sql);
    }

    public static function removeMeetingUsers()
    {
        $meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query(sprintf("DELETE FROM meetings_users WHERE meeting_id IN ('%s')", implode("', '", $meeting_ids)));
    }

    public static function getCreatedMeetingIds()
    {
        $meeting_ids = [];
        foreach (self::$createdMeetings as $meeting) {
            $meeting_ids[] = $meeting->id;
        }
        return $meeting_ids;
    }
}

class MeetingMock extends Meeting
{
    public function set_notification_body($xtpl, &$meeting)
    {
        return $xtpl;
    }

    public function getNotificationEmailTemplate()
    {
        $templateName = $this->getTemplateNameForNotificationEmail();
        return $this->createNotificationEmailTemplate($templateName);
    }
}
