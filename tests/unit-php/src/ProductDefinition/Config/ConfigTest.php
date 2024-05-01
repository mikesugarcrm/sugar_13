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

namespace Sugarcrm\SugarcrmTestsUnit\ProductDefinition\Config;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SugarConfig;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Cache\CacheInterface;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\SourceInterface;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Config;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\ProductDefinition\Config\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var MockObject|SugarConfig
     */
    protected $sugarConfig;

    /**
     * @var MockObject|SourceInterface
     */
    protected $source;

    /**
     * @var MockObject|CacheInterface
     */
    protected $cache;

    /**
     * @var MockObject|Config
     */
    protected $config;

    /**
     * @codeCoverageIgnore
     */
    protected function setUp(): void
    {
        $this->source = $this->createMock(SourceInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([SugarConfig::getInstance()])
            ->setMethods(['getSource', 'getCache', 'isInstallInProgress'])
            ->getMock();
    }

    /**
     * @covers ::__construct
     */
    public function testGetProductDefinitionWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $sugarConfig = $this->createMock(SugarConfig::class);
        $sugarConfig->expects($this->once())
            ->method('get')
            ->with('product_definition', $this->anything())
            ->willReturn(['type' => '']);

        (new Config($sugarConfig));
    }

    /**
     * @covers ::getProductDefinition
     */
    public function testGetProductDefinitionIsInstallInProgress()
    {
        $this->config->expects($this->once())
            ->method('isInstallInProgress')
            ->willReturn(true);

        $this->config->expects($this->never())
            ->method('getCache')
            ->willReturn($this->cache);

        $this->assertEquals([], $this->config->getProductDefinition());
    }

    /**
     * @covers ::getProductDefinition
     */
    public function testGetProductDefinitionCacheIsEmpty()
    {
        $this->config->expects($this->once())
            ->method('isInstallInProgress')
            ->willReturn(false);

        $this->config->expects($this->once())
            ->method('getCache')
            ->willReturn($this->cache);
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn('');

        $this->assertEquals([], $this->config->getProductDefinition());
    }

    /**
     * @covers ::getProductDefinition
     */
    public function testGetProductDefinition()
    {
        $this->config->expects($this->once())
            ->method('isInstallInProgress')
            ->willReturn(false);

        $this->config->expects($this->once())
            ->method('getCache')
            ->willReturn($this->cache);
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn('{"key2":"value"}');

        $this->assertEquals(['key2' => 'value'], $this->config->getProductDefinition());
    }

    /**
     * @covers ::updateProductDefinition
     */
    public function testUpdateProductDefinition()
    {
        $this->config->expects($this->once())
            ->method('getSource')
            ->willReturn($this->source);
        $this->source->expects($this->once())
            ->method('getDefinition')
            ->willReturn('{"key2":"value"}');

        $this->config->expects($this->once())
            ->method('getCache')
            ->willReturn($this->cache);
        $this->cache->expects($this->once())
            ->method('set')
            ->with('{"key2":"value"}');

        $this->assertTrue($this->config->updateProductDefinition());
    }
}
