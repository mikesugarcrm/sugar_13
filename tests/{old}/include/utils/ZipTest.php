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

require_once 'include/utils/zip_utils.php';

/**
 * @ticket 40957
 */
class ZipTest extends TestCase
{
    public $testdir;

    /**
     * @requires extension zip
     */
    protected function setUp(): void
    {
        $this->testdir = sugar_cached('tests/{old}/include/utils/ziptest');
        sugar_mkdir($this->testdir . '/testarchive', null, true);
        sugar_touch($this->testdir . '/testarchive/testfile1.txt');
        sugar_touch($this->testdir . '/testarchive/testfile2.txt');
        sugar_touch($this->testdir . '/testarchive/testfile3.txt');
        sugar_mkdir($this->testdir . '/testarchiveoutput', null, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->testdir)) {
            rmdir_recursive($this->testdir);
        }
    }

    public function testZipADirectory()
    {
        zip_dir($this->testdir . '/testarchive', $this->testdir . '/testarchive.zip');

        $this->assertTrue(file_exists($this->testdir . '/testarchive.zip'));
    }

    public function testZipADirectoryFailsWhenDirectorySpecifedDoesNotExists()
    {
        $this->assertFalse(zip_dir($this->testdir . '/notatestarchive', $this->testdir . '/testarchive.zip'));
    }

    /**
     * @depends testZipADirectory
     */
    public function testExtractEntireArchive()
    {
        zip_dir($this->testdir . '/testarchive', $this->testdir . '/testarchive.zip');
        unzip($this->testdir . '/testarchive.zip', $this->testdir . '/testarchiveoutput');

        $this->assertTrue(file_exists($this->testdir . '/testarchiveoutput/testfile1.txt'));
        $this->assertTrue(file_exists($this->testdir . '/testarchiveoutput/testfile2.txt'));
        $this->assertTrue(file_exists($this->testdir . '/testarchiveoutput/testfile3.txt'));
    }

    /**
     * @depends testZipADirectory
     */
    public function testExtractSingleFileFromAnArchive()
    {
        zip_dir($this->testdir . '/testarchive', $this->testdir . '/testarchive.zip');
        unzip_file($this->testdir . '/testarchive.zip', 'testfile1.txt', $this->testdir . '/testarchiveoutput');

        $this->assertTrue(file_exists($this->testdir . '/testarchiveoutput/testfile1.txt'));
        $this->assertFalse(file_exists($this->testdir . '/testarchiveoutput/testfile2.txt'));
        $this->assertFalse(file_exists($this->testdir . '/testarchiveoutput/testfile3.txt'));
    }

    /**
     * @depends testZipADirectory
     */
    public function testExtractTwoIndividualFilesFromAnArchive()
    {
        zip_dir($this->testdir . '/testarchive', $this->testdir . '/testarchive.zip');
        unzip_file($this->testdir . '/testarchive.zip', ['testfile2.txt', 'testfile3.txt'], $this->testdir . '/testarchiveoutput');

        $this->assertFalse(file_exists($this->testdir . '/testarchiveoutput/testfile1.txt'));
        $this->assertTrue(file_exists($this->testdir . '/testarchiveoutput/testfile2.txt'));
        $this->assertTrue(file_exists($this->testdir . '/testarchiveoutput/testfile3.txt'));
    }

    public function testExtractFailsWhenArchiveDoesNotExist()
    {
        $this->assertFalse(unzip($this->testdir . '/testarchivenothere.zip', $this->testdir . '/testarchiveoutput'));
    }

    public function testExtractFailsWhenExtractDirectoryDoesNotExist()
    {
        $this->assertFalse(unzip($this->testdir . '/testarchive.zip', $this->testdir . '/testarchiveoutputnothere'));
    }

    public function testExtractFailsWhenFilesDoNotExistInArchive()
    {
        $this->assertFalse(unzip_file($this->testdir . '/testarchive.zip', 'testfile4.txt', $this->testdir . '/testarchiveoutput'));
    }
}
