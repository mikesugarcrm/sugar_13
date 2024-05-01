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

class SetValueActionTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->setPreference('datef', 'Y-m-d');
        //Set the time format preference to include seconds since the test uses '2001-01-10 11:45:00' which contains seconds
        $GLOBALS['current_user']->setPreference('timef', 'H:i:s');
    }

    protected function tearDown(): void
    {
        SugarTestTaskUtilities::removeAllCreatedTasks();
    }

    public function testSetValueRemoveNewLinesFromExpression()
    {
        $target = 'name';
        $expr = 'concat("Hello",
" ",
"World")';
        $action = ActionFactory::getNewAction('SetValue', ['target' => $target, 'value' => $expr]);

        $value = SugarTestReflection::getProtectedValue($action, 'expression');
        $this->assertStringNotContainsString("\n", $value);
    }

    public function testGetJavascriptFireReturnsTheCorrectMethod()
    {
        $target = 'name';
        $expr = 'concat("Hello", " ", "World")';
        $action = ActionFactory::getNewAction('SetValue', ['target' => $target, 'value' => $expr]);
        $expected = 'new SUGAR.forms.SetValueAction(\'name\',\'concat(\"Hello\", \" \", \"World\")\')';
        $this->assertEquals($expected, $action->getJavascriptFire());
    }

    public function testSetValues()
    {
        $task = new Task();

        //Test Date value
        $task->date_due = '2001-01-10 11:45:00';
        $target = 'date_start';
        $expr = 'addDays($date_due, -7)';
        $action = ActionFactory::getNewAction('SetValue', ['target' => $target, 'value' => $expr]);
        $action->fire($task);

        $this->assertEquals($task->$target, TimeDate::getInstance()->fromDb('2001-01-10 11:45:00')->get('- 7 days')->asDb());

        //Test string value
        $target = 'name';
        $expr = 'concat("Hello", " ", "World")';
        $action = ActionFactory::getNewAction('SetValue', ['target' => $target, 'value' => $expr]);
        $action->fire($task);
        $this->assertEquals($task->$target, 'Hello World');

        //Test numeric value
        $target = 'name';
        $expr = 'ceiling(pi)';
        $action = ActionFactory::getNewAction('SetValue', ['target' => $target, 'value' => $expr]);
        $action->fire($task);
        $this->assertEquals($task->$target, 4);
    }

    /**
     * check that SetValueAction sets NULL to empty dates
     */
    public function testSetValueActionForEmptyDateWithSaveInDB()
    {
        $task = SugarTestTaskUtilities::createTask();
        $task->date_start = '';
        $task->priority = 'High';
        $action = ActionFactory::getNewAction('SetValue', [
            'target' => 'date_start',
            'value' => 'ifElse(and(equal($priority,"Low"),equal($date_start,"")),now(),$date_start)',
        ]);
        $action->fire($task);
        $this->assertNull($task->date_start);
        $task->save();

        // Be sure that new task will be from DB
        BeanFactory::clearCache();
        $task = BeanFactory::getBean('Tasks', $task->id);
        $this->assertEquals($task->date_start, '');
    }
}
