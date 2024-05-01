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
use Sugarcrm\Sugarcrm\SugarCloud\Discovery;
use Sugarcrm\Sugarcrm\SugarCloud\UserApi;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SugarCloud\UserApi
 */
class UserApiTest extends TestCase
{
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
     * @var UserApi
     */
    private $userApi;

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
    private $userSrn = 'srn:dev:iam::2927985500:user:12345';

    /**
     * @var string
     */
    private $token = 'access-token';

    /**
     * @var string
     */
    private $userApiUrl = 'https://user-api.url';

    /**
     * @var string
     */
    private $expectedResetMfaEndpoint;

    /**
     * @var array
     */
    private $expectedRequestParams;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->httpClient = $this->createMock(Client::class);
        $this->discovery = $this->createMock(Discovery::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);

        $this->container->method('get')->willReturnMap(
            [
                [LoggerInterface::class, $this->logger],
            ]
        );

        $this->expectedResetMfaEndpoint = $this->userApiUrl . UserApi::RESET_MFA_ENDPOINT . '/' . $this->userSrn;
        $this->expectedRequestParams = [
            'headers' => ['Authorization' => 'Bearer ' . $this->token],
            'timeout' => UserApi::REQUEST_TIMEOUT,
        ];

        $this->userApi = new UserApi($this->httpClient, $this->discovery, $this->container);
    }

    /**
     * @covers ::resetMfa
     */
    public function testResetMfaDiscoveryError(): void
    {
        $this->discovery->expects(self::once())
            ->method('getServiceUrl')
            ->with('iam-user-http:v1alpha')
            ->willReturn(null);

        $this->assertFalse($this->userApi->resetMfa($this->userSrn, $this->token));
    }

    /**
     * @covers ::resetMfa
     */
    public function testResetMfaRequestException(): void
    {
        $this->discovery->expects(self::once())
            ->method('getServiceUrl')
            ->with('iam-user-http:v1alpha')
            ->willReturn($this->userApiUrl);
        $this->httpClient->expects($this->once())
            ->method('__call')
            ->with('put', [$this->expectedResetMfaEndpoint, $this->expectedRequestParams])
            ->willThrowException(new \Exception('test'));

        $this->logger->expects($this->once())->method('error');

        $this->assertFalse($this->userApi->resetMfa($this->userSrn, $this->token));
    }

    /**
     * @covers ::resetMfa
     */
    public function testResetMfaBadResponseFromApi(): void
    {
        $this->discovery->expects(self::once())
            ->method('getServiceUrl')
            ->with('iam-user-http:v1alpha')
            ->willReturn($this->userApiUrl);
        $this->httpClient->expects($this->once())
            ->method('__call')
            ->with('put', [$this->expectedResetMfaEndpoint, $this->expectedRequestParams])
            ->willReturn($this->response);

        $this->response->expects($this->once())->method('getStatusCode')->willReturn(400);
        $this->response->expects($this->once())->method('getBody')->willReturn($this->stream);
        $this->logger->expects($this->once())->method('error');

        $this->assertFalse($this->userApi->resetMfa($this->userSrn, $this->token));
    }

    /**
     * @covers ::resetMfa
     */
    public function testResetMfa(): void
    {
        $this->discovery->expects(self::once())
            ->method('getServiceUrl')
            ->with('iam-user-http:v1alpha')
            ->willReturn($this->userApiUrl);
        $this->httpClient->expects($this->once())
            ->method('__call')
            ->with('put', [$this->expectedResetMfaEndpoint, $this->expectedRequestParams])
            ->willReturn($this->response);

        $this->response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $this->logger->expects($this->never())->method('error');

        $this->assertTrue($this->userApi->resetMfa($this->userSrn, $this->token));
    }
}
