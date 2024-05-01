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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\OAuth2\Client\Provider;

use InvalidArgumentException;
use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Grant\ClientCredentials;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider;
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Grant\JwtBearer;
use Psr\SimpleCache\CacheInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider
 */
class IdmProviderTest extends TestCase
{
    /**
     * @var RequestFactory|MockObject
     */
    protected $requestFactory;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var array
     */
    protected $idmModeConfig;

    /**
     * @var \SugarCacheAbstract
     */
    protected $sugarCache;

    /** @var array */
    protected $beanList;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->beanList = $GLOBALS['beanList'] ?? null;
        $GLOBALS['beanList'] = [
            'Administration' => MockAdministration::class,
        ];

        $this->requestFactory = $this->getMockBuilder(RequestFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequestWithOptions'])
            ->getMock();

        $this->request = $this->createMock(RequestInterface::class);
        $this->sugarCache = $this->createMock(CacheInterface::class);

        $this->idmModeConfig = [
            'clientId' => 'srn:test',
            'clientSecret' => 'testSecret',
            'redirectUri' => '',
            'urlAuthorize' => 'http://testUrlAuth',
            'urlAccessToken' => 'http://testUrlAccessToken',
            'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
            'urlUserInfo' => 'http:://testUrlUserInfo',
            'urlRevokeToken' => 'http:://testUrlRevokeToken',
            'keySetId' => 'testSet',
            'urlKeys' => 'http://sts.sugarcrm.local/keys/testSet',
            'idpUrl' => 'http://idp.test',
            'caching' => [
                'ttl' => [
                    'userInfo' => 12,
                    'introspectToken' => 15,
                    'keySet' => 3600,
                ],
            ],
            'requestedOAuthScopes' => [
                'offline',
                'https://apis.sugarcrm.com/auth/crm',
                'profile',
                'email',
                'address',
                'phone',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $GLOBALS['beanList'] = $this->beanList;
        parent::tearDown();
    }

    public function getRequiredOptionsProvider()
    {
        return [
            'missingClientSecret' => [
                [
                    'clientId' => 'testLocal',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingClientId' => [
                [
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingKeySetId' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingUrlKeys' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'keySetId' => 'test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingIdpUrl' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                ],
            ],
            'missingUserInfoUrl' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingUserInfoUrl' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
        ];
    }

    /**
     * @covers ::getRequiredOptions
     *
     * @dataProvider getRequiredOptionsProvider
     */
    public function testGetRequiredOptions(array $options)
    {
        $this->expectException(InvalidArgumentException::class);
        new IdmProvider($options);
    }

    /**
     * @covers ::getAccessToken
     */
    public function testGetAccessTokenOptions()
    {
        $authUrl = 'http://testUrlAuth';

        $grant = $this->getMockBuilder(ClientCredentials::class)
            ->setMethods(['prepareRequestParameters'])
            ->disableOriginalConstructor()
            ->getMock();

        $grant->expects($this->once())
            ->method('prepareRequestParameters')
            ->with($this->isType('array'), $this->isType('array'))
            ->willReturn([
                'client_id' => 'srn:test',
                'client_secret' => 'testSecret',
                'redirect_uri' => '',
                'grant_type' => 'client_credentials',
            ]);

        $response = $this->createMock(RequestInterface::class);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods([
                'verifyGrant',
                'getAccessTokenUrl',
                'getRequest',
                'getParsedResponse',
                'prepareAccessTokenResponse',
                'createAccessToken',
            ])
            ->getMock();

        $provider->expects($this->once())
            ->method('verifyGrant')
            ->willReturn($grant);

        $provider->expects($this->once())
            ->method('getAccessTokenUrl')
            ->willReturn($authUrl);

        $provider->expects($this->once())
            ->method('getRequest')
            ->with($this->equalTo('POST'), $this->equalTo($authUrl), $this->callback(function ($options) {
                $this->assertArrayHasKey('headers', $options);
                $this->assertArrayHasKey('Authorization', $options['headers']);
                $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', urlencode('srn:test'), urlencode('testSecret'))), $options['headers']['Authorization']);
                return true;
            }))
            ->willReturn($response);

        $provider->expects($this->once())->method('getParsedResponse')->willReturn([]);
        $provider->expects($this->once())->method('prepareAccessTokenResponse')->willReturn([]);
        $provider->expects($this->once())->method('createAccessToken');

        $provider->getAccessToken('client_credentials');
    }

    /**
     * @covers ::checkResponse
     */
    public function testInvalidResponse()
    {
        $authUrl = 'http://testUrlAuth';
        $grant = $this->getMockBuilder(AuthorizationCode::class)
            ->setMethods(['prepareRequestParameters'])
            ->disableOriginalConstructor()
            ->getMock();
        $grant->method('prepareRequestParameters')->willReturn(['client_id' => 'srn:test', 'client_secret' => 'secret']);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(json_encode('invalid response'));

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods([
                'verifyGrant',
                'getAccessTokenUrl',
                'getRequest',
                'getResponse',
                'createAccessToken',
            ])
            ->getMock();

        $provider->method('verifyGrant')->willReturn($grant);
        $provider->method('getAccessTokenUrl')->willReturn($authUrl);
        $provider->method('getRequest')->willReturn($request);
        $provider->method('getResponse')->willReturn($response);

        $this->expectException(IdentityProviderException::class);
        $provider->getAccessToken('authorization_code');
    }

    /**
     * @covers ::introspectToken
     */
    public function testIntrospectToken()
    {
        $authUrl = 'http://testUrlAuth';

        $token = new AccessToken(['access_token' => 'token']);

        $response = ['sub' => 'max'];

        $this->requestFactory->expects($this->once())
            ->method('getRequestWithOptions')
            ->with(
                $this->equalTo(IdmProvider::METHOD_POST),
                $this->equalTo($authUrl),
                $this->callback(function ($options) {
                    $this->assertEquals(
                        'Basic c3JuJTNBdGVzdDp0ZXN0U2VjcmV0',
                        $options['headers']['Authorization']
                    );
                    $this->assertEquals('token=token', $options['body']);
                    return true;
                })
            )
            ->willReturn($this->request);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods([
                'getResourceOwnerDetailsUrl',
                'getRequestFactory',
                'getParsedResponse',
                'getSugarCache',
            ])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $provider->expects($this->once())
            ->method('getResourceOwnerDetailsUrl')
            ->willReturn($authUrl);

        $provider->expects($this->once())
            ->method('getRequestFactory')
            ->willReturn($this->requestFactory);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($response);

        $this->sugarCache->expects($this->once())
            ->method('set')
            ->with('oidc_introspect_token_' . hash('sha256', 'token'), $response, 15);

        $provider->introspectToken($token);
    }

    /**
     * @covers ::revokeToken
     */
    public function testRevokeToken(): void
    {
        $authUrl = 'http:://testUrlRevokeToken';

        $token = new AccessToken(['access_token' => 'token']);

        $this->requestFactory->expects($this->once())
            ->method('getRequestWithOptions')
            ->with(
                $this->equalTo(IdmProvider::METHOD_POST),
                $this->equalTo($authUrl),
                $this->callback(function ($options) {
                    $this->assertEquals(
                        'Basic c3JuJTNBdGVzdDp0ZXN0U2VjcmV0',
                        $options['headers']['Authorization']
                    );
                    $this->assertEquals('token=token', $options['body']);
                    return true;
                })
            )
            ->willReturn($this->request);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods([
                'getRequestFactory',
                'getParsedResponse',
                'getSugarCache',
            ])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $provider->expects($this->once())
            ->method('getRequestFactory')
            ->willReturn($this->requestFactory);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn('');

        $provider->revokeToken($token);
    }

    /**
     * @covers ::introspectToken
     */
    public function testIntrospectTokenCanUseCacheAndNotCallRemote()
    {
        $token = new AccessToken(['access_token' => 'token']);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods(['getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $this->sugarCache->method('get')
            ->with('oidc_introspect_token_' . hash('sha256', 'token'))
            ->willReturn('some-introspect-response');

        $provider->expects($this->never())->method('getParsedResponse');
        $this->sugarCache->expects($this->never())->method('set');

        $provider->introspectToken($token);
    }

    /**
     * @covers ::remoteIdpAuthenticate
     */
    public function testRemoteIdpAuthenticate()
    {
        $expectedResult = ['result' => 'success'];
        $accessToken = new AccessToken(['access_token' => 'testToken', 'expires_in' => '900']);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods(
                ['getRequestWithOptions', 'getRequestFactory', 'getParsedResponse', 'getAccessToken']
            )
            ->getMock();

        $provider->expects($this->once())
            ->method('getRequestFactory')
            ->willReturn($this->requestFactory);

        $provider->expects($this->once())
            ->method('getAccessToken')
            ->with('client_credentials', ['scope' => 'https://apis.sugarcrm.com/auth/iam.password'])
            ->willReturn($accessToken);

        $this->requestFactory->expects($this->once())
            ->method('getRequestWithOptions')
            ->with(
                $this->equalTo(IdmProvider::METHOD_POST),
                $this->equalTo('http://idp.test/authenticate'),
                $this->callback(function ($options) {
                    $this->assertEquals('Bearer testToken', $options['headers']['Authorization']);
                    $this->assertEquals('user_name=test&password=test1&tid=srn%3Atenant', $options['body']);
                    return true;
                })
            )
            ->willReturn($this->request);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->request)
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $provider->remoteIdpAuthenticate('test', 'test1', 'srn:tenant'));
    }

    /**
     * @covers ::getJwtBearerAccessToken
     */
    public function testGetJwtBearerAccessToken()
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods(['getAccessToken'])
            ->getMock();

        $provider->expects($this->once())->method('getAccessToken')->willReturnCallback(
            function ($token, $options) {
                $this->assertInstanceOf(JwtBearer::class, $token);
                $this->assertEquals(
                    [
                        'scope' => 'offline https://apis.sugarcrm.com/auth/crm profile email address phone',
                        'assertion' => 'assertion',
                    ],
                    $options
                );
            }
        );
        $provider->getJwtBearerAccessToken('assertion');
    }

    /**
     * @covers ::getKeySet
     */
    public function testGetKeySet()
    {
        $expectedKeys = [
            'keys' => [
                ['private'],
                ['public'],
            ],
        ];
        $expectedResult = [
            'keys' => [
                ['private'],
                ['public'],
            ],
            'keySetId' => 'testSet',
            'clientId' => 'srn:test',
        ];
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods([
                'getAccessToken',
                'getAuthenticatedRequest',
                'getParsedResponse',
                'getSugarCache',
            ])
            ->getMock();

        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $accessToken = new AccessToken(['access_token' => 'testToken', 'expires_in' => '900']);

        $this->sugarCache->expects($this->once())
            ->method('get')
            ->with('oidc_key_set_testSet')
            ->willReturn(null);

        $provider->expects($this->once())
            ->method('getAccessToken')
            ->with('client_credentials', ['scope' => 'hydra.keys.get'])
            ->willReturn($accessToken);

        $provider->expects($this->once())
            ->method('getAuthenticatedRequest')
            ->with(
                IdmProvider::METHOD_GET,
                'http://sts.sugarcrm.local/keys/testSet',
                $accessToken,
                ['scope' => 'hydra.keys.get']
            )->willReturn($this->request);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->request)
            ->willReturn($expectedKeys);

        $this->sugarCache->expects($this->once())
            ->method('set')
            ->with('oidc_key_set_testSet', $expectedKeys['keys'], 3600);

        $this->assertEquals($expectedResult, $provider->getKeySet());
    }

    /**
     * @covers ::getKeySet
     */
    public function testGetKeySetCanUseCacheAndNotCallRemote()
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods(['getAccessToken', 'getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $this->sugarCache->method('get')
            ->with('oidc_key_set_testSet')
            ->willReturn([['private'], ['public']]);

        $provider->expects($this->never())->method('getAccessToken');
        $provider->expects($this->never())->method('getParsedResponse');
        $this->sugarCache->expects($this->never())->method('set');

        $provider->getKeySet();
    }

    /**
     * @covers ::__construct
     */
    public function testProviderUsesOwnHttpClient()
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                [
                    'clientId' => 'test',
                    'clientSecret' => 'testSecret',
                    'redirectUri' => '',
                    'urlAuthorize' => '',
                    'urlAccessToken' => 'http://testUrlAccessToken',
                    'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                    'http_client' => [
                        'retry_count' => 5,
                        'delay_strategy' => 'exponential',
                    ],
                ],
            ])
            ->setMethods(['verifyGrant'])
            ->getMock();

        $httpClient = $provider->getHttpClient();
        $this->assertArrayHasKey('handler', $httpClient->getConfig());
        $this->assertMatchesRegularExpression('/retryDecider.*?Function/', (string)$httpClient->getConfig()['handler']);
    }

    /**
     * @covers ::getUserInfo
     */
    public function testGetUserInfo()
    {
        $token = new AccessToken(['access_token' => 'token']);

        $response = [
            'preferred_username' => 'test',
            'status' => 0,
        ];

        /** @var IdmProvider|MockObject $provider */
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods(['getRequestFactory', 'getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $provider->expects($this->once())
            ->method('getRequestFactory')
            ->willReturn($this->requestFactory);

        $this->requestFactory->expects($this->once())
            ->method('getRequestWithOptions')
            ->with(
                $this->equalTo(IdmProvider::METHOD_POST),
                $this->equalTo('http:://testUrlUserInfo'),
                $this->isType('array')
            )
            ->willReturn($this->request);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->request)
            ->willReturn($response);

        $this->sugarCache->expects($this->once())
            ->method('set')
            ->with('oidc_user_info_' . hash('sha256', 'token'), $response, 12);

        $result = $provider->getUserInfo($token);
        $this->assertEquals('test', $result['preferred_username']);
        $this->assertEquals(0, $result['status']);
    }

    /**
     * @covers ::getUserInfo
     */
    public function testGetUserInfoCanUseCacheAndNotCallRemote()
    {
        $token = new AccessToken(['access_token' => 'token']);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods(['getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $this->sugarCache->method('get')
            ->with('oidc_user_info_' . hash('sha256', 'token'))
            ->willReturn('some-user-info');

        $provider->expects($this->never())->method('getParsedResponse');
        $this->sugarCache->expects($this->never())->method('set');

        $provider->getUserInfo($token);
    }

    public function setCacheDoesNotStoreDataIfTTLIsNotCorrectProvider()
    {
        return [
            [0],
            [null],
        ];
    }

    /**
     * @covers ::setCache
     *
     * @dataProvider setCacheDoesNotStoreDataIfTTLIsNotCorrectProvider
     *
     * @param mixed $ttl
     */
    public function testSetCacheDoesNotStoreDataIfTTLIsNotCorrect($ttl)
    {
        $token = new AccessToken(['access_token' => 'token']);

        $this->idmModeConfig['caching']['ttl']['userInfo'] = $ttl;

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->idmModeConfig])
            ->setMethods(['getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);
        $provider->method('getParsedResponse')->willReturn('some-data');

        $this->sugarCache->expects($this->never())->method('set');

        $provider->getUserInfo($token);
    }

    /**
     * @return array
     * @see testProviderHttpClientProxy
     */
    public function getHttpClientProxy(): array
    {
        return [
            'proxyBase' => [
                'adminSettings' => [
                    'proxy_on' => 1,
                    'proxy_host' => 'proxy.host.local',
                    'proxy_port' => '9180',
                ],
                'expectsProxy' => 'proxy.host.local:9180',
            ],
            'proxyWithAuth' => [
                'adminSettings' => [
                    'proxy_on' => 1,
                    'proxy_host' => 'proxy.host.local',
                    'proxy_port' => '9180',
                    'proxy_auth' => true,
                    'proxy_username' => 'proxyUserName',
                    'proxy_password' => 'proxyPassword',
                ],
                'expectsProxy' => 'proxyUserName:proxyPassword@proxy.host.local:9180',
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::createHttpClient
     * @param array $adminSettings
     * @param string $expectsProxy
     * @dataProvider getHttpClientProxy
     */
    public function testProviderHttpClientProxy(array $adminSettings, string $expectsProxy): void
    {
        MockAdministration::$willReturn = $adminSettings;

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                [
                    'clientId' => 'test',
                    'clientSecret' => 'testSecret',
                    'redirectUri' => '',
                    'urlAuthorize' => '',
                    'urlAccessToken' => 'http://testUrlAccessToken',
                    'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ])
            ->setMethods(['verifyGrant'])
            ->getMock();

        $httpClient = $provider->getHttpClient();
        $config = $httpClient->getConfig();

        $this->assertArrayHasKey('proxy', $config);
        $this->assertEquals($expectsProxy, $config['proxy']);

        $this->assertGreaterThanOrEqual(1, safeCount(MockAdministration::$call));
        $this->assertEquals('proxy', MockAdministration::$call[0][0]);
    }

    /**
     * @covers ::__construct
     * @covers ::createHttpClient
     */
    public function testProviderHttpClientWithoutProxy(): void
    {
        MockAdministration::$willReturn = [
            'proxy_on' => false,
            'proxy_host' => 'proxy.host.local',
            'proxy_port' => '9180',
        ];

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                [
                    'clientId' => 'test',
                    'clientSecret' => 'testSecret',
                    'redirectUri' => '',
                    'urlAuthorize' => '',
                    'urlAccessToken' => 'http://testUrlAccessToken',
                    'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'urlRevokeToken' => 'http:://testUrlRevokeToken',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ])
            ->setMethods(['verifyGrant'])
            ->getMock();

        $httpClient = $provider->getHttpClient();
        $config = $httpClient->getConfig();

        $this->assertArrayNotHasKey('proxy', $config);
        $this->assertGreaterThanOrEqual(1, safeCount(MockAdministration::$call));
        $this->assertEquals('proxy', MockAdministration::$call[0][0]);
    }
}

class MockAdministration
{
    public static $call = [];
    public static $willReturn = [];

    public $settings = [];

    public function retrieveSettings($category = false, $clean = false)
    {
        static::$call[] = [$category, $clean];
        $this->settings = static::$willReturn;
        return $this;
    }
}
