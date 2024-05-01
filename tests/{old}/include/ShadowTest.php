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

require_once 'include/utils/zip_utils.php';

/**
 * Test some scenarios that were problematic with Shadow
 */
class ShadowTest extends RestTestBase
{
    /**
     * @var string|bool|mixed
     */
    public $dir;

    protected function setUp(): void
    {
        $this->dir = getcwd();
        chdir(sugar_root_dir());
        parent::setUp();
    }

    protected function tearDown(): void
    {
        chdir($this->dir);
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDown();
    }

    public function testZipDir()
    {
        $arch = 'upload://test.zip';
        $dir = 'upload://import';
        $testfile = "$dir/shadowtest-file.txt";
        SugarTestHelper::saveFile($testfile);
        SugarTestHelper::saveFile($arch);
        file_put_contents($testfile, 'test');
        @unlink($arch);
        zip_dir($dir, $arch);
        $this->assertTrue(file_exists($arch));
    }

    public function testFileMime()
    {
        if (!mime_is_detectable()) {
            $this->markTestSkipped('Requires functions to detect mime type');
        }
        $filename = sugar_cached('test.txt');
        SugarTestHelper::saveFile($filename);
        file_put_contents($filename, 'This is a text of a test. And this is a test of a text.');
        $this->assertEquals('text/plain', get_file_mime_type($filename, 'wront/type'));
    }
}
