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

require_once 'include/download_file.php';
require_once 'include/utils/file_utils.php';

class Bug55455Test extends TestCase
{
    private $actualFile = 'upload/sugartestfile.txt';
    private $mockFile = 'thisfilenamedoesnotexist.doc';

    protected function setUp(): void
    {
        sugar_file_put_contents($this->actualFile, create_guid());
    }

    protected function tearDown(): void
    {
        unlink($this->actualFile);
    }

    public function testProperMimeTypeFetching()
    {
        // This test is a *little* loose since not all servers are the same.
        // Additionally, in some odd cases, PHP errors on finfo and mime_content_type
        // calls and, when this happens, the mime getter will return application/octet-stream
        $dl = new DownloadFile();
        $actual = $dl->getMimeType($this->actualFile);
        $expected = mime_is_detectable() ? 'text/plain' : 'application/octet-stream';
        $this->assertEquals($expected, $actual, "Returned mime type [$actual] was not $expected");

        $mime = $dl->getMimeType($this->mockFile);
        $this->assertFalse($mime, "$mime should be (boolean) FALSE");
    }
}
