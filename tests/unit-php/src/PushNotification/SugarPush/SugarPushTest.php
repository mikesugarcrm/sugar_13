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
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\PushNotification\SugarPush\SugarPush;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\PushNotification\SugarPush\SugarPush
 */
class SugarPushTest extends TestCase
{
    private $sugarConfig;
    private $config;

    protected function setUp(): void
    {
        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;
        $this->config = \SugarConfig::getInstance();
    }

    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->sugarConfig;
        $this->config->clearCache();
    }

    /**
     * Data provider for testRequest.
     */
    public function requestDataProvider()
    {
        return [
            [
                'register',
                ['android', 'device_id'],
                ['PUT', '/device', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'json' => ['application_id' => 'fcm', 'device_id' => 'device_id'],
                ]],
                200,
                true,
            ],
            [
                'update',
                ['android', 'old_id', 'new_id'],
                ['POST', '/device', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'json' => ['application_id' => 'fcm', 'device_id' => 'old_id', 'new_device_id' => 'new_id'],
                ]],
                200,
                true,
            ],
            [
                'delete',
                ['android', 'device_id'],
                ['DELETE', '/device', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'json' => ['application_id' => 'fcm', 'device_id' => 'device_id'],
                ]],
                200,
                true,
            ],
            [
                'setActive',
                ['test', true],
                ['POST', '/device', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'authorize_as_application' => true,
                    'json' => ['user_logged_in' => true, 'user_id' => 'test'],
                ]],
                200,
                true,
            ],
            [
                'setActive',
                ['test', false],
                ['POST', '/device', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'authorize_as_application' => true,
                    'json' => ['user_logged_out' => true, 'user_id' => 'test'],
                ]],
                200,
                true,
            ],
            [
                'setActive',
                ['test', false],
                ['POST', '/device', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'authorize_as_application' => true,
                    'json' => ['user_logged_out' => true, 'user_id' => 'test'],
                ]],
                404,
                false,
            ],
            [
                'setActive',
                ['test', false],
                ['POST', '/device', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'authorize_as_application' => true,
                    'json' => ['error' => 'unknown'],
                ]],
                200,
                false,
            ],
            [
                'send',
                [['will'], ['title' => 'test', 'body' => 'hello']],
                ['PUT', '/notification', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'authorize_as_application' => true,
                    'json' => ['target_user_id' => 'will', 'title' => 'test', 'body' => 'hello'],
                ]],
                200,
                true,
            ],
            [
                'send',
                [['will', 'max'], ['title' => 'test', 'body' => 'hello']],
                ['PUT', '/notification', [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'authorize_as_application' => true,
                    'json' => ['target_user_id' => 'will,max', 'title' => 'test', 'body' => 'hello'],
                ]],
                400,
                false,
            ],
        ];
    }

    /**
     * @covers ::register
     * @covers ::update
     * @covers ::delete
     * @covers ::send
     * @dataProvider requestDataProvider
     */
    public function testRequest($method, $methodParams, $requestParams, $statusCode, $expected)
    {
        $responseMock = $this->createPartialMock(Response::class, ['getStatusCode']);
        $responseMock->method('getStatusCode')
            ->willReturn($statusCode);
        $clientMock = $this->getMockBuilder(GuzzleClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['request'])
            ->getMock();
        $clientMock->expects($this->any())
            ->method('request')
            ->with($this->equalTo($requestParams[0]), $this->equalTo($requestParams[1]), $this->equalTo($requestParams[2]))
            ->willReturn($responseMock);
        $serviceMock = $this->getMockBuilder(SugarPush::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSuccess'])
            ->getMock();
        $serviceMock->method('isSuccess')
            ->willReturn($responseMock->getStatusCode() == 200);
        TestReflection::setProtectedValue($serviceMock, 'client', $clientMock);
        $result = call_user_func_array([$serviceMock, $method], $methodParams);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testGetServiceURL.
     */
    public function getServiceURLDataProvider()
    {
        return [
            [
                ['sugar_push' => ['service_urls' => ['default' => 'default-url', 'us-west-2' => 'us-west-2-url']]],
                'us-west-2',
                'prod',
                'us-west-2-url',
            ],
            [
                ['sugar_push' => ['service_urls' => []]],
                'eu-west-2',
                'prod',
                '',
            ],
            [
                ['sugar_push' => ['service_urls' => ['default' => 'default-url', 'us-west-2' => 'us-west-2-url']]],
                'eu-west-2',
                'prod',
                'default-url',
            ],
            [
                ['sugar_push' => ['service_urls' => ['default' => 'default-url', 'us-west-2' => 'us-west-2-url']]],
                '', // no region
                '', // no environment
                '', // no url
            ],
            [
                ['sugar_push' => [
                    'service_urls' => ['default' => 'default-url', 'us-west-2' => 'us-west-2-url.prod.service']],
                ],
                'us-west-2',
                'prod',
                'us-west-2-url.prod.service',
            ],
            [
                ['sugar_push' => [
                    'service_urls' => ['default' => 'default-url', 'us-west-2' => 'us-west-2-url.prod.service']],
                ],
                'us-west-2',
                'stage',
                'us-west-2-url.stage.service',
            ],
            [
                ['sugar_push' => [
                    'service_urls' => ['default' => '%s.stage.url']],
                ],
                'us-west-2',
                'stage',
                'us-west-2.stage.url',
            ],
            [
                ['sugar_push' => [
                    'service_urls' => ['default' => '%s.prod.url']],
                ],
                'us-west-2',
                'prod',
                'us-west-2.prod.url',
            ],
        ];
    }

    /**
     * @covers ::getServiceURL
     * @dataProvider getServiceURLDataProvider
     */
    public function testGetServiceURL($config, $region, $environment, $expected)
    {
        $GLOBALS['sugar_config'] = $config;
        $this->config->clearCache();
        $serviceMock = $this->getMockBuilder(SugarPush::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionAndEnvironment'])
            ->getMock();
        $serviceMock->method('getRegionAndEnvironment')->willReturn([$region, $environment]);
        $result = TestReflection::callProtectedMethod($serviceMock, 'getServiceURL');
        $this->assertEquals($expected, $result);
    }
}
