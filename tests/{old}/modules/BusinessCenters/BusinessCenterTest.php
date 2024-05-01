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

namespace Sugarcrm\SugarcrmTestsUnit\modules\BusinessCenter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \BusinessCenter
 */
class BusinessCenterTest extends TestCase
{
    /**
     * Local cache that holds ids for table names that need hard deleting
     * @var array
     */
    private static $deleteCache = [];

    /**
     * BusinessCenter bean
     * @var BusinessCenter
     */
    private static $bc;

    /**
     * BusinessCenter Record view defs
     * @var array
     */
    private static $recordViewDefs = [];

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

    /**
     * Simply loads the environment and sets up some of the needed data
     */
    public static function setUpBeforeClass(): void
    {
        \SugarTestHelper::init();
        static::$bc = static::getDecoratedBusinessCenterBean();
        static::relateBusinessCenterToHolidays(static::$bc);
        static::$recordViewDefs = \MetaDataManager::getManager()->getModuleViewFields('BusinessCenters', 'record');
    }

    public static function tearDownAfterClass(): void
    {
        $db = \DBManagerFactory::getInstance();
        foreach (static::$deleteCache as $table => $ids) {
            $in = "'" . implode("','", $ids) . "'";
            $sql = "DELETE FROM $table WHERE id IN ($in)";
            $db->query($sql);
        }

        // Now delete the related records
        static::$bc->load_relationship('business_holidays');
        $relTable = static::$bc->business_holidays->getRelationshipObject()->getRelationshipTable();
        if ($relTable) {
            $sql = "DELETE FROM $relTable WHERE business_center_id = " . $db->quoted(static::$bc->id);
            $db->query($sql);
        }

        \MetaDataManager::resetManagers();
        \SugarTestHelper::tearDown();
    }

    public function testVardefAndBeanFieldsAlign()
    {
        // Vardef fields array
        $fieldDefs = static::$bc->field_defs;

        foreach (static::$dayMap as $day) {
            // Fields needed to test for positivity
            $om = $day . '_open_minutes';
            $oh = $day . '_open_hour';
            $cm = $day . '_close_minutes';
            $ch = $day . '_close_hour';

            // Assertions against the object for properties
            $this->assertTrue(property_exists(static::$bc, $om));
            $this->assertTrue(property_exists(static::$bc, $oh));
            $this->assertTrue(property_exists(static::$bc, $cm));
            $this->assertTrue(property_exists(static::$bc, $ch));

            // Assertions against the vardef index
            $this->assertArrayHasKey($om, $fieldDefs);
            $this->assertArrayHasKey($oh, $fieldDefs);
            $this->assertArrayHasKey($cm, $fieldDefs);
            $this->assertArrayHasKey($ch, $fieldDefs);

            // Assertions against the vardef field name
            $this->assertSame($fieldDefs[$om]['name'], $om);
            $this->assertSame($fieldDefs[$oh]['name'], $oh);
            $this->assertSame($fieldDefs[$cm]['name'], $cm);
            $this->assertSame($fieldDefs[$ch]['name'], $ch);

            // Assertions against the vardef field labels
            $this->assertSame($fieldDefs[$om]['vname'], 'LBL_' . strtoupper($om));
            $this->assertSame($fieldDefs[$oh]['vname'], 'LBL_' . strtoupper($oh));
            $this->assertSame($fieldDefs[$cm]['vname'], 'LBL_' . strtoupper($cm));
            $this->assertSame($fieldDefs[$ch]['vname'], 'LBL_' . strtoupper($ch));

            // Assertions against the error condition that needed fixing
            $cm = $day . '_closed_minutes';
            $ch = $day . '_closed_hour';
            $this->assertFalse(property_exists(static::$bc, $cm));
            $this->assertFalse(property_exists(static::$bc, $ch));
            $this->assertArrayNotHasKey($cm, $fieldDefs);
            $this->assertArrayNotHasKey($ch, $fieldDefs);
        }
    }

    /**
     * Provider for ::testHasFields
     * @return array
     */
    public function providerHasFields(): array
    {
        return [
            [
                [
                    'timezone' => [
                        'name' => 'timezone',
                        'type' => 'enum',
                    ],
                ],
            ],
            [
                [
                    'address_street' => [
                        'name' => 'address_street',
                        'type' => 'text',
                    ],
                ],
            ],
            [
                [
                    'address_city' => [
                        'name' => 'address_city',
                        'type' => 'varchar',
                    ],
                ],
            ],
            [
                [
                    'address_state' => [
                        'name' => 'address_state',
                        'type' => 'varchar',
                    ],
                ],
            ],
            [
                [
                    'address_postalcode' => [
                        'name' => 'address_postalcode',
                        'type' => 'varchar',
                    ],
                ],
            ],
            [
                [
                    'address_country' => [
                        'name' => 'address_country',
                        'type' => 'varchar',
                    ],
                ],
            ],
        ];
    }

    /**
     * @coversNothing
     * @dataProvider providerHasFields
     * @param array $fields Field definitions.
     */
    public function testHasFields(array $fields)
    {
        $fieldKey = array_keys($fields)[0];
        $this->assertArrayHasKey($fieldKey, static::$bc->field_defs);

        $fieldDef = static::$bc->field_defs[$fieldKey];
        $this->assertEquals($fields[$fieldKey]['name'], $fieldDef['name']);
        $this->assertEquals($fields[$fieldKey]['type'], $fieldDef['type']);
    }

    /**
     * Provider for ::testCheckHasFieldOnRecordView
     * @return array
     */
    public function hasFieldOnRecordViewProvider(): array
    {
        return [
            ['timezone'],
            ['address_street'],
            ['address_city'],
            ['address_state'],
            ['address_postalcode'],
            ['address_country'],
        ];
    }

    /**
     * Checks that the desired fields are on the record view.
     *
     * @coversNothing
     * @param string $fieldName Name of the field which we would like to
     *   confirm is on the record view.
     * @dataProvider hasFieldOnRecordViewProvider
     */
    public function testCheckHasFieldOnRecordView(string $fieldName)
    {
        $this->assertContains($fieldName, static::$recordViewDefs);
    }

    /**
     * Provider for ::testGetNormalizedDay
     * @return array
     */
    public function getNormalizedDayProvider()
    {
        return [
            ['day' => 'su', 'expect' => 'sunday'],
            ['day' => 'sunday', 'expect' => 'sunday'],
            ['day' => 'm', 'expect' => 'monday'],
            ['day' => 'monday', 'expect' => 'monday'],
            ['day' => 't', 'expect' => 'tuesday'],
            ['day' => 'tuesday', 'expect' => 'tuesday'],
            ['day' => 'w', 'expect' => 'wednesday'],
            ['day' => 'wednesday', 'expect' => 'wednesday'],
            ['day' => 'th', 'expect' => 'thursday'],
            ['day' => 'thursday', 'expect' => 'thursday'],
            ['day' => 'f', 'expect' => 'friday'],
            ['day' => 'friday', 'expect' => 'friday'],
            ['day' => 's', 'expect' => 'saturday'],
            ['day' => 'saturday', 'expect' => 'saturday'],
        ];
    }

    /**
     * Tests the day normalizers
     * @covers ::getNormalizedDay
     * @dataProvider getNormalizedDayProvider
     * @param string $day Day name or shortcode
     * @param string $expect Lower case weekday name
     */
    public function testGetNormalizedDay($day, $expect)
    {
        $actual = static::$bc->getNormalizedDay($day);
        $this->assertSame($expect, $actual);
    }

    /**
     * Provider for ::testIsOpen
     * @return array
     */
    public function isOpenProvider()
    {
        return [
            ['day' => 'su', 'expect' => false],
            ['day' => 'sunday', 'expect' => false],
            ['day' => 'm', 'expect' => false],
            ['day' => 'monday', 'expect' => false],
            ['day' => 't', 'expect' => true],
            ['day' => 'tuesday', 'expect' => true],
            ['day' => 'w', 'expect' => true],
            ['day' => 'wednesday', 'expect' => true],
            ['day' => 'th', 'expect' => true],
            ['day' => 'thursday', 'expect' => true],
            ['day' => 'f', 'expect' => true],
            ['day' => 'friday', 'expect' => true],
            ['day' => 's', 'expect' => true],
            ['day' => 'saturday', 'expect' => true],
        ];
    }

    /**
     * Tests whether a day is open
     * @covers ::isOpen
     * @dataProvider isOpenProvider
     * @param string $day Day name or shortcode
     * @param boolean $expect
     */
    public function testIsOpen($day, $expect)
    {
        $actual = static::$bc->isOpen($day);
        $this->assertSame($expect, $actual);
    }

    /**
     * Provider for ::getOpenTime ::getCloseTime ::getTimeForTypeOnDay
     * @return array
     */
    public function getTimeProvider()
    {
        return [
            [
                'day' => 'su',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 'sunday',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 'm',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 'monday',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 't',
                'expect' => [
                    'open' => '0930',
                    'close' => '1700',
                ],
            ],
            [
                'day' => 'tuesday',
                'expect' => [
                    'open' => '0930',
                    'close' => '1700',
                ],
            ],
            [
                'day' => 'w',
                'expect' => [
                    'open' => '0700',
                    'close' => '1930',
                ],
            ],
            [
                'day' => 'wednesday',
                'expect' => [
                    'open' => '0700',
                    'close' => '1930',
                ],
            ],
            [
                'day' => 'th',
                'expect' => [
                    'open' => '0000',
                    'close' => '2359',
                ],
            ],
            [
                'day' => 'thursday',
                'expect' => [
                    'open' => '0000',
                    'close' => '2359',
                ],
            ],
            [
                'day' => 'f',
                'expect' => [
                    'open' => '0800',
                    'close' => '1715',
                ],
            ],
            [
                'day' => 'friday',
                'expect' => [
                    'open' => '0800',
                    'close' => '1715',
                ],
            ],
            [
                'day' => 's',
                'expect' => [
                    'open' => '1045',
                    'close' => '1430',
                ],
            ],
            [
                'day' => 'saturday',
                'expect' => [
                    'open' => '1045',
                    'close' => '1430',
                ],
            ],
        ];
    }

    /**
     * Gets the time the day opens
     * @covers ::getOpenTime
     * @dataProvider getTimeProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetOpenTime($day, $expect)
    {
        $actual = static::$bc->getOpenTime($day);
        $this->assertSame($expect['open'], $actual);
    }

    /**
     * Gets the time the day closes
     * @covers ::getCloseTime
     * @dataProvider getTimeProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetCloseTime($day, $expect)
    {
        $actual = static::$bc->getCloseTime($day);
        $this->assertSame($expect['close'], $actual);
    }

    /**
     * Tests various elements of the hours dropdown list function
     * @covers ::getHoursDropdown
     */
    public function testGetHoursDropdown()
    {
        $data = static::$bc->getHoursDropdown();

        // We should have 24 hours
        $this->assertCount(24, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey(8, $data);
        $this->assertArrayHasKey(23, $data);
        $this->assertArrayNotHasKey(24, $data);
        $this->assertSame('00', $data[0]);
        $this->assertSame('08', $data[8]);
        $this->assertSame('23', $data[23]);
    }

    /**
     * Tests various elements of the hours dropdown list function
     * @covers ::getMinutesDropdown
     */
    public function testGetMinutesDropdown()
    {
        $data = static::$bc->getMinutesDropdown();

        // We should have 60 minutes
        $this->assertCount(60, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey(8, $data);
        $this->assertArrayHasKey(59, $data);
        $this->assertArrayNotHasKey(60, $data);
        $this->assertSame('00', $data[0]);
        $this->assertSame('08', $data[8]);
        $this->assertSame('59', $data[59]);
    }

    /**
     * Tests that the wrong string passed into the time unit dropdown function
     * returns an empty array. Also adds a simple count test for minutes and
     * hours. All other cases are tested above.
     * @covers ::getTimeUnitDropdown
     */
    public function testGetTimeUnitDropdown()
    {
        $this->assertSame([], static::$bc->getTimeUnitDropdown('foo'));
        $this->assertCount(24, static::$bc->getTimeUnitDropdown('hours'));
        $this->assertCount(60, static::$bc->getTimeUnitDropdown('minutes'));

        // The method DOES expect normalized inputs, so test those
        $this->assertSame([], static::$bc->getTimeUnitDropdown('Hours'));
        $this->assertSame([], static::$bc->getTimeUnitDropdown('MINUTES'));
        $this->assertSame([], static::$bc->getTimeUnitDropdown('hour'));
        $this->assertSame([], static::$bc->getTimeUnitDropdown('minute'));
    }

    /**
     * Gets the time the day opens or closes
     * @covers ::getTimeForTypeOnDay
     * @dataProvider getTimeProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetTimeForTypeOnDay($day, $expect)
    {
        $actual = static::$bc->getTimeForTypeOnDay($day, 'open');
        $this->assertSame($expect['open'], $actual);

        $actual = static::$bc->getTimeForTypeOnDay($day, 'close');
        $this->assertSame($expect['close'], $actual);
    }

    /**
     * Provider for ::testGetHoursOpenForDay
     * @return array
     */
    public function getHoursOpenForDayProvider()
    {
        return [
            ['day' => 'su', 'expect' => 0.00],
            ['day' => 'sunday', 'expect' => 0.00],
            ['day' => 'm', 'expect' => 0.00],
            ['day' => 'monday', 'expect' => 0.00],
            ['day' => 't', 'expect' => 7.50],
            ['day' => 'tuesday', 'expect' => 7.50],
            ['day' => 'w', 'expect' => 12.50],
            ['day' => 'wednesday', 'expect' => 12.50],
            ['day' => 'th', 'expect' => 24.00],
            ['day' => 'thursday', 'expect' => 24.00],
            ['day' => 'f', 'expect' => 9.25],
            ['day' => 'friday', 'expect' => 9.25],
            ['day' => 's', 'expect' => 3.75],
            ['day' => 'saturday', 'expect' => 3.75],
            // Tests a garbage day that can't be normalized
            ['day' => 'foo', 'expect' => 0.00],
        ];
    }

    /**
     * Tests calculating the open hours for a day of the week
     * @covers ::getHoursOpenForDay
     * @dataProvider getHoursOpenForDayProvider
     * @param string $day Day name or shortcode
     * @param float $expect
     */
    public function testGetHoursOpenForDay($day, $expect)
    {
        $actual = static::$bc->getHoursOpenForDay($day);
        $this->assertSame($expect, $actual);
    }

    /**
     * Provider for ::testGetIncrementedBusinessDatetime
     * @return array
     */
    public function getIncrementedBusinessDatetimeProvider()
    {
        return [
            // Tuesday, before open should land on the same day
            [
                'date' => '4/9/2019 04:47:00',
                'interval' => 4,
                'unit' => 'hours',
                'expect' => '2019-04-09T13:30:00-04:00',
            ],
            // Tuesday, holiday, no daylight savings
            [
                'date' => '12/24/2019 16:15:00',
                'interval' => 18,
                'unit' => 'hours',
                'expect' => '2019-12-26T17:15:00-05:00',
            ],
            // Monday, start on closed day, in Daylight Savings
            [
                'date' => '4/8/2019 16:15:00',
                'interval' => 6,
                'unit' => 'hours',
                'expect' => '2019-04-09T15:30:00-04:00',
            ],
            // Friday, after hours, land on next day
            [
                'date' => '2/15/2019 21:05:23',
                'interval' => 2.5,
                'unit' => 'hours',
                'expect' => '2019-02-16T13:15:00-05:00',
            ],
            // Saturday, after hours, during Daylight Savings cutover
            [
                'date' => '3/9/2019 14:45:18',
                'interval' => 4,
                'unit' => 'hours',
                'expect' => '2019-03-12T13:30:00-04:00',
            ],
            // Day test, with a holiday
            [
                'date' => '12/24/2019 16:15:00',
                'interval' => 2,
                'unit' => 'days',
                'expect' => '2019-12-27T17:15:00-05:00',
            ],
            // Day test, with an extended holiday and days closed
            [
                'date' => '11/27/2019 19:35:00',
                'interval' => 2,
                'unit' => 'days',
                'expect' => '2019-12-03T17:00:00-05:00',
            ],
            // Tuesday, day test, before open time
            [
                'date' => '5/14/2019 06:22:00',
                'interval' => 2,
                'unit' => 'days',
                'expect' => '2019-05-15T19:30:00-04:00',
            ],
            // Bad unit send back what was presented
            [
                'date' => '5/14/2019 06:22:00',
                'interval' => 2,
                'unit' => 'day',
                'expect' => '2019-05-14T06:22:00-04:00',
            ],
            // No interval send back what was presented
            [
                'date' => '11/27/2019 19:35:00',
                'interval' => 0,
                'unit' => 'days',
                'expect' => '2019-11-27T19:35:00-05:00',
            ],
        ];
    }

    /**
     * Tests getting an incremented time
     * @covers ::getIncrementedBusinessDatetime
     * @dataProvider getIncrementedBusinessDatetimeProvider
     * @group BCCalculations
     * @param string $date The date string to use for an input
     * @param float $interval The interval to get the new date based on
     * @param string $unit Either hours or days
     * @param string $expect The expected date, an ISO8601 version of the new date
     */
    public function testGetIncrementedBusinessDatetime($date, $interval, $unit, $expect)
    {
        $actual = static::$bc->getIncrementedBusinessDatetime($date, $interval, $unit);
        $this->assertSame($expect, $actual);
    }

    /**
     * Provider for ::testGetHolidays
     * @return array
     */
    public function getHolidaysProvider()
    {
        return [
            [
                'from' => '1/1/2019',
                'to' => '1/1/2020',
                'count' => 8,
                'clear' => true,
                'key' => '2019-11-29',
            ],
            [
                'from' => '11/28/2019',
                'to' => '11/29/2019',
                'count' => 2,
                'clear' => true,
                'key' => '2019-11-28',
            ],
            [
                'from' => '12/1/2019',
                'to' => '1/1/2020',
                'count' => 3,
                'clear' => true,
                'key' => '2020-01-01',
            ],
            [
                'from' => '1/2/2020',
                'to' => '1/31/2020',
                'count' => 1,
                'clear' => false,
                'key' => '2020-01-01',
            ],
            [
                'from' => '1/10/2020',
                'to' => '1/14/2020',
                'count' => 1,
                'clear' => false,
                'key' => '2020-01-01',
            ],
            [
                'from' => '2/1/2020',
                'to' => '3/1/2020',
                'count' => 0,
                'clear' => false,
                'key' => '',
            ],
        ];
    }

    /**
     * Tests getting holidays for a date range
     * @covers ::getHolidays
     * @dataProvider getHolidaysProvider
     * @group BCCalculations
     * @param string $from The date to get holidays from
     * @param string $to The date to get holidays to
     * @param int $count Expected count of rows in the array
     * @param bool $clear Should we clear the cache as a part of the test
     * @param string $key If provided, a key expected to be found in the holidays array
     */
    public function testGetHolidays($from, $to, $count, $clear, $key)
    {
        $fromDT = new \SugarDateTime($from);
        $toDT = new \SugarDateTime($to);

        $actual = static::$bc->getHolidays($fromDT, $toDT, $clear);
        $this->assertCount($count, $actual);

        if ($key) {
            $this->assertArrayHasKey($key, $actual);
        }
    }

    /**
     * Provider for ::testGetBusinessTimeBetween
     * @return array
     */
    public function getBusinessTimeBetweenProvider()
    {
        return [
            [
                'start' => '9/1/2019 08:00:00',
                'end' => '9/4/2019 14:00:00',
                'expect' => 14.5,
            ],
            [
                'start' => '9/4/2019 08:00:00',
                'end' => '9/1/2019 14:00:00',
                'expect' => 0,
            ],
            [
                'start' => '8/1/2019 08:00:00',
                'end' => '8/4/2019 14:00:00',
                'expect' => 28.98,
            ],
            [
                'start' => '2019-08-30 09:15:00',
                'end' => '2019-09-04 17:30:00',
                'expect' => 29.75,
            ],
            [
                'start' => '9/25/2019 00:00:00',
                'end' => '9/25/2019 17:15:00',
                'expect' => 10.25,
            ],
            [
                'start' => '9/25/2019 08:00:00',
                'end' => '9/25/2019 08:00:00',
                'expect' => 0,
            ],
        ];
    }

    /**
     * Tests getting business hours for a datetime range
     * @covers ::getBusinessTimeBetween
     * @dataProvider getBusinessTimeBetweenProvider
     * @param string $start The start date to get business time
     * @param string $end The end date to get business time
     * @param float $expect Expected business time
     */
    public function testGetBusinessTimeBetween(string $start, string $end, float $expect)
    {
        $startDT = new \SugarDateTime($start, new \DateTimeZone('America/New_York'));
        $endDT = new \SugarDateTime($end, new \DateTimeZone('America/New_York'));

        $actual = static::$bc->getBusinessTimeBetween($startDT, $endDT);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Provider for ::testGetOpenTimeElements ::testGetCloseTimeElements
     * @return array
     */
    public function getTimeElementsProvider()
    {
        return [
            // Closed day expects 0 for open and close elements
            [
                'day' => 'su',
                'expect' => [
                    'open' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                ],
            ],
            [
                'day' => 'sunday',
                'expect' => [
                    'open' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                ],
            ],
            [
                'day' => 'm',
                'expect' => [
                    'open' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                ],
            ],
            [
                'day' => 'monday',
                'expect' => [
                    'open' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                ],
            ],
            [
                'day' => 't',
                'expect' => [
                    'open' => [
                        'hour' => 9,
                        'minutes' => 30,
                    ],
                    'close' => [
                        'hour' => 17,
                        'minutes' => 0,
                    ],
                ],
            ],
            [
                'day' => 'tuesday',
                'expect' => [
                    'open' => [
                        'hour' => 9,
                        'minutes' => 30,
                    ],
                    'close' => [
                        'hour' => 17,
                        'minutes' => 0,
                    ],
                ],
            ],
            [
                'day' => 'w',
                'expect' => [
                    'open' => [
                        'hour' => 7,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 19,
                        'minutes' => 30,
                    ],
                ],
            ],
            [
                'day' => 'wednesday',
                'expect' => [
                    'open' => [
                        'hour' => 7,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 19,
                        'minutes' => 30,
                    ],
                ],
            ],
            [
                'day' => 'th',
                'expect' => [
                    'open' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 23,
                        'minutes' => 59,
                    ],
                ],
            ],
            [
                'day' => 'thursday',
                'expect' => [
                    'open' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 23,
                        'minutes' => 59,
                    ],
                ],
            ],
            [
                'day' => 'f',
                'expect' => [
                    'open' => [
                        'hour' => 8,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 17,
                        'minutes' => 15,
                    ],
                ],
            ],
            [
                'day' => 'friday',
                'expect' => [
                    'open' => [
                        'hour' => 8,
                        'minutes' => 0,
                    ],
                    'close' => [
                        'hour' => 17,
                        'minutes' => 15,
                    ],
                ],
            ],
            [
                'day' => 's',
                'expect' => [
                    'open' => [
                        'hour' => 10,
                        'minutes' => 45,
                    ],
                    'close' => [
                        'hour' => 14,
                        'minutes' => 30,
                    ],
                ],
            ],
            [
                'day' => 'saturday',
                'expect' => [
                    'open' => [
                        'hour' => 10,
                        'minutes' => 45,
                    ],
                    'close' => [
                        'hour' => 14,
                        'minutes' => 30,
                    ],
                ],
            ],
        ];
    }

    /**
     * Gets the open time elements for the day
     * @covers ::getOpenTimeElements
     * @dataProvider getTimeElementsProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetOpenTimeElements($day, $expect)
    {
        $actual = static::$bc->getOpenTimeElements($day);
        $this->assertSame($expect['open'], $actual);
    }

    /**
     * Gets the close time elements for the day
     * @covers ::getCloseTimeElements
     * @dataProvider getTimeElementsProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetCloseTimeElements($day, $expect)
    {
        $actual = static::$bc->getCloseTimeElements($day);
        $this->assertSame($expect['close'], $actual);
    }

    /**
     * Tests whether a business center has business hours
     * @covers ::hasBusinessHours
     */
    public function testHasBusinessHours()
    {
        // Test false on a new business center with no setup
        $bc = static::getBusinessCenterBean();
        $this->assertFalse($bc->hasBusinessHours());

        // Test true on our decorated business center bean
        $this->assertTrue(static::$bc->hasBusinessHours());
    }

    /**
     * Tests that a business center with no business hours returns the input time
     * unmodified
     * @covers ::getIncrementedBusinessDatetime
     */
    public function testBusinessCenterReturnsInputWhenNotSetup()
    {
        $bc = static::getBusinessCenterBean();

        // This is only to ensure consistency in the test suites, since we don't
        // know what timezone some of our suites run in
        $bc->timezone = 'America/Los_Angeles';

        $actual = $bc->getIncrementedBusinessDatetime('5/14/2019 06:22:00', 8.0, 'hours');
        $this->assertSame('2019-05-14T06:22:00-07:00', $actual);
    }

    /**
     * Provider for ::testCanCalculateIncrement
     * @return array
     */
    public function canCalculateIncrementProvider()
    {
        return [
            // Tests no business hours set
            [
                'open' => false,
                'interval' => 1,
                'unit' => 'days',
                'expect' => false,
            ],
            // Tests bad interval
            [
                'open' => true,
                'interval' => 0,
                'unit' => 'days',
                'expect' => false,
            ],
            // Tests bad unit
            [
                'open' => true,
                'interval' => 0.1,
                'unit' => 'day',
                'expect' => false,
            ],
            // Tests all good
            [
                'open' => true,
                'interval' => 0.1,
                'unit' => 'hours',
                'expect' => true,
            ],
        ];
    }

    /**
     * Tests if a given business center can calculate increments
     * @covers ::canCalculateIncrement
     * @dataProvider canCalculateIncrementProvider
     * @param boolean $open Flag that tells the test method which business center to use
     * @param float $interval The interval to increment by
     * @param string $unit The unit to increment by
     * @param boolean $expect The expected result
     */
    public function testCanCalculateIncrement($open, $interval, $unit, $expect)
    {
        $bc = $open ? static::$bc : static::getBusinessCenterBean();
        $actual = $bc->canCalculateIncrement($interval, $unit);
        $this->assertSame($expect, $actual);
    }

    /**
     * Gets a new, stripped down business center bean with just a name
     * @return BusinessCenter
     */
    private static function getBusinessCenterBean()
    {
        $bean = new \BusinessCenter();
        $bean->name = 'Test Business Center';

        // Since M-F are defaulted as open days we need
        // to force all days closed for the tests that
        // use this particular business center bean
        foreach (static::$dayMap as $day) {
            $prop = 'is_open_' . $day;
            $bean->$prop = 0;
        }

        return $bean;
    }

    /**
     * Utility method to get the bean we need for testing
     * @return BusinessCenter
     */
    private static function getDecoratedBusinessCenterBean()
    {
        $bean = static::getBusinessCenterBean();

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

        // So we have an actual record to be able to get joined records on
        $bean->save();

        // Simulates a retrieve of the record
        $bean->fill_in_additional_detail_fields();

        static::addBeanToDeleteList($bean);

        return $bean;
    }

    /**
     * Creates several holidays and relates them to the test business center
     * @param BusinessCenter $bc The test business center
     */
    private static function relateBusinessCenterToHolidays(\BusinessCenter $bc)
    {
        $dates = [
            1 => '2019-01-01',
            2 => '2019-05-27',
            3 => '2019-07-04',
            4 => '2019-11-28',
            5 => '2019-11-29',
            6 => '2019-12-25',
            7 => '2019-12-31',
            8 => '2020-01-01',
        ];

        // Holder for adding relationships
        $ids = [];

        for ($i = 1; $i < 9; $i++) {
            // Set up the holiday record
            $h = new \Holiday();
            $h->name = 'Business Center Holiday Test ' . $i;
            $h->holiday_date = $dates[$i];
            $h->related_module = 'BusinessCenters';
            $h->related_module_id = $bc->id;

            // Save it
            $h->save();

            // Register it for delete
            static::addBeanToDeleteList($h);

            // Relate it
            $h->load_relationship('business_holidays');
            $h->business_holidays->add($bc->id);
        }
    }

    /**
     * Adds a bean id to the delete list for cleanup
     * @param SugarBean $bean The bean to delete
     */
    private static function addBeanToDeleteList(\SugarBean $bean)
    {
        static::$deleteCache[$bean->getTableName()][$bean->id] = $bean->id;
    }
}
