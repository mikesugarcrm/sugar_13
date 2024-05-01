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

class ImporterTest extends TestCase
{
    private $importModule;

    // date_entered and last_name
    private static $CsvContent = [
        0 => '"3/26/2011 10:02am","Doe"',
        1 => '"2011-3-26 10:02 am","Doe"',
        2 => '"3.26.2011 10.02","Doe"',
    ];

    protected function setUp(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->importModule = 'Contacts';
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM contacts where created_by='{$GLOBALS['current_user']->id}'");

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        restore_error_handler();
    }

    public function providerCsvData()
    {
        return [
            [0, '2011-03-26 10:02:00', 'm/d/Y', 'h:ia'],
            [1, '2011-03-26 10:02:00', 'Y-m-d', 'h:ia'],
            [2, '2011-03-26 10:02:00', 'm.d.Y', 'H.i'],
        ];
    }

    /**
     * @dataProvider providerCsvData
     */
    public function testDateTimeImport($content_idx, $expected_datetime, $date_format, $time_format)
    {
        $file = 'upload://ImporterTest.csv';
        $ret = file_put_contents($file, self::$CsvContent[$content_idx]);
        $this->assertGreaterThan(0, $ret, 'Failed to write to ' . $file . ' for content ' . $content_idx);

        $importSource = new ImportFile(\UploadStream::STREAM_NAME . '://' . 'ImporterTest.csv', ',', '"');

        $bean = BeanFactory::newBean($this->importModule);

        $_REQUEST['columncount'] = 2;
        $_REQUEST['colnum_0'] = 'date_entered';
        $_REQUEST['colnum_1'] = 'last_name';
        $_REQUEST['import_module'] = 'Contacts';
        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_dateformat'] = $date_format;
        $_REQUEST['importlocale_timeformat'] = $time_format;
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';

        $importer = new Importer($importSource, $bean);
        $importer->import();

        $query = "SELECT date_entered from contacts where created_by='{$GLOBALS['current_user']->id}'";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($expected_datetime, $GLOBALS['db']->fromConvert($row['date_entered'], 'datetime'), 'Got incorrect date_entered.');
    }

    public function providerIdData()
    {
        return [
            //Valid ids
            ['12345', '12345'],
            ['12345-6789-1258', '12345-6789-1258'],
            ['aaaBBB12AA122cccD', 'aaaBBB12AA122cccD'],
            ['aaa-BBB-12AA122-cccD', 'aaa-BBB-12AA122-cccD'],
            ['aaa_BBB_12AA122_cccD', 'aaa_BBB_12AA122_cccD'],
            ['aaa.BBB.12AA122.cccD', 'aaa.BBB.12AA122.cccD'],
            //Invalid
            ['1242', '12*'],
            ['abdcd36', 'abdcd$'],
            ['1234-asdf3535353523', '1234-asdf####23'],
        ];
    }

    /**
     * @ticket PAT-784
     * @dataProvider providerIdData
     */
    public function testConvertID($expected, $dirty)
    {
        $c = new Contact();
        $importer = new PAT784ImporterStub('UNIT TEST', $c);
        $actual = $importer->convertID($dirty);

        $this->assertEquals(
            $expected,
            $actual,
            "Error converting id during import process $actual , expected: $expected, before conversion: $dirty"
        );
    }
}

/**
 * Mock importer class
 */
class PAT784ImporterStub extends Importer
{
    public function convertID($id)
    {
        return $this->_convertId($id);
    }

    public function getFieldSanitizer()
    {
    }
}
