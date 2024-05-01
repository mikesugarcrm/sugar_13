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
 * Test Days Before date filter
 */
class Bug60372Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('timedate');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test if days before filter returns proper query
     *
     * @param $daysBefore - Number of days before today
     * @param $expected - Expected generated day
     * @param $currentDate - In regards to current date
     *
     * @dataProvider filterDataProvider
     */
    public function testDateTimeFiscalQueryFilter($qualifier, $days, $expected, $currentDate)
    {
        global $timedate;
        $timedate->setNow($timedate->fromDb($currentDate));

        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('reporter', new Report());
        $SWFDT = new SugarWidgetFieldDateTime($layoutManager);
        $layoutDef = [
            'type' => 'datetime',
            'input_name0' => $days,
        ];

        $result = $SWFDT->$qualifier($layoutDef);

        $this->assertStringContainsString($expected, $result);
    }

    public static function filterDataProvider()
    {
        $db = DBManagerFactory::getInstance();
        return [
            [
                'queryFilterTP_last_n_days',
                5,
                '* >= ' .
                $db->convert($db->quoted('2014-01-26 00:00:00'), 'datetime') . ' AND ' .
                '* <= ' .
                $db->convert($db->quoted('2014-01-30 23:59:59'), 'datetime'),
                '2014-01-30 08:00:00',
            ],
            [
                'queryFilterTP_next_n_days',
                2,
                '* >= ' .
                $db->convert($db->quoted('2014-02-15 00:00:00'), 'datetime') . ' AND ' .
                '* <= ' .
                $db->convert($db->quoted('2014-02-16 23:59:59'), 'datetime'),
                '2014-02-15 07:00:00',
            ],
        ];
    }
}
