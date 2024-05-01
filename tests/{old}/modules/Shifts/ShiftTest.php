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
 * @coversDefaultClass \Shift
 */
class ShiftTest extends TestCase
{
    /**
     * Shift bean
     * @var SugarBean
     */
    private $shift;

    /**
     * Local cache that holds ids for table names that need hard deleting
     * @var array
     */
    private $deleteCache = [];

    /**
     * Mapping of days used for some of our tests
     * @var array
     */
    private static $dayMap = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    ];

    protected function setUp(): void
    {
        \SugarTestHelper::init();
        $this->shift = $this->getDecoratedShiftBean();
    }

    protected function tearDown(): void
    {
        $db = \DBManagerFactory::getInstance();
        foreach ($this->deleteCache as $table => $ids) {
            $in = "'" . implode("','", $ids) . "'";
            $sql = "DELETE FROM $table WHERE id IN ($in)";
            $db->query($sql);
        }
        \SugarTestHelper::tearDown();
    }

    /**
     * Gets a new, stripped down shift bean with just a name
     * @return Shift
     */
    private function getShiftBean()
    {
        $bean = new \Shift();
        $bean->name = 'Test Shift';

        // Since M-F are defaulted as open days we need
        // to force all days closed for the tests that
        // use this particular shift bean
        foreach (static::$dayMap as $day) {
            $prop = 'is_open_' . $day;
            $bean->$prop = 0;
        }

        return $bean;
    }

    /**
     * Utility method to get the bean we need for testing
     * @return Shift
     */
    private function getDecoratedShiftBean()
    {
        $bean = $this->getShiftBean();

        // Needed for time based calculations
        $bean->timezone = 'America/New_York';

        // Start with explicit open markers
        $bean->is_open_sunday = 0;
        $bean->is_open_monday = 0;
        $bean->is_open_tuesday = 1;
        $bean->is_open_wednesday = 1;
        $bean->is_open_thursday = 1;
        $bean->is_open_friday = 1;
        $bean->is_open_saturday = 1;

        // Now handle hours
        $bean->tuesday_open_hour = '09';
        $bean->tuesday_open_minutes = '30';
        $bean->tuesday_close_hour = '17';
        $bean->tuesday_close_minutes = '00';
        $bean->wednesday_open_hour = '07';
        $bean->wednesday_open_minutes = '00';
        $bean->wednesday_close_hour = '19';
        $bean->wednesday_close_minutes = '30';
        $bean->friday_open_hour = '08';
        $bean->friday_open_minutes = '00';
        $bean->friday_close_hour = '17';
        $bean->friday_close_minutes = '15';
        $bean->saturday_open_hour = '10';
        $bean->saturday_open_minutes = '45';
        $bean->saturday_close_hour = '14';
        $bean->saturday_close_minutes = '30';
        $bean->save();

        $this->addBeanToDeleteList($bean);

        return $bean;
    }

    /**
     * Gets the time the day opens or closes
     * @covers ::getTimeForTypeOnDay
     * @dataProvider getTimeProvider
     * @param string $day Day name
     * @param array $expect
     */
    public function testGetTimeForTypeOnDay($day, $expect)
    {
        $actual = $this->shift->getTimeForTypeOnDay($day, 'open');
        $this->assertSame($expect['open'], $actual);

        $actual = $this->shift->getTimeForTypeOnDay($day, 'close');
        $this->assertSame($expect['close'], $actual);
    }

    /**
     * Provider for ::getOpenTime ::getCloseTime ::getTimeForTypeOnDay
     * @return array
     */
    public function getTimeProvider()
    {
        return [
            [
                'day' => 'sunday',
                'expect' => [
                    'open' => ['hour' => 0, 'minutes' => 0],
                    'close' => ['hour' => 0, 'minutes' => 0],
                ],
            ],
            [
                'day' => 'monday',
                'expect' => [
                    'open' => ['hour' => 0, 'minutes' => 0],
                    'close' => ['hour' => 0, 'minutes' => 0],
                ],
            ],
            [
                'day' => 'tuesday',
                'expect' => [
                    'open' => ['hour' => 9, 'minutes' => 30],
                    'close' => ['hour' => 17, 'minutes' => 0],
                ],
            ],
            [
                'day' => 'wednesday',
                'expect' => [
                    'open' => ['hour' => 7, 'minutes' => 0],
                    'close' => ['hour' => 19, 'minutes' => 30],
                ],
            ],
            [
                'day' => 'thursday',
                'expect' => [
                    'open' => ['hour' => 0, 'minutes' => 0],
                    'close' => ['hour' => 0, 'minutes' => 0],
                ],
            ],
            [
                'day' => 'friday',
                'expect' => [
                    'open' => ['hour' => 8, 'minutes' => 0],
                    'close' => ['hour' => 17, 'minutes' => 15],
                ],
            ],
            [
                'day' => 'saturday',
                'expect' => [
                    'open' => ['hour' => 10, 'minutes' => 45],
                    'close' => ['hour' => 14, 'minutes' => 30],
                ],
            ],
        ];
    }

    /**
     * Adds a bean id to the delete list for cleanup
     * @param SugarBean $bean The bean to delete
     */
    private function addBeanToDeleteList(\SugarBean $bean)
    {
        $this->deleteCache[$bean->getTableName()][$bean->id] = $bean->id;
    }
}
