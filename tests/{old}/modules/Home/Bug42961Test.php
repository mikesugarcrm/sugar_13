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
 * @brief Try to find force_unifedsearch fields
 * @ticket 42961
 */
class Bug42961Test extends TestCase
{
    /**
     * @brief generation of new cache file and search for force_unifiedsearch fields in it
     * @group 42961
     */
    public function testBuildCache()
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $unifiedSearchAdvanced = new UnifiedSearchAdvanced();
        $unifiedSearchAdvanced->buildCache();
        $this->assertFileExists($GLOBALS['sugar_config']['cache_dir'] . 'modules/unified_search_modules.php', 'Here should be cache file with data');
        include $GLOBALS['sugar_config']['cache_dir'] . 'modules/unified_search_modules.php';
        $force_unifiedsearch = 0;
        foreach ($unified_search_modules as $moduleName => $moduleInformation) {
            foreach ($moduleInformation['fields'] as $fieldName => $fieldInformation) {
                if (array_key_exists('force_unifiedsearch', $fieldInformation)) {
                    $force_unifiedsearch++;
                }
            }
        }
        $this->assertGreaterThan(0, $force_unifiedsearch, 'Here should be fields with force_unifiedsearch key');
    }
}
