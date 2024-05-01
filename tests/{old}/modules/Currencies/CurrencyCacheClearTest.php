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

class CurrencyCacheClearTest extends TestCase
{
    /**
     * @var \User
     */
    //@codingStandardsIgnoreStart
    public $_user;
    //@codingStandardsIgnoreEnd

    protected $testCacheFile;

    protected function setUp(): void
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->testCacheFile = sugar_cached('api/metadata/metadata_unit_test.php');
        //Turn off caching now() or else date_modified checks are invalid
        TimeDate::getInstance()->allow_cache = false;

        // Start fresh
        MetaDataManager::clearAPICache(true);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        if (file_exists($this->testCacheFile)) {
            @unlink($this->testCacheFile);
        }
        TimeDate::getInstance()->allow_cache = true;
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        // End fresh
        MetaDataManager::clearAPICache(true);
    }


    public function testResetMetadataCache()
    {
        $db = DBManagerFactory::getInstance();

        // Get the private metadata manager for $platform
        $mm = MetaDataManager::getManager();

        // Get the current metadata to ensure there is a cache built
        $data = $mm->getMetadata();

        // Assert that there is a private base metadata cache
        $dateModified = $db->getOne("SELECT date_modified FROM metadata_cache WHERE type='meta:hash:base'");
        $this->assertNotEmpty($dateModified);

        // Test that currencies are in the metadata
        $this->assertArrayHasKey('currencies', $data, 'currencies key not found in metadata');

        // Force a change in filemtime by sleeping. Not ideal, but it works
        sleep(1);

        // A save call on a Currency bean will refresh the currency section of the metadata
        $defaultCurrency = BeanFactory::newBean('Currencies');
        $defaultCurrency = $defaultCurrency->retrieve('-99');
        $defaultCurrency->save();

        // Get the current metadata to ensure there is a cache built
        $data = $mm->getMetadata();

        // Test the file first
        $newDateModified = $db->getOne("SELECT date_modified FROM metadata_cache WHERE type='meta:hash:base'");
        $this->assertNotEmpty($newDateModified);

        // Test the time on the new file
        $this->assertGreaterThan($dateModified, $newDateModified, 'Second cache file make time is not greater than the first.');

        // Test that currencies are still there
        $data = $mm->getMetadata();

        // Test that currencies are in the metadata
        $this->assertArrayHasKey('currencies', $data, 'currencies key not found in metadata');
    }
}
