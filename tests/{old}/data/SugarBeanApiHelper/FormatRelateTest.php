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

class SugarBeanApiHelper_FormatRelateTest extends TestCase
{
    /** @var Account */
    private static $account;

    /** @var Contact */
    private static $contact;

    /** @var Task */
    private static $task;

    public static function setUpBeforeClass(): void
    {
        /** @var User $user */
        $user = SugarTestHelper::setUp('current_user');

        $account = self::$account = SugarTestAccountUtilities::createAccount(null, [
            'assigned_user_id' => $user->id,
        ]);

        $contact = self::$contact = SugarTestContactUtilities::createContact(null, [
            'assigned_user_id' => $user->id,
        ]);

        $task = self::$task = SugarTestTaskUtilities::createTask(null, [
            'parent_type' => $account->module_name,
            'parent_id' => $account->id,
        ]);

        // link relate record
        $task->load_relationship('contacts');
        $task->contacts->add($contact);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testRetrieval()
    {
        /** @var Task $task */
        $task = BeanFactory::retrieveBean(self::$task->module_name, self::$task->id, [
            'use_cache' => false,
        ]);
        $this->assertFormat($task);
    }

    public function testQuery()
    {
        $q = new SugarQuery();
        $q->from(self::$task);
        $q->where()->equals('id', self::$task->id);
        $tasks = self::$task->fetchFromQuery($q);

        $this->assertCount(1, $tasks);
        $task = array_shift($tasks);

        $this->assertFormat($task);
    }

    private function assertFormat(Task $task)
    {
        global $current_user;

        $api = SugarTestRestUtilities::getRestServiceMock();
        $data = ApiHelper::getHelper($api, $task)->formatForApi($task);

        // check that *_owner fields are populated properly on the bean,
        // they are needed for proper population of ACL metadata
        $this->assertEquals($task->parent_name_owner, $current_user->id);
        $this->assertEquals($task->contact_name_owner, $current_user->id);

        // check parent record
        $this->assertArrayHasKey('_acl', $data['parent']);
        $this->assertEquals(self::$account->module_name, $data['parent']['type']);
        $this->assertEquals(self::$account->id, $data['parent']['id']);
        $this->assertEquals(self::$account->name, $data['parent']['name']);

        // check relate record
        $this->assertArrayHasKey('_acl', $data['contacts']);
        $this->assertEquals(self::$contact->id, $data['contacts']['id']);
        $this->assertEquals(self::$contact->name, $data['contacts']['name']);
    }
}
