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
 * Test class for SugarACLStrategy.
 */
class SugarACLStrategyTest extends TestCase
{
    /**
     * @var SugarACLStrategy
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = $this->getMockForAbstractClass('SugarACLStrategy');
    }

    /**
     * @covers SugarACLStrategy::getCurrentUser
     */
    public function testGetCurrentUser()
    {
        // Remove the following lines when you implement this test.
        $user1 = $this->createMock('User');
        $context = ['user' => $user1];
        $user2 = $this->createMock('User');

        $this->assertEquals($user1, $this->object->getCurrentUser($context));
        unset($GLOBALS['current_user']);
        $this->assertNull($this->object->getCurrentUser([]));

        $GLOBALS['current_user'] = $user2;
        $this->assertEquals($user2, $this->object->getCurrentUser([]));

        $this->assertEquals($user1, $this->object->getCurrentUser($context));
    }

    /**
     * @covers SugarACLStrategy::getUserID
     */
    public function testGetUserID()
    {
        $user1 = $this->createMock('User');
        $user2 = $this->createMock('User');

        $user1->id = 111;
        $user2->id = 222;

        $this->assertNull($this->object->getUserID([]));

        $GLOBALS['current_user'] = $user2;

        $this->assertEquals($user2->id, $this->object->getUserID([]));

        $this->assertEquals($user1->id, $this->object->getUserID(['user' => $user1]));

        $this->assertEquals(333, $this->object->getUserID(['user_id' => 333]));

        $this->assertEquals($user1->id, $this->object->getUserID(['user_id' => 333, 'user' => $user1]));
    }
}
