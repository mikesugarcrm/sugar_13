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

class SugarTestImportUtilitiesTest extends TestCase
{
    protected function tearDown(): void
    {
        //   SugarTestImportUtilities::removeAllCreatedFiles();
    }

    public function testCanCreateFile()
    {
        $columns = null;
        $filename = SugarTestImportUtilities::createFile();

        $this->assertTrue(is_file($filename));
        $fp = fopen($filename, 'r');
        $i = 0;
        $buffer = '';
        while (!feof($fp)) {
            $columns = $buffer;
            $buffer = fgetcsv($fp, 4096);
            if ($buffer !== false) {
                $i++;
            }
        }
        fclose($fp);
        $this->assertEquals(2000, $i);
        $this->assertCount(3, $columns);
    }

    public function testCanCreateFileAndSpecifyLines()
    {
        $columns = null;
        $filename = SugarTestImportUtilities::createFile(1);
        $this->assertTrue(is_file($filename));
        $fp = fopen($filename, 'r');
        $i = 0;
        $buffer = '';
        while (!feof($fp)) {
            $buffer = fgetcsv($fp, 4096);
            if ($buffer !== false) {
                $i++;
                $columns = $buffer;
            }
        }
        fclose($fp);
        $this->assertEquals(1, $i);
        $this->assertEquals(3, count((array)$columns));
    }

    public function testCanCreateFileAndSpecifyLinesAndColumns()
    {
        $columns = null;
        $filename = SugarTestImportUtilities::createFile(2, 5);

        $this->assertTrue(is_file($filename));
        $fp = fopen($filename, 'r');
        $i = 0;
        $buffer = '';
        while (!feof($fp)) {
            $columns = $buffer;
            $buffer = fgetcsv($fp, 4096);
            if ($buffer !== false) {
                $i++;
            }
        }
        fclose($fp);
        $this->assertEquals(2, $i);
        $this->assertCount(5, $columns);
    }

    public function testCanRemoveAllCreatedFiles()
    {
        $filesCreated = [];

        for ($i = 0; $i < 5; $i++) {
            $filesCreated[] = SugarTestImportUtilities::createFile();
        }
        $filesCreated[] = $filesCreated[4] . '-0';

        SugarTestImportUtilities::removeAllCreatedFiles();

        foreach ($filesCreated as $filename) {
            $this->assertFalse(is_file($filename));
        }
    }
}
