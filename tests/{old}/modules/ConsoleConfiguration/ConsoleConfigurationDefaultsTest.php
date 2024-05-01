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

class ConsoleConfigurationDefaultsTest extends TestCase
{
    // holds any current config already set up in the DB for ConsoleConfiguration
    private $currentConfig;

    protected function setUp(): void
    {
        $admin = BeanFactory::newBean('Administration');
        $this->currentConfig = $admin->getConfigForModule('ConsoleConfiguration', 'base', true);
        sugar_cache_clear('ModuleConfig-ConsoleConfiguration');
        $this->clearConsoleConfigurationConfigs();
    }

    protected function tearDown(): void
    {
        $this->saveConfig($this->currentConfig);
    }

    /**
     * Tests the setupConsoleConfigurationSettings for a fresh install where configs are not in the db
     *
     * @covers ::setupConsoleConfigurationSettings
     */
    public function testSetupConsoleConfigurationSettingsFreshInstall()
    {
        ConsoleConfigurationDefaults::setupConsoleConfigurationSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('ConsoleConfiguration', 'base', true);

        // On fresh install, is_setup should be 0 in the DB
        $this->assertSame(
            0,
            $adminConfig['is_setup'],
            'On a fresh install, ConsoleConfiguration config is_setup should be 0'
        );
    }

    /**
     * Tests that existing config values are preserved when is_setup is equal to 1
     *
     * @covers ::setupConsoleConfigurationSettings
     */
    public function testSetupConsoleConfigurationSettings_IsSetup_ValuesNotChanged()
    {
        $testValue = [
            'fake_console_id' => ['Accounts', 'Opportunities'],
        ];
        $setupConfig = [
            'is_setup' => 1,
            'enabled_modules' => $testValue,
        ];

        $this->saveConfig($setupConfig);
        $defaultConfig = ConsoleConfigurationDefaults::getDefaults();
        ConsoleConfigurationDefaults::setupConsoleConfigurationSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('ConsoleConfiguration', 'base', true);

        // Assert that the saved settings for the fake console ID are preserved
        $this->assertArrayHasKey(
            'fake_console_id',
            $adminConfig['enabled_modules']
        );

        // Assert that the settings also include the settings for the default consoles
        $this->assertEquals(
            $adminConfig['enabled_modules'],
            array_merge($defaultConfig['enabled_modules'], $testValue)
        );
    }

    /**
     * Tests that the config defaults metadata is included as its own config setting
     */
    public function testIncludeDefaultsInConfig()
    {
        ConsoleConfigurationDefaults::setupConsoleConfigurationSettings();
        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('ConsoleConfiguration', 'base', true);
        $defaultConfig = ConsoleConfigurationDefaults::getDefaults();
        $this->assertArrayHasKey('defaults', $adminConfig);
        $this->assertEquals(
            $adminConfig['defaults'],
            $defaultConfig
        );
    }

    /**
     * Tests that existing config values are ignored when is_setup is equal to 0
     *
     * @covers ::setupConsoleConfigurationSettings
     */
    public function testSetupConsoleConfigurationSettings_IsNotSetup_ConfigOverridden()
    {
        $setupConfig = [
            'is_setup' => 0,
            'enabled_modules' => ['not a real module'],
        ];

        $this->saveConfig($setupConfig);

        ConsoleConfigurationDefaults::setupConsoleConfigurationSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('ConsoleConfiguration', 'base', true);
        $defaultConfig = ConsoleConfigurationDefaults::getDefaults();

        // Assert that the default values are not overridden by the config settings
        $this->assertEquals(
            $defaultConfig['enabled_modules'],
            $adminConfig['enabled_modules']
        );
    }

    /**
     * Local function to iterate through a config array and save those settings using the admin bean
     *
     * @param $cfg {Array} an array of key => value pairs of config values for the config table
     */
    protected function saveConfig($cfg)
    {
        $admin = BeanFactory::newBean('Administration');

        foreach ($cfg as $name => $value) {
            $admin->saveSetting('ConsoleConfiguration', $name, $value, 'base');
        }
    }

    /**
     * Clears the Console configs from the database
     */
    protected function clearConsoleConfigurationConfigs()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config WHERE category = 'ConsoleConfiguration'");
    }
}
