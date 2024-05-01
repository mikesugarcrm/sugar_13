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

require_once 'include/utils/file_utils.php';

class FileUtilsTests extends TestCase
{
    private $testFileWithExt = 'upload/sugartestfile.txt';
    private $testFileNoExt = 'upload/noextfile';
    private $testFileNotExists = 'thisfilenamedoesnotexist.doc';

    protected function setUp(): void
    {
        sugar_file_put_contents($this->testFileWithExt, create_guid());
        sugar_file_put_contents($this->testFileNoExt, create_guid());
    }

    protected function tearDown(): void
    {
        unlink($this->testFileWithExt);
        unlink($this->testFileNoExt);
    }

    public function testIsMimeDetectableByFinfo()
    {
        $expected = function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close');
        $actual = mime_is_detectable_by_finfo();
        $this->assertEquals($expected, $actual, 'FInfo check failed for mime detection');
    }

    public function testIsMimeDetectable()
    {
        $expected = (function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close'))
            ||
            function_exists('mime_content_type') || function_exists('ext2mime');
        $actual = mime_is_detectable();
        $this->assertEquals($expected, $actual, 'Check failed for mime detection');
    }

    public function testEmail2GetMime()
    {
        $email = new Email();
        $expected = $email->email2GetMime($this->testFileWithExt);
        $actual = $this->getDefaultMimeType();
        $this->assertEquals($expected, $actual, "Email bean returned $actual but was expected $expected");
    }

    public function testDownloadFileGetMimeType()
    {
        require_once 'include/download_file.php';
        $dl = new DownloadFile();

        // Assert #1 file with extension
        $expected = $this->getDefaultMimeType();
        $actual = $dl->getMimeType($this->testFileWithExt);
        $this->assertEquals($expected, $actual, "Download File mime getter with extension returned $actual but expected $expected");

        // Assert #2 file with no extension
        $actual = $dl->getMimeType($this->testFileNoExt);
        $this->assertEquals($expected, $actual, "Download File mime getter without extension returned $actual but expected $expected");

        // Assert #3 nonexistent file
        $condition = $dl->getMimeType($this->testFileNotExists);
        $this->assertFalse($condition, "Nonexistent file mime getter expected (bool) FALSE but returned $condition");
    }

    public function testUploadFileGetSoapMime()
    {
        $ul = new UploadFile();

        // Assert #1 file with extension
        $expected = $this->getDefaultMimeType();
        $actual = $ul->getMimeSoap($this->testFileWithExt);
        $this->assertEquals($expected, $actual, "Upload File SOAP mime getter with extension returned $actual but expected $expected");

        // Assert #2 file with no extension
        $actual = $ul->getMimeSoap($this->testFileNoExt);
        $this->assertEquals($expected, $actual, "Upload File SOAP mime getter without extension returned $actual but expected $expected");

        // Assert #3 nonexistent file
        $actual = $ul->getMimeSoap($this->testFileNotExists);
        $this->assertEquals('application/octet-stream', $actual, "Nonexistent Upload File SOAP mime getter expected 'application/octet-stream' but returned $actual");
    }

    public function testUploadFileGetMime()
    {
        $ul = new UploadFile();

        // Assert #1 - file with extension and type set
        $files = ['name' => $this->testFileWithExt, 'type' => 'text/plain'];
        $actual = $ul->getMime($files);
        $this->assertEquals('text/plain', $actual, "Upload File Get Mime should have returned 'text/plain' but returned $actual");

        // Assert #2 - file without extension and type set to octet-stream
        $files = ['name' => $this->testFileNoExt, 'type' => 'application/octet-stream', 'tmp_name' => $this->testFileNoExt];
        $actual = $ul->getMime($files);
        $expected = $this->getDefaultMimeType();
        $this->assertEquals($expected, $actual, "Upload File Get Mime on file with no extension should have returned $expected but returneded $actual");

        // Assert #3 - nonexistent file
        $files = ['name' => $this->testFileNotExists, 'type' => 'application/octet-stream', 'tmp_name' => $this->testFileNotExists];
        $actual = $ul->getMime($files);
        $this->assertEquals('application/octet-stream', $actual, "Upload File Get Mime on nonexistent file should have returned 'application/octet-stream' but returned $actual");
    }

    public function testWriteArrayToFileWithKeyValuePair()
    {
        $the_file = 'temp_file.php';
        $testArray = [
            'name' => 'solution_number',
            'vname' => 'LBL_SOLUTION_NUMBER',
            'type' => 'int',
            'len' => 11,
            'auto_increment' => 'true',
            'required' => 'true',
            'enable_range_search' => '',
            'full_text_search' => [
                'enabled' => 1,
                'searchable' => '',
                'boost' => 1,
            ],
            'merge_filter' => 'disabled',
            'autoinc_next' => '51',
            'dbType' => 'int',
        ];

        $the_name = 'mod_strings';
        write_array_to_file_as_key_value_pair($the_name, $testArray, $the_file, 'w');

        if (!file_exists($the_file)) {
            $this->assertEquals(1, 0, 'failed to write to file!');
        } else {
            $mod_strings = [];
            require $the_file;
            foreach ($testArray as $key => $value) {
                if (!is_array($value)) {
                    $this->assertEquals($mod_strings[$key], $value, "key=$key value=$value doesn't match");
                } else {
                    $this->assertEquals(var_export($mod_strings[$key], true), var_export($value, true));
                }
            }
            // clean up
            unlink($the_file);
        }
    }

    protected function getDefaultMimeType()
    {
        $mime = 'text/plain';

        if (!mime_is_detectable()) {
            $mime = 'application/octet-stream';
        }

        return $mime;
    }
}
