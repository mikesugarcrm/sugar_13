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

/**
 * Bug50780 - Due to the recent security fixes for the web services, subqueries on tables no longer work as expected.
 * This has presented a problem to some of our partner's since they were relying on the subqueries to return
 * relationship information on records. We could resolve this by adding back more tables to the allowed list of tables
 * to be queried in a subquery, but this has been voted against from most of the engineering staff. Another approach
 * would be to add enhancements to our API to allow for querying limited result sets for relationship data. Adding
 * offset and limit support to the get_relationships call is one such approach.
 */
class Bug50780Test extends SOAPTestCase
{
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();
        $this->login(); // Logging in just before the SOAP call as this will also commit any pending DB changes


        for ($x = 0; $x < 4; $x++) {
            $mid = SugarTestMeetingUtilities::createMeeting();
            SugarTestMeetingUtilities::addMeetingUserRelation($mid->id, self::$user->id);
        }

        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingUsers();

        parent::tearDown();
    }


    public function testGetRelationshipReturnAllMeetings()
    {
        $result = $this->soapClient->get_relationships(
            $this->sessionId,
            'Users',
            self::$user->id,
            'meetings',
            '',
            ['id', 'name'],
            '',
            0,
            'date_entered',
            0,
            false
        );
        $result = object_to_array_deep($result);

        $this->assertEquals(4, safeCount($result['entry_list']));
    }

    public function testGetRelationshipReturnNothingWithOffsetSetHigh()
    {
        $result = $this->soapClient->get_relationships(
            $this->sessionId,
            'Users',
            self::$user->id,
            'meetings',
            '',
            ['id', 'name'],
            '',
            0,
            'date_entered',
            5,
            4
        );
        $result = object_to_array_deep($result);

        $this->assertEquals(0, safeCount($result['entry_list']));
    }

    public function testGetRelationshipReturnThirdMeeting()
    {
        $result = $this->soapClient->get_relationships(
            $this->sessionId,
            'Users',
            self::$user->id,
            'meetings',
            '',
            ['id', 'name'],
            '',
            0,
            'date_entered',
            2,
            1
        );
        $result = object_to_array_deep($result);

        $this->assertEquals(1, safeCount($result['entry_list']));
    }

    public function testGetRelationshipOffsetDoesntReturnSameRecords()
    {
        $result1 = $this->soapClient->get_relationships(
            $this->sessionId,
            'Users',
            self::$user->id,
            'meetings',
            '',
            ['id', 'name', 'date_entered'],
            '',
            0,
            'date_entered',
            0,
            2
        );
        $result1 = object_to_array_deep($result1);

        $this->assertEquals(2, safeCount($result1['entry_list']));

        $result2 = $this->soapClient->get_relationships(
            $this->sessionId,
            'Users',
            self::$user->id,
            'meetings',
            '',
            ['id', 'name', 'date_entered'],
            '',
            0,
            'date_entered',
            2,
            2
        );
        $result2 = object_to_array_deep($result2);

        $this->assertEquals(2, safeCount($result2['entry_list']));
    }
}
