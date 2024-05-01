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

namespace Sugarcrm\SugarcrmTestsUnit\CSP;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\CSP\Directive;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\CSP\Directive
 */
class DirectiveTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $directive = Directive::create('default-src', '*.sugarcrm.com');
        $this->assertInstanceOf(Directive::class, $directive);
        $this->assertFalse($directive->isHidden());
    }

    /**
     * @covers ::create
     */
    public function testCreateHidden()
    {
        $directive = Directive::createHidden('default-src', '*.sugarcrm.com');
        $this->assertInstanceOf(Directive::class, $directive);
        $this->assertTrue($directive->isHidden());
    }

    /**
     * @covers ::create
     */
    public function testCreateFromInvalidDirectiveName()
    {
        $this->expectException(InvalidArgumentException::class);
        Directive::create('unknown-src', '*.sugarcrm.com');
    }

    /**
     * @covers ::create
     */
    public function testCreateFromInvalidDirectiveSource()
    {
        $this->expectException(InvalidArgumentException::class);
        Directive::create('img-src', '*.sugarcrm.com invalid_value');
    }

    public function providerCSPSources(): array
    {
        return [
            ['localhost', true],
            ['192.168.1.2', true],
            ['http://192.168.1.2', true],
            ['https://192.168.1.2', true],
            ['https://192.168.1.2:8888', true],
            ['http://192.168.1.2:8888', true],
            ['https://example.com', true],
            ['http://foobar.com', true],
            ['*.example.com', true],
            ['http://*.example.com', true],
            ['https://*.example.com', true],
            ['https://example.com:8080', true],
            ['http://example.com:8080', true],
            ['http://*.example.com:8080', true],
            ['https://*.example.com:8080', true],
            ['ws://*.example.com', true],
            ['wss://*.example.com', true],
            ['ws://*.example.com:8080', true],
            ['wss://*.example.com:8080', true],
            ["'unsafe-eval'", true],
            ["'unsafe-inline'", true],
            ["'unsafe-hashes'", true],
            ['data:', true],
            ['blob:', true],
            ['http:', true],
            ['https:', true],
            ['ws:', false],
            ['wss:', false],
            ["'self'", true],
            ['*', true],
            ['foobar', false],
            ['comma,separated.com', false],
            ['$pecI@lCh@r$', false],
        ];
    }

    /**
     * @dataProvider providerCSPSources
     * @param $cspSource
     * @param $expected
     * @covers ::isValidSrcValue
     */
    public function testIsValidCSPSrcValue($cspSource, $expected)
    {
        $this->assertEquals($expected, Directive::isValidSrcValue($cspSource));
    }

    /**
     * @covers ::sanitizeSrcValue
     */
    public function testSanitizeSrcValue()
    {
        $this->assertEquals("*.mashable.com *.sugarcrm.com 'self'", Directive::sanitizeSrcValue('*.mashable.com *.mashable.com  *.sugarcrm.com; &#039;self&#039;'));
    }


    /**
     * @covers ::name
     */
    public function testName()
    {
        $directive = Directive::create('default-src', '*.sugarcrm.com');
        $this->assertEquals('default-src', $directive->name());
    }

    /**
     * @covers ::source
     */
    public function testSource()
    {
        $directive = Directive::create('default-src', '*.sugarcrm.com');
        $this->assertEquals('*.sugarcrm.com', $directive->source());
    }

    /**
     * @covers ::value
     */
    public function testValue()
    {
        $directive = Directive::create('default-src', '*.sugarcrm.com');
        $this->assertEquals('default-src *.sugarcrm.com', $directive->value());
    }

    /**
     * @covers ::__toString()
     */
    public function testToString()
    {
        $directive = Directive::create('default-src', '*.sugarcrm.com');
        $this->assertEquals('default-src *.sugarcrm.com', (string)$directive);
    }
}
