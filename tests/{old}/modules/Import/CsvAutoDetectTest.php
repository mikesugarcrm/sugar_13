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

class CsvAutoDetectTest extends TestCase
{
    private static $CsvContent = [
        0 => "\"date_entered\",\"description\"\n\"3/26/2011 10:02am\",\"test description\"",
        1 => "\"date_entered\"\t\"description\"\n\"2011-3-26 10:2 am\"\t\"test description\"",
        2 => "\"date_entered\",\"description\"\n\"3.26.2011 15.02\",\"test description\"",
        3 => "\"3/26/2011 10:02am\",\"some text\"\n\"4/26/2011 11:20am\",\"some more jim's text\"",
        4 => "\"date_entered\",\"description\"\n\"2010/03/26 10:2am\",\"test description\"",
        5 => "'date_entered','description'\n'26/3/2011 15:02','test description'",
        6 => "\"date_entered\"|\"description\"\n\"3/26/2011 10:02am\"|\"test description\"",
    ];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('files');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function providerCsvData()
    {
        return [
            [0, ',', '"', 'm/d/Y', 'h:ia', true],
            [1, "\t", '"', 'Y-m-d', 'h:i a', true],
            [2, ',', '"', 'm.d.Y', 'H.i', true],
            [3, ',', '"', 'm/d/Y', 'h:ia', false],
            [4, ',', '"', 'Y/m/d', 'h:ia', true],
            [5, ',', "'", 'd/m/Y', 'H:i', true],
            [6, '|', '"', 'm/d/Y', 'h:ia', true],
        ];
    }

    /**
     * @dataProvider providerCsvData
     */
    public function testGetCsvProperties($content_idx, $delimiter, $enclosure, $date, $time, $header)
    {
        $file = $GLOBALS['sugar_config']['tmp_dir'] . 'test.csv';
        SugarTestHelper::saveFile($file);

        $dirName = dirname($file);
        SugarTestHelper::ensureDir($dirName);
        $ret = file_put_contents($file, self::$CsvContent[$content_idx]);
        $this->assertGreaterThan(0, $ret, 'Failed to write to ' . $file . ' for content ' . $content_idx);

        $auto = new CsvAutoDetect($file);
        $del = $enc = $hasHeader = false;
        $ret = $auto->getCsvSettings($del, $enc);
        $this->assertEquals(true, $ret, 'Failed to parse and get csv properties');

        // delimiter
        $this->assertEquals($delimiter, $del, 'Incorrect delimiter');

        // enclosure
        $this->assertEquals($enclosure, $enc, 'Incorrect enclosure');

        // date format
        $date_format = $auto->getDateFormat();
        $this->assertEquals($date, $date_format, 'Incorrect date format');

        // time format
        $time_format = $auto->getTimeFormat();
        $this->assertEquals($time, $time_format, 'Incorrect time format');

        // header
        $ret = $auto->hasHeader($hasHeader, 'Contacts');
        $this->assertTrue($ret, 'Failed to detect header');
        $this->assertEquals($header, $hasHeader, 'Incorrect header');

        // remove temp file
        unlink($GLOBALS['sugar_config']['tmp_dir'] . 'test.csv');
    }
}
