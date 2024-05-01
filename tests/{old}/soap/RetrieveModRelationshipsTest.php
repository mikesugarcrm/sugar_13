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

use PHPUnit\Framework\TestCase;

require_once 'soap/SoapRelationshipHelper.php';

class RetrieveModRelationshipsTest extends TestCase
{
    protected static $call;
    protected static $call2;
    protected static $contact;
    protected static $contact2;
    protected static $meeting;
    protected static $meeting2;

    public static function setUpBeforeClass(): void
    {
        global $current_user;

        // User
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->save();
        $GLOBALS['db']->commit();
        $GLOBALS['db']->query("UPDATE users SET default_team = 'West', team_set_id = 'West' WHERE id = '" . $current_user->id . "'");

        // Call 1
        self::$call = SugarTestCallUtilities::createCall();
        self::$call->load_relationship('teams');
        self::$call->teams->replace(['East', 'West']);
        self::$call->calls_user_id = $current_user->id;
        self::$call->save();
        $GLOBALS['db']->commit();
        self::$call->load_relationships('users');
        self::$call->users->add($current_user);
        $GLOBALS['db']->query("UPDATE calls SET team_id = 'East' WHERE id = '" . self::$call->id . "'");

        // Call 2
        self::$call2 = SugarTestCallUtilities::createCall();
        self::$call2->load_relationship('teams');
        self::$call2->teams->replace(['West']);
        self::$call2->calls_user_id = $current_user->id;
        self::$call2->save();
        $GLOBALS['db']->commit();
        self::$call2->load_relationships('users');
        self::$call2->users->add($current_user);
        $GLOBALS['db']->query("UPDATE calls SET team_id = 'West' WHERE id = '" . self::$call2->id . "'");

        // Contact 1
        self::$contact = SugarTestContactUtilities::createContact();
        self::$contact->load_relationship('teams');
        self::$contact->teams->replace(['East', 'West']);
        self::$contact->email1 = 'soap@sugarcrm.com';
        self::$contact->contacts_users_id = $current_user->id;
        self::$contact->save();
        $GLOBALS['db']->commit();
        $GLOBALS['db']->query("UPDATE contacts SET team_id = 'East' WHERE id = '" . self::$contact->id . "'");

        // Contact 2
        self::$contact2 = SugarTestContactUtilities::createContact();
        self::$contact2->load_relationship('teams');
        self::$contact2->teams->replace(['North']);
        self::$contact2->email1 = 'soap.user@sugarcrm.com';
        self::$contact2->contacts_users_id = $current_user->id;
        self::$contact2->save();
        $GLOBALS['db']->commit();
        // Use fake team_set_id to get excluded from query result (excluded by team security query)
        $GLOBALS['db']->query("UPDATE contacts SET team_id = 'North', team_set_id = 'North' WHERE id = '" . self::$contact2->id . "'");

        // Meeting 1
        self::$meeting = SugarTestMeetingUtilities::createMeeting();
        self::$meeting->load_relationship('teams');
        self::$meeting->teams->replace(['East', 'West']);
        self::$meeting->assigned_user_id = $current_user->id;
        self::$meeting->save();
        $GLOBALS['db']->commit();
        self::$meeting->load_relationship('users');
        self::$meeting->users->add($current_user);
        $GLOBALS['db']->query("UPDATE meetings SET team_id = 'East' WHERE id = '" . self::$meeting->id . "'");

        // Meeting 2
        self::$meeting = SugarTestMeetingUtilities::createMeeting();
        self::$meeting->load_relationship('teams');
        self::$meeting->teams->replace(['South']);
        self::$meeting->assigned_user_id = $current_user->id;
        self::$meeting->save();
        $GLOBALS['db']->commit();
        self::$meeting->load_relationship('users');
        self::$meeting->users->add($current_user);
        // Use fake team_set_id to get excluded from query result (excluded by team security query)
        $GLOBALS['db']->query("UPDATE meetings SET team_id = 'South', team_set_id = 'South' WHERE id = '" . self::$meeting->id . "'");
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
    }

    /**
     * Test get next offset in different inputs
     *
     * @group 68901
     * @dataProvider getDataForRetrieveModifiedRelationships
     */
    public function testRetrieveModifiedRelationships($module_name, $related_module, $relationship_query, $show_deleted, $offset, $max_results, $select_fields, $relationship_name, $count)
    {
        global $current_user;

        $relationship_query .= "'{$current_user->id}'";
        $return = retrieve_modified_relationships($module_name, $related_module, $relationship_query, $show_deleted, $offset, $max_results, $select_fields, $relationship_name);

        $this->assertEquals($count, $return['total_count'], 'Totals do not match for ' . $related_module);
    }

    /**
     * Data provider for testRetrieveModifiedRelationships
     *
     * @return array
     */
    public static function getDataForRetrieveModifiedRelationships()
    {
        $calls_meetings_select_fields = ['id', 'date_modified', 'deleted', 'name', 'rt.deleted synced'];
        $contacts_select_fields = ['id', 'date_modified', 'deleted', 'first_name', 'last_name', 'rt.deleted synced'];

        return [
            ['Users', 'Calls', '({0}.deleted = 0) AND m2.id = ', 0, 0, 3000, $calls_meetings_select_fields, 'calls_users', 2],
            ['Users', 'Contacts', '({0}.deleted = 0) AND m2.id = ', 0, 0, 3000, $contacts_select_fields, 'contacts_users', 1],
            ['Users', 'Meetings', '({0}.deleted = 0) AND m2.id = ', 0, 0, 3000, $calls_meetings_select_fields, 'meetings_users', 1],
        ];
    }
}
