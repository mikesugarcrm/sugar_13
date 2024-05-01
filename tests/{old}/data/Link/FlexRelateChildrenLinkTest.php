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
 * @covers FlexRelateChildrenLink
 */
class FlexRelateChildrenLinkTest extends TestCase
{
    /**
     * @var Contact
     */
    private static $contact;

    /**#@+
     * @var Task
     */
    private static $task1;
    private static $task2;
    private static $task3;

    /**#@-
     */

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        self::$contact = SugarTestContactUtilities::createContact();
        self::$task1 = SugarTestTaskUtilities::createTask();
        self::$task2 = SugarTestTaskUtilities::createTask();
        self::$task3 = SugarTestTaskUtilities::createTask();

        self::$contact->load_relationship('tasks');
        self::$contact->load_relationship('tasks_parent');

        /** @var Link2 $tasks */
        $tasks = self::$contact->tasks;
        $tasks->add([self::$task1, self::$task2]);

        /** @var Link2 $taskParent */
        $taskParent = self::$contact->tasks_parent;
        $taskParent->add([self::$task2, self::$task3]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testAllTasksAreDisplayed()
    {
        $tasks = $this->selectTasks();

        // make sure result contains all tasks and there are no duplicates
        $this->assertCount(3, $tasks);
        $this->assertContains(self::$task1->id, $tasks);
        $this->assertContains(self::$task2->id, $tasks);
        $this->assertContains(self::$task3->id, $tasks);
    }

    public function testAllTasksAreUnlinked()
    {
        self::$contact->load_relationship('all_tasks');

        /** @var Link2 $allTasks */
        $allTasks = self::$contact->all_tasks;
        $allTasks->delete(self::$contact->id, self::$task1);
        $allTasks->delete(self::$contact->id, self::$task2);
        $allTasks->delete(self::$contact->id, self::$task3);

        // make sure all tasks are unlinked
        $tasks = $this->selectTasks();
        $this->assertCount(0, $tasks);
    }

    private function selectTasks()
    {
        $q = new SugarQuery();
        $q->from(new Task());
        $q->select('id');
        $q->joinSubpanel(self::$contact, 'all_tasks');

        return array_map(function (array $row) {
            return $row['id'];
        }, $q->execute());
    }
}
