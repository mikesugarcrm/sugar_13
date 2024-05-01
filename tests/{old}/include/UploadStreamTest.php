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

class UploadStreamTest extends TestCase
{
    private $file;
    private $content;

    protected function setUp(): void
    {
        $this->file = 'upload://upload_stream_test.txt';
        $this->content = 'test';
        file_put_contents($this->file, $this->content);
    }

    protected function tearDown(): void
    {
        unlink($this->file);
    }

    public function testUploadStreamWrapperCorrectlyResolvesPath()
    {
        $this->assertFileExists($this->file);
        $this->assertEquals($this->content, file_get_contents($this->file));
    }

    public function testUploadStreamWrapperPreventsPathTraversal()
    {
        /**
         * upload:// stream wrapper returns null if filename contains path traversal
         * file_get_contents raises warning
         */
        $caughtErrNo = null;
        set_error_handler(static function (int $errno) use (&$caughtErrNo) {
            $caughtErrNo = $errno;
        });
        try {
            $this->assertFalse(file_get_contents('upload://../index.php'));
        } finally {
            restore_error_handler();
        }
        $this->assertSame(E_WARNING, $caughtErrNo);
    }
}
