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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Calendar;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \CalendarActivity
 */
class CalendarActivityTest extends TestCase
{
    public function calendarActivitiesProvider()
    {
        return [
            'Meeting subject without HTML characters' => [
                $this->createActivity('Meeting', ['name' => 'Hello World']),
                ['name' => 'Hello World'],
            ],
            'Meeting subject with decoded HTML characters' => [
                $this->createActivity('Meeting', ['name' => '<Hello World>']),
                ['name' => '&lt;Hello World&gt;'],
            ],
            'Meeting subject with encoded HTML characters' => [
                $this->createActivity('Meeting', ['name' => 'Hello &lt;br&gt; World']),
                ['name' => 'Hello &lt;br&gt; World'],
            ],
            'Meeting subject with HTML tag' => [
                $this->createActivity('Meeting', ['name' => 'Hello <br> World']),
                ['name' => 'Hello &lt;br&gt; World'],
            ],
            'Call subject without HTML characters' => [
                $this->createActivity('Call', ['name' => 'Hello World']),
                ['name' => 'Hello World'],
            ],
            'Call subject with decoded HTML characters' => [
                $this->createActivity('Call', ['name' => '<Hello World>']),
                ['name' => '&lt;Hello World&gt;'],
            ],
            'Call subject with encoded HTML characters' => [
                $this->createActivity('Call', ['name' => 'Hello &lt;br&gt; World']),
                ['name' => 'Hello &lt;br&gt; World'],
            ],
            'Call subject with HTML tag' => [
                $this->createActivity('Call', ['name' => 'Hello <br> World']),
                ['name' => 'Hello &lt;br&gt; World'],
            ],
            'Task subject without HTML characters' => [
                $this->createActivity('Task', ['name' => 'Hello World']),
                ['name' => 'Hello World'],
            ],
            'Task subject with decoded HTML characters' => [
                $this->createActivity('Task', ['name' => '<Hello World>']),
                ['name' => '&lt;Hello World&gt;'],
            ],
            'Task subject with encoded HTML characters' => [
                $this->createActivity('Task', ['name' => 'Hello &lt;br&gt; World']),
                ['name' => 'Hello &lt;br&gt; World'],
            ],
            'Task subject with HTML tag' => [
                $this->createActivity('Task', ['name' => 'Hello <br> World']),
                ['name' => 'Hello &lt;br&gt; World'],
            ],
        ];
    }

    /**
     * @covers ::prepareActivities
     * @dataProvider calendarActivitiesProvider
     * @param \SugarBean $activityBean
     * @param array $expectedPropertyValues
     */
    public function testPrepareActivities(\SugarBean $activityBean, array $expectedPropertyValues)
    {
        $activity = $this->createPartialMock('\\CalendarActivity', []);
        $activity->sugar_bean = $activityBean;

        $activityList = TestReflection::callProtectedMethod('\\CalendarActivity', 'prepareActivities', [[$activity]]);
        $resultActivity = $activityList[0];

        foreach ($expectedPropertyValues as $property => $value) {
            $this->assertSame($resultActivity->sugar_bean->$property, $value);
        }
    }

    /**
     * @param string $objectName Bean name for the activity (e.g. Task, Meeting, Call)
     * @param array $data Array of properties and values to be assigned
     * @return \SugarBean activity
     */
    private function createActivity($objectName, $data = [])
    {
        $activity = $this->createPartialMock("\\{$objectName}", []);

        foreach ($data as $property => $value) {
            $activity->$property = $value;
        }

        return $activity;
    }
}
