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

/**
 * Bug 57887 - Changes to mobile layouts do not take effect
 */
class RestBug57887Test extends RestTestBase
{
    /**
     * Test view defs
     *
     * @var array
     */
    private $newDefs = [
        'LBL_PANEL_DEFAULT' => [
            ['name', '(empty)'],
            ['phone_office', '(empty)'],
            ['date_modified', '(empty)'],
        ],
    ];

    /**
     * Custom file to be checked and deleted
     * @var string
     */
    private $metadataFile = 'custom/modules/Accounts/clients/mobile/views/detail/detail.php';

    /**
     * List of backed up metadata caches
     *
     * @var array
     */
    private $backedUp = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Backup existing files if needed
        SugarTestHelper::saveFile($this->metadataFile);
        @unlink($this->metadataFile);

        $dir = $this->getMetadataCacheDir();
        $tempdir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/';
        $files = glob($dir . '*.php');
        foreach ($files as $file) {
            $filename = $tempdir . basename($file);
            if (rename($file, $filename)) {
                $this->backedUp[$filename] = $file;
            }
        }

        // Add the current metadata file to the restore list
        if (file_exists($this->metadataFile)) {
            $filename = $tempdir . basename($this->metadataFile);
            $this->backedUp[$filename] = $this->metadataFile;
        }

        //Turn off caching now() or else date_modified checks are invalid
        TimeDate::getInstance()->allow_cache = false;
    }

    protected function tearDown(): void
    {
        // Clear the cache
        $this->clearMetadataCache();

        // Restore the backups
        foreach ($this->backedUp as $temp => $file) {
            rename($temp, $file);
        }

        TimeDate::getInstance()->allow_cache = true;

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCacheIsRefreshedAfterLayoutIsSaved()
    {
        // Build the cache
        $mm = MetaDataManager::getManager('mobile');
        $mm->getMetadata();
        $db = DBManagerFactory::getInstance();

        // Assert that there is a private base metadata cache
        $dateModified = $db->getOne("SELECT date_modified FROM metadata_cache WHERE type='meta_hash_mobile_base'");
        $this->assertNotEmpty($dateModified);

        // Confirm custom file does not exist in the file map cache
        $exists = file_exists($this->metadataFile);
        $this->assertFalse($exists, 'The custom file was found in the file map cache');

        // Make a change to the layouts using the parsers
        $parser = new SidecarGridLayoutMetaDataParser(MB_WIRELESSDETAILVIEW, 'Accounts', '', MB_WIRELESS);
        $parser->_viewdefs['panels'] = $this->newDefs;
        $parser->handleSave(false);

        // Confirm custom file is in the file map cache
        $exists = (bool)file_exists($this->metadataFile);
        $this->assertTrue($exists, 'The custom file was not found in the file map cache');

        // Confirm metadata cache is now updated and that the change was picked
        // up and returned accordingly
        $mm = MetaDataManager::getManager('mobile');
        $data = $mm->getMetadata();
        $panels = $data['modules']['Accounts']['views']['detail']['meta']['panels'];
        $fields = $panels[0]['fields'];
        $this->assertEquals(3, safeCount($fields), 'Fields array should only contain 3 elements');
        $this->assertEquals('date_modified', $fields[2]['name'], 'The third field name should be date_modified');
    }

    protected function getMetadataCacheDir()
    {
        return sugar_cached('api/metadata/');
    }
}
