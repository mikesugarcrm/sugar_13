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

namespace Sugarcrm\SugarcrmTestsUnit\ProductDefinition\Config\Source;

use LoggerManager;
use PHPUnit\Framework\MockObject\MockObject;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\HttpSource as HttpSource;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\HttpSource
 */
class HttpSourceTest extends TestCase
{
    /**
     * @var MockObject|HttpClient
     */
    protected $httpClient;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $response;

    /**
     * @var MockObject|HttpSource
     */
    protected $source;

    /**
     * @var MockObject|LoggerManager
     */
    protected $logger;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->logger = $this->createMock(LoggerManager::class);

        $this->source = $this->getMockBuilder(HttpSource::class)
            ->setConstructorArgs([
                [
                    'base_uri' => 'http://localhost/',
                    'fallback_version' => '9.0.0',
                ],
            ])
            ->setMethods(['getSugarVersion', 'getLogger', 'validateResponse'])
            ->getMock();
        $this->source->expects($this->any())->method('getSugarVersion')->willReturn('9.2.0');
        $this->source->expects($this->any())->method('getLogger')->willReturn($this->logger);
        $this->source->expects($this->any())->method('validateResponse')->willReturn(true);
        $this->source->setHttpClient($this->httpClient);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructBaseUriMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new HttpSource([]));
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinitionHttpExceptionIsThrowed()
    {
        $this->logger->expects($this->exactly(3))
            ->method('__call');

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(['GET', '9.2.0'], ['GET', HttpSource::DEFAULT_FALLBACK_VERSION])
            ->willThrowException(new \Exception('test'));

        $this->response->expects($this->never())
            ->method('getStatusCode');

        $this->assertNull($this->source->getDefinition());
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinitionWrongResponseStatus()
    {
        $this->logger->expects($this->exactly(3))
            ->method('__call');

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(['GET', '9.2.0'], ['GET', HttpSource::DEFAULT_FALLBACK_VERSION])
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(SymfonyResponse::HTTP_NOT_FOUND);

        $this->assertNull($this->source->getDefinition());
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinition()
    {
        $this->logger->expects($this->exactly(2))
            ->method('__call');

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(['GET', '9.2.0'], ['GET', HttpSource::DEFAULT_FALLBACK_VERSION])
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturnOnConsecutiveCalls(SymfonyResponse::HTTP_NOT_FOUND, SymfonyResponse::HTTP_OK);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn('{"key":"value"}');

        $this->assertEquals('{"key":"value"}', $this->source->getDefinition());
    }

    /**
     * @covers ::validateResponse
     * @param $response
     * @param $expected
     *
     * @dataProvider validateResponseProvider
     */
    public function testValidateResponse($response, $expected)
    {
        $source = $this->getMockBuilder(HttpSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ret = TestReflection::callProtectedMethod($source, 'validateResponse', [$response]);
        $this->assertSame($expected, $ret);
    }

    public function validateResponseProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'valid response' => ['{"MODULES":{"Bugs":["CURRENT","SUGAR_SERVE"]},"DASHLETS":{"commentlog-dashlet":["CURRENT","SUGAR_SERVE","SUGAR_SELL"]},"RECORDS":{"Dashboards":{"c108bb4a-775a-11e9-b570-f218983a1c3e":["SUGAR_SERVE"]}},"FIELDS":{"Accounts":{"business_center_id":["SUGAR_SERVE","SUGAR_SELL"]}}}', true],
            'invalid response, wrong json format' => ['{"MODULES":{"Bugs":["CURRENT","SUGAR_SERVE"]},"DASHLETS":{"commentlog-dashlet":["CURRENT","SUGAR_SERVE","SUGAR_SELL"]},"RECORDS":{"Dashboards":{"c108bb4a-775a-11e9-b570-f218983a1c3e":["SUGAR_SERVE"]}},"FIELDS":{"Accounts":{"business_center_id":["SUGAR_SERVE","SUGAR_SELL"]}}', false],
            'invalid response, empty body' => [null, false],
            'invalid response, missing FIELDS' => ['{"MODULES":{"Bugs":["CURRENT","SUGAR_SERVE"]},"DASHLETS":{"commentlog-dashlet":["CURRENT","SUGAR_SERVE","SUGAR_SELL"]},"RECORDS":{"Dashboards":{"c108bb4a-775a-11e9-b570-f218983a1c3e":["SUGAR_SERVE"]}}}', false],
        ];
        // @codingStandardsIgnoreEnd
    }
}
