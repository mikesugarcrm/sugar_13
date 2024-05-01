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

class TimeDateTest extends TestCase
{
    /**
     * @var TimeDate
     */
    protected $time_date;

    public const DEFAULT_TIME_FORMAT = 'H:i';

    public static function setUpBeforeClass(): void
    {
        unset($GLOBALS['disable_date_format']);
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->time_date = new TimeDate();
        unset($GLOBALS['disable_date_format']);
        $this->noUserCache();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public static function dateTestSet()
    {
        return [
            ['db' => '2005-10-25 07:00:00', 'df' => 'd-m-Y', 'tf' => '', 'tz' => 'America/Los_Angeles', 'display' => '25-10-2005', 'dbdate' => '2005-10-25 00:00:00'],
            // add times
            ['db' => '2005-10-26 06:42:00', 'df' => 'd-m-Y', 'tf' => 'h.iA', 'tz' => 'America/Los_Angeles', 'display' => '25-10-2005 11.42PM', 'dbdate' => '2005-10-25 23:42:00'],
            // GMT+0 timezone
            ['db' => '2005-11-25 00:00:00', 'df' => 'd-m-Y', 'tf' => '', 'tz' => 'Europe/London', 'display' => '25-11-2005', 'dbdate' => '2005-11-25 00:00:00'],
            // GMT+1
            ['db' => '2005-11-24 23:00:00', 'df' => 'd;m;Y', 'tf' => '', 'tz' => 'Europe/Oslo', 'display' => '25;11;2005', 'dbdate' => '2005-11-25 00:00:00'],
            // DST in effect
            ['db' => '2005-10-24 23:00:00', 'df' => 'd-m-Y', 'tf' => '', 'tz' => 'Europe/London', 'display' => '25-10-2005', 'dbdate' => '2005-10-25 00:00:00'],
            // different format
            ['db' => '1997-10-25 07:00:00', 'df' => 'Y-m-d', 'tf' => '', 'tz' => 'America/Los_Angeles', 'display' => '1997-10-25', 'dbdate' => '1997-10-25 00:00:00'],
            ['db' => '1997-01-25 00:00:00', 'df' => 'm-d-Y', 'tf' => '', 'tz' => 'Europe/London', 'display' => '01-25-1997', 'dbdate' => '1997-01-25 00:00:00'],
            // with times
            ['db' => '2005-10-25 10:42:24', 'df' => 'd/m/Y', 'tf' => 'H:i:s', 'tz' => 'America/Los_Angeles', 'display' => '25/10/2005 03:42:24', 'dbdate' => '2005-10-25 03:42:24'],
            ['db' => '2005-10-25 02:42:24', 'df' => 'd/m/Y', 'tf' => 'H:i:s', 'tz' => 'Europe/London', 'display' => '25/10/2005 03:42:24', 'dbdate' => '2005-10-25 03:42:24'],
            ['db' => '2005-10-25 01:42:24', 'df' => 'd/m/Y', 'tf' => 'H:i:s', 'tz' => 'Asia/Jerusalem', 'display' => '25/10/2005 03:42:24', 'dbdate' => '2005-10-25 03:42:24'],
            // FAIL! FIXME: same format leads to no TZ conversion
            ['db' => '2005-10-25 10:42:24', 'df' => 'Y-m-d', 'tf' => 'H:i:s', 'tz' => 'America/Los_Angeles', 'display' => '2005-10-25 03:42:24', 'dbdate' => '2005-10-25 03:42:24'],
            // short times
            ['db' => '2005-10-25 10:42:00', 'df' => 'd/m/Y', 'tf' => 'H:i', 'tz' => 'America/Los_Angeles', 'display' => '25/10/2005 03:42', 'dbdate' => '2005-10-25 03:42:00'],
            ['db' => '2005-10-25 22:00:00', 'df' => 'd/m/Y', 'tf' => 'ha', 'tz' => 'America/Los_Angeles', 'display' => '25/10/2005 03pm', 'dbdate' => '2005-10-25 15:00:00'],
            ['db' => '2005-10-25 10:00:00', 'df' => 'd/m/Y', 'tf' => 'h', 'tz' => 'America/Los_Angeles', 'display' => '25/10/2005 03', 'dbdate' => '2005-10-25 03:00:00'],
            ['db' => '2005-10-25 20:00:00', 'df' => 'd/m/Y', 'tf' => 'H', 'tz' => 'America/Los_Angeles', 'display' => '25/10/2005 13', 'dbdate' => '2005-10-25 13:00:00'],
            ['db' => '2005-10-25 07:00:00', 'df' => 'd/m/Y', 'tf' => 'ha', 'tz' => 'America/Los_Angeles', 'display' => '25/10/2005 12am', 'dbdate' => '2005-10-25 00:00:00'],
            ['db' => '2005-10-25 19:00:00', 'df' => 'd/m/Y', 'tf' => 'ha', 'tz' => 'America/Los_Angeles', 'display' => '25/10/2005 12pm', 'dbdate' => '2005-10-25 12:00:00'],
        ];
    }

    public static function dateTestSetNonEmptyTimeFormat()
    {
        foreach (self::dateTestSet() as $set) {
            if (empty($set['tf'])) {
                continue;
            }

            yield $set;
        }
    }

    public function timetestSet()
    {
        return [
            // full time
            ['db' => '11:45:00', 'tf' => '', 'display' => '11:45'],
            ['db' => '05:17:28', 'tf' => 'H.i.s', 'display' => '05.17.28'],
            // short ones
            ['db' => '17:34:00', 'tf' => 'H:i', 'display' => '17:34'],
            ['db' => '11:42:00', 'tf' => 'h.iA', 'display' => '11.42AM'],
            ['db' => '15:00:00', 'tf' => 'ha', 'display' => '03pm'],
            ['db' => '15:00:00', 'tf' => 'H', 'display' => '15'],
            // FIXME: is this a valid format? it doesn't allow roundtrip
            ['db' => '03:00:00', 'tf' => 'h', 'display' => '03'],
            // weirdo
            ['db' => '16:42:34', 'tf' => 's:i:H', 'display' => '34:42:16'],
        ];
    }

    private function noUserCache()
    {
        $this->time_date->allow_cache = false;
    }

    private function setPrefs($datef, $timef, $tz)
    {
        $GLOBALS['current_user']->setPreference('datef', $datef);
        $GLOBALS['current_user']->setPreference('timef', $timef);
        $GLOBALS['current_user']->setPreference('timezone', $tz);
        // new object to avoid TZ caching
        $this->time_date = new TimeDate();
        $this->noUserCache();
    }

    private function dateOnly($datetime)
    {
        // FIXME: assumes dates have no spaces
        $dt = explode(' ', $datetime);
        return $dt[0];
    }

    private function timeOnly($datetime)
    {
        // FIXME: assumes dates have no spaces
        $dt = explode(' ', $datetime);
        if (count($dt) > 1) {
            return $dt[1];
        }
        return $datetime;
    }

    /**
     * test conversion from local datetime to DB datetime
     * @dataProvider dateTestSet
     */
    public function testToDbFormats($db, $df, $tf, $tz, $display, $dbdate)
    {
        $tf = empty($tf) ? self::DEFAULT_TIME_FORMAT : $tf;
        $this->setPrefs($df, $tf, $tz);
        $this->assertEquals(
            $db,
            $this->time_date->to_db($display),
            "Broken conversion for '$df $tf' with date '$display' and TZ $tz"
        );
    }

    /**
     * test conversion from full local datetime to DB date
     * @dataProvider dateTestSet
     */
    public function testToDbDateFormatsWithOffset($db, $df, $tf, $tz, $display, $dbdate)
    {
        $tf = empty($tf) ? self::DEFAULT_TIME_FORMAT : $tf;
        $this->setPrefs($df, $tf, $tz);
        $this->assertEquals(
            $this->dateOnly($db),
            $this->time_date->to_db_date($display, true),
            "Broken conversion for '{$df} $tf' with date '{$display}' and TZ {$tz}"
        );
    }

    /**
     * test conversion from local date to DB date, no TZ handling
     * @dataProvider dateTestSet
     */
    public function testToDbDateFormatsNoOffset($db, $df, $tf, $tz, $display, $dbdate)
    {
        $tf = empty($tf) ? self::DEFAULT_TIME_FORMAT : $tf;
        $this->setPrefs($df, $tf, $tz);
        $this->assertEquals(
            $this->dateOnly($dbdate),
            $this->time_date->to_db_date($this->dateOnly($display), false),
            "Broken conversion for '{$df} $tf' with date '{$display}' and TZ {$tz}"
        );
    }

    /**
     * test conversion from full local datetime to DB time
     * @dataProvider dateTestSet
     */
    public function testToDbTimeFormatsWithTz($db, $df, $tf, $tz, $display, $dbdate)
    {
        $tf = empty($tf) ? self::DEFAULT_TIME_FORMAT : $tf;
        $this->setPrefs($df, $tf, $tz);
        if (strpos($display, ' ') === false) {
            $display = $this->time_date->expandDate($display, "$df $tf");
        }
        $this->assertEquals(
            $this->timeOnly($db),
            $this->time_date->to_db_time($display, true),
            "Broken conversion for '{$df} $tf' with date '{$display}' and TZ {$tz}"
        );
    }

    /**
     * test conversion from local time to DB time, no TZ handling
     * @dataProvider timeTestSet
     */
    public function testToDbTimeFormatsNoTz($db, $tf, $display)
    {
        $tf = empty($tf) ? self::DEFAULT_TIME_FORMAT : $tf;
        $this->setPrefs('Y-m-d', $tf, '');
        $this->assertEquals(
            $db,
            $this->time_date->to_db_time($display, false),
            "Broken conversion for '$tf' with date '{$display}'"
        );
    }

    /**
     * test conversion from local date+time to DB date+time, no TZ handling
     * @dataProvider dateTestSet
     */
    public function testToDbDateTimeFormats($db, $df, $tf, $tz, $display, $dbdate)
    {
        $tf = empty($tf) ? self::DEFAULT_TIME_FORMAT : $tf;
        $this->setPrefs($df, $tf, $tz);
        $dt = explode(' ', $display);
        if (count($dt) > 1) {
            [$date, $time] = $dt;
        } else {
            $date = $dt[0];
            $z = new DateTime('@0', new DateTimeZone('GMT'));
            $time = $z->format($tf);
        }
        $this->assertEquals(
            explode(' ', $dbdate),
            $this->time_date->to_db_date_time($date, $time),
            "Broken conversion for '{$df} $tf' with date '{$display}' and TZ {$tz}"
        );
    }


    /**
     * test conversion from DB date+time to local date+time with TZ handling
     * @dataProvider dateTestSet
     */
    public function testToDisplayDateTimeFormats($db, $df, $tf, $tz, $display, $dbdate)
    {
        $this->setPrefs($df, $tf, $tz);
        $result = $this->time_date->to_display_date_time($db, true, true, $GLOBALS['current_user']);
        if (empty($tf)) {
            $result = $this->dateOnly($result);
        }
        $this->assertEquals(
            $display,
            $result,
            "Broken conversion for '$df' with date '{$db}' and TZ {$tz}"
        );
    }

    /**
     * test conversion from DB date+time to local date+time without TZ handling
     * @dataProvider dateTestSet
     */
    public function testToDisplayFormatsNoTz($db, $df, $tf, $tz, $display, $dbdate)
    {
        $this->setPrefs($df, $tf, $tz);
        if (!empty($tf)) {
            $df .= " $tf";
        }
        $result = $this->time_date->to_display($dbdate, $this->time_date->get_db_date_time_format(), $df);
        if (empty($tf)) {
            $result = $this->dateOnly($result);
        }
        $this->assertEquals(
            $display,
            $result,
            "Broken conversion for '$df' with date '{$db}' and TZ {$tz}"
        );
    }

    /**
     * test conversion from DB time to local time without TZ conversion
     * @dataProvider timeTestSet
     */
    public function testToDisplayTimeFormatsNoTZ($db, $tf, $display)
    {
        $this->setPrefs('Y-m-d', $tf, '');
        $this->assertEquals(
            $this->timeOnly($display),
            $this->time_date->to_display_time($db, true, false),
            "Broken conversion for '$tf' with date '{$db}'"
        );
    }

    /**
     * Test conversion from DB time to local time with TZ conversion
     *
     * @dataProvider dateTestSetNonEmptyTimeFormat
     */
    public function testToDisplayTimeFormatsWithTZ($db, $df, $tf, $tz, $display)
    {
        $this->setPrefs($df, $tf, $tz);
        $result = $this->time_date->to_display_time($db, true, true);
        $result = $this->timeOnly($result);
        $this->assertEquals(
            $this->timeOnly($display),
            $result,
            "Broken conversion for '{$tf}' with date '{$db}' and TZ {$tz}"
        );
    }

    /**
     * test conversion from DB date to local date without TZ handling
     * @dataProvider dateTestSet
     */
    public function testToDisplayDateFormatsNoTz($db, $df, $tf, $tz, $display, $dbdate)
    {
        $this->setPrefs($df, $tf, $tz);
        $result = $this->time_date->to_display_date($this->dateOnly($dbdate), false);
        $this->assertEquals(
            $this->dateOnly($display),
            $this->dateOnly($result),
            "Broken conversion for '{$df}' with date '{$dbdate}' and TZ {$tz}"
        );
    }

    /**
     * test conversion from DB date to local date with TZ handling
     * @dataProvider dateTestSet
     */
    public function testToDisplayDateFormatsWithTz($db, $df, $tf, $tz, $display, $dbdate)
    {
        $this->setPrefs($df, $tf, $tz);
        $result = $this->time_date->to_display_date($db, true);
        $this->assertEquals(
            $this->dateOnly($display),
            $this->dateOnly($result),
            "Broken conversion for '{$df}' with date '{$dbdate}' and TZ {$tz}"
        );
    }

    public function midnightDataSet()
    {
        return [
            ['tf' => 'H:i', 'time' => '00:00'],
            ['tf' => 'H:i:s', 'time' => '00:00:00'],
            ['tf' => 'h:i', 'time' => '12:00'],
            ['tf' => 'h:i:s', 'time' => '12:00:00'],
            ['tf' => 'h`iA', 'time' => '12`00AM'],
            ['tf' => 'h`i`sa', 'time' => '12`00`00am'],
        ];
    }

    /**
     * test midnight formatting
     * @dataProvider midnightDataSet
     */
    public function testGetMidnight($tf, $time)
    {
        $this->setPrefs('', $tf, 'America/Los_Angeles');
        $this->assertEquals(
            $time,
            $this->time_date->get_default_midnight(),
            "Bad midnight value for $time format $tf"
        );
    }

    public function testSwapFormatsWithTheSameDateFormat()
    {
        $original_date = '2005-12-25';
        $original_format = 'Y-m-d';
        $new_format = $original_format;
        $expected_new_date = $original_date;

        $new_date = $this->time_date->swap_formats(
            $original_date,
            $original_format,
            $new_format
        );

        $this->assertEquals($expected_new_date, $new_date);
    }

    public function testSwapFormatsFromMdyFormatToDmyFormat()
    {
        $original_date = '12-25-2005';
        $original_format = 'm-d-Y';
        $new_format = 'd-m-Y';
        $expected_new_date = '25-12-2005';

        $new_date = $this->time_date->swap_formats(
            $original_date,
            $original_format,
            $new_format
        );

        $this->assertEquals(
            $expected_new_date,
            $new_date,
            "Convert from $original_format to $new_format failed."
        );
    }

    public function testSwapFormatsWithTheSameDatetimeFormat()
    {
        $original_date = '2005-12-25 12:55:35';
        $original_format = 'Y-m-d H:i:s';
        $new_format = $original_format;
        $expected_new_date = $original_date;

        $new_date = $this->time_date->swap_formats(
            $original_date,
            $original_format,
            $new_format
        );

        $this->assertEquals(
            $expected_new_date,
            $new_date,
            'Same datetime format not returned.'
        );
    }

    public function testSwapFormatsFromYmdhiFormatToYmdhisFormat()
    {
        $original_date = '2005-12-25 12:55';
        $original_format = 'Y-m-d H:i';
        $new_format = 'Y-m-d H:i:s';
        $expected_new_date = '2005-12-25 12:55:00';

        $new_date = $this->time_date->swap_formats(
            $original_date,
            $original_format,
            $new_format
        );

        $this->assertEquals($expected_new_date, $new_date);
    }

    public function testSwapFormatsFromYmdhiFormatToYmdhiaFormat()
    {
        $original = '2005-12-25 13:55';
        $original_format = 'Y-m-d H:i';
        $new_format = 'Y-m-d h:ia';
        $expected = '2005-12-25 01:55pm';

        $new = $this->time_date->swap_formats(
            $original,
            $original_format,
            $new_format
        );

        $this->assertEquals($expected, $new);
    }

    public function testAllDateFormatSwappingCombinations()
    {
        $orig_formats_and_dates = [
            'Y-m-d' => '2006-12-23',
            'm-d-Y' => '12-23-2006',
            'd-m-Y' => '23-12-2006',
            'Y/m/d' => '2006/12/23',
            'm/d/Y' => '12/23/2006',
            'd/m/Y' => '23/12/2006',
        ];

        $new_formats_and_dates = $orig_formats_and_dates;

        foreach ($orig_formats_and_dates as $orig_format => $orig_date) {
            foreach ($new_formats_and_dates as $new_format => $expected_date) {
                $new_date = $this->time_date->swap_formats(
                    $orig_date,
                    $orig_format,
                    $new_format
                );

                $this->assertEquals(
                    $expected_date,
                    $new_date,
                    "Convert from $orig_format to $new_format failed."
                );

                if ($expected_date != $new_date) {
                    return;
                }
            }
        }
    }

    /**
     * @ticket 17528
     */
    public function testSwapDatetimeFormatToDbFormat()
    {
        $date = '10-25-2007 12:00am';
        $format = $this->time_date->get_date_time_format();
        $db_format = $this->time_date->get_db_date_time_format();

        $this->assertEquals(
            $this->time_date->swap_formats(
                $date,
                'm-d-Y h:ia',
                $this->time_date->get_db_date_time_format()
            ),
            '2007-10-25 00:00:00'
        );
    }

    /**
     * @ticket 17528
     */
    public function testTodbCanHandleDdmmyyyyFormats()
    {
        $old_pattern = $GLOBALS['current_user']->getPreference('datef');
        $GLOBALS['current_user']->setPreference('datef', 'd-m-Y');
        $db_date_pattern = '/2007-10-25 [0-9]{2}:[0-9]{2}:[0-9]{2}/';
        $this->assertMatchesRegularExpression(
            $db_date_pattern,
            $this->time_date->to_db('25-10-2007')
        );

        $this->noUserCache();
        $GLOBALS['current_user']->setPreference('datef', 'm-d-Y');
        $this->assertMatchesRegularExpression(
            $db_date_pattern,
            $this->time_date->to_db('10-25-2007')
        );
        $GLOBALS['current_user']->setPreference('datef', $old_pattern);
    }

    /**
     * @ticket 17528
     */
    public function testTodbCanHandleMmddyyyyFormats()
    {
        $old_date = $GLOBALS['current_user']->getPreference('datef');

        $GLOBALS['current_user']->setPreference('datef', 'm-d-Y');
        $db_date_pattern = '/2007-10-25 [0-9]{2}:[0-9]{2}:[0-9]{2}/';
        $this->assertMatchesRegularExpression(
            $db_date_pattern,
            $this->time_date->to_db('10-25-2007')
        );

        $GLOBALS['current_user']->setPreference('datef', $old_date);
    }

    /**
     * @ticket 17528
     */
    public function testTodbdateCanHandleDdmmyyyyFormats()
    {
        $old_date = $GLOBALS['current_user']->getPreference('datef');

        $GLOBALS['current_user']->setPreference('datef', 'd-m-Y');
        $this->assertEquals(
            $this->time_date->to_db_date('25-10-2007'),
            '2007-10-25'
        );

        $GLOBALS['current_user']->setPreference('datef', $old_date);
    }

    /**
     * @ticket 17528
     */
    public function testTodbdateCanHandleMmddyyyyFormats()
    {
        $old_date = $GLOBALS['current_user']->getPreference('datef');
        $GLOBALS['current_user']->setPreference('datef', 'm-d-Y');
        $this->assertEquals(
            '2007-10-25',
            $this->time_date->to_db_date('10-25-2007')
        );

        $GLOBALS['current_user']->setPreference('datef', $old_date);
    }

    public function testConvertMmddyyyyFormatToYyyymmdd()
    {
        $this->assertEquals(
            '2007-11-02',
            $this->time_date->swap_formats(
                '11-02-2007',
                'm-d-Y',
                'Y-m-d'
            )
        );
    }

    public function testGeneratingDefaultMidnight()
    {
        if (!is_callable([$this->time_date, 'get_default_midnight'])) {
            $this->markTestSkipped('Method is no longer public');
        }
        $old_time = $GLOBALS['current_user']->getPreference('timef');

        $GLOBALS['current_user']->setPreference('timef', 'H:i:s');
        $this->assertEquals(
            '00:00:00',
            $this->time_date->get_default_midnight()
        );

        $GLOBALS['current_user']->setPreference('timef', 'h:ia');
        $this->assertEquals(
            '12:00am',
            $this->time_date->get_default_midnight()
        );

        $GLOBALS['current_user']->setPreference('timef', $old_time);
    }

    /**
     * tests for check_matching_format
     * @dataProvider dateTestSet
     */
    public function testCheckMatchingFormats($db, $df, $tf, $tz, $display, $dbdate)
    {
        if (!empty($tf)) {
            $df = $this->time_date->merge_date_time($df, $tf);
        }
        $this->assertTrue(
            $this->time_date->check_matching_format($display, $df),
            "Broken match for '$df' with date '{$display}'"
        );
    }

    public function badMatchTestSet()
    {
        return [
            ['format' => 'Y-m-d', 'date' => ''],
            ['format' => 'Y-m-d', 'date' => 'blah-blah-blah'],
            ['format' => 'Y-m-d', 'date' => '1-2'],
            ['format' => 'Y-m-d', 'date' => '2007-10'],
            //FIXME: array("format" => "Y-m-d", "date" => "200-10-25"),
            ['format' => 'Y-m-d', 'date' => '2007-101-25'],
            ['format' => 'Y-m-d', 'date' => '2007-Oct-25'],
            //FIXME: array("format" => "Y-m-d", "date" => "2007-10-250"),
            ['format' => 'd-m-Y', 'date' => '2007-10-25'],
            ['format' => 'd-m-Y', 'date' => '10/25/2007'],
            //FIXME: array("format" => "Y-m-d", "date" => "here: 2007-20-25"),
            //FIXME: array("format" => "Y-m-d", "date" => "2007-20-25 here"),
        ];
    }

    /**
     * tests for check_matching_format
     * @dataProvider badMatchTestSet
     */
    public function testCheckbadMatchingFormats($format, $date)
    {
        // Some bad dates not detected by current code, it's too lenient
        $this->assertFalse(
            $this->time_date->check_matching_format($date, $format),
            "Broken match for '$format' with date '$date'"
        );
    }

    /**
     * test fetching user settings
     */
    public function testGetUserSettings()
    {
        $this->setPrefs('d/m/Y', 'h:i:sA', 'America/Lima');
        $this->assertEquals('dd/mm/yyyy', $this->time_date->get_user_date_format());
        //FIXME: $this->assertEquals('11:00:00PM', $this->time_date->get_user_time_format());
        $tz = $this->time_date->getUserTimeZone();
        $this->assertEquals(-300, $tz['gmtOffset']);
        //      $this->assertEquals(60, $tz["dstOffset"]);
    }

    /**
     * test getting GMT dates
     */
    public function testGetGMT()
    {
        $gmt = $this->time_date->nowDb();
        $dt = date_parse_from_format('Y-m-d H:i:s', $gmt);
        $this->assertEquals($dt['year'], gmdate('Y'));
        $this->assertEquals($dt['month'], gmdate('m'));
        $this->assertEquals($dt['day'], gmdate('d'));

        $gmt = $this->time_date->nowDb();
        $dt = date_parse_from_format('Y-m-d', $gmt);
        $this->assertEquals($dt['year'], gmdate('Y'));
        $this->assertEquals($dt['month'], gmdate('m'));
        $this->assertEquals($dt['day'], gmdate('d'));
    }

    /**
     * test getting DB date formats indifferent ways
     */
    public function testGetDB()
    {
        $this->assertEquals(
            gmdate($this->time_date->merge_date_time(
                $this->time_date->get_db_date_format(),
                $this->time_date->get_db_time_format()
            )),
            $this->time_date->nowDb()
        );
    }

    public function testGetCalFormats()
    {
        $cal_tests = [
            ['df' => 'Y-m-d', 'caldf' => '%Y-%m-%d', 'tf' => 'H:i:s', 'caltf' => '%H:%M:%S'],
            ['df' => 'd/m/Y', 'caldf' => '%d/%m/%Y', 'tf' => 'h:i:sa', 'caltf' => '%I:%M:%S%P'],
            ['df' => 'm/d/Y', 'caldf' => '%m/%d/%Y', 'tf' => 'H:i', 'caltf' => '%H:%M'],
            ['df' => 'Y-m-d', 'caldf' => '%Y-%m-%d', 'tf' => 'h:iA', 'caltf' => '%I:%M%p'],
        ];
        foreach ($cal_tests as $datetest) {
            $this->setPrefs($datetest['df'], $datetest['tf'], 'America/Los_Angeles');
            $this->assertEquals(
                $datetest['caldf'],
                $this->time_date->get_cal_date_format(),
                "Bad cal date format for '{$datetest['df']}'"
            );
            $this->assertEquals(
                $datetest['caltf'],
                $this->time_date->get_cal_time_format(),
                "Bad cal time format for '{$datetest['tf']}'"
            );
            $this->assertEquals(
                $this->time_date->merge_date_time($datetest['caldf'], $datetest['caltf']),
                $this->time_date->get_cal_date_time_format(),
                "Bad cal datetime format for '{$datetest['df']} {$datetest['tf']}'"
            );
        }
    }

    public function dayDataSet()
    {
        return [
            ['date' => '2010-05-19', 'start' => '2010-05-19 07:00:00', 'end' => '2010-05-20 06:59:59', 'tz' => 'America/Los_Angeles'],
            ['date' => '2010-01-19', 'start' => '2010-01-19 08:00:00', 'end' => '2010-01-20 07:59:59', 'tz' => 'America/Los_Angeles'],
            ['date' => '2010-05-19', 'start' => '2010-05-18 23:00:00', 'end' => '2010-05-19 22:59:59', 'tz' => 'Europe/London'],
            ['date' => '2010-01-19', 'start' => '2010-01-19 00:00:00', 'end' => '2010-01-19 23:59:59', 'tz' => 'Europe/London'],
            ['date' => '2010-05-19', 'start' => '2010-05-18 22:00:00', 'end' => '2010-05-19 21:59:59', 'tz' => 'Europe/Oslo'],
        ];
    }

    /**
     * test for handleOffsetMax
     * @dataProvider dayDataSet
     */
    public function testDayMinMax($date, $start, $end, $tz)
    {
        $this->setPrefs('', '', $tz);
        $dates = $this->time_date->handleOffsetMax($date);
        $this->assertEquals(
            $start,
            $dates['min'],
            "Bad min result for {$date} tz {$tz}"
        );
        $this->assertEquals(
            $end,
            $dates['max'],
            "Bad max result for {$date} tz {$tz}"
        );
    }

    /**
     * test for getDayStartEndGMT
     * @dataProvider dayDataSet
     */
    public function testGetDayStartEnd($date, $start, $end, $tz)
    {
        $this->setPrefs('m/d/Y', '', $tz);
        $date_arr = explode('-', $date);
        $date = $date_arr[1] . '/' . $date_arr[2] . '/' . $date_arr[0];
        $dates = $this->time_date->getDayStartEndGMT($date);
        $this->assertEquals(
            $start,
            $dates['start'],
            "Bad min result for {$date} tz {$tz}"
        );
        $this->assertEquals(
            $end,
            $dates['end'],
            "Bad max result for {$date} tz {$tz}"
        );
    }

    public function ampmDataSet()
    {
        return [
            ['date' => '05:17:28', 'mer' => 'am', 'tf' => 'H:i:s', 'display' => '05:17:28'],
            ['date' => '05:17:28', 'mer' => 'am', 'tf' => 'h:i:sa', 'display' => '05:17:28am'],
            // short ones
            ['date' => '17:34', 'mer' => 'pm', 'tf' => 'H:i', 'display' => '17:34'],
            ['date' => '11:42', 'mer' => 'PM', 'tf' => 'h:iA', 'display' => '11:42PM'],
            ['date' => '11:42', 'mer' => 'pm', 'tf' => 'h:iA', 'display' => '11:42pm'],
            ['date' => '03', 'mer' => 'AM', 'tf' => 'ha', 'display' => '03AM'],
            ['date' => '15', 'mer' => 'AM', 'tf' => 'H', 'display' => '15'],
            // bug 58924 - what if we have spaces in our time format?
            ['date' => '11.13', 'mer' => 'AM', 'tf' => 'h.i A', 'display' => '11.13 AM'],
            ['date' => '11.14', 'mer' => 'PM', 'tf' => 'h.i A', 'display' => '11.14 PM'],
            ['date' => '10.15', 'mer' => 'am', 'tf' => 'h.i a', 'display' => '10.15 am'],
        ];
    }

    /**
     * test for merge_time_meridiem
     * @dataProvider ampmDataSet
     */
    public function testMergeAmPm($date, $mer, $tf, $display)
    {
        $amdate = $this->time_date->merge_time_meridiem($date, $tf, $mer);
        $this->assertEquals(
            $display,
            $amdate,
            "Bad min result for {$date} format {$tf}"
        );
    }

    public function providerSplitDateTime()
    {
        return [
            ['2009-10-04 02:00:00', '2009-10-04', '02:00:00'],
            ['10/04/2010 2:00pm', '10/04/2010', '2:00pm'],
            ['10-04-2010 2:00', '10-04-2010', '2:00'],
        ];
    }

    /**
     * @dataProvider providerSplitDateTime
     */
    public function testSplitDateTime(
        $datetime,
        $date,
        $time
    ) {

        $this->assertEquals($date, $this->time_date->getDatePart($datetime));
        $this->assertEquals($time, $this->time_date->getTimePart($datetime));
    }

    public function testNoCache()
    {
        $this->setPrefs('Y-m-d', 'H:i:s', 'GMT');
        $now1 = $this->time_date->now();
        sleep(2);
        $now2 = $this->time_date->now();
        $this->assertNotEquals($now1, $now2, 'now() should produce different result when not cached');
    }

    public function testCache()
    {
        $this->setPrefs('Y-m-d', 'H:i:s', 'GMT');
        $this->time_date->allow_cache = true;
        $now1 = $this->time_date->now();
        sleep(2);
        $now2 = $this->time_date->now();
        $this->assertEquals($now1, $now2, 'now() should produce same result when cached');
    }

    public function stringFormats()
    {
        return [
            ['H:i', '15:38', '15:38:00'],
            ['h:ia', '03:38pm', '15:38:00'],
            ['h:iA', '03:38PM', '15:38:00'],
            ['h:ia', '03:38am', '03:38:00'],
            ['h:iA', '03:38AM', '03:38:00'],
            ['H.i', '15.38', '15:38:00'],
            ['h.ia', '03.38pm', '15:38:00'],
            ['h.iA', '03.38PM', '15:38:00'],
            ['h:ia', '03:38 pm', '15:38:00'],
            ['h:iA', '03:38 PM', '15:38:00'],
            ['h.ia', '03.38am', '03:38:00'],
            ['h.iA', '03.38AM', '03:38:00'],
            ['h:ia', '03:38 am', '03:38:00'],
            ['h:iA', '03:38 AM', '03:38:00'],
        ];
    }

    /**
     * @dataProvider stringFormats
     * @param string $format
     * @param string $string
     * @param string $result
     */
    public function testCreateFromString($format, $string, $result)
    {
        $this->setPrefs('Y-m-d', $format, 'GMT');
        $tz = new DateTimeZone('GMT');
        $date = SugarDateTime::createFromFormat($format, $string, $tz);
        $this->assertInstanceOf('SugarDateTime', $date, "Parsing $string failed with PHP parser");
        $this->assertEquals($result, $this->time_date->getTimePart($date->asDb()));
    }

    public function testGetTimeDate()
    {
        global $current_user;
        $this->setPrefs('Y-m-d', 'H:i', 'GMT');

        $f = $this->time_date->get_date_time_format();
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(true);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(false);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(null);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(true, null);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(false, null);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(null, null);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(true, $current_user);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(false, $current_user);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format(null, $current_user);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format($current_user);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format($current_user);
        $this->assertEquals('Y-m-d H:i', $f);

        $f = $this->time_date->get_date_time_format($current_user);
        $this->assertEquals('Y-m-d H:i', $f);
    }

    public function dateRanges()
    {
        return [
            ['yesterday', '2011-08-29 00:00:00', '2011-08-29 23:59:59'],
            ['today', '2011-08-30 00:00:00', '2011-08-30 23:59:59'],
            ['tomorrow', '2011-08-31 00:00:00', '2011-08-31 23:59:59'],
            ['last_7_days', '2011-08-24 00:00:00', '2011-08-30 23:59:59'],
            ['next_7_days', '2011-08-30 00:00:00', '2011-09-05 23:59:59'],
            ['last_30_days', '2011-08-01 00:00:00', '2011-08-30 23:59:59'],
            ['next_30_days', '2011-08-30 00:00:00', '2011-09-28 23:59:59'],
            ['next_month', '2011-09-01 00:00:00', '2011-09-30 23:59:59'],
            ['last_month', '2011-07-01 00:00:00', '2011-07-31 23:59:59'],
            ['this_month', '2011-08-01 00:00:00', '2011-08-31 23:59:59'],
            ['next_year', '2012-01-01 00:00:00', '2012-12-31 23:59:59'],
            ['last_year', '2010-01-01 00:00:00', '2010-12-31 23:59:59'],
            ['this_year', '2011-01-01 00:00:00', '2011-12-31 23:59:59'],
        ];
    }

    /**
     * @dataProvider dateRanges
     */
    public function testparseDateRange($range, $start, $end)
    {
        $this->time_date->setNow(SugarDateTime::createFromFormat(TimeDate::DB_DATETIME_FORMAT, '2011-08-30 12:01:02', new DateTimeZone($this->time_date->userTimezone())));
        $this->time_date->allow_cache = true;
        $daterage = $this->time_date->parseDateRange($range);
        $this->assertEquals($start, $daterage[0]->format(TimeDate::DB_DATETIME_FORMAT), 'Start date is wrong');
        $this->assertEquals($end, $daterage[1]->format(TimeDate::DB_DATETIME_FORMAT), 'End date is wrong');
    }

    public function isoDateTimeTestSet()
    {
        return [
            // with offset
            ['input' => '2012-12-12T10:35:15-0700', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '2012-12-12T09:35:15-0800', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Los_Angeles',],
            ['input' => '2012-12-12T17:35:15-0000', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/London',],
            ['input' => '2012-12-12T17:35:15+0000', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/London',],
            ['input' => '2012-12-12T17:35:15Z', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/London',],
            ['input' => '2012-12-12T19:35:15+0200', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/Helsinki',],
            ['input' => '2015-03-27T13:50:48-04:30', 'dbdate' => '2015-03-27 18:20:48', 'tz' => 'America/Caracas',],
            // with offset in permissible but "strange" formats
            ['input' => '2012-12-12T10:35:15.1234-0700', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '20121212T103515-0700', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '20121212T103515.5678-0700', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '20121212T10:35:15-0700', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '2012-12-12T103515-0700', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '2015-03-27T135048-04:30', 'dbdate' => '2015-03-27 18:20:48', 'tz' => 'America/Caracas',],
            // without offset
            ['input' => '2012-12-12T10:35:15', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '2012-12-12T09:35:15', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Los_Angeles',],
            ['input' => '2012-12-12T17:35:15', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/London',],
            ['input' => '2012-12-12T19:35:15', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/Helsinki',],
            // without offset in permissible but "strange" formats
            ['input' => '2012-12-12T10:35:15.1234', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '20121212T103515', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '20121212T103515.1234', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '20121212T10:35:15', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['input' => '2012-12-12T103515', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
        ];
    }

    /**
     * @dataProvider isoDateTimeTestSet
     */
    public function testFromIso($input, $dbdate, $tz)
    {
        $this->setPrefs('d/m/Y', 'h:i:sA', $tz);

        $timeDate = new TimeDate();

        $dateTime = $timeDate->fromIso($input);
        $this->assertEquals($dbdate, $timeDate->asDb($dateTime));
    }

    public function isoDateTimeReverseTestSet()
    {
        return [
            ['output' => '2012-12-12T10:35:15-07:00', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Boise',],
            ['output' => '2012-12-12T09:35:15-08:00', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'America/Los_Angeles',],
            ['output' => '2012-12-12T17:35:15+00:00', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/London',],
            ['output' => '2012-12-12T19:35:15+02:00', 'dbdate' => '2012-12-12 17:35:15', 'tz' => 'Europe/Helsinki',],
        ];
    }

    /**
     * @dataProvider isoDateTimeReverseTestSet
     */

    public function testAsIso($output, $dbdate, $tz)
    {
        $this->setPrefs('d/m/Y', 'h:i:sA', $tz);

        $timeDate = new TimeDate();

        $dateTime = $timeDate->fromDb($dbdate);
        $outTime = $timeDate->asIso($dateTime);
        $this->assertEquals($output, $outTime);
        $this->assertEquals($dbdate, $timeDate->asDb($dateTime));
    }


    /**
     * @dataProvider isoDateTimeReverseTestSet
     */
    public function testAsIsoWithOptions($output, $dbdate, $tz)
    {
        $this->setPrefs('d/m/Y', 'h:i:sA', $tz);

        $timeDate = new TimeDate();

        $dateTime = $timeDate->fromDb($dbdate);
        $outTime = $timeDate->asIso($dateTime, null, ['stripTZColon' => true]);
        $c = substr($outTime, -3, 1);
        $this->assertNotEquals(':', $c);
        $outTime = $timeDate->asIso($dateTime, null, ['stripTZColon' => false]);
        $c = substr($outTime, -3, 1);
        $this->assertEquals(':', $c);
    }

    public function testAsDbDefaultsToSettingGMT()
    {
        $dateMock = $this->createPartialMock('DateTime', ['setTimezone', 'format']);
        $dateMock->expects($this->once())
            ->method('setTimezone');
        $dateMock->expects($this->once())
            ->method('format');
        $this->time_date->asDb($dateMock, true);
    }

    public function testAsDbDontSetGMT()
    {
        $dateMock = $this->createPartialMock('DateTime', ['setTimezone', 'format']);
        $dateMock->expects($this->never())//dont set GMT
        ->method('setTimezone');
        $dateMock->expects($this->once())
            ->method('format');
        $this->time_date->asDb($dateMock, false);
    }

    /**
     * asDbType should process correct params to asDb, asDbDate and asDbtime
     *
     * @param string $type any type
     * @param int $value offset in secods
     * @param bool $GMT flag of GMT offset
     * @param string $method name of expected method
     * @dataProvider getDataForTestAsDbType
     */
    public function testAsDbType($type, $value, $GMT, $method)
    {
        $value = new DateTime('+' . $value . ' seconds');
        /** @var TimeDate $timeDate */
        $timeDate = $this->createPartialMock('TimeDate', [$method]);
        if (isset($GMT)) {
            $timeDate->expects($this->once())->method($method)->with($this->equalTo($value), $this->equalTo($GMT));
        } else {
            $timeDate->expects($this->once())->method($method)->with($this->equalTo($value));
        }

        $timeDate->asDbType($value, $type, $GMT);
    }

    /**
     * Data provider for testAsDbType
     * @return array
     */
    public static function getDataForTestAsDbType()
    {
        return [
            ['date', 1, false, 'asDbDate'],
            ['time', 2, null, 'asDbtime'],
            ['datetime', 3, false, 'asDb'],
            ['datetimecombo', 4, false, 'asDb'],
            ['other', 5, false, 'asDb'],

            ['date', 6, true, 'asDbDate'],
            ['datetime', 7, true, 'asDb'],
            ['datetimecombo', 8, true, 'asDb'],
            ['other', 9, true, 'asDb'],
        ];
    }

    /**
     * Test fromUserType()
     *
     * @dataProvider fromUserTypeDataProvider
     */
    public function testFromUserType($date, $type, $user, $timezone, $expected, $function, $dateFormat)
    {
        $this->setPrefs($dateFormat, 'H:i:s', 'GMT');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $value = $this->time_date->fromUserType($date, $type, $user);

        $this->assertStringContainsString($expected, $value->$function());
    }

    public static function fromUserTypeDataProvider()
    {
        return [
            [
                '2014-01-01 09:00:00', 'date', null, 'Pacific/Kwajalein', '2013-12-31', 'asDbDate', 'Y-m-d H:i:s',
            ],
            [
                '2014-01-01 12:12:12', 'datetime', null, 'America/Denver', '2014-01-01 19:12:12', 'asDb', 'Y-m-d',
            ],
            [
                '12:12:12', 'time', null, 'GMT', '12:12:12', 'asDb', 'Y-m-d',
            ],
        ];
    }
}
