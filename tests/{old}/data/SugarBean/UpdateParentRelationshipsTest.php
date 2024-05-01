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

class UpdateParentRelationshipsTest extends TestCase
{
    /**#@+
     * @var Account
     */
    private static $account1;
    private static $account2;
    /**#@-*/

    /**
     * @var Call
     */
    private static $call;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        self::$account1 = SugarTestAccountUtilities::createAccount();
        self::$account2 = SugarTestAccountUtilities::createAccount();
        self::$call = SugarTestCallUtilities::createCall();

        // link call to account
        self::$call->load_relationship('accounts');
        self::$call->accounts->add(self::$account1);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testUpdateParentRelationships()
    {
        /** @var Call $call */
        $call = BeanFactory::getBean('Calls', self::$call->id, [
            'use_cache' => false,
        ]);

        $call->load_relationship('accounts');
        $def = SugarRelationshipFactory::getInstance()->getRelationshipDef('account_calls');

        $relationship = $this->getMockBuilder('One2MBeanRelationship')
            ->setConstructorArgs([$def])
            ->setMethods(['callAfterAdd', 'callAfterDelete'])
            ->getMock();

        $linked = $unlinked = [];
        $this->collectInvocations($relationship, 'callAfterAdd', $linked);
        $this->collectInvocations($relationship, 'callAfterDelete', $unlinked);

        SugarTestReflection::setProtectedValue($call->accounts, 'relationship', $relationship);

        // link call to another account
        $call->parent_id = self::$account2->id;
        $call->save();

        // make sure unlink from old account is tracked from both sides
        $this->assertContains([
            self::$call->id,
            self::$account1->id,
            'accounts',
        ], $unlinked);

        $this->assertContains([
            self::$account1->id,
            self::$call->id,
            'calls',
        ], $unlinked);

        // make sure link to new account is tracked from both sides
        $this->assertContains([
            self::$call->id,
            self::$account2->id,
            'accounts',
        ], $linked);

        $this->assertContains([
            self::$account2->id,
            self::$call->id,
            'calls',
        ], $linked);
    }

    public function testUpdateParentRelationshipsResetsParentFields()
    {
        $parentId = '1234';
        $parent_type = 'Documents';

        $task = self::getMockBuilder(Task::class)->setMethods(['load_relationship'])->getMock();
        $task->method('load_relationship')->with('accounts')->willReturnCallback(function ($arg) {
            return $arg == 'accounts';
        });
        $task->accounts =
            self::getMockBuilder(Link2::class)->disableOriginalConstructor()->setMethods(['delete'])->getMock();
        $task->accounts->expects($this->once())
            ->method('delete')
            ->withAnyParameters()
            ->willReturnCallback(function () use ($task) {
                $task->parent_id = null;
            });

        $task->parent_id = $parentId;
        $task->parent_type = $parent_type;
        //Fake that this task was previously associated with an Account
        $task->fetched_row = [
            'parent_id' => 'acct_123',
            'parent_type' => 'Accounts',
        ];

        SugarTestReflection::callProtectedMethod($task, 'update_parent_relationships');

        $this->assertEquals($parent_type, $task->parent_type);
        $this->assertEquals($parentId, $task->parent_id);
    }

    private function collectInvocations(MockObject $mock, $method, &$result)
    {
        $mock->expects($this->any())
            ->method($method)
            ->will($this->returnCallback(function (SugarBean $focus, SugarBean $related, $link) use (&$result) {
                $result[] = [$focus->id, $related->id, $link];
            }));
    }
}
