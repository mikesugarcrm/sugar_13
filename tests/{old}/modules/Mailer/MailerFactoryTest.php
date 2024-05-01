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
 * @group mailer
 */
class MailerFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group bug59513
     */
    public function testGetMailerForUser_UserHasAMailConfiguration_ReturnsSmtpMailerWithExpectedFromEmailAddress()
    {
        $expected = 'foo@bar.com';

        $outboundSmtpEmailConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $outboundSmtpEmailConfiguration->setFrom($expected, 'Foo Bar');

        MailerFactoryTest_MockMailerFactory::$outboundEmailConfiguration = $outboundSmtpEmailConfiguration;

        $mailer = MailerFactoryTest_MockMailerFactory::getMailerForUser($GLOBALS['current_user']);
        $from = $mailer->getHeader(EmailHeaders::From);
        $actual = $from->getEmail();
        self::assertEquals(
            $expected,
            $actual,
            "The mailer should have been an SmtpMailer instance with '{$expected}' as the From email address"
        );
    }

    /**
     * @group bug59513
     * @group functional
     */
    public function testGetMailerForUser_UsesACustomSendingStrategy_MailConfigurationExists_ReturnsCustomMailer()
    {
        SugarTestHelper::ensureDir('custom/modules/Mailer');

        // the name of the custom strategy that is expected
        $namespace = SugarAutoLoader::NS_ROOT . 'custom';
        $class = 'TestMailer';
        $fqn = $namespace . '\\' . $class;

        $outboundSmtpEmailConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $outboundSmtpEmailConfiguration->setFrom('foo@bar.com', 'Foo Bar');

        $strategies = [
            'smtp' => $fqn,
        ];

        MailerFactoryTest_MockMailerFactory::$outboundEmailConfiguration = $outboundSmtpEmailConfiguration;
        MailerFactoryTest_MockMailerFactory::$strategies = $strategies;

        $file = "custom/src/{$class}.php";
        SugarTestHelper::saveFile($file);

        $customMailer = <<<PHP
<?php

namespace {$namespace};

class {$class} extends \BaseMailer
{
    public function connect() {}
    public function send() {}
}

PHP;
        mkdir('custom/src', 0755, true);
        file_put_contents($file, $customMailer);

        $actual = MailerFactoryTest_MockMailerFactory::getMailerForUser($GLOBALS['current_user']);
        self::assertInstanceOf($fqn, $actual, "The mailer should have been a {$fqn}");
    }

    /**
     * @group bug59513
     */
    public function testGetMailerForUser_UserHasNoMailConfigurations_ThrowsMailerException()
    {
        $this->expectException(MailerException::class);
        MockMailerFactoryThrowsException::getMailerForUser($GLOBALS['current_user']);
    }

    public function testGetMailer_ModeIsInvalid_ThrowsException()
    {
        $mockOutboundEmailConfiguration = self::getMockBuilder('OutboundEmailConfiguration')
            ->setMethods(['getMode'])
            ->setConstructorArgs([$GLOBALS['current_user']])
            ->getMock();

        $mockOutboundEmailConfiguration->expects(self::any())
            ->method('getMode')
            ->will(self::returnValue('asdf')); // some asinine value that wouldn't actually be used

        $mockOutboundEmailConfiguration->setFrom('foo@bar.com');

        $this->expectException(MailerException::class);
        MailerFactory::getMailer($mockOutboundEmailConfiguration); // hopefully nothing is actually returned
    }

    /**
     * There is no currently no concept of a non-SMTP mailer. When the mode is default, then the configuration is
     * incapable of sending email even though the record exists. The record may exist because it was an SMTP
     * configuration at one time, but has since changed for some reason. Or, it may exist for legacy reasons or have
     * been mistakenly left around by a unit test. Regardless of the reason, there is no Mailer strategy that matches
     * the "default" mode, so we don't want to support the notion that such a strategy exists.
     */
    public function testGetMailer_ModeIsDefault_ThrowsException()
    {
        $mockOutboundEmailConfiguration = self::getMockBuilder('OutboundEmailConfiguration')
            ->setMethods(['getMode'])
            ->setConstructorArgs([$GLOBALS['current_user']])
            ->getMock();

        $mockOutboundEmailConfiguration->expects(self::any())
            ->method('getMode')
            ->will(self::returnValue('default'));

        $mockOutboundEmailConfiguration->setFrom('foo@bar.com');

        $this->expectException(MailerException::class);
        MailerFactory::getMailer($mockOutboundEmailConfiguration); // hopefully nothing is actually returned
    }
}

class MailerFactoryTest_MockMailerFactory extends MailerFactory
{
    public static $outboundEmailConfiguration;
    public static $strategies;

    public static function getOutboundEmailConfiguration(User $user)
    {
        return self::$outboundEmailConfiguration;
    }

    public static function getStrategies()
    {
        if (!self::$strategies) {
            return parent::getStrategies();
        }

        return self::$strategies;
    }
}

class MockMailerFactoryThrowsException extends MailerFactory
{
    public static function getOutboundEmailConfiguration(User $user)
    {
        throw new MailerException();
    }
}
