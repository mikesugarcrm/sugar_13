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
 * @ticket 42427
 */
class Bug42427Test extends TestCase
{
    private $configBackup = [];

    protected function setUp(): void
    {
        global $sugar_config;
        \SugarConfig::getInstance()->clearCache();
        $this->configBackup['languages'] = $GLOBALS['sugar_config']['languages'];
        $GLOBALS['sugar_config']['languages'] = [
            'en_us' => 'English (US)',
            'fr_test' => 'Test Lang',
            'de_test' => 'Another test Lang',
        ];
        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
        sugar_cache_clear('app_list_strings.de_test');

        if (isset($sugar_config['default_language'])) {
            $this->configBackup['backup_default_language'] = $sugar_config['default_language'];
        }
    }

    protected function tearDown(): void
    {
        $sugar_config = [];
        unlink('include/language/fr_test.lang.php');
        unlink('include/language/de_test.lang.php');

        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
        sugar_cache_clear('app_list_strings.de_test');

        if (isset($this->configBackup['backup_default_language'])) {
            $sugar_config['default_language'] = $this->configBackup['backup_default_language'];
        }
        $GLOBALS['sugar_config']['languages'] = $this->configBackup['languages'];
        \SugarConfig::getInstance()->clearCache();
    }

    public function testWillLoadEnUsStringIfDefaultLanguageIsNotEnUs()
    {
        $sugar_config = [];
        file_put_contents('include/language/fr_test.lang.php', '<?php $app_list_strings = array(); ?>');
        file_put_contents('include/language/de_test.lang.php', '<?php $app_list_strings = array(); ?>');

        $sugar_config['default_language'] = 'fr_test';

        $strings = return_app_list_strings_language('de_test');

        $this->assertArrayHasKey('lead_source_default_key', $strings);
    }
}
