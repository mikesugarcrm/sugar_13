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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Csrf;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Csrf\CsrfAuthenticator
 */
class CsrfAuthenticatorTest extends TestCase
{
    /**
     * @covers ::getFormToken
     * @covers ::getToken
     */
    public function testGetFormToken()
    {
        $csrfToken = $this->getMockBuilder(\Symfony\Component\Security\Csrf\CsrfToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->createMock(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class);

        $manager->expects($this->once())
            ->method('getToken')
            ->with($this->equalTo('session_form'))
            ->will($this->returnValue($csrfToken));

        $manager->expects($this->never())
            ->method('refreshToken');

        $sut = $this->getCsrfAuthMock(null);
        TestReflection::setProtectedValue($sut, 'manager', $manager);
        $sut->getFormToken();
    }

    /**
     * @covers ::isFormTokenValid
     * @covers ::isTokenValid
     * @dataProvider providerTestIsFormTokenValid
     */
    public function testIsFormTokenValid($softFail, array $post, $valid, $expected)
    {
        $manager = $this->createMock(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class);
        $manager->expects($this->any())
            ->method('isTokenValid')
            ->will($this->returnValue($valid));

        $sut = $this->getCsrfAuthMock(null);
        TestReflection::setProtectedValue($sut, 'manager', $manager);
        TestReflection::setProtectedValue($sut, 'softFailForm', $softFail);

        $this->assertEquals($expected, $sut->isFormTokenValid($post));
    }

    public function providerTestIsFormTokenValid()
    {
        return [
            [
                false,  // no soft fail
                [],// no data, always fails
                false,  // token test result
                false,  // expected
            ],
            [
                false,  // no soft fail
                [],// no data, always fails
                true,   // token test result
                false,  // expected
            ],
            [
                false,  // no soft fail
                ['csrf_token' => '1234567890'],
                true,  // token test result
                true,  // expected
            ],
            [
                false,  // no soft fail
                ['csrf_token' => '1234567890'],
                false,  // token test result
                false,  // expected
            ],
            [
                true,  // soft fail
                ['csrf_token' => '1234567890'],
                false,  // token test result
                true,  // expected
            ],
            [
                false,  // no soft fail
                ['wrong_csrf' => '1234567890'],
                true,  // token test result - has no influence on this test
                false,  // expected
            ],
        ];
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Security\Csrf\CsrfAuthenticator
     */
    protected function getCsrfAuthMock(array $methods = null)
    {
        $manager = $this->createMock(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        // SugarConfig stubbing get to always return default
        $config = $this->getMockBuilder('SugarConfig')
            ->setMethods(['get'])
            ->getMock();

        $config->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function () {
                $args = func_get_args();
                return $args[1];
            }));

        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Security\Csrf\CsrfAuthenticator::class)
            ->setConstructorArgs([$manager, $logger, $config])
            ->setMethods($methods)
            ->getMock();
    }
}
