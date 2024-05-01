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

require_once 'modules/UpgradeWizard/uw_utils.php';

class Bug41058Test extends TestCase
{
    public $user;
    public $backupConfig;
    public $backupSystemLocaleNameFormat;
    public $loc;

    protected function setUp(): void
    {
        global $sugar_config;

        $this->backupConfig = $sugar_config;

        $this->user = SugarTestUserUtilities::createAnonymousUser(true);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
        $GLOBALS['current_user']->save();

        $this->loc = Localization::getObject();
        if ($this->loc->invalidLocaleNameFormatUpgrade()) {
            rename($this->loc->invalidNameFormatUpgradeFilename, $this->loc->invalidNameFormatUpgradeFilename . '.backup');
        }
    }

    protected function tearDown(): void
    {
        $errors = [];
        global $sugar_config, $sugar_version, $mod_strings;

        if (file_exists($this->loc->invalidNameFormatUpgradeFilename)) {
            unlink($this->loc->invalidNameFormatUpgradeFilename);
        }
        if (file_exists($this->loc->invalidNameFormatUpgradeFilename . '.backup')) {
            rename($this->loc->invalidNameFormatUpgradeFilename . '.backup', $this->loc->invalidNameFormatUpgradeFilename);
        }
        unset($this->loc);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);

        $sugar_config = $this->backupConfig;
        if (!rebuildConfigFile($sugar_config, $sugar_version)) {
            logThis('*** ERROR: could not write config.php!');
            $errors[] = $mod_strings['ERR_UW_CONFIG_WRITE'];
        }

        unset($this->backupSystemLocaleNameFormat);
        unset($sugar_config);
        unset($sugar_version);
        unset($mod_strings);
        unset($app_strings);
        unset($app_list_strings);
        unset($locale);
        unset($_REQUEST);
    }


    /**
     * Tests that Localization::isAllowedNameFormat returns true for valid name formats
     * @param $name_format valid name format from dataProvider
     * @dataProvider goodLocaleNameFormatProvider
     */
    public function testCheckReturnsTrueForValidNameFormats($name_format)
    {
        $this->assertTrue($this->loc->isAllowedNameFormat($name_format));
    }

    /**
     * Tests that Localization::isAllowedNameFormat returns false for invalid name formats
     * @param $name_format invalid name format from dataProvider
     * @dataProvider badLocaleNameFormatProvider
     */
    public function testCheckReturnsFalseForInvalidNameFormats($name_format)
    {
        $this->assertFalse($this->loc->isAllowedNameFormat($name_format));
    }

    /**
     * Tests that good Locale Name Format strings from user preferences get added to the config
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends      testCheckReturnsTrueForValidNameFormats
     */
    public function testUserPreferenceForLocaleNameFormatUpgrade($name_format)
    {
        global $sugar_config;

        $this->user->setPreference('default_locale_name_format', $name_format);
        $this->user->savePreferencesToDB();
        $this->user->save();

        $ulnf = $this->user->getPreference('default_locale_name_format');
        $this->assertSame($name_format, $ulnf);

        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        upgradeUserPreferences();
        $this->assertArrayHasKey($name_format, $sugar_config['name_formats']);
    }

    /**
     * Tests that bad Locale Name Format strings from user preferences do not get added to the config
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends      testCheckReturnsFalseForInvalidNameFormats
     */
    public function testBadUserPreferenceForLocaleNameFormatUpgrade($name_format)
    {
        global $sugar_config;

        $this->user->setPreference('default_locale_name_format', $name_format);
        $this->user->savePreferencesToDB();
        $this->user->save();

        $ulnf = $this->user->getPreference('default_locale_name_format');
        $this->assertSame($name_format, $ulnf);

        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        upgradeUserPreferences();
        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        $coreDefaults = $this->loc->getLocaleConfigDefaults();
        $this->assertSame($coreDefaults['default_locale_name_format'], $this->user->getPreference('default_locale_name_format'));
    }


    /**
     * Tests that good Locale Name Format strings from global preference get added to the config
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends      testCheckReturnsTrueForValidNameFormats
     */
    public function testGlobalPreferenceForLocaleNameFormatUpgrade($name_format)
    {
        $errors = [];
        $mod_strings = [];
        global $sugar_config, $sugar_version;

        $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        $sugar_config['default_locale_name_format'] = $name_format;
        if (!rebuildConfigFile($sugar_config, $sugar_version)) {
            $errors[] = $mod_strings['ERR_UW_CONFIG_WRITE'];
            $this->fail('Could not rebuild config file, please check your installation.');
        }
        upgradeUserPreferences();
        require 'config.php';
        $this->assertSame($name_format, $sugar_config['default_locale_name_format']);
        $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);
    }

    /**
     * Tests that bad Locale Name Format strings from global preference do not get added to the config
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends      testCheckReturnsFalseForInvalidNameFormats
     */
    public function testInvalidGlobalPreferenceForLocaleNameFormatUpgrade($name_format)
    {
        $errors = [];
        $mod_strings = [];
        global $sugar_config, $sugar_version;

        $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        $sugar_config['default_locale_name_format'] = $name_format;
        if (!rebuildConfigFile($sugar_config, $sugar_version)) {
            $errors[] = $mod_strings['ERR_UW_CONFIG_WRITE'];
            $this->fail('Could not rebuild config file, please check your installation.');
        }
        upgradeUserPreferences();
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        require 'config.php';
        $coreDefaults = $this->loc->getLocaleConfigDefaults();
        $this->assertSame($coreDefaults['default_locale_name_format'], $sugar_config['default_locale_name_format']);
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);
    }

    /**
     * Tests that UI presents a message on the locale settings page when there was an invalid name format during an upgrade
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends      testCheckReturnsFalseForInvalidNameFormats
     */
    public function testMessageIsShownWhenInvalidLocaleNameFormatIsFoundInUpgrade($name_format)
    {
        $mod_strings = [];
        global $sugar_config, $locale, $app_strings, $app_list_strings;

        require 'modules/Administration/language/en_us.lang.php';

        $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);

        require_once 'include/utils/layout_utils.php';

        $this->expectOutputRegex('/' . $mod_strings['ERR_INVALID_LOCALE_NAME_FORMAT_UPGRADE'] . '/');
        require 'modules/Administration/Locale.php';
    }

    /**
     * Tests that UI does not present a message on the locale settings page when there wasn't an invalid name format during an upgrade
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends      testCheckReturnsTrueForValidNameFormats
     */
    public function testMessageIsNotShownWhenNoInvalidLocaleNameFormatIsFoundInUpgrade($name_format)
    {
        global $sugar_config, $locale, $app_strings, $app_list_strings;

        require 'modules/Administration/language/en_us.lang.php';

        $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);

        $this->setOutputCallback(function ($output) use ($mod_strings) {
            $this->assertStringNotContainsString($mod_strings['ERR_INVALID_LOCALE_NAME_FORMAT_UPGRADE'], $output);
        });

        require 'modules/Administration/Locale.php';
    }

    /**
     * Test that file gets removed after a save from Locale page
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends      testCheckReturnsFalseForInvalidNameFormats
     */
    public function testFileGetsRemovedAfterLocaleSave($name_format)
    {
        global $sugar_config, $locale, $app_strings, $app_list_strings;
        require 'modules/Administration/language/en_us.lang.php';

        $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);
        try {
            $_REQUEST['process'] = 'true';
            require 'modules/Administration/Locale.php';
        } catch (Exception $e) {
            $this->assertFileDoesNotExist($this->loc->invalidNameFormatUpgradeFilename);
        }
        // this is just to suppress output... remove when this is a proper test
        $this->expectOutputRegex('/Locale/');
    }

    /**
     * Test that bad formats are not added to the list for the dropdown
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     */
    public function testBadFormatsInConfigAreNotIncludedInDropdown($name_format)
    {
        $localeDefaults = $this->loc->getLocaleConfigDefaults();
        $formats = $localeDefaults['name_formats'];

        $formats[$name_format] = $name_format;

        $list = $this->loc->getUsableLocaleNameOptions($formats);
        $this->assertArrayHasKey($name_format, $formats);
        $this->assertArrayNotHasKey($name_format, $list);
    }

    /**
     * Data provider of allowed name formats
     * @return array of allowed name format strings
     */
    public function goodLocaleNameFormatProvider()
    {
        $goodFormatsArray = [
            ['`l` `f` `s`'],
            ['l_f_s'],
            ['*-s-f-l-*'],
            ['{[`~!@#$%^&*()_-+=;:\'"/?\\|.>s, f, l    <]}'],
        ];

        return $goodFormatsArray;
    }

    /**
     * Data provider of disallowed name formats
     * @return array of disallowed name format strings
     */
    public function badLocaleNameFormatProvider()
    {
        $badFormatsArray = [
            ['`l` `f` `s`: `t`'],
            ['alpha-bits'],
            ['*-s-f-l-*-bad_name_format'],
            ['bad{[`~!@#$%^&*()_-+=;:\'"/?\\|.>s, f, l    <]}'],
        ];

        return $badFormatsArray;
    }
}
