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
 * @coversDefaultClass Administration
 */
class AdministrationTest extends TestCase
{
    protected $configs = [
        ['name' => 'AdministrationTest', 'value' => 'Base', 'platform' => 'base', 'category' => 'Forecasts'],
        ['name' => 'AdministrationTest', 'value' => 'Portal', 'platform' => 'portal', 'category' => 'Forecasts'],
        ['name' => 'AdministrationTest', 'value' => '["Portal"]', 'platform' => 'json', 'category' => 'Forecasts'],
    ];

    public static function setUpBeforeClass(): void
    {
        sugar_cache_clear('admin_settings_cache');
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('moduleList');
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        foreach ($this->configs as $config) {
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
    }

    protected function tearDown(): void
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        $db->commit();
    }

    public function testRetrieveSettingsByInvalidModuleReturnsEmptyArray()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('InvalidModule', 'base');

        $this->assertEmpty($results);
    }

    public function testRetrieveSettingsByValidModuleWithPlatformReturnsOneRow()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertTrue(safeCount($results) > 0);
    }

    public function testRetrieveSettingsByValidModuleWithPlatformOverRidesBasePlatform()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'portal');

        $this->assertEquals('Portal', $results['AdministrationTest']);
    }

    public function testCacheExist()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertNotEmpty(sugar_cache_retrieve('ModuleConfig-Forecasts'));
    }

    public function testCacheSameAsReturn()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertSame($results, sugar_cache_retrieve('ModuleConfig-Forecasts'));
    }

    /**
     * @dataProvider providerTestCacheClearedAfterSave
     * @param string $platform The platform of the setting being saved
     * @param bool $shouldClearBaseCache Whether the base cache should be cleared after save
     * @param bool $shouldClearMobileCache Whether the mobile cache should be cleared after save
     */
    public function testCacheClearedAfterSave($platform, $shouldClearBaseCache, $shouldClearMobileCache)
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        // Ensure that getConfigForModule has filled the cache for base and mobile
        sugar_cache_clear('ModuleConfig-Forecasts');
        sugar_cache_clear('ModuleConfig-Forecastsmobile');
        $admin->getConfigForModule('Forecasts');
        $admin->getConfigForModule('Forecasts', 'mobile');
        $this->assertNotEmpty(sugar_cache_retrieve('ModuleConfig-Forecasts'));
        $this->assertNotEmpty(sugar_cache_retrieve('ModuleConfig-Forecastsmobile'));

        // Save new base settings and confirm that the caches are cleared appropriately
        $admin->saveSetting('Forecasts', 'AdministrationTest', 'testCacheClearedAfterSave', $platform);
        $shouldClearBaseCache ? $this->assertEmpty(sugar_cache_retrieve('ModuleConfig-Forecasts')) :
            $this->assertNotEmpty(sugar_cache_retrieve('ModuleConfig-Forecasts'));
        $shouldClearMobileCache ? $this->assertEmpty(sugar_cache_retrieve('ModuleConfig-Forecastsmobile')) :
            $this->assertNotEmpty(sugar_cache_retrieve('ModuleConfig-Forecastsmobile'));
    }

    /**
     * Provider for the testCacheClearedAfterSave function
     * @return array[]
     */
    public function providerTestCacheClearedAfterSave()
    {
        // [platform, shouldClearBaseCache, shouldClearMobileCache]
        return [
            ['base', true, true],
            ['mobile', false, true],
        ];
    }

    public function testJsonValueIsArray()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'json');

        $this->assertEquals(['Portal'], $results['AdministrationTest']);
    }

    /**
     * @dataProvider configValueIntegrityProvider
     */
    public function testConfigValueIntegrity($value, $expected)
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('PHPUnit', 'Test', $value, 'base');
        $config = $admin->getConfigForModule('PHPUnit', 'base', true);
        $this->assertSame($expected, $config['Test']);
    }

    /**
     * @return array
     */
    public function configValueIntegrityProvider()
    {
        return [
            ['A', 'A'], // simple string
            ['A\\B', 'A\\B'], // slashes
            ['Русский', 'Русский'], // unicode
            ['7.0', '7.0'], // simple number
            ['7.0.0', '7.0.0'],
            [7, 7],      // integer
            [['portal'], ['portal']], // indexed array
            [['foo' => 'bar'], ['foo' => 'bar']], // associative array
            ['"value1"', '"value1"'], // quoted string
            [[2 => '"val"ue2'], [2 => '"val"ue2']], // array with quoted string
        ];
    }

    /**
     * @covers ::saveConfig
     */
    public function testSaveConfig()
    {
        // Don't allow the user to use the system configuration to guarantee that the true system configuration's name
        // and email address are retrieved from the database instead of being replaced by the user's name and primary
        // email address.
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        $_POST['mail_smtpserver'] = 'smtp.example.com';
        $_POST['mail_smtpport'] = 1025;
        $_POST['notify_fromname'] = 'Sugar';
        $_POST['notify_fromaddress'] = 'sugar@ex.com';
        // The following are ignored.
        $_POST['type'] = 'system-override';
        $_POST['email_address'] = 'foo@bar.com';
        $_POST['test'] = 'test';

        $admin = BeanFactory::newBean('Administration');
        $admin->saveConfig();

        unset($_POST['mail_smtpserver']);
        unset($_POST['mail_smtpport']);
        unset($_POST['notify_fromname']);
        unset($_POST['notify_fromaddress']);
        unset($_POST['type']);
        unset($_POST['email_address']);

        $this->assertSame('Sugar', $admin->settings['notify_fromname'], 'notify_fromname is incorrect');
        $this->assertSame('sugar@ex.com', $admin->settings['notify_fromaddress'], 'notify_fromaddress is incorrect');

        $oe = BeanFactory::newBean('OutboundEmail');
        $system = $oe->getSystemMailerSettings();
        $this->assertSame('smtp.example.com', $system->mail_smtpserver, 'The servers should match');
        $this->assertEquals(1025, $system->mail_smtpport, 'The ports should match');
        $this->assertSame('Sugar', $system->name, 'The names should match');
        $this->assertSame('sugar@ex.com', $system->email_address, 'The email addresses should match');

        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE config SET value='do_not_reply@example.com' WHERE name='fromaddress' AND category='notify'");
        $db->query("UPDATE config SET value='SugarCRM' WHERE name='fromname' AND category='notify'");
        OutboundEmailConfigurationTestHelper::tearDown();
    }
}
