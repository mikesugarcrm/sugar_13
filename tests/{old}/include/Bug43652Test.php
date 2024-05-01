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
 * @ticket 43652
 */
class Bug43652Test extends TestCase
{
    private $fileData1;

    /** @var ExternalAPIBase */
    private $extAPI;

    protected function setUp(): void
    {
        $this->extAPI = $this->getMockForAbstractClass('ExternalAPIBase');
        $this->fileData1 = sugar_cached('unittest');
        file_put_contents($this->fileData1, 'Unit test for mime type');
    }

    protected function tearDown(): void
    {
        unlink($this->fileData1);
    }

    public static function fileMimeProvider()
    {
        return [
            [['name' => 'te.st.png', 'type' => 'img/png'], 'img/png'],
            [['name' => 'test.jpg', 'type' => 'img/jpeg'], 'img/jpeg'],
            [['name' => 'test.out', 'type' => 'application/octet-stream'], 'application/octet-stream'],
            [['name' => 'test_again', 'type' => 'img/png'], 'img/png'],
        ];
    }

    /**
     * Test the getMime function for the use case where the mime type is already provided.
     *
     * @dataProvider fileMimeProvider
     */
    public function testUploadFileWithMimeType($file_info, $expectedMime)
    {
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);

        $this->assertEquals($expectedMime, $mime);
    }

    /**
     * Test file with no extension but with provided mime-type
     *
     * @return void
     */
    public function testUploadFileWithEmptyFileExtension()
    {
        $file_info = ['name' => 'test', 'type' => 'application/octet-stream', 'tmp_name' => $this->fileData1];
        $expectedMime = $this->extAPI->isMimeDetectionAvailable() ? 'text/plain' : 'application/octet-stream';
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);
        $this->assertEquals($expectedMime, $mime);
    }


    /**
     * Test file with no extension and no provided mime-type
     *
     * @return void
     */
    public function testUploadFileWithEmptyFileExtenEmptyMime()
    {
        $file_info = ['name' => 'test', 'tmp_name' => $this->fileData1];
        $expectedMime = $this->extAPI->isMimeDetectionAvailable() ? 'text/plain' : 'application/octet-stream';
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);
        $this->assertEquals($expectedMime, $mime);
    }
}
