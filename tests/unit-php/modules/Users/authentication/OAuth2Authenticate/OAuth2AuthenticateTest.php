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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Users\authentication\OAuth2Authenticate;

use OAuth2Authenticate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderApiLoginManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\OAuth2StateRegistry;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @coversDefaultClass \OAuth2Authenticate
 */
class OAuth2AuthenticateTest extends TestCase
{
    /**
     * @var OAuth2Authenticate|MockObject
     */
    protected $authMock;

    /**
     * @var AuthProviderApiLoginManagerBuilder|MockObject
     */
    protected $authProviderBuilder;

    /**
     * @var AuthenticationProviderManager|MockObject
     */
    protected $authManager;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var OAuth2StateRegistry|MockObject
     */
    protected $stateRegistryMock;

    /**
     * @var IdmProvider|MockObject
     */
    protected $idmProviderMock;

    /**
     * @var \SugarConfig
     */
    protected $config;

    /** @var array */
    protected $sugarConfig;


    /**
     * set up
     */
    protected function setUp(): void
    {
        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;
        $GLOBALS['sugar_config'] = [
            'site_url' => 'http://test.sugarcrm.local',
        ];

        $this->config = \SugarConfig::getInstance();
        $this->config->clearCache();

        $this->authMock = $this->getMockBuilder(OAuth2Authenticate::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAuthProviderApiLoginBuilder',
                'getTenant',
                'getIdmProvider',
                'getStateRegistry',
                'createState',
                'getIDMModeConfig',
                'getCurrentUser',
            ])
            ->getMock();

        $this->stateRegistryMock = $this->createMock(OAuth2StateRegistry::class);
        $this->idmProviderMock = $this->createMock(IdmProvider::class);

        $this->sugarUser = $this->createMock(\User::class);
        $this->sugarUser->id = 'userId';

        $this->authProviderBuilder = $this->createMock(AuthProviderApiLoginManagerBuilder::class);
        $this->authManager = $this->createMock(AuthenticationProviderManager::class);

        $this->authMock->method('getAuthProviderApiLoginBuilder')->willReturn($this->authProviderBuilder);
        $this->authMock->method('getTenant')->willReturn('srn:tenant');
        $this->authMock->method('getIdmProvider')->willReturn($this->idmProviderMock);
        $this->authMock->method('getStateRegistry')->willReturn($this->stateRegistryMock);
        $this->authMock->method('getCurrentUser')->willReturn($this->sugarUser);
        $this->authProviderBuilder->method('buildAuthProviders')->willReturn($this->authManager);
    }

    /**
     * tear down
     */
    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->sugarConfig;
        $this->config->clearCache();
    }

    /**
     * @covers ::getLoginUrl
     */
    public function testGetLoginUrlWithValidConfig(): void
    {
        $idmMode = [
            'enabled' => true,
            'clientId' => 'testLocal',
            'clientSecret' => 'testLocalSecret',
            'stsUrl' => 'http://sts.sugarcrm.local',
            'idpUrl' => 'http://idp.url',
            'stsKeySetId' => 'keySetId',
            'requestedOAuthScopes' => ['offline', 'profile'],
            'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
        ];

        $expectedQueryData = [
            'state' => 'base_generated',
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'redirect_uri' => 'http://test.sugarcrm.local/?module=Users&action=OAuth2CodeExchange',
            'client_id' => 'testLocal',
            'scope' => 'offline profile',
            'tenant_hint' => 'srn:cloud:idp:eu:0000000001:tenant',
        ];
        $expectedUrl = 'http://sts.sugarcrm.local/oauth2/auth?' . http_build_query($expectedQueryData);

        $this->authMock->expects($this->any())->method('getIDMModeConfig')->willReturn($idmMode);
        $this->authMock->expects($this->once())->method('createState')->willReturn('generated');
        $this->idmProviderMock->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with([
                'scope' => [
                    'offline',
                    'profile',
                ],
                'state' => 'base_generated',
                'tenant_hint' => 'srn:cloud:idp:eu:0000000001:tenant',
            ])->willReturn($expectedUrl);
        $this->assertEquals($expectedUrl, $this->authMock->getLoginUrl());
    }

    /**
     * @covers ::getLoginUrl
     */
    public function testGetLoginUrlWithEmptyConfig()
    {
        $this->authMock->expects($this->any())->method('getIDMModeConfig')->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->authMock->getLoginUrl();
    }

    /**
     * @covers ::getLogoutUrl
     */
    public function testGetLogoutUrl(): void
    {
        $idmMode = [
            'enabled' => true,
            'clientId' => 'testLocal',
            'clientSecret' => 'testLocalSecret',
            'stsUrl' => 'http://sts.sugarcrm.local',
            'idpUrl' => 'http://idp.url',
            'stsKeySetId' => 'keySetId',
            'requestedOAuthScopes' => ['offline', 'profile'],
            'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
        ];

        $this->authMock->expects($this->any())->method('getIDMModeConfig')->willReturn($idmMode);
        $this->assertEquals(
            'http://idp.url/logout?redirect_uri=http%3A%2F%2Ftest.sugarcrm.local%2F%23logout&' .
            'user_hint=srn%3Acloud%3Aiam%3A%3A0000000001%3Auser%3AuserId',
            $this->authMock->getLogoutUrl()
        );
    }


    /**
     * @covers ::loginAuthenticate
     */
    public function testLoginAuthenticateAuthenticationException()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                throw new AuthenticationException();
            }
        );

        $this->expectException(AuthenticationException::class);
        $this->authMock->loginAuthenticate('user', 'password');
    }

    /**
     * @covers ::loginAuthenticate
     */
    public function testLoginAuthenticateAuthenticationError()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                $token->setAuthenticated(false);
                return $token;
            }
        );
        $this->assertFalse($this->authMock->loginAuthenticate('user', 'password'));
    }

    /**
     * @covers ::loginAuthenticate
     */
    public function testLoginAuthenticate()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function (TokenInterface $token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                $user = new User();
                $user->setSugarUser($this->sugarUser);
                $token->setUser($user);

                $resultToken = new UsernamePasswordToken(
                    $user,
                    $token->getCredentials(),
                    $token->getProviderKey(),
                    $token->getRoleNames()
                );

                return $resultToken;
            }
        );
        $result = $this->authMock->loginAuthenticate('user', 'password');
        $this->assertEquals(['user_id' => 'userId', 'scope' => null], $result);
    }
}
