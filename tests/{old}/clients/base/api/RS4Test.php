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
 * RS4: Prepare ConfigModule Api.
 */
class RS4Test extends TestCase
{
    /**
     * @var User
     */
    protected static $admin;

    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var mixed
     */
    protected $config;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$admin = SugarTestHelper::setUp('current_user', [true, true]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        global $current_user;
        $this->api = new ConfigModuleApi();
        $this->config = $this->api->config(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            ['module' => 'Accounts']
        );
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, false);
    }

    protected function tearDown(): void
    {
        $this->api->configSave(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            array_merge(['module' => 'Accounts'], $this->config)
        );
    }

    public function testNoAccess()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->configSave(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts']
        );
    }

    public function testEmptyModule()
    {
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->api->config(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            []
        );
    }

    public function testSave()
    {
        $config = ['RS4Test_param1' => 'value1', 'RS4Test_param2' => ['RS4Test_param3' => 'value2']];
        $result = $this->api->configSave(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            array_merge(['module' => 'Accounts'], $config)
        );
        $this->assertArrayHasKey('RS4Test_param1', $result);
        $this->assertEquals($config['RS4Test_param1'], $result['RS4Test_param1']);
        $this->assertArrayHasKey('RS4Test_param2', $result);
        $this->assertEquals($config['RS4Test_param2'], $result['RS4Test_param2']);
    }
}
