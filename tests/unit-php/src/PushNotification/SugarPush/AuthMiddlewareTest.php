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

namespace Sugarcrm\SugarcrmTestsUnit\PushNotification\SugarPush;

use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Token\AccessToken;
use GuzzleHttp\Psr7\Request;
use Psr\SimpleCache\CacheInterface;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\PushNotification\SugarPush\AuthMiddleware;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\PushNotification\SugarPush\AuthMiddleware
 */
class AuthMiddlewareTest extends TestCase
{
    /**
     * @covers ::getApplicationAccessToken
     */
    public function testGetApplicationAccessToken()
    {
        $authMock = $this->getMockBuilder(AuthMiddleware::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheMock = $this->createMock(CacheInterface::class);
        $tokenMock = $this->createPartialMock(AccessToken::class, ['hasExpired']);
        $tokenMock->method('hasExpired')->willReturn(false);
        $providerMock = $this->getMockBuilder(IdmProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccessToken'])
            ->getMock();
        $providerMock->method('getAccessToken')->willReturn($tokenMock);
        TestReflection::setProtectedValue($authMock, 'cache', $cacheMock);
        TestReflection::setProtectedValue($authMock, 'provider', $providerMock);
        $result = TestReflection::callProtectedMethod($authMock, 'getApplicationAccessToken');
        $this->assertEquals($tokenMock, $result);
    }
}
