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

class RNameLinkFieldTest extends TestCase
{
    /**#@+
     * @var User
     */
    private static $user1;
    private static $user2;
    /**#@-*/

    /**
     * @var Meeting
     */
    private static $meeting;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        self::$user1 = SugarTestUserUtilities::createAnonymousUser();
        self::$user2 = SugarTestUserUtilities::createAnonymousUser();

        self::$meeting = SugarTestMeetingUtilities::createMeeting();
        self::$meeting->set_accept_status(self::$user1, 'accept');
        self::$meeting->set_accept_status(self::$user2, 'decline');
    }

    /**
     * @test
     */
    public function user1AcceptedTheMeeting()
    {
        $this->assertSame('accept', $this->fetchAcceptStatus(self::$user1));
    }

    /**
     * @test
     */
    public function user2DeclinedTheMeeting()
    {
        $this->assertSame('decline', $this->fetchAcceptStatus(self::$user2));
    }

    private function fetchAcceptStatus(User $user)
    {
        $query = new SugarQuery();
        $query->from(self::$meeting);
        $query->select('accept_status_users');
        $query->where()->equals('id', self::$meeting->id);
        $query->setJoinOn(['baseBeanId' => $user->id]);

        $data = $query->execute();

        $this->assertCount(1, $data);
        $row = array_shift($data);

        return $row['accept_status_users'];
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}
