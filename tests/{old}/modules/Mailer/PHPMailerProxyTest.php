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
 * @coversDefaultClass PHPMailerProxy
 * @group email
 * @group mailer
 */
class PHPMailerProxyTest extends TestCase
{
    /**
     * Stores the original sugar_config to restore after tests
     * @var array
     */
    protected $oldConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        global $sugar_config;
        $this->oldConfig = $sugar_config;
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        global $sugar_config;
        $sugar_config = $this->oldConfig;
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that the $SMTPKeepAlive flag is set on a fresh PHPMailerProxy
     * according to config settings
     *
     * @dataProvider providerTestSMTPKeepAliveConfig
     * @param $keepAliveConfigValue
     */
    public function testSMTPKeepAliveConfig($keepAliveConfigValue)
    {
        global $sugar_config;
        $sugar_config['smtp_mailer_keep_alive'] = $keepAliveConfigValue;

        $mockProxy = $this->getMockBuilder('PHPMailerProxy')
            ->getMock();

        $this->assertEquals($keepAliveConfigValue, $mockProxy->SMTPKeepAlive);
    }

    /**
     * Provider for testSMTPKeepAliveConfig
     *
     * @return array
     */
    public function providerTestSMTPKeepAliveConfig()
    {
        return [
            [true],
            [false],
        ];
    }
}
