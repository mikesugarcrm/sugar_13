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
 * Class TimestampExpressionTest
 *
 * @coversDefaultClass TimestampExpression
 */
class TimestampExpressionTest extends TestCase
{
    /**
     * @var User
     */
    protected static $user;

    /**
     * Setup before the test is run
     */
    public static function setUpBeforeClass(): void
    {
        static::$user = SugarTestHelper::setUp('current_user');
    }

    /**
     * Clean up after the full test is done.
     */
    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * TearDown after each tear
     *
     * The cache needs to be reset since it caches the user preferences
     */
    protected function tearDown(): void
    {
        sugar_cache_reset_full();
    }

    /**
     * Data provider handling all the allowed date formats.
     *
     * @return array
     */
    public static function dataProviderDates()
    {
        return [
            ['04/14/2014', 'm/d/Y', 1397433600],
            ['14/04/2014', 'd/m/Y', 1397433600],
            ['2014/04/14', 'Y/m/d', 1397433600],
            ['04.14.2014', 'm.d.Y', 1397433600],
            ['14.04.2014', 'd.m.Y', 1397433600],
            ['2014.04.14', 'Y.m.d', 1397433600],
            ['04-14-2014', 'm-d-Y', 1397433600],
            ['14-04-2014', 'd-m-Y', 1397433600],
            ['2014-04-14', 'Y-m-d', 1397433600],
        ];
    }

    /**
     * @dataProvider dataProviderDates
     * @covers ::evaluate
     *
     * @param string $date
     * @param string $format
     * @param int $expected
     */
    public function testTimestampEvaluateJustDate($date, $format, $expected)
    {
        static::$user->setPreference('datef', $format);
        $result = Parser::evaluate('timestamp("' . $date . '")')->evaluate();
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider with all the dates/time formats the system supports
     *
     * @return array
     */
    public static function dataProviderDateTime()
    {
        return [
            ['04/14/2014', '23:00', 'm/d/Y', 'H:i', 1397516400],
            ['14/04/2014', '11:00pm', 'd/m/Y', 'H:ia', 1397516400],
            ['2014/04/14', '11:00PM', 'Y/m/d', 'H:iA', 1397516400],
            ['04.14.2014', '11:00 pm', 'm.d.Y', 'H:i a', 1397516400],
            ['14.04.2014', '11:00 PM', 'd.m.Y', 'H:i A', 1397516400],
            ['2014.04.14', '23.00', 'Y.m.d', 'H.i', 1397516400],
            ['04-14-2014', '11.00pm', 'm-d-Y', 'H.ia', 1397516400],
            ['14-04-2014', '11.00PM', 'd-m-Y', 'H.iA', 1397516400],
            ['2014-04-14', '11.00 pm', 'Y-m-d', 'H.i a', 1397516400],
            ['2014-04-14', '11.00 PM', 'Y-m-d', 'H.i A', 1397516400],
        ];
    }

    /**
     * @dataProvider dataProviderDateTime
     * @covers ::evaluate
     *
     * @param string $date
     * @param string $time
     * @param string $date_format
     * @param string $time_format
     * @param int $expected
     */
    public function testTimestampEvaluateDateTime($date, $time, $date_format, $time_format, $expected)
    {
        static::$user->setPreference('datef', $date_format);
        static::$user->setPreference('timef', $time_format);
        $result = Parser::evaluate('timestamp("' . $date . ' ' . $time . '")')->evaluate();
        $this->assertEquals($expected, $result);
    }
}
