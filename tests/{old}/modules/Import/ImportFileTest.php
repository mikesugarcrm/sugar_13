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
use Sugarcrm\Sugarcrm\Security\InputValidation\Exception\ViolationException;

class ImportFileTest extends TestCase
{
    protected $unlink = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        foreach ($this->unlink as $file) {
            @unlink($file);
        }
        SugarTestHelper::tearDown();
    }

    /**
     * @ticket 23380
     */
    public function testFileImportNoEnclosers()
    {
        $file = SugarTestImportUtilities::createFile(2, 1);
        $importFile = new ImportFile($file, ',', '', true, false);
        $row = $importFile->getNextRow();
        $this->assertEquals($row, ['foo00']);
        $row = $importFile->getNextRow();
        $this->assertEquals($row, ['foo10']);
    }

    public function testLoadNonExistantFile()
    {
        $this->expectException(ViolationException::class);
        $importFile = new ImportFile(ImportCacheFiles::getImportDir() . '/thisfileisntthere' . date('YmdHis') . '.csv', ',', '"');
        $this->assertFalse($importFile->fileExists());
    }

    public function testLoadGoodFile()
    {
        $file = SugarTestImportUtilities::createFile(2, 1);
        $importFile = new ImportFile($file, ',', '"', true, false);
        $this->assertTrue($importFile->fileExists());
    }

    /**
     * @ticket 39494
     */
    public function testLoadFileWithByteOrderMark()
    {
        $this->uploadFile('Bug39494ImportFile.txt');
        $importFile = new ImportFile($this->getUploadedFileName('Bug39494ImportFile.txt'), "\t", '', false);

        $this->assertTrue($importFile->fileExists());
        $row = $importFile->getNextRow();
        $this->assertEquals($row, ['name', 'city']);
        $row = $importFile->getNextRow();
        $this->assertEquals($row, ['tester1', 'wuhan']);
    }

    public function testGetNextRow()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $row = $importFile->getNextRow();
        $this->assertEquals(['foo00', 'foo01'], $row);
        $row = $importFile->getNextRow();
        $this->assertEquals(['foo10', 'foo11'], $row);
        $row = $importFile->getNextRow();
        $this->assertEquals(['foo20', 'foo21'], $row);
    }

    /**
     * @ticket 41361
     */
    public function testGetNextRowWithEOL()
    {
        $file = SugarTestImportUtilities::createFileWithEOL(1, 1);
        $importFile = new ImportFile($file, ',', '"', true, false);
        $row = $importFile->getNextRow();
        // both \r\n and \n should be properly replaced with PHP_EOL
        $this->assertEquals(['start0' . PHP_EOL . '0' . PHP_EOL . 'end'], $row);
    }

    public function testLoadEmptyFile()
    {
        $emptyFile = ImportCacheFiles::getImportDir() . '/empty' . date('YmdHis') . '.csv';
        file_put_contents($emptyFile, '');
        $this->unlink[] = $emptyFile;

        $importFile = new ImportFile($emptyFile, ',', '"', false);

        $this->assertFalse($importFile->getNextRow());

        $importFile = new ImportFile($emptyFile, ',', '', false);

        $this->assertFalse($importFile->getNextRow());
    }

    public function testDeleteFileOnDestroy()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        unset($importFile);

        $this->assertFalse(is_file($file));
    }

    public function testNotDeleteFileOnDestroy()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', false);

        unset($importFile);

        $this->assertTrue(is_file($file));
    }

    public function testGetFieldCount()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $importFile->getNextRow();
        $this->assertEquals(2, $importFile->getFieldCount());
    }

    public function testMarkRowAsDuplicate()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $row = $importFile->getNextRow();
        $importFile->markRowAsDuplicate();

        $fp = sugar_fopen(ImportCacheFiles::getDuplicateFileName(), 'r');
        $duperow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals($row, $duperow);
    }

    public function testWriteError()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $row = $importFile->getNextRow();
        $importFile->writeError('Some Error', 'field1', 'foo');

        $fp = sugar_fopen(ImportCacheFiles::getErrorFileName(), 'r');
        $errorrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals(['Some Error', 'field1', 'foo', 1], $errorrow);

        $fp = sugar_fopen(ImportCacheFiles::getErrorRecordsWithoutErrorFileName(), 'r');
        $errorrecordrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals($row, $errorrecordrow);
    }

    public function testWriteErrorRecord()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $row = $importFile->getNextRow();
        $importFile->writeErrorRecord();

        $fp = sugar_fopen(ImportCacheFiles::getErrorRecordsWithoutErrorFileName(), 'r');
        $errorrecordrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals($row, $errorrecordrow);
    }

    public function testWriteStatus()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $importFile->getNextRow();
        $importFile->writeError('Some Error', 'field1', 'foo');
        $importFile->getNextRow();
        $importFile->markRowAsDuplicate();
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->writeStatus();

        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(), 'r');
        $statusrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals([3, 1, 1, 1, 0, $file], $statusrow);
    }

    public function testWriteStatusWithTwoErrorsInOneRow()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $row = $importFile->getNextRow();
        $importFile->writeError('Some Error', 'field1', 'foo');
        $importFile->writeError('Some Error', 'field1', 'foo');
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->writeStatus();

        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(), 'r');
        $statusrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals([3, 1, 0, 2, 0, $file], $statusrow);

        $fp = sugar_fopen(ImportCacheFiles::getErrorRecordsWithoutErrorFileName(), 'r');
        $errorrecordrow = fgetcsv($fp);

        $this->assertEquals($row, $errorrecordrow);

        $this->assertFalse(fgetcsv($fp), 'Should be only 1 record in the csv file');
        fclose($fp);
    }

    public function testWriteStatusWithTwoUpdatedRecords()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"', true, false);

        $row = $importFile->getNextRow();
        $importFile->markRowAsImported(false);
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->getNextRow();
        $importFile->markRowAsImported();
        $importFile->writeStatus();

        $fp = sugar_fopen(ImportCacheFiles::getStatusFileName(), 'r');
        $statusrow = fgetcsv($fp);
        fclose($fp);

        $this->assertEquals([3, 0, 0, 2, 1, $file], $statusrow);
    }

    public function testWriteRowToLastImport()
    {
        $file = SugarTestImportUtilities::createFile(3, 2);
        $importFile = new ImportFile($file, ',', '"');
        $record = $importFile->writeRowToLastImport('Tests', 'Test', 'TestRunner');

        $query = "SELECT *
                        FROM users_last_import
                        WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'
                            AND import_module = 'Tests'
                            AND bean_type = 'Test'
                            AND bean_id = 'TestRunner'
                            AND id = '$record'
                            AND deleted=0";

        $result = $GLOBALS['db']->query($query);

        $this->assertNotNull($GLOBALS['db']->fetchByAssoc($result));

        $query = "DELETE FROM users_last_import
                        WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'
                            AND import_module = 'Tests'
                            AND bean_type = 'Test'
                            AND bean_id = 'TestRunner'
                            AND id = '$record'
                            AND deleted=0";
        $GLOBALS['db']->query($query);
    }

    public function providerEncodingData()
    {
        return [
            ['TestCharset.csv', 'UTF-8'],
            ['TestCharset2.csv', 'ISO-8859-1'],
        ];
    }

    /**
     * @dataProvider providerEncodingData
     */
    public function testCharsetDetection($file, $encoding)
    {
        $this->uploadFile($file);
        $importFile = new ImportFile($this->getUploadedFileName($file), ',', '"', false);

        $this->assertTrue($importFile->fileExists());
        $charset = $importFile->autoDetectCharacterSet();
        $this->assertEquals($encoding, $charset, 'detected char encoding is incorrect.');
    }

    public function providerRowCountData()
    {
        return [
            ['TestCharset.csv', 2, false],
            ['TestCharset2.csv', 11, true],
            ['TestCharset2.csv', 12, false],
        ];
    }

    /**
     * @dataProvider providerRowCountData
     */
    public function testRowCount($file, $count, $hasHeader)
    {
        $this->uploadFile($file);
        $importFile = new ImportFile($this->getUploadedFileName($file), ',', '"', false);

        $this->assertTrue($importFile->fileExists());
        $importFile->setHeaderRow($hasHeader);
        $c = $importFile->getTotalRecordCount();
        $this->assertEquals($count, $c, 'incorrect row count.');
    }

    public function providerFieldCountData()
    {
        return [
            ['TestCharset.csv', 2],
            ['TestCharset2.csv', 5],
        ];
    }

    /**
     * @dataProvider providerFieldCountData
     */
    public function testFieldCount($file, $count)
    {
        $this->uploadFile($file);
        $importFile = new ImportFile($this->getUploadedFileName($file), ',', '"', false);

        $this->assertTrue($importFile->fileExists());
        $c = $importFile->getNextRow();
        $c = $importFile->getFieldCount();
        $this->assertEquals($count, $c, 'incorrect row count.');
    }

    public function providerLineCountData()
    {
        return [
            ['TestCharset.csv', 2],
            ['TestCharset2.csv', 12],
        ];
    }

    /**
     * @dataProvider providerLineCountData
     */
    public function testLineCount($file, $count)
    {
        $this->uploadFile($file);
        $importFile = new ImportFile($this->getUploadedFileName($file), ',', '"', false);

        $this->assertTrue($importFile->fileExists());
        $c = $importFile->getNumberOfLinesInfile();
        $this->assertEquals($count, $c, 'incorrect row count.');
    }

    public function providerDateFormatData()
    {
        return [
            ['TestCharset.csv', 'd/m/Y'],
            ['TestCharset2.csv', 'm/d/Y'],
        ];
    }

    /**
     * @dataProvider providerDateFormatData
     */
    public function testDateFormat($file, $format)
    {
        $this->uploadFile($file);
        $importFile = new ImportFile($this->getUploadedFileName($file), ',', '"', false);

        $this->assertTrue($importFile->fileExists());
        $ret = $importFile->autoDetectCSVProperties();
        $this->assertTrue($ret, 'Failed to auto detect properties.');
        $c = $importFile->getDateFormat();
        $this->assertEquals($format, $c, 'incorrect date format.');
    }

    public function providerTimeFormatData()
    {
        return [
            ['TestCharset.csv', 'h:ia'],
            ['TestCharset2.csv', 'H:i'],
        ];
    }

    /**
     * @dataProvider providerTimeFormatData
     */
    public function testTimeFormat($file, $format)
    {
        $this->uploadFile($file);
        $importFile = new ImportFile($this->getUploadedFileName($file), ',', '"', false);

        $this->assertTrue($importFile->fileExists());
        $ret = $importFile->autoDetectCSVProperties();
        $this->assertTrue($ret, 'Failed to auto detect properties.');
        $c = $importFile->getTimeFormat();
        $this->assertEquals($format, $c, 'incorrect time format.');
    }

    /**
     * @ticket 48289
     */
    public function testTabDelimiter()
    {
        $this->uploadFile('TestCharset.csv');

        // use '\t' to simulate the bug
        $importFile = new ImportFile($this->getUploadedFileName('TestCharset.csv'), '\t', '"', false);
        $this->assertTrue($importFile->fileExists());
        $c = $importFile->getNextRow();
        $this->assertTrue(is_array($c), 'incorrect return type.');
        $this->assertEquals(1, count($c), 'incorrect array count.');
    }

    /**
     * Returns filename converted to UploadStream
     * @param string $file
     * @return string
     */
    private function getUploadedFileName($file)
    {
        return \UploadStream::STREAM_NAME . '://' . $file;
    }

    /**
     * Copy test file into upload dir
     *
     * @param $file
     */
    private function uploadFile($file)
    {
        $dst = 'upload://' . $file;
        copy(__DIR__ . '/' . $file, $dst);
        $this->unlink[] = $dst;
    }
}
