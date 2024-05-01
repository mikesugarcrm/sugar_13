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

class VisualPipelineDefaultsTest extends TestCase
{
    // holds any current config already set up in the DB for VisualPipelines
    private static $currentConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = BeanFactory::newBean('Administration');
        $this->currentConfig = $admin->getConfigForModule('VisualPipeline');
        $this->clearVisualPipelineConfigs();
    }

    protected function tearDown(): void
    {
        $this->saveConfig($this->currentConfig);

        parent::tearDown();
    }

    /**
     * Tests the setupVisualPipelineSettings for a fresh install where configs are not in the db
     *
     * @covers ::setupPipelineSettings
     */
    public function testSetupPipelineSettingsFreshInstall()
    {
        VisualPipelineDefaults::setupPipelineSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('VisualPipeline');

        // On fresh install, is_setup should be 0 in the DB
        $this->assertSame(
            0,
            $adminConfig['is_setup'],
            'On a fresh install, VisualPipeline config is_setup should be 0'
        );
    }

    /**
     * Existing config values are overwritten when is_setup is equal to 1
     *
     * @covers ::setPipelineSettings
     */
    public function testSetupPipelineSettings_IsNotSetup_ConfigOverridden()
    {
        $setupConfig = [
            'is_setup' => 0,
            'table_header' => [],
        ];

        $this->saveConfig($setupConfig);

        VisualPipelineDefaults::setupPipelineSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('VisualPipeline');

        $defaultConfig = VisualPipelineDefaults::getDefaults();

        // Check value from VisualPipelineDefault and make sure they're in the db on upgrade
        $this->assertNotEmpty(
            $defaultConfig['table_header'],
            'On an upgrade with config data existing but NOT set up, new default settings should override pre-existing settings in the config table'
        );
    }

    /**
     * Existing config values are preserved when is_setup is equal to 1
     *
     * @covers ::setupPipelineSettings
     */
    public function testSetupVisualPipelineSettings_IsSetup_ValuesNotChanged()
    {
        $admin = null;
        $testValue = 'visualPipelineTest';
        $setupConfig = [
            'is_setup' => 1,
            'table_header' => $testValue,

        ];

        $this->saveConfig($setupConfig);

        $defaultConfig = VisualPipelineDefaults::getDefaults();

        VisualPipelineDefaults::setupPipelineSettings();

        $adminConfig = $admin->getConfigForModule('VisualPipeline');

        $this->assertSame(
            $testValue,
            $adminConfig['table_header'],
            'On an upgrade with config data already set up, pre-existing settings should be preserved'
        );

        // Check value from VisualPipelineDefaults
        $this->assertSame(
            $defaultConfig['enabled_modules'],
            $adminConfig['enabled_modules'],
            "On an upgrade with config data already set up, default settings that don't override pre-existing settings should be in the config table"
        );
    }

    /**
     * Local function to iterate through a config array and save those settings using the adminBean
     *
     * @param $cfg {Array} an array of key => value pairs of config values for the config table
     */
    protected function saveConfig($cfg)
    {
        $admin = BeanFactory::newBean('Administration');

        foreach ($cfg as $name => $value) {
            $adminBean->saveSetting('VisualPipeline', $name, $value, 'base');
        }
    }

    /**
     * Clears the Tile View configs from the database
     */
    protected function clearVisualPipelineConfigs()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config WHERE category = 'VisualPipeline'");
    }
}
