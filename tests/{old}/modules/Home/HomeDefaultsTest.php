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

class HomeDefaultsTest extends TestCase
{
    // holds any current config already set up in the DB for Home
    private $currentConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = BeanFactory::newBean('Administration');
        $this->currentConfig = $admin->getConfigForModule('Home');
        $this->clearHomeConfigs();
    }

    protected function tearDown(): void
    {
        $this->saveConfig($this->currentConfig);

        parent::tearDown();
    }

    /**
     * Tests the setupHomeSettings for a fresh install where configs are not in the db
     *
     * @covers ::setupHomeSettings
     */
    public function testsetupHomeSettingsFreshInstall()
    {
        HomeDefaults::setupHomeSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('Home');

        // On fresh install, is_setup should be 0 in the DB
        $this->assertSame(
            'ocean',
            $adminConfig['color'],
            'On a fresh install, Home config color should be ocean'
        );
        $this->assertSame(
            'sicon-home-lg',
            $adminConfig['icon'],
            'On a fresh install, Home config icon should be sicon-home-lg'
        );
        $this->assertSame(
            'icon',
            $adminConfig['display_type'],
            'On a fresh install, Home config display_type should be icon'
        );
    }

    /**
     * Existing config values(if any) should not be overwritten
     *
     * @covers ::setupHomeSettings
     */
    public function testSetupHomeSettings_WithExsitingValues()
    {
        $setupConfig = [
            'color' => 'blue',
            'icon' => 'sicon-arrow-down',
            'display_type' => 'icon',
        ];

        $this->saveConfig($setupConfig);

        HomeDefaults::setupHomeSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('Home');

        $defaultConfig = HomeDefaults::getDefaults();

        $this->assertNotEquals(
            'blue',
            $defaultConfig['color'],
            'Does not change if property already exists in the config table'
        );

        $this->assertSame(
            'blue',
            $adminConfig['color'],
            'Value should be that same earlier if it don'
        );

        $this->assertNotEquals(
            'sicon-arrow-down',
            $defaultConfig['icon'],
            'Does not change if property already exists in the config table'
        );

        $this->assertSame(
            'sicon-arrow-down',
            $adminConfig['icon'],
            'Does not change if property already exists in the config table'
        );
    }

    /**
     * Provide test data for getSettings function test
     *
     * @return array
     */
    public function settingsProvider(): array
    {
        return [
            ['', HomeDefaults::getDefaults()],
            ['color', 'ocean',],
            ['display_type', 'icon',],
            ['icon', 'sicon-home-lg',],
        ];
    }

    /**
     * Test for getSettings function
     *
     * @param string $property to get the setting value
     * @param array|string $result expected result
     * @dataProvider settingsProvider
     * @covers ::getSettings
     */
    public function testGetSettings($property, $result)
    {
        HomeDefaults::setupHomeSettings();

        $settings = HomeDefaults::getSettings($property);

        $this->assertSame(
            $result,
            $settings,
            'Should result specific value of a property and all if property is empty'
        );
    }

    /**
     * Local function to iterate through a config array and save those settings using the adminBean
     *
     * @param array $cfg an array of key => value pairs of config values for the config table
     */
    protected function saveConfig($cfg)
    {
        $admin = BeanFactory::newBean('Administration');

        foreach ($cfg as $name => $value) {
            $admin->saveSetting('Home', $name, $value, 'base');
        }
    }

    /**
     * Clears the Home configs from the database
     */
    protected function clearHomeConfigs()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config WHERE category = 'Home'");
    }
}
