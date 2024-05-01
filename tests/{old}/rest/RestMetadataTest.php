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


class RestMetadataTest extends RestTestBase
{
    public $createdFiles = [];

    protected function tearDown(): void
    {
        // Cleanup
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }

            if (file_exists($file . '.testbackup')) {
                rename($file . '.testbackup', $file);
            }
        }

        sugar_cache_clear('app_strings.en_us');

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testFullMetadata()
    {
        $restReply = $this->restCall('metadata');

        $this->assertTrue(isset($restReply['reply']['_hash']), 'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['modules']), 'Modules are missing.');

        $this->assertTrue(isset($restReply['reply']['fields']), 'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['views']), 'Views are missing.');
        $this->assertTrue(isset($restReply['reply']['currencies']), 'Currencies are missing.');
        $this->assertTrue(isset($restReply['reply']['jssource']), 'JSSource is missing.');
        // SIDECAR-14 - Move server info into the metadata api
        $this->assertTrue(isset($restReply['reply']['server_info']), 'ServerInfo is missing');
    }


    /**
     * @group rest
     */
    public function testFullMetadaNoAuth()
    {
        $restReply = $this->restCall('metadata/public?app_name=superAwesome&platform=portal');
        $this->assertTrue(isset($restReply['reply']['_hash']), 'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['config']), 'Portal Configs are missing.');
        $this->assertTrue(isset($restReply['reply']['fields']), 'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['views']), 'Views are missing.');
        $this->assertTrue(isset($restReply['reply']['jssource']), 'JSSource is missing.');
        // SIDECAR-14 - Move server info into the metadata api
        $this->assertFalse(isset($restReply['reply']['server_info']), 'ServerInfo should not be in public metadata');
    }

    /**
     * @group rest
     */
    public function testMetadataLanguage()
    {
        $langContent = "<?php\n\$app_strings['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'] = 'UnitTest';\n";

        $fileLoc = 'custom/include/language/en_us.lang.php';
        if (file_exists($fileLoc)) {
            rename($fileLoc, $fileLoc . '.testbackup');
        }

        $this->createdFiles[] = $fileLoc;
        SugarAutoLoader::ensureDir('custom/include/language');
        file_put_contents($fileLoc, $langContent);
        sugar_cache_clear('app_strings.en_us');
        $langStrings = return_application_language('en_us');
        $this->assertEquals($langStrings['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], 'UnitTest', 'The override is not taking effect in the same instance, there is no hope for the rest of this test.');
        // No current user
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata/public?platform=portal&type_filter=labels');

        $this->assertArrayHasKey('en_us', $restReply['reply']['labels']);
        $fileLoc = ltrim($GLOBALS['sugar_config']['site_url'], $restReply['reply']['labels']['en_us']);
        $en_us = json_decode(file_get_contents($GLOBALS['sugar_config']['site_url'] . '/' . $restReply['reply']['labels']['en_us']), true);
        $this->assertEquals($en_us['app_strings']['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], 'UnitTest');

        // Current user is logged in & submit language
        $restReply = $this->restCall('metadata');
        $this->assertArrayHasKey('en_us', $restReply['reply']['labels']);
        $en_us = json_decode(file_get_contents($GLOBALS['sugar_config']['site_url'] . '/' . $restReply['reply']['labels']['en_us']), true);
        $this->assertEquals($en_us['app_strings']['LBL_KEYBOARD_SHORTCUTS_HELP_TITLE'], 'UnitTest');

        // TODO add test for user pref when that field gets added
    }
}
