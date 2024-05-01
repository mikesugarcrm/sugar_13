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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Container
 */
class ContainerTest extends TestCase
{
    /**
     * @covers ::__get
     * @dataProvider dataProviderTestGetOverloadLazyLoad
     *
     * @param string $property
     */
    public function testGetOverloadLazyLoad($property)
    {
        $initMethod = 'init' . ucfirst($property);
        $container = $this->getContainerMock([$initMethod]);
        $container->expects($this->once())
            ->method($initMethod);

        $container->$property;
    }

    public function dataProviderTestGetOverloadLazyLoad()
    {
        return [
            ['logger'],
            ['metaDataHelper'],
            ['queueManager'],
            ['client'],
            ['indexPool'],
            ['indexManager'],
            ['mappingManager'],
            ['indexer'],
        ];
    }

    /**
     * @covers ::getConfig
     * @covers ::setConfig
     */
    public function testSetConfig()
    {
        $container = $this->getContainerMock();

        // empty base values
        $this->assertEquals([], $container->getConfig('engine'));
        $this->assertEquals([], $container->getConfig('global'));

        // default value
        $this->assertEquals(['default'], $container->getConfig('foo', ['default']));

        // setter existing key
        $container->setConfig('engine', ['bar']);
        $this->assertEquals(['bar'], $container->getConfig('engine'));

        // setter new key
        $container->setConfig('new', ['beer']);
        $this->assertEquals(['beer'], $container->getConfig('new'));
    }

    /**
     * @covers ::__construct
     * @covers ::registerProviders
     * @covers ::registerProvider
     * @covers ::getRegisteredProviders
     * @covers ::isProviderAvailable
     * @covers ::unregisterProvider
     */
    public function testRegisterProviders()
    {
        // test if default providers are properly registered
        $container = new Container();
        $this->assertEquals(['GlobalSearch', 'Visibility'], $container->getRegisteredProviders());

        // Register/unregister new provider
        $this->assertFalse($container->isProviderAvailable('new'));
        $container->registerProvider('new', 'class');
        $this->assertTrue($container->isProviderAvailable('new'));
        $container->unregisterProvider('new');
        $this->assertFalse($container->isProviderAvailable('new'));
    }

    /**
     * @covers ::getProvider
     * @dataProvider dataProviderTestGetProvider
     *
     * @param string $provider
     * @param string $class
     */
    public function testGetProvider($provider, $class)
    {
        $user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $container = $this->getContainerMock(['getCurrentUser']);

        $container->expects($this->once())
            ->method('getCurrentUser')
            ->will($this->returnValue($user));

        $container->registerProviders();

        $provider = $container->getProvider($provider);
        $this->assertInstanceOf($class, $provider);
    }

    public function dataProviderTestGetProvider()
    {
        return [
            [
                'GlobalSearch',
                '\\' . \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch::class,
            ],
        ];
    }

    /**
     * @covers ::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf(
            \Sugarcrm\Sugarcrm\Elasticsearch\Container::class,
            Container::getInstance()
        );
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $this->assertInstanceOf(
            \Sugarcrm\Sugarcrm\Elasticsearch\Container::class,
            Container::create()
        );
    }

    /**
     * @covers ::initLogger
     */
    public function testInitLogger()
    {
        $container = $this->getContainerMock();
        TestReflection::callProtectedMethod($container, 'initLogger');

        $this->assertInstanceOf(
            '\\' . \Sugarcrm\Sugarcrm\Elasticsearch\Logger::class,
            TestReflection::getProtectedValue($container, 'logger')
        );
    }

    /**
     * Get Container mock
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected function getContainerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Container::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
