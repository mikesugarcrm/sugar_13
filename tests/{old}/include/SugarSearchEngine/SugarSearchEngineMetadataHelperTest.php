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

class SugarSearchEngineMetadataHelperTest extends TestCase
{
    private $cacheRenamed;
    private $cacheFile;
    private $backupCacheFile;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');

        $this->cacheFile = sugar_cached('modules/ftsModulesCache.php');
        $this->backupCacheFile = sugar_cached('modules/ftsModulesCache.php') . '.save';

        if (file_exists($this->cacheFile)) {
            $this->cacheRenamed = true;
            rename($this->cacheFile, $this->backupCacheFile);
        } else {
            $this->cacheRenamed = false;
        }
    }

    protected function tearDown(): void
    {
        if ($this->cacheRenamed) {
            if (file_exists($this->backupCacheFile)) {
                rename($this->backupCacheFile, $this->cacheFile);
            }
        } elseif (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        SugarTestHelper::tearDown();
    }

    public function testGetFtsSearchFields()
    {
        $ftsFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule('Accounts');
        $this->assertContains('name', array_keys($ftsFields));
        $this->assertArrayHasKey('name', $ftsFields['name'], 'name key not found');

        //Pass in a sugar bean for the test
        $account = BeanFactory::getBean('Accounts', null);
        $ftsFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($account);
        $this->assertContains('name', array_keys($ftsFields));
    }


    public function testGetFtsSearchFieldsForAllModules()
    {
        $ftsFieldsByModule = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
        $this->assertContains('Contacts', array_keys($ftsFieldsByModule));
        $this->assertContains('first_name', array_keys($ftsFieldsByModule['Contacts']));
    }


    public function isModuleEnabledProvider()
    {
        return [
            ['Contacts', true],
            ['BadModule', false],
            ['Notifications', false],
        ];
    }

    /**
     * @dataProvider isModuleEnabledProvider
     */
    public function testIsModuleFtsEnabled($module, $actualResult)
    {
        $expected = SugarSearchEngineMetadataHelper::isModuleFtsEnabled($module);
        $this->assertEquals($expected, $actualResult);
    }

    public function testClearCache()
    {
        // testing clearCache() is dangerous because various UnifiedSearchAdvanced
        // methods depend on the cache values. So, to prevent other tests failing
        // due to us clearing the cache, let's preserve the cache values for
        // every key we're going to clear, and then restore them when we're
        // done.
        $preTestCacheValues = [];
        SugarSearchEngineMetadataHelper::getUserEnabledFTSModules(); // populates the cache.
        $usa = new UnifiedSearchAdvanced();
        $list = $usa->retrieveEnabledAndDisabledModules();
        foreach ($list as $modules) {
            foreach ($modules as $module) {
                $cacheKey = SugarSearchEngineMetadataHelper::FTS_FIELDS_CACHE_KEY_PREFIX . $module['module'];
                $preTestCacheValues[$cacheKey] = sugar_cache_retrieve($cacheKey);
            }
        }

        SugarSearchEngineMetadataHelper::clearCache();

        foreach ($list as $modules) {
            foreach ($modules as $module) {
                $cacheKey = SugarSearchEngineMetadataHelper::FTS_FIELDS_CACHE_KEY_PREFIX . $module['module'];
                $cacheValue = sugar_cache_retrieve($cacheKey);
                $errorMsg = "Cache value for module {$module['module']} is not empty after clearCache().";
                $this->assertTrue(empty($cacheValue), $errorMsg);
            }
        }

        foreach ($preTestCacheValues as $key => $value) {
            sugar_cache_put($key, $value);
        }
    }
}
