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
 * @coversDefaultClass OutboundSmtpEmailConfiguration
 * @group email
 * @group outboundemailconfiguration
 */
class OutboundSmtpEmailConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testSetSecurityProtocol_PassInAValidProtocol_SecurityProtocolIsSet()
    {
        $configuration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $expected = OutboundSmtpEmailConfiguration::SecurityProtocolSsl;

        $configuration->setSecurityProtocol($expected);
        $actual = $configuration->getSecurityProtocol();
        self::assertEquals($expected, $actual, "The security protocol should have been set to {$expected}");
    }

    public function testSetSecurityProtocol_PassInAnInvalidProtocol_ThrowsException()
    {
        $configuration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $securityProtocol = 'asdf'; // some asinine value that wouldn't actually be used

        $this->expectException(MailerException::class);
        $configuration->setSecurityProtocol($securityProtocol);
    }

    public function testSetMode_ValidModeSmtpIsInAllCaps_ModeBecomesLowerCaseSmtp()
    {
        $configuration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);

        $expected = OutboundEmailConfigurationPeer::MODE_SMTP;
        $configuration->setMode(strtoupper($expected));
        $actual = $configuration->getMode();
        self::assertEquals($expected, $actual, "The mode should have been a {$expected}");
    }

    public function testSetMode_NoMode_ModeBecomesSmtp()
    {
        $configuration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $configuration->setMode('');

        $expected = OutboundEmailConfigurationPeer::MODE_SMTP;
        $actual = $configuration->getMode();
        self::assertEquals($expected, $actual, "The mode should have been a {$expected}");
    }

    /**
     * @covers ::setPassword
     * @covers ::getPassword
     */
    public function testGetPassword()
    {
        $configuration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $configuration->setPassword('abc&amp;');
        $this->assertEquals('abc&', $configuration->getPassword(), 'The smtp password should be html decoded');
    }
}
