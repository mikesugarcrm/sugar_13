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


class RestMetadataSugarLayoutsTest extends RestTestBase
{
    /**
     * @var mixed
     */
    public $mobileAuthToken;
    public $baseAuthToken;
    private $testPaths = [
        'wiggle' => 'clients/base/layouts/wiggle/wiggle.php',
        'woggle' => 'custom/clients/base/layouts/woggle/woggle.php',
        'pizzle' => 'clients/mobile/layouts/dizzle/dazzle.php', // Tests improperly named metadata files
        'pozzle' => 'custom/clients/mobile/layouts/pozzle/pozzle.php',
    ];

    private $testFilesCreated = [];

    private $oldFileContents = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->testPaths as $file) {
            preg_match('#clients/(.*)/layouts/#', $file, $m);
            $platform = $m[1];
            $filename = basename($file, '.php');
            $contents = "<?php\n\$viewdefs['$platform']['layout']['$filename'] = array('test' => 'foo');\n";
            if (file_exists($file)) {
                $this->oldFileContents[$file] = file_get_contents($file);
            } else {
                $this->testFilesCreated[] = $file;
                SugarAutoLoader::ensureDir(dirname($file));
            }

            file_put_contents($file, $contents);
        }

        $this->restLogin('', '', 'mobile');
        $this->mobileAuthToken = $this->authToken;
        $this->restLogin('', '', 'base');
        $this->baseAuthToken = $this->authToken;
        $this->clearMetadataCache();
    }

    protected function tearDown(): void
    {
        foreach ($this->oldFileContents as $file => $contents) {
            file_put_contents($file, $contents);
        }

        foreach ($this->testFilesCreated as $file) {
            unlink($file);
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testBaseLayoutRequestAll()
    {
        $this->clearMetadataCache();
        $reply = $this->restCall('metadata');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('wiggle', $reply['reply']['layouts'], 'Test result not found');
    }

    /**
     * @group rest
     */
    public function testBaseLayoutRequestLayoutsOnly()
    {
        $this->clearMetadataCache();
        $reply = $this->restCall('metadata?type_filter=layouts');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('woggle', $reply['reply']['layouts'], 'Test result not found');
    }

    /**
     * @group rest
     */
    public function testMobileLayoutRequestAll()
    {
        $this->authToken = $this->mobileAuthToken;
        $this->clearMetadataCache();
        $reply = $this->restCall('metadata');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('pozzle', $reply['reply']['layouts'], 'Test result not found');
    }

    /**
     * @group rest
     */
    public function testMobileLayoutRequestLayoutsOnly()
    {
        $this->authToken = $this->mobileAuthToken;
        $this->clearMetadataCache();
        $reply = $this->restCall('metadata?type_filter=layouts');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertTrue(!isset($reply['reply']['layouts']['dizzle']), 'Incorrectly picked up metadata that should not have been read');
        $this->assertArrayHasKey('wiggle', $reply['reply']['layouts'], 'BASE metadata not picked up');
        $this->assertNotEmpty($reply['reply']['layouts']['wiggle']['meta']['test'], 'Test result data not returned');
    }
}
