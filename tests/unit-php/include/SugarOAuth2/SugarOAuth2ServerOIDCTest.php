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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarOAuth2;

use OAuth2AuthenticateException;
use OAuth2ServerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SugarApiExceptionNeedLogin;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderApiLoginManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderOIDCManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\IdmNonrecoverableException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\ServiceAccount;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\CodeToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\RefreshToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\RevokeToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\Util\Uuid;
use SugarOAuth2ServerOIDC;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass SugarOAuth2ServerOIDC
 */
class SugarOAuth2ServerOIDCTest extends TestCase
{
    /**
     * @var \SugarOAuth2StorageOIDC|MockObject
     */
    protected $storage;

    /**
     * @var SugarOAuth2ServerOIDC|MockObject
     */
    protected $oAuth2Server;

    /**
     * @var AuthProviderOIDCManagerBuilder|MockObject
     */
    protected $authProviderBuilder;

    /**
     * @var AuthProviderBasicManagerBuilder|MockObject
     */
    protected $authProviderBasicBuilder;

    /**
     * @var AuthProviderApiLoginManagerBuilder|MockObject
     */
    protected $authProviderApiLoginBuilder;

    /**
     * @var AuthenticationProviderManager|MockObject
     */
    protected $authManager;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var User|MockObject
     */
    protected $user;

    /**
     * @var array
     */
    protected $inputData = [];

    /** @var \User|MockObject */
    protected $mockedUser;

    /** @var array */
    protected $beanList;

    /** @var array */
    protected $sugarConfig;

    /** @var array */
    protected $currentUser;

    /** @var \SugarConfig */
    protected $config;

    /**
     * @var string
     */
    protected $stsAccessToken = '4swYymAtLvC9-pGAo3YKJYkLa-7UWFN-jfp5jxP4GfE.wS2Lih_FhXsbyaeZLgpM_1pOIvhCxr-ZgEQXWcKtNko';
    /**
     * @var string
     */
    protected $sugarAccessToken = '956fc0c6-eb25-491c-aa19-411bde06e238';

    protected $idmMode = [
        'enabled' => true,
        'clientId' => 'testLocal',
        'clientSecret' => 'testLocalSecret',
        'stsUrl' => 'http://sts.sugarcrm.local',
        'idpUrl' => 'http://sugar.dolbik.local/idm289idp/web/',
        'stsKeySetId' => 'KeySetName',
        'tid' => 'srn:cluster:sugar::0000000001:tenant',
        'idpServiceName' => 'idm',
        'crmOAuthScope' => 'email account',
    ];

    /**
     * @var array|null
     */
    protected $currentSession = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $sugarConfig = [];
        if (isset($_SESSION)) {
            $this->currentSession = $_SESSION;
        }
        $this->storage = $this->createMock(\SugarOAuth2StorageOIDC::class);
        $this->storage->refreshToken = $this->createMock(\OAuthToken::class);
        $this->oAuth2Server = $this->getMockBuilder(SugarOAuth2ServerOIDC::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAuthProviderBuilder',
                'getAuthProviderBasicBuilder',
                'getAuthProviderApiLoginBuilder',
                'genAccessToken',
                'setLogger',
                'getIdmModeConfig',
                'getIdpConfig',
            ])
            ->getMock();

        TestReflection::setProtectedValue($this->oAuth2Server, 'storage', $this->storage);
        TestReflection::setProtectedValue($this->oAuth2Server, 'logger', \LoggerManager::getLogger());

        $configMock = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([\SugarConfig::getInstance()])
            ->setMethods(['getIdmSettings'])
            ->getMock();

        $idmSettingsMock = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($sugarConfig[Config::IDM_MODE_KEY])) {
            foreach ($this->idmMode as $key => $value) {
                $idmSettingsMock->settings[Config::IDM_MODE_KEY . '_' . $key] = $value;
            }
        }

        $configMock->expects($this->any())
            ->method('getIdmSettings')
            ->willReturn($idmSettingsMock);

        $this->oAuth2Server->method('getIdpConfig')->willReturn($configMock);

        $this->oAuth2Server->method('getidmModeConfig')
            ->willReturn($this->idmMode);

        $this->authProviderBuilder = $this->createMock(AuthProviderOIDCManagerBuilder::class);
        $this->authProviderBasicBuilder = $this->createMock(AuthProviderBasicManagerBuilder::class);
        $this->authProviderApiLoginBuilder = $this->createMock(AuthProviderApiLoginManagerBuilder::class);
        $this->authManager = $this->createMock(AuthenticationProviderManager::class);
        $this->authProviderBuilder->method('buildAuthProviders')->willReturn($this->authManager);
        $this->authProviderBasicBuilder->method('buildAuthProviders')->willReturn($this->authManager);
        $this->authProviderApiLoginBuilder->method('buildAuthProviders')->willReturn($this->authManager);
        $this->sugarUser = $this->createMock(\User::class);
        $this->sugarUser->id = 'testUserId';
        $this->user = new User();
        $this->user->setSugarUser($this->sugarUser);
        $this->inputData = [
            'grant_type' => 'password',
            'scope' => null,
            'username' => 'test',
            'password' => 'test',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ];

        $this->beanList = $GLOBALS['beanList'] ?? null;
        $GLOBALS['beanList'] = ['Users' => 'User'];

        $this->mockedUser = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->mockedUser->id = 'testId';
        $this->mockedUser->module_name = 'Users';
        \BeanFactory::registerBean($this->mockedUser);

        $this->currentUser = $GLOBALS['current_user'] ?? null;
        $GLOBALS['current_user'] = $this->createMock(\User::class);

        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;

        $this->config = \SugarConfig::getInstance();
        $this->config->clearCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        \BeanFactory::unregisterBean($this->mockedUser);

        $GLOBALS['beanList'] = $this->beanList;

        $GLOBALS['sugar_config'] = $this->sugarConfig;
        $this->config->clearCache();

        $GLOBALS['current_user'] = $this->currentUser;

        $_SESSION = $this->currentSession;
    }

    /**
     * @covers ::grantAccessToken
     */
    public function testGrantAccessTokenWithInvalidClient()
    {
        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with('client_id', 'client_secret')
            ->willReturn(false);

        $this->storage->expects($this->never())->method('checkRestrictedGrantType');
        $this->storage->expects($this->never())->method('checkUserCredentials');

        $this->expectException(OAuth2ServerException::class);
        $this->expectExceptionMessage('invalid_client');
        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     */
    public function testGrantAccessTokenWithInvalidGrantType()
    {
        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($this->inputData['client_id'], $this->inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($this->inputData['client_id'], $this->inputData['grant_type'])
            ->willReturn(false);
        $this->storage->expects($this->never())->method('checkUserCredentials');

        $this->expectException(OAuth2ServerException::class);
        $this->expectExceptionMessage('unauthorized_client');
        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * Provides data for testGrantAccessTokenWithEmptyUsernameOrPassword
     *
     * @return array
     */
    public function grantAccessTokenWithEmptyUsernameOrPasswordProvider()
    {
        return [
            'emptyUsername' => [
                'username' => '',
                'password' => 'test',
            ],
            'emptyPassword' => [
                'username' => 'test',
                'password' => '',
            ],
        ];
    }

    /**
     * @covers ::grantAccessToken
     *
     * @param string $username
     * @param string $password
     *
     * @dataProvider grantAccessTokenWithEmptyUsernameOrPasswordProvider
     */
    public function testGrantAccessTokenWithEmptyUsernameOrPassword($username, $password)
    {
        $this->inputData['username'] = $username;
        $this->inputData['password'] = $password;

        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($this->inputData['client_id'], $this->inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($this->inputData['client_id'], $this->inputData['grant_type'])
            ->willReturn(true);
        $this->storage->expects($this->never())->method('checkUserCredentials');

        $this->expectException(OAuth2ServerException::class);
        $this->expectExceptionMessage('invalid_request');
        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     */
    public function testGrantAccessTokenWithInvalidUsernameOrPassword()
    {
        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($this->inputData['client_id'], $this->inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($this->inputData['client_id'], $this->inputData['grant_type'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkUserCredentials')
            ->with($this->inputData['client_id'], $this->inputData['username'], $this->inputData['password'])
            ->willThrowException(new SugarApiExceptionNeedLogin(null));

        $this->expectException(SugarApiExceptionNeedLogin::class);
        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     */
    public function testGrantAccessTokenWithValidUsernameOrPasswordUserJWTBearerFlowError()
    {
        $this->oAuth2Server->setPlatform('test');

        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($this->inputData['client_id'], $this->inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($this->inputData['client_id'], $this->inputData['grant_type'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkUserCredentials')
            ->with($this->inputData['client_id'], $this->inputData['username'], $this->inputData['password'])
            ->willReturn(['user_id' => 'seed_sally_id', 'scope' => null]);

        $this->oAuth2Server->expects($this->once())->method('getAuthProviderApiLoginBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderApiLoginBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(JWTBearerToken::class, $token);
                $this->assertEquals('srn:cluster:iam::0000000001:user:seed_sally_id', $token->getIdentity());
                $this->assertEquals('test', $token->getAttribute('platform'));
                throw new AuthenticationException();
            }
        );

        $this->expectException(SugarApiExceptionNeedLogin::class);
        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     */
    public function testGrantAccessTokenWithValidUsernameOrPasswordUser()
    {
        $this->oAuth2Server->setPlatform('test');

        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($this->inputData['client_id'], $this->inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($this->inputData['client_id'], $this->inputData['grant_type'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkUserCredentials')
            ->with($this->inputData['client_id'], $this->inputData['username'], $this->inputData['password'])
            ->willReturn(['user_id' => 'seed_sally_id', 'scope' => null]);

        $this->oAuth2Server->expects($this->once())->method('getAuthProviderApiLoginBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderApiLoginBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(JWTBearerToken::class, $token);
                $this->assertEquals('srn:cluster:iam::0000000001:user:seed_sally_id', $token->getIdentity());
                $this->assertEquals('test', $token->getAttribute('platform'));
                $token->setAttribute('token', 'resultToken');
                $token->setAttribute('refresh_token', 'testRefreshToken');
                $token->setAttribute('expires_in', '1');
                $token->setAttribute('token_type', 'bearer');
                $token->setAttribute('scope', 'offline');
                $token->setAttribute('srn', 'srn:cluster:iam::0000000001:user:seed_sally_id');
                return $token;
            }
        );

        $this->oAuth2Server->setOldRefreshToken('testOldRefreshTokenId');

        $result = $this->oAuth2Server->grantAccessToken($this->inputData);

        $expectedResult = [
            'access_token' => 'resultToken',
            'expires_in' => '1',
            'token_type' => 'bearer',
            'scope' => 'offline',
            'refresh_token' => 'testRefreshToken',
        ];
        foreach ($expectedResult as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }

        $this->assertEquals('resultToken', $this->storage->refreshToken->download_token);
    }

    /**
     * @covers ::grantAccessToken
     */
    public function testGrantAccessTokenForPortalStore(): void
    {
        $this->storage->expects($this->once())
            ->method('hasPortalStore')
            ->with('client_id')
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($this->inputData['client_id'], $this->inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($this->inputData['client_id'], $this->inputData['grant_type'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkUserCredentials')
            ->with($this->inputData['client_id'], $this->inputData['username'], $this->inputData['password'])
            ->willReturn(['user_id' => 'seed_sally_id', 'scope' => null]);

        $this->oAuth2Server->method('genAccessToken')->willReturn('newToken');

        $result = $this->oAuth2Server->grantAccessToken($this->inputData);
        $this->assertEquals('newToken', $result['access_token']);
    }

    /**
     * @covers ::verifyAccessToken
     * @covers ::setPlatform
     */
    public function testVerifyAccessTokenWithAuthenticationException()
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                throw new AuthenticationException();
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');

        $this->expectException(OAuth2AuthenticateException::class);
        $this->expectExceptionMessage('invalid_grant');
        $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
    }

    /**
     * @covers ::verifyAccessToken
     */
    public function testVerifyAccessTokenWithNonrecoverableException(): void
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                throw new IdmNonrecoverableException('None recoverable');
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');

        $this->expectException(OAuth2AuthenticateException::class);
        $this->expectExceptionMessage('idm_nonrecoverable_error');
        $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
    }

    /**
     * @covers ::verifyAccessToken
     * @covers ::setPlatform
     */
    public function testVerifyAccessTokenWithNotAuthenticationToken()
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                return $token;
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');
        $result = $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
        $this->assertEquals([], $result);
    }

    /**
     * @covers ::verifyAccessToken
     * @covers ::setPlatform
     */
    public function testVerifyAccessToken()
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                $token->setUser($this->user);
                $token->setAuthenticated(true);
                $token->setAttribute('client_id', 'testClient');
                $token->setAttribute('exp', '123');
                return $token;
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');
        $result = $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
        $this->assertEquals(['client_id' => 'testClient', 'user_id' => 'testUserId', 'expires' => '123'], $result);
    }

    /**
     * @covers ::verifyAccessToken
     */
    public function testVerifyAccessTokenWithServiceAccount(): void
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->user = new ServiceAccount();
        $this->user->setSrn('srn:cluster:iam::0000000001:sa:service_account_id');
        $this->user->setSugarUser($this->sugarUser);

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                $token->setUser($this->user);
                $token->setAuthenticated(true);
                $token->setAttribute('client_id', 'testClient');
                $token->setAttribute('exp', '123');
                return $token;
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');
        $result = $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
        $expectedResult = [
            'client_id' => 'testClient',
            'user_id' => 'testUserId',
            'expires' => '123',
            'serviceAccount' => $this->user,
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers ::verifyAccessToken
     */
    public function testVerifyAccessTokenFromPortal()
    {
        $tokenData = [
            'client_id' => 'sugar',
            'user_id' => 'user_id',
            'expires' => (time() + 7200),
        ];

        $this->oAuth2Server->expects($this->never())->method('getAuthProviderBuilder');
        $this->storage->expects($this->once())
            ->method('getAccessToken')
            ->with($this->sugarAccessToken)
            ->willReturn($tokenData);
        $this->assertEquals($tokenData, $this->oAuth2Server->verifyAccessToken($this->sugarAccessToken));
    }

    /**
     * Provides data for testGrantAccessTokenWillThrowException
     *
     * @return array
     */
    public function grantAccessTokenWillThrowExceptionProvider(): array
    {
        return [
            [
                'emptyGrantType' => ['grant_type' => null],
                'refreshTokenGrantAndEmptyToken' => [
                    'grant_type' => \OAuth2::GRANT_TYPE_REFRESH_TOKEN,
                    'refresh_token' => null,
                ],
                'authCodeGrantAndEmptyCode' => [
                    'grant_type' => \OAuth2::GRANT_TYPE_AUTH_CODE,
                    'code' => null,
                    'scope' => 'offline',
                ],
                'authCodeGrantAndEmptyScope' => [
                    'grant_type' => \OAuth2::GRANT_TYPE_AUTH_CODE,
                    'code' => 'code',
                    'scope' => null,
                ],
            ],
        ];
    }

    /**
     * Checks negative logic when parameters are invalid.
     *
     * @param array $inputData
     *
     * @covers ::grantAccessToken()
     *
     * @dataProvider grantAccessTokenWillThrowExceptionProvider
     */
    public function testGrantAccessTokenWillThrowException(array $inputData): void
    {
        $this->expectException(OAuth2ServerException::class);
        $this->expectExceptionMessage('invalid_request');
        $this->oAuth2Server->grantAccessToken($inputData);
    }

    /**
     * Checks logic handled by parent method on refresh flow.
     *
     * @covers ::grantAccessToken()
     *
     * @throws OAuth2ServerException
     */
    public function testGrantAccessTokenHandledByParentRefreshFlow(): void
    {
        $inputData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => Uuid::uuid4(),
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ];

        /** @var SugarOAuth2ServerOIDC|MockObject $oAuth2ServerMock */
        $oAuth2ServerMock = $this->getMockBuilder(SugarOAuth2ServerOIDC::class)
            ->setConstructorArgs([$this->storage, []])
            ->setMethods([
                'getAuthProviderBuilder',
                'getAuthProviderBasicBuilder',
                'genAccessToken',
                'createAccessToken',
            ])
            ->getMock();

        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($inputData['client_id'], $inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($inputData['client_id'], $inputData['grant_type'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('getRefreshToken')
            ->with($inputData['refresh_token'])
            ->willReturn([
                'refresh_token' => 'refresh_token',
                'client_id' => 'client_id',
                'user_id' => 'user_id',
                'expires' => time() + 3600,
            ]);

        $oAuth2ServerMock->expects($this->once())
            ->method('createAccessToken')
            ->with('client_id', 'user_id');

        $oAuth2ServerMock->grantAccessToken($inputData);
    }

    /**
     * Checks logic handled by parent method on auth code flow.
     *
     * @covers ::grantAccessToken()
     */
    public function testGrantAccessTokenHandledByParentAuthCodeFlow(): void
    {
        $inputData = [
            'grant_type' => 'authorization_code',
            'code' => Uuid::uuid4(),
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'scope' => 'offline',
        ];

        /** @var SugarOAuth2ServerOIDC|MockObject $oAuth2ServerMock */
        $oAuth2ServerMock = $this->getMockBuilder(SugarOAuth2ServerOIDC::class)
            ->setConstructorArgs([$this->storage, []])
            ->setMethods([
                'getAuthProviderBuilder',
                'getAuthProviderBasicBuilder',
                'genAccessToken',
                'createAccessToken',
            ])
            ->getMock();

        $this->storage->expects($this->once())
            ->method('checkClientCredentials')
            ->with($inputData['client_id'], $inputData['client_secret'])
            ->willReturn(true);

        $this->storage->expects($this->once())
            ->method('checkRestrictedGrantType')
            ->with($inputData['client_id'], $inputData['grant_type'])
            ->willReturn(true);

        $this->expectException(OAuth2ServerException::class);
        $this->expectExceptionMessage('unsupported_grant_type');
        $oAuth2ServerMock->grantAccessToken($inputData);
    }

    /**
     * Checks auth code flow logic.
     *
     * @covers ::grantAccessToken()
     */
    public function testGrantAccessTokenAuthCodeFlow(): void
    {
        $inputData = [
            'grant_type' => 'authorization_code',
            'code' => 'authorization_code',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'scope' => 'offline',
        ];

        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBasicBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBasicBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(CodeToken::class, $token);
                $this->assertEquals('authorization_code', $token->getCredentials());
                $token->setAttribute('token', 'resultToken');
                $token->setAttribute('refresh_token', 'refreshToken');
                $token->setAttribute('expires_in', '1');
                $token->setAttribute('token_type', 'bearer');
                $token->setAttribute('scope', 'offline');
                return $token;
            }
        );

        $result = $this->oAuth2Server->grantAccessToken($inputData);

        $this->assertEquals('resultToken', $result['access_token']);
        $this->assertEquals('refreshToken', $result['refresh_token']);
        $this->assertEquals('resultToken', $result['download_token']);
    }

    /**
     * Checks refresh token flow logic.
     *
     * @covers ::grantAccessToken()
     */
    public function testGrantAccessTokenAuthRefreshFlow(): void
    {
        $inputData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'refresh_token',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'scope' => 'offline',
        ];

        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBasicBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBasicBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(RefreshToken::class, $token);
                $this->assertEquals('refresh_token', $token->getCredentials());
                $token->setAttribute('token', 'resultToken');
                $token->setAttribute('refresh_token', 'refreshToken');
                $token->setAttribute('expires_in', '1');
                $token->setAttribute('token_type', 'bearer');
                $token->setAttribute('scope', 'offline');
                return $token;
            }
        );

        $result = $this->oAuth2Server->grantAccessToken($inputData);

        $this->assertEquals('resultToken', $result['access_token']);
        $this->assertEquals('refreshToken', $result['refresh_token']);
        $this->assertEquals('resultToken', $result['download_token']);
    }

    /**
     * @covers ::getSudoToken
     */
    public function testGetSudoTokenWithRefresh(): void
    {
        $this->oAuth2Server->expects($this->once())
            ->method('getAuthProviderApiLoginBuilder')
            ->willReturn($this->authProviderApiLoginBuilder);

        $this->storage->expects($this->once())
            ->method('loadUserFromName')
            ->with('testUser', $this->isFalse())
            ->willReturn($this->mockedUser);

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(JWTBearerToken::class, $token);
                $this->assertEquals('srn:cluster:iam::0000000001:user:testId', $token->getIdentity());
                $this->assertEquals('test', $token->getAttribute('platform'));

                $token->setAttribute('token', 'resultToken');
                $token->setAttribute('refresh_token', 'resultRefreshToken');
                $token->setAttribute('expires_in', '1');
                $token->setAttribute('token_type', 'bearer');
                $token->setAttribute('scope', 'offline');
                return $token;
            }
        );

        $resultToken = $this->oAuth2Server->getSudoToken('testUser', 'testClient', 'test', false, true);
        $this->assertIsArray($resultToken);
        $this->assertEquals('resultToken', $resultToken['access_token']);
        $this->assertEquals('resultRefreshToken', $resultToken['refresh_token']);
    }

    /**
     * @covers ::getSudoToken
     */
    public function testGetSudoTokenForInactiveUser(): void
    {
        $currentUser = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $currentUser->id = 'test-admin-Id';
        $currentUser->module_name = 'Users';
        $GLOBALS['current_user'] = $currentUser;

        $this->mockedUser->status = 'Inactive';

        $this->storage->expects($this->once())
            ->method('loadUserFromName')
            ->with('testUser', $this->isTrue())
            ->willReturn($this->mockedUser);

        $this->oAuth2Server->expects($this->once())
            ->method('getAuthProviderApiLoginBuilder')
            ->willReturn($this->authProviderApiLoginBuilder);

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(JWTBearerToken::class, $token);
                $this->assertEquals('srn:cluster:iam::0000000001:user:testId', $token->getIdentity());
                $this->assertEquals('test', $token->getAttribute('platform'));
                $this->assertEquals('srn:cluster:iam::0000000001:user:test-admin-Id', $token->getAttribute('sudoer'));

                $token->setAttribute('token', 'resultToken');
                $token->setAttribute('refresh_token', 'resultRefreshToken');
                $token->setAttribute('expires_in', '1');
                $token->setAttribute('token_type', 'bearer');
                $token->setAttribute('scope', 'offline');
                return $token;
            }
        );

        $resultToken = $this->oAuth2Server->getSudoToken('testUser', 'testClient', 'test', true);
        $this->assertIsArray($resultToken);
        $this->assertEquals('resultToken', $resultToken['access_token']);
        $this->assertArrayNotHasKey('refresh_token', $resultToken);
    }

    /**
     * @covers ::unsetRefreshToken
     */
    public function testUnsetRefreshToken(): void
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBasicBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBasicBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(RevokeToken::class, $token);
                $this->assertEquals('test-token', $token->getCredentials());
                return $token;
            }
        );

        $this->oAuth2Server->unsetRefreshToken('test-token');
    }

    /**
     * @covers ::unsetAccessToken
     */
    public function testUnsetAccessToken(): void
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBasicBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBasicBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(RevokeToken::class, $token);
                $this->assertEquals('test-token', $token->getCredentials());
                return $token;
            }
        );

        $this->oAuth2Server->unsetAccessToken('test-token');
    }
}
