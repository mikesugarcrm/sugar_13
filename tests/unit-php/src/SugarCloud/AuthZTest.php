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

namespace Sugarcrm\SugarcrmTestsUnit\SugarCloud;

use GuzzleHttp\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Sugarcrm\Sugarcrm\SugarCloud\AuthZ;
use Sugarcrm\Sugarcrm\SugarCloud\Discovery;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SugarCloud\AuthZ
 */
class AuthZTest extends TestCase
{
    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var Client|MockObject
     */
    private $httpClient;

    /**
     * @var Discovery|MockObject
     */
    private $discovery;

    /**
     * @var ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $config = [
        'tid' => 'srn:dev:iam:na:2927985500:tenant',
    ];

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $tenant;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @var array
     */
    private $requestParams;

    /**
     * @var AuthZ
     */
    private $authZ;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(CacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->httpClient = $this->createMock(Client::class);
        $this->discovery = $this->createMock(Discovery::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);

        $this->token = 'token';
        $this->tenant = 'srn:dev:iam:na:2927985500:tenant';
        $this->permissions = ['srn:dev:iam:::permission:crm.sa'];
        $this->cacheKey = md5($this->token) . 'authz.srn:dev:iam:na:2927985500:tenant';

        $this->requestParams = [
            'headers' => ['Authorization' => 'Bearer ' . $this->token],
            'body' => json_encode([
                'requested_resource' => $this->tenant,
                'required_permissions' => $this->permissions,
                'token' => $this->token,
                'return_claims' => false,
            ]),
            'timeout' => 10,
        ];

        $this->container->method('get')->willReturnMap(
            [
                [CacheInterface::class, $this->cache],
                [LoggerInterface::class, $this->logger],
            ]
        );

        $this->authZ = new AuthZ($this->config, $this->container, $this->httpClient, $this->discovery);
    }

    /**
     * @covers ::checkPermission
     */
    public function testCheckPermissionWithoutPassedPermissions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->discovery->expects(self::never())->method('getServiceUrl');
        $this->authZ->checkPermission($this->token, $this->tenant, []);
    }

    /**
     * @return array
     */
    public function checkPermissionWithCachedDataProvider(): array
    {
        return [
            'authorizedInCache' => [
                'cacheData' => true,
                'expectedResult' => true,
            ],
            'noAuthorizedInCache' => [
                'cacheData' => false,
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @param bool $cacheData
     * @param bool $expectedResult
     *
     * @covers ::checkPermission
     * @dataProvider checkPermissionWithCachedDataProvider
     */
    public function testCheckPermissionWithCachedData(bool $cacheData, bool $expectedResult): void
    {
        $this->discovery->expects(self::never())->method('getServiceUrl');
        $this->cache->expects(self::once())
            ->method('get')
            ->with($this->cacheKey)
            ->willReturn($cacheData);

        self::assertEquals(
            $expectedResult,
            $this->authZ->checkPermission($this->token, $this->tenant, $this->permissions)
        );
    }

    /**
     * @covers ::checkPermission
     */
    public function testCheckPermissionDiscoveryError(): void
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with($this->cacheKey)
            ->willReturn(null);

        $this->discovery->expects(self::once())
            ->method('getServiceUrl')
            ->with('iam-authz-http:v1alpha')
            ->willReturn(null);

        self::assertFalse($this->authZ->checkPermission($this->token, $this->tenant, $this->permissions));
    }

    /**
     * @return array
     */
    public function checkPermissionBadResponseCodeProvider(): array
    {
        return [
            '400code' => [
                'code' => Response::HTTP_BAD_REQUEST,
            ],
            '500code' => [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
        ];
    }

    /**
     * @param int $code
     *
     * @covers ::checkPermission
     *
     * @dataProvider checkPermissionBadResponseCodeProvider
     */
    public function testCheckPermissionBadResponseCode(int $code): void
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with($this->cacheKey)
            ->willReturn(null);

        $this->discovery->expects(self::once())
            ->method('getServiceUrl')
            ->with('iam-authz-http:v1alpha')
            ->willReturn('https://authz.url');

        $this->httpClient->expects(self::once())
            ->method('__call')
            ->with('post', ['https://authz.url/v1alpha/iam/authz/authorize-token', $this->requestParams])
            ->willReturn($this->response);

        $this->response->expects(self::once())->method('getStatusCode')->willReturn($code);

        self::assertFalse($this->authZ->checkPermission($this->token, $this->tenant, $this->permissions));
    }

    /**
     * @return array
     */
    public function checkPermissionContentFromAuthZProvider(): array
    {
        return [
            'invalidJson' => [
                'json' => '{1234',
                'expectedResult' => false,
            ],
            'unknownJsonStructure' => [
                'json' => json_encode(['a' => 1]),
                'expectedResult' => false,
            ],
            'validJsonStructureNotAuthorised' => [
                'json' => json_encode(['authorized' => false]),
                'expectedResult' => false,
            ],
            'validJsonStructureAuthorised' => [
                'json' => json_encode(['authorized' => true]),
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @param string $json
     * @param bool $expectedResult
     *
     * @covers ::checkPermission
     *
     * @dataProvider checkPermissionContentFromAuthZProvider
     */
    public function testCheckPermissionContentFromAuthZ(string $json, bool $expectedResult): void
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with($this->cacheKey)
            ->willReturn(null);

        $this->discovery->expects(self::once())
            ->method('getServiceUrl')
            ->with('iam-authz-http:v1alpha')
            ->willReturn('https://authz.url');

        $this->httpClient->expects(self::once())
            ->method('__call')
            ->with('post', ['https://authz.url/v1alpha/iam/authz/authorize-token', $this->requestParams])
            ->willReturn($this->response);

        $this->response->expects(self::once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->response->expects(self::once())->method('getBody')->willReturn($this->stream);
        $this->stream->expects(self::once())->method('getContents')->willReturn($json);

        self::assertEquals(
            $expectedResult,
            $this->authZ->checkPermission($this->token, $this->tenant, $this->permissions)
        );
    }
}
