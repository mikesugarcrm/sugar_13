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
use Sugarcrm\Sugarcrm\SugarCloud\Discovery;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SugarCloud\Discovery
 */
class DiscoveryTest extends TestCase
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
    private $services = '{"services":[
      {
         "name":"discovery:v1",
         "type":"rest",
         "endpoints":[
            {
               "url":"https://discovery.service.sugarcrm.com",
               "region":"us-west-2"
            },
            {
               "url":"https://discovery.service.sugarcrm.com",
               "region":"eu-west-1"
            }
         ]
      },
      {
         "name":"sts-issuer",
         "type":"rest",
         "endpoints":[
            {
               "url":"https://sts-usw2.service.sugarcrm.com",
               "region":"us-west-2"
            },
            {
               "url":"https://sts-euw1.service.sugarcrm.com",
               "region":"eu-west-1"
            }
         ]
      }]}';

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(CacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->httpClient = $this->createMock(Client::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);

        $this->container->method('get')->willReturnMap(
            [
                [CacheInterface::class, $this->cache],
                [LoggerInterface::class, $this->logger],
            ]
        );
    }

    /**
     * @return array[]
     */
    public function getServiceUrlProvider(): array
    {
        return [
            'serviceExistsForRegion' => [
                'name' => 'sts-issuer',
                'config' => [
                    'tid' => 'srn:cloud:iam:us-west-2:2927985500:tenant',
                    'discoveryUrl' => 'https://discovery.url',
                ],
                'data' => $this->services,
                'expected' => 'https://sts-usw2.service.sugarcrm.com',
            ],
            'serviceDoesNotExistsForRegion' => [
                'name' => 'sts-issuer',
                'config' => [
                    'tid' => 'srn:cloud:iam:us-west-1:2927985500:tenant',
                    'discoveryUrl' => 'https://discovery.url',
                ],
                'data' => $this->services,
                'expected' => null,
            ],
            'serviceDoesNotExists' => [
                'name' => 'service',
                'config' => [
                    'tid' => 'srn:cloud:iam:us-west-1:2927985500:tenant',
                    'discoveryUrl' => 'https://discovery.url',
                ],
                'data' => $this->services,
                'expected' => null,
            ],
        ];
    }

    /**
     * @param string $name
     * @param array $config
     * @param string $data
     * @param string|null $expected
     *
     * @dataProvider getServiceUrlProvider
     *
     * @covers ::getServiceUrl
     */
    public function testGetServiceUrlWithCache(
        string  $name,
        array   $config,
        string  $data,
        ?string $expected
    ): void {

        $data = json_decode($data, true);
        $discovery = new Discovery($config, $this->container, $this->httpClient);
        $this->cache->expects(self::once())
            ->method('get')
            ->with('discovery.' . $config['tid'])
            ->willReturn($data);

        $this->httpClient->expects(self::never())->method('__call');
        $this->cache->expects(self::never())->method('set');

        self::assertEquals($expected, $discovery->getServiceUrl($name));
    }

    /**
     * @param string $name
     * @param array $config
     * @param string $data
     * @param string|null $expected
     *
     * @dataProvider getServiceUrlProvider
     *
     * @covers ::getServiceUrl
     */
    public function testGetServiceUrlWithHttpRequest(
        string  $name,
        array   $config,
        string  $data,
        ?string $expected
    ): void {

        $discovery = new Discovery($config, $this->container, $this->httpClient);
        $this->cache->expects(self::once())
            ->method('get')
            ->with('discovery.' . $config['tid'])
            ->willReturn(null);
        $this->cache->expects(self::once())
            ->method('set')
            ->with('discovery.' . $config['tid'], json_decode($data, true), 86400);

        $this->httpClient->expects(self::once())
            ->method('__call')
            ->with('get', ['https://discovery.url/v1/services', ['timeout' => 2]])
            ->willReturn($this->response);
        $this->response->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->response->expects(self::once())->method('getBody')->willReturn($this->stream);
        $this->stream->expects(self::once())->method('getContents')->willReturn($data);

        self::assertEquals($expected, $discovery->getServiceUrl($name));
    }

    /**
     * @covers ::getServiceUrl
     */
    public function testGetServiceUrlResponseCodeFromDiscoveryNotOK(): void
    {
        $config = [
            'tid' => 'srn:cloud:iam:us-west-2:2927985500:tenant',
            'discoveryUrl' => 'https://discovery.url/',
        ];
        $discovery = new Discovery($config, $this->container, $this->httpClient);
        $this->cache->expects(self::once())
            ->method('get')
            ->with('discovery.' . $config['tid'])
            ->willReturn(null);
        $this->cache->expects(self::never())->method('set');

        $this->httpClient->expects(self::once())
            ->method('__call')
            ->with('get', ['https://discovery.url/v1/services', ['timeout' => 2]])
            ->willReturn($this->response);
        $this->response->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);

        self::assertNull($discovery->getServiceUrl('sts-issuer'));
    }

    /**
     * @return array
     */
    public function getServiceUrlInvalidJsonInResponseProvider(): array
    {
        return [
            'invalidJson' => [
                'json' => '{1234',
            ],
            'noServicesInJson' => [
                'json' => '{"a":"123"}',
            ],
        ];
    }

    /**
     * @param string $json
     *
     * @dataProvider getServiceUrlInvalidJsonInResponseProvider
     *
     * @covers ::getServiceUrl
     */
    public function testGetServiceUrlInvalidJsonInResponse(string $json): void
    {
        $config = [
            'tid' => 'srn:cloud:iam:us-west-2:2927985500:tenant',
            'discoveryUrl' => 'https://discovery.url/',
        ];
        $discovery = new Discovery($config, $this->container, $this->httpClient);
        $this->cache->expects(self::once())
            ->method('get')
            ->with('discovery.' . $config['tid'])
            ->willReturn(null);
        $this->cache->expects(self::never())->method('set');

        $this->httpClient->expects(self::once())
            ->method('__call')
            ->with('get', ['https://discovery.url/v1/services', ['timeout' => 2]])
            ->willReturn($this->response);
        $this->response->expects(self::once())->method('getStatusCode')->willReturn(Response::HTTP_OK);

        $this->response->expects(self::once())->method('getBody')->willReturn($this->stream);
        $this->stream->expects(self::once())->method('getContents')->willReturn($json);

        self::assertNull($discovery->getServiceUrl('sts-issuer'));
    }
}
