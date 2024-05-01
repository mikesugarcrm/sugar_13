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

namespace Sugarcrm\SugarcrmTestsUnit\ValueObjects;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Dns\Resolver;
use Sugarcrm\Sugarcrm\Security\ValueObjects\ExternalResource;

class ExternalResourceTest extends TestCase
{
    /**
     * @dataProvider validUrlsDataProvider
     *
     * @param string $url
     * @param string $scheme
     */
    public function testCreationFromValidUrl(string $url, string $scheme): void
    {
        $resolver = $this->createStub(Resolver::class);
        $resolver->method('resolveToIp')->will($this->returnValue('8.8.8.8'));
        $urlObject = ExternalResource::fromString($url, [], $resolver);

        $this->assertNotNull($urlObject);

        $ip = $urlObject->getIp();

        $this->assertEquals("$scheme://$ip", $urlObject->getConvertedUrl());
    }

    /**
     * @dataProvider invalidUrlsDataProvider
     *
     * @param string $url
     */
    public function testCreationFromInvalidUrl(string $url): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ExternalResource::fromString($url);
    }

    public function testPrivateIps()
    {
        $this->expectException(\InvalidArgumentException::class);
        ExternalResource::fromString('http://127.0.0.1/foo/bar', ['127.0.0.0|127.255.255.255']);
    }

    public function validUrlsDataProvider(): array
    {
        return [
            [
                'url' => 'http://www.google.com',
                'scheme' => 'http',
            ],
            [
                'url' => 'https://www.google.com',
                'scheme' => 'https',
            ],
            [
                'url' => 'http://127.0.0.1',
                'scheme' => 'http',
            ],
        ];
    }

    public function invalidUrlsDataProvider(): array
    {
        return [
            [
                'url' => 'ftp://test.com',
            ],
        ];
    }
}
