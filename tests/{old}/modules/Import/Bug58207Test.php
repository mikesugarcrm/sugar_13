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
 * Test checks Import when not using UTF-8 encoding
 *
 * @ticket 58207
 * @author avucinic
 */
class Bug58207Test extends TestCase
{
    private $file;
    private $sugarConfig;

    protected function setUp(): void
    {
        // SJIS encoded Japanese CSV
        $this->file = 'Bug58207Test.csv';
        $dst = 'upload://' . $this->file;
        copy(__DIR__ . '/' . $this->file, $dst);

        global $sugar_config;
        $this->sugarConfig = $sugar_config;
        $sugar_config['default_export_charset'] = 'SJIS';

        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        global $sugar_config;
        $sugar_config = $this->sugarConfig;
    }

    /**
     * Import a SJIS encoded file, and check if getNextRow() properly
     * converts all the data into UTF-8
     */
    public function testFileImportEncoding()
    {
        $importFile = new ImportFile(\UploadStream::STREAM_NAME . '://' . $this->file, ',', '"', false, false);

        $row = $importFile->getNextRow();

        // Hardcode some Japanese strings
        $this->assertEquals('名前', $row[0]);
        $this->assertEquals('請求先郵便番号', $row[10]);
        $this->assertEquals('年間売上', $row[20]);
        $this->assertEquals('チームID', $row[30]);
    }
}
