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
 * @group email
 * @group outboundemailconfiguration
 */
class OutboundEmailConfigurationPeerTest extends TestCase
{
    private $systemOverrideConfiguration;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        OutboundEmailConfigurationTestHelper::setUp();

        $this->systemOverrideConfiguration =
            OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
                $GLOBALS['current_user']->id
            );
    }

    protected function tearDown(): void
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testListMailConfigurations_NoSystemOrSystemOverrideConfigurationsExist_SystemConfigurationIsNotAllowed_SystemOverrideConfigurationIsCreatedAndReturned()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $configuration = MockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS['current_user']);

        $expected = 'system-override';
        $actual = $configuration[0]->getConfigType();
        $this->assertEquals($expected, $actual, 'The system-override configuration expected');

        $actual = $configuration[0]->getPersonal();
        $this->assertTrue($actual, 'The system-override configuration should be a personal configuration');
    }

    public function testListMailConfigurations_NoSystemOrSystemOverrideConfigurationsExist_SystemConfigurationIsAllowed_SystemConfigurationIsCreatedAndReturned()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $this->setUpMockOutboundEmailConfigurationPeer(true);

        $configuration = MockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS['current_user']);

        $expected = 'system';
        $actual = $configuration[0]->getConfigType();
        $this->assertEquals($expected, $actual, "Expected configuration of type 'system'");

        $actual = $configuration[0]->getPersonal();
        $this->assertFalse($actual, 'The system configuration should not be a personal configuration');
    }

    public function testListMailConfigurations_SystemConfigurationIsNotAllowedAndUserHasUserAndSystemOverrideConfigurations_ReturnsAllExceptTheSystemConfiguration()
    {
        $userConfigurations = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);

        $expected = [
            $this->systemOverrideConfiguration->id => $this->systemOverrideConfiguration->type,
            $userConfigurations[0]['outbound']->id => $userConfigurations[0]['outbound']->type,
            $userConfigurations[1]['outbound']->id => $userConfigurations[1]['outbound']->type,
        ];

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $configurations = MockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS['current_user']);
        $actual = [];

        foreach ($configurations as $configuration) {
            $actual[$configuration->getConfigId()] = $configuration->getConfigType();
        }

        $this->assertEquals($expected, $actual, 'The wrong configurations were returned');
    }

    public function testListMailConfigurations_SystemConfigurationIsAllowedAndUserHasUserAndSystemOverrideConfigurations_ReturnsAllExceptTheSystemOverrideConfiguration()
    {
        $userConfigurations = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $expected = [
            $systemConfiguration->id => $systemConfiguration->type,
            $userConfigurations[0]['outbound']->id => $userConfigurations[0]['outbound']->type,
            $userConfigurations[1]['outbound']->id => $userConfigurations[0]['outbound']->type,
        ];

        $this->setUpMockOutboundEmailConfigurationPeer(true);

        $configurations = MockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS['current_user']);
        $actual = [];

        foreach ($configurations as $configuration) {
            $actual[$configuration->getConfigId()] = $configuration->getConfigType();
        }

        $this->assertEquals($expected, $actual, 'The wrong configurations were returned');
    }

    public function testGetSystemMailConfiguration_SystemConfigurationIsNotAllowed_ReturnsTheUsersSystemOverrideConfiguration()
    {
        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $configuration = MockOutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);

        $expected = $this->systemOverrideConfiguration->id;
        $actual = $configuration->getConfigId();
        $this->assertEquals($expected, $actual, "The user's system-override configuration should have been returned");
    }

    public function testGetSystemMailConfiguration_SystemConfigurationIsAllowed_ReturnsTheSystemConfiguration()
    {
        $this->setUpMockOutboundEmailConfigurationPeer(true);

        $configuration = MockOutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);

        $expected = 'system';
        $actual = $configuration->getConfigType();
        $this->assertEquals($expected, $actual, "The system configuration should be of type 'system'");

        $actual = $configuration->getPersonal();
        $this->assertFalse($actual, 'The system configuration should not be a personal configuration');
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsAllowedAndSystemConfigurationIsValid_ReturnsTrue()
    {
        $this->setUpMockOutboundEmailConfigurationPeer(true);

        $actual = MockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS['current_user']);
        self::assertTrue($actual, 'There should be a system configuration and the host should not be empty');
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsAllowedAndSystemConfigurationIsInvalid_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = [
            'name' => 'System',
            'type' => 'system',
            'user_id' => '1',
            'from_email' => 'foo@bar.com',
            'from_name' => 'Foo Bar',
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => '',
            'mail_smtpport' => '25',
            'mail_smtpuser' => 'foo',
            'mail_smtppass' => 'foobar',
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '0',
        ];
        $configuration = OutboundEmailConfigurationTestHelper::mergeOutboundEmailConfigurations($configuration);
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $this->setUpMockOutboundEmailConfigurationPeer(true);

        $actual = MockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS['current_user']);
        self::assertFalse($actual, 'There should be a system configuration but the host should be empty');
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsNotAllowedAndSystemOverrideConfigurationIsValid_ReturnsTrue()
    {
        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $actual = MockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS['current_user']);
        self::assertTrue($actual, 'There should be a system-override configuration and the host should not be empty');
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsNotAllowedAndSystemOverrideConfigurationIsInvalid_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = [
            'name' => 'System Override',
            'type' => 'system-override',
            'user_id' => $GLOBALS['current_user']->id,
            'from_email' => 'foo@bar.com',
            'from_name' => 'Foo Bar',
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => '',
            'mail_smtpport' => '25',
            'mail_smtpuser' => 'foo',
            'mail_smtppass' => 'foobar',
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '0',
        ];
        $configuration = OutboundEmailConfigurationTestHelper::mergeOutboundEmailConfigurations($configuration);
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $actual = MockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS['current_user']);
        self::assertFalse($actual, 'There should be a system-override configuration but the host should be empty');
    }

    public function testValidSystemMailConfigurationExists_AuthRequired_NoUserPassword_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = [
            'name' => 'System Override',
            'type' => 'system-override',
            'user_id' => $GLOBALS['current_user']->id,
            'from_email' => 'foo@bar.com',
            'from_name' => 'Foo Bar',
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => 'smtp.example.com',
            'mail_smtpport' => '25',
            'mail_smtpuser' => '',
            'mail_smtppass' => '',
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '0',
        ];
        $configuration = OutboundEmailConfigurationTestHelper::mergeOutboundEmailConfigurations($configuration);
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $actual = MockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS['current_user']);
        self::assertFalse($actual, 'There should be a system-override configuration but the host should be empty');
    }


    public function testValidSystemMailConfigurationExists_AuthNotRequired_NoUserOrPassword_ReturnsTrue()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = [
            'name' => 'System Override',
            'type' => 'system-override',
            'user_id' => $GLOBALS['current_user']->id,
            'from_email' => 'foo@bar.com',
            'from_name' => 'Foo Bar',
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => 'smtp.example.com',
            'mail_smtpport' => '25',
            'mail_smtpuser' => '',
            'mail_smtppass' => '',
            'mail_smtpauth_req' => '0',
            'mail_smtpssl' => '0',
        ];
        $configuration = OutboundEmailConfigurationTestHelper::mergeOutboundEmailConfigurations($configuration);
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $actual = MockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS['current_user']);
        self::assertTrue($actual, 'Configuration should be Valid - Auth Not Required - No Name or Password exists');
    }


    public function testValidSystemMailConfigurationExists_AuthRequired_UserPasswordExist_ReturnsTrue()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = [
            'name' => 'System Override',
            'type' => 'system-override',
            'user_id' => $GLOBALS['current_user']->id,
            'from_email' => 'foo@bar.com',
            'from_name' => 'Foo Bar',
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => 'smtp.example.com',
            'mail_smtpport' => '25',
            'mail_smtpuser' => 'mickey',
            'mail_smtppass' => 'mouse',
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '0',
        ];
        $configuration = OutboundEmailConfigurationTestHelper::mergeOutboundEmailConfigurations($configuration);
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $actual = MockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS['current_user']);
        self::assertTrue($actual, 'Configuration should be Valid - Auth Required -  Name and Password exist');
    }

    public function testGetMailConfigurationStatusForUser_NoSMTPServer_ReturnsInvalidSystemConfiguration()
    {
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $configuration = new OutboundEmail();
        $configuration->retrieve($systemConfiguration->id);
        $configuration->mail_smtpserver = '';
        $configuration->save();

        $mockOutboundEmail = $this->createPartialMock('OutboundEmail', ['isAllowUserAccessToSystemDefaultOutbound', 'getSystemMailerSettings']);
        $mockOutboundEmail->expects($this->any())
            ->method('isAllowUserAccessToSystemDefaultOutbound')
            ->will($this->returnValue(false));
        $mockOutboundEmail->expects($this->any())
            ->method('getSystemMailerSettings')
            ->will($this->returnValue([]));

        MockOutboundEmailConfigurationPeer::$outboundEmail = $mockOutboundEmail;
        $status = MockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS['current_user']);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_INVALID_SYSTEM_CONFIG, $status, 'Invalid system configuration should be returned');
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersSet_ReturnsValidConfiguration()
    {
        $this->setUpMockOutboundEmailConfigurationPeer(true);

        $status = MockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS['current_user']);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, 'Should return a valid configuration');
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationNotSet_NoUserData_ReturnsValidUserConfiguration()
    {
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $configuration = new OutboundEmail();
        $configuration->disable_row_level_security = true;
        $configuration->retrieve($systemConfiguration->id);
        $configuration->mail_smtpauth_req = '0';
        $configuration->save();

        $userConfiguration = new OutboundEmail();
        $userConfiguration->retrieve($this->systemOverrideConfiguration->id);
        $userConfiguration->mail_smtpuser = '';
        $userConfiguration->mail_smtppass = '';
        $userConfiguration->save();

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $status = MockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS['current_user']);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, 'The config should be valid');
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationSet_ValidUserData_ReturnsValidConfiguration()
    {
        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $status = MockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS['current_user']);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, 'The configuration should be valid');
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationSet_NoUserData_ReturnsInvalidConfiguration()
    {
        $outboundEmailConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $mockOutboundEmail = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['isAllowUserAccessToSystemDefaultOutbound'])
            ->getMock();
        $mockOutboundEmail->expects($this->any())
            ->method('isAllowUserAccessToSystemDefaultOutbound')
            ->will($this->returnValue(false));

        MockOutboundEmailConfigurationPeer::$outboundEmail = $mockOutboundEmail;
        MockOutboundEmailConfigurationPeer::$systemMailConfiguration = $outboundEmailConfiguration;

        $status = MockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS['current_user']);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_INVALID_USER_CONFIG, $status, 'The user configuration should not be valid');
    }

    public function testGetMailConfigurationStatusForUser_ValidSystemConfig_AllowAllUsersNotSet_SMTPAuthenticationSet_NoUserData_ReturnsValidConfiguration()
    {
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $configuration = new OutboundEmail();
        $configuration->disable_row_level_security = true;
        $configuration->retrieve($systemConfiguration->id);
        $configuration->mail_smtpauth_req = '0';
        $configuration->save();

        $this->setUpMockOutboundEmailConfigurationPeer(false);

        $status = MockOutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($GLOBALS['current_user']);

        $this->assertEquals(OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG, $status, 'The configuration should be valid');
    }

    private function setUpMockOutboundEmailConfigurationPeer($isAllowUserAccessToSystemDefaultOutbound)
    {
        $mockOutboundEmail = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['isAllowUserAccessToSystemDefaultOutbound'])
            ->getMock();
        $mockOutboundEmail->expects($this->any())
            ->method('isAllowUserAccessToSystemDefaultOutbound')
            ->will($this->returnValue($isAllowUserAccessToSystemDefaultOutbound));

        MockOutboundEmailConfigurationPeer::$outboundEmail = $mockOutboundEmail;
    }
}

class MockOutboundEmailConfigurationPeer extends OutboundEmailConfigurationPeer
{
    public static $outboundEmail;
    public static $systemMailConfiguration;

    public static function loadOutboundEmail()
    {
        return self::$outboundEmail;
    }

    public static function getSystemMailConfiguration(User $user, Localization $locale = null, $charset = null)
    {
        if (self::$systemMailConfiguration) {
            return self::$systemMailConfiguration;
        }

        return parent::getSystemMailConfiguration($user, $locale, $charset);
    }
}
