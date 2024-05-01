<?php

declare(strict_types=1);
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

namespace Sugarcrm\SugarcrmTestsUnit\Security\HttpClient;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient
 */
class ExternalResourceClientTest extends TestCase
{
    public function providerValidTimeouts(): array
    {
        return [
            [1],
            [10.5],
            [-5],
        ];
    }

    /**
     * @dataProvider providerValidTimeouts
     * @doesNotPerformAssertions
     * @covers ::setTimeout
     */
    public function testValidTimeouts(float $timeout): void
    {
        (new ExternalResourceClient())->setTimeout($timeout);
    }

    public function providerInvalidTimeouts(): array
    {
        return [
            [0],
            [0.0],
        ];
    }

    /**
     * @dataProvider providerInvalidTimeouts
     * @covers ::setTimeout
     */
    public function testInvalidNames(float $timeout): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ExternalResourceClient())->setTimeout($timeout);
    }

    public function bodyFormatProvider()
    {
        return [
            ['post', '{foo: "bar"}', '{foo: "bar"}'],
            ['post', ['foo' => 'bar'], 'foo=bar'],
            ['post', null, ''],
            ['put', '{foo: "bar"}', '{foo: "bar"}'],
            ['put', ['foo' => 'bar'], 'foo=bar'],
            ['put', null, ''],
            ['patch', '{foo: "bar"}', '{foo: "bar"}'],
            ['patch', ['foo' => 'bar'], 'foo=bar'],
            ['patch', null, ''],
        ];
    }

    /**
     * @dataProvider bodyFormatProvider
     * @covers ::post
     * @covers ::put
     * @covers ::patch
     */
    public function testBodyFormat($method, $body, $expected)
    {
        $client = $this->getMockBuilder(ExternalResourceClient::class)
            ->onlyMethods(['request'])
            ->getMock();
        $client->expects($this->once())
            ->method('request')
            ->with(strtoupper($method), 'https://example.com/', $expected);
        $client->$method('https://example.com/', $body);
    }
}
