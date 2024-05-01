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
 * Class ConfigModuleApiTest
 * @coversDefaultClass \ConfigModuleApi
 */
class ConfigModuleApiTest extends TestCase
{
    protected $createdBeans = [];

    protected function setUp(): void
    {
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('moduleList');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $GLOBALS['current_user']->is_admin = 1;
    }

    protected function tearDown(): void
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'testSetting'");
        $db->commit();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * test the create api
     * @group api
     * @covers ::configSave
     */
    public function testCreateConfig()
    {
        // Get the real data that is in the system, not the partial data we have saved

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = [
            'module' => 'Contacts',
            'testSetting' => 'My voice is my passport, verify me',
        ];
        $apiClass = new ConfigModuleApi();
        $result = $apiClass->configSave($api, $args);
        $this->assertArrayHasKey('testSetting', $result);
        $this->assertEquals($result['testSetting'], 'My voice is my passport, verify me');

        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Contacts', 'base');

        $this->assertArrayHasKey('testSetting', $results);
        $this->assertEquals($results['testSetting'], 'My voice is my passport, verify me');
    }

    /**
     * test the get config
     * @group api
     * @covers ::config
     */
    public function testReadConfig()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Contacts', 'testSetting', 'My voice is my passport, verify me', 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'Contacts',
        ];
        $apiClass = new ConfigModuleApi();
        $result = $apiClass->config($api, $args);
        $this->assertArrayHasKey('testSetting', $result);
        $this->assertEquals($result['testSetting'], 'My voice is my passport, verify me');
    }

    /**
     * test the update config
     * @group api
     * @covers ::configSave
     */
    public function testUpdateConfig()
    {
        $testSetting = 'My voice is my passport, verify me';
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Contacts', 'testSetting', $testSetting, 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'Contacts',
            'testSetting' => strrev($testSetting),
        ];
        $apiClass = new ConfigModuleApi();
        $result = $apiClass->configSave($api, $args);
        $this->assertArrayHasKey('testSetting', $result);
        $this->assertEquals($result['testSetting'], strrev($testSetting));

        $results = $admin->getConfigForModule('Contacts', 'base');

        $this->assertArrayHasKey('testSetting', $results);
        $this->assertNotEquals($results['testSetting'], $testSetting);
        $this->assertEquals($results['testSetting'], strrev($testSetting));
    }

    /**
     * test the create api using bad credentials, should receive a failure
     *
     * @group api
     * @covers ::configSave
     */
    public function testCreateBadCredentialsConfig()
    {
        $GLOBALS['current_user']->is_admin = 0;

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = [
            'module' => 'Contacts',
            'testSetting' => 'My voice is my passport, verify me',
        ];
        $apiClass = new ConfigModuleApi();
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $apiClass->configSave($api, $args);
    }

    /**
     * @covers ::configSave
     */
    public function testResaveConfig()
    {
        $admin = BeanFactory::newBean('Administration');
        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $apiClass = new ConfigModuleApi();

        // Let's save the test setting for the first time
        $this->assertConfigUpdated($apiClass, $api, $admin, 'foo');
        // Let's change the test setting and update it
        $this->assertConfigUpdated($apiClass, $api, $admin, 'bar');
    }

    /**
     * Reusable assert that will verify that the setting changes in the testResaveConfig
     *
     * @param ConfigModuleApi $apiClass
     * @param ServiceBase $api
     * @param Administration $admin
     * @param mixed $value
     * @throws SugarApiExceptionNotAuthorized
     * @see testResaveConfig
     */
    private function assertConfigUpdated(ConfigModuleApi $apiClass, ServiceBase $api, Administration $admin, $value)
    {
        $args = [
            'module' => 'Contacts',
            'testSetting' => $value,
        ];

        $result = $apiClass->configSave($api, $args);
        $this->assertArrayHasKey('testSetting', $result);
        $this->assertEquals($value, $result['testSetting']);

        $config = $admin->getConfigForModule('Contacts', 'base');
        $this->assertArrayHasKey('testSetting', $config);
        $this->assertEquals($value, $config['testSetting']);
    }

    /**
     * @dataProvider dataProviderGetPlatform
     * @param string $platform The value to test
     * @param string $expected What should be returned
     * @covers ::getPlatform
     */
    public function testGetPlatform($platform, $expected)
    {
        $apiClass = new ConfigModuleApi();

        $actual = SugarTestReflection::callProtectedMethod($apiClass, 'getPlatform', [$platform]);

        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderGetPlatform()
    {
        return [
            [
                'base',
                'base',
            ],
            [
                'mobile',
                'mobile',
            ],
            [
                'portal',
                'portal',
            ],
            [
                'my_test_platform',
                'base',
            ],
        ];
    }
}
