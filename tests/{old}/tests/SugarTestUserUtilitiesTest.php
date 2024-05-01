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

/**
 * @group utilities
 */
class SugarTestUserUtilitiesTest extends TestCase
{
    private $before_snapshot = [];

    protected function setUp(): void
    {
        $this->before_snapshot = $this->takeUserDBSnapshot();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestUserUtilities::removeAllCreatedUserSignatures();
    }

    private function takeUserDBSnapshot()
    {
        $snapshot = [];
        $query = 'SELECT * FROM users ORDER BY id';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $snapshot[] = $row;
        }
        return $snapshot;
    }

    private function takeTeamDBSnapshot()
    {
        $snapshot = [];
        $query = 'SELECT * FROM teams';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $snapshot[] = $row;
        }
        return $snapshot;
    }

    private function takeSignatureDBSnapshot()
    {
        $snapshot = [];
        $query = 'SELECT * FROM users_signatures';
        $result = $GLOBALS['db']->query($query);

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $snapshot[] = $row;
        }

        return $snapshot;
    }

    public function testCanCreateAnAnonymousUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $this->assertInstanceOf('User', $user);

        $after_snapshot = $this->takeUserDBSnapshot();
        $this->assertNotEquals(
            $this->before_snapshot,
            $after_snapshot,
            'Simply insure that something was added'
        );
    }

    public function testCanCreateAnAnonymousUserButDoNotSaveIt()
    {
        $user = SugarTestUserUtilities::createAnonymousUser(false);

        $this->assertInstanceOf('User', $user);

        $after_snapshot = $this->takeUserDBSnapshot();
        $this->assertEquals(
            $this->before_snapshot,
            $after_snapshot,
            'Simply insure that something was added'
        );
    }

    public function testAnonymousUserHasARandomUserName()
    {
        $first_user = SugarTestUserUtilities::createAnonymousUser();
        $this->assertTrue(!empty($first_user->user_name), 'team name should not be empty');

        $second_user = SugarTestUserUtilities::createAnonymousUser();
        $this->assertNotEquals(
            $first_user->user_name,
            $second_user->user_name,
            'each user should have a unique name property'
        );
    }

    public function testCanTearDownAllCreatedAnonymousUsers()
    {
        $userIds = [];
        $before_snapshot_teams = $this->takeTeamDBSnapshot();
        for ($i = 0; $i < 5; $i++) {
            $userIds[] = SugarTestUserUtilities::createAnonymousUser()->id;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $this->assertEquals(
            $this->before_snapshot,
            $this->takeUserDBSnapshot(),
            'SugarTest_UserUtilities::removeAllCreatedAnonymousUsers() should have removed the users it added'
        );
        $this->assertEquals(
            $before_snapshot_teams,
            $this->takeTeamDBSnapshot(),
            'SugarTest_UserUtilities::removeAllCreatedAnonymousUsers() should have removed the teams it added'
        );

        $count = function ($table, $where) {
            $num = 0;
            $sql = "SELECT COUNT(*) c FROM {$table} WHERE {$where}";
            if ($row = $GLOBALS['db']->fetchByAssoc($GLOBALS['db']->query($sql))) {
                $num = $row['c'];
            }
            return $num;
        };

        $in = "'" . implode("', '", $userIds) . "'";
        $sqls = [
            'email_addresses' => "id IN (SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module ='Users' AND bean_id IN ({$in}))",
            'emails_beans' => "bean_module='Users' AND bean_id IN ({$in})",
            'email_addr_bean_rel' => "bean_module='Users' AND bean_id IN ({$in})",
        ];
        foreach ($sqls as $table => $where) {
            $this->assertEquals(
                0,
                $count($table, $where),
                "Email address references should have been deleted from {$table}"
            );
        }
    }

    public function testCanCreateAUserSignature()
    {
        $beforeSnapshot = $this->takeSignatureDBSnapshot();
        $signature = SugarTestUserUtilities::createUserSignature();

        $this->assertInstanceOf('UserSignature', $signature);

        $afterSnapshot = $this->takeSignatureDBSnapshot();
        $this->assertNotEquals($beforeSnapshot, $afterSnapshot, 'The user signature was not added');
    }

    public function testGetCreatedUserSignatureIds()
    {
        $signature1 = SugarTestUserUtilities::createUserSignature();
        $signature2 = SugarTestUserUtilities::createUserSignature();

        $expected = [
            $signature1->id,
            $signature2->id,
        ];
        $actual = SugarTestUserUtilities::getCreatedUserSignatureIds();
        $this->assertEquals($expected, $actual, 'The wrong user signature IDs were returned');
    }

    public function testCanTearDownAllCreatedUserSignatures()
    {
        $expected = $this->takeSignatureDBSnapshot();

        for ($i = 0; $i < 5; $i++) {
            SugarTestUserUtilities::createUserSignature();
        }

        SugarTestUserUtilities::removeAllCreatedUserSignatures();

        $actual = $this->takeSignatureDBSnapshot();
        $this->assertEquals($expected, $actual, 'The user signatures were not removed');
    }
}
