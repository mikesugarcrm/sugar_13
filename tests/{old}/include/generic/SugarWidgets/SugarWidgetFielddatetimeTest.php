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
 * @coversDefaultClass SugarWidgetFieldDateTime
 */
class SugarWidgetFielddatetimeTest extends TestCase
{
    /**
     * @var SugarWidgetFieldDateTime
     */
    private $widgetField;
    private $meetingIds;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $layoutManager = new LayoutManager();
        $layoutManager->defs['reporter'] = new Report();
        $this->widgetField = new SugarWidgetFieldDateTime($layoutManager);
    }

    protected function tearDown(): void
    {
        unset($this->widgetField);
        if (!empty($this->meetingIds)) {
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . implode("','", $this->meetingIds) . "')");
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Check if the returned data is formatted properly
     *
     * @param array $layout_def Layout def for the field
     * @param string $expected Expected value
     *
     * @dataProvider providerDisplayListweek
     */
    public function testDisplayListweek($layoutDef, $expected)
    {
        $display = $this->widgetField->displayListweek($layoutDef);

        $this->assertEquals($expected, $display);
    }

    /**
     * @return array ($layoutDef, $expected)
     */
    public static function providerDisplayListweek()
    {
        return [
            [
                [
                    'name' => 'date_entered',
                    'column_function' => 'week',
                    'qualifier' => 'week',
                    'table_key' => 'self',
                    'table_alias' => 'opportunities',
                    'column_key' => 'self:date_entered',
                    'type' => 'datetime',
                    'fields' => [
                        'OPPORTUNITIES_WEEK_DAT3634CE' => '2015-19',
                    ],
                ],
                'W19 2015',
            ],
        ];
    }

    /**
     * Check if the returned data is correct ISO Year-Week
     *
     * @param string $date_start Start Date of the meeting
     * @param string $expected Expected ISO Year-Week
     *
     * @dataProvider providerTestQuerySelectWeek
     */
    public function testQuerySelectweek(string $date_start, string $expected)
    {
        $layoutDef = [
            'name' => 'date_start',
            'label' => 'Week: Start Date',
            'column_function' => 'week',
            'qualifier' => 'week',
            'table_key' => 'self',
            'table_alias' => 'meetings',
            'column_key' => 'self:date_start',
            'type' => 'datetimecombo',
            'fields' => [],
        ];
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->name = 'Meeting';
        $meeting->date_start = $date_start;
        $this->meetingIds[] = $meeting->save();

        $query = $this->widgetField->querySelectweek($layoutDef);

        $result = $GLOBALS['db']->query("SELECT {$query} FROM meetings WHERE id = '{$meeting->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($expected, $row['meetings_week_date_start']);
    }

    /**
     * @return array
     */
    public function providerTestQuerySelectWeek()
    {
        return [
            ['2019-12-29 13:00:00', '2019-52'],
            ['2019-12-31 13:00:00', '2020-01'],
            ['2020-01-01 13:00:00', '2020-01'],
            ['2020-12-31 13:00:00', '2020-53'],
            ['2021-01-01 13:00:00', '2020-53'],
            ['2021-01-05 13:00:00', '2021-01'],
        ];
    }
}
