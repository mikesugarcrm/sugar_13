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

namespace Sugarcrm\SugarcrmTestsUnit\modules\EAPM\clients\base\api;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AuthApi
 */
class AuthApiTest extends TestCase
{
    /**
     * @covers ::getAuthInfo
     */
    public function testGetAuthInfo()
    {
        $this->expectException(\SugarApiExceptionMissingParameter::class);

        $expected = [
            'auth_warning' => 'fake_warning',
            'auth_url' => 'fake_url',
        ];

        $authApiMock = $this->getMockBuilder('\AuthApi')
            ->setMethods(['getExternalApi', 'getAuthWarning'])
            ->getMock();

        $extApiMock = $this->getMockBuilder('\ExtAPIGoogleEmail')
            ->setMethods(['getClient'])
            ->getMock();

        $clientMock = $this->getMockBuilder('\\' . \Google\Client::class)
            ->setMethods(['createAuthUrl'])
            ->getMock();

        $clientMock->method('createAuthUrl')
            ->willReturn('fake_url');

        $extApiMock->method('getClient')
            ->willReturn($clientMock);

        $authApiMock->method('getExternalApi')
            ->willReturn($extApiMock);

        $authApiMock->method('getAuthWarning')
            ->willReturn('fake_warning');

        $result = $authApiMock->getAuthInfo(new \RestService(), ['application' => 'GoogleEmail']);

        $this->assertSame($expected, $result);

        // expect exception
        $authApiMock->getAuthInfo(new \RestService(), []);
    }
}
