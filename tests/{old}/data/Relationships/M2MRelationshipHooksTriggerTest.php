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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class M2MRelationshipHooksTriggerTest
 *
 * @covers M2MRelationship
 */
class M2MRelationshipHooksTriggerTest extends TestCase
{
    /** @var Call */
    protected $call;

    /** @var User */
    protected $user;

    /** @var array */
    protected $def;

    /** @var MockObject */
    protected $relationship;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $this->call = SugarTestCallUtilities::createCall();
        $this->user = SugarTestUserUtilities::createAnonymousUser();

        $this->def = [
            'table' => 'calls_users',
            'join_table' => 'calls_users',
            'name' => 'calls_users',
            'lhs_module' => 'Calls',
            'rhs_module' => 'Users',
            'relationship_type' => 'many-to-many',
            'join_key_lhs' => 'call_id',
            'join_key_rhs' => 'user_id',
        ];

        $this->relationship = $this->getMockBuilder('M2MRelationship')
            ->setMethods(['callBeforeAdd',
                'callAfterAdd',
                'callBeforeUpdate',
                'callAfterUpdate',
                'callBeforeDelete',
                'callAfterDelete',
            ])
            ->setConstructorArgs([$this->def])
            ->getMock();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers M2MRelationship::add
     */
    public function testNewRelationTriggersOnlyAddHooks()
    {
        $this->relationship->expects($this->atLeastOnce())->method('callBeforeAdd');
        $this->relationship->expects($this->atLeastOnce())->method('callAfterAdd');
        $this->relationship->expects($this->never())->method('callBeforeUpdate');
        $this->relationship->expects($this->never())->method('callAfterUpdate');
        $this->relationship->expects($this->never())->method('callBeforeDelete');
        $this->relationship->expects($this->never())->method('callAfterDelete');

        $this->call = SugarTestCallUtilities::createCall();
        $this->user = SugarTestUserUtilities::createAnonymousUser();

        $this->relationship->add($this->call, $this->user);
    }

    /**
     * @covers M2MRelationship::add
     */
    public function testUpdatedRelationTriggersOnlyUpdateHooks()
    {
        $this->call->load_relationship('users');
        $this->call->users->add($this->user);
        $this->call->set_accept_status($this->user, 'none');

        $this->relationship->expects($this->never())->method('callBeforeAdd');
        $this->relationship->expects($this->never())->method('callAfterAdd');
        $this->relationship->expects($this->never())->method('callBeforeDelete');
        $this->relationship->expects($this->never())->method('callAfterDelete');

        $this->relationship->add($this->call, $this->user, ['accept_status' => 'tentative']);
    }

    /**
     * @covers M2MRelationship::remove
     */
    public function testRemovedRelationTriggersOnlyDeleteHooks()
    {
        $this->call->load_relationship('users');
        $this->call->users->add($this->user);

        $this->relationship->expects($this->atLeastOnce())->method('callBeforeDelete');
        $this->relationship->expects($this->atLeastOnce())->method('callAfterDelete');
        $this->relationship->expects($this->never())->method('callBeforeAdd');
        $this->relationship->expects($this->never())->method('callAfterAdd');
        $this->relationship->expects($this->never())->method('callBeforeUpdate');
        $this->relationship->expects($this->never())->method('callAfterUpdate');

        $this->relationship->remove($this->call, $this->user);
    }
}
