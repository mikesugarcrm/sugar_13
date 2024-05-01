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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Dns;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Dns\CachedResolver;
use Sugarcrm\Sugarcrm\Security\Dns\NativeResolver;
use Sugarcrm\Sugarcrm\Security\Dns\ResolverFactory;
use Sugarcrm\Sugarcrm\Security\Dns\ResolverLogger;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Dns\ResolverFactory
 */
class ResolverFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        \BeanFactory::setBeanClass('Administration', AdministrationMock::class);
    }

    public static function tearDownAfterClass(): void
    {
        \BeanFactory::unsetBeanClass(\Administration::class);
    }

    protected function tearDown(): void
    {
        \SugarConfig::getInstance()->clearCache();
        $GLOBALS['sugar_config']['security']['use_doh'] = false;
    }

    public function factoryDataProvider(): array
    {
        return [
            [false, NativeResolver::class],
            [true, CachedResolver::class],
        ];
    }

    /**
     * @dataProvider factoryDataProvider()
     * @param bool $useDoh
     * @param string $expectedResolver
     * @return void
     */
    public function testFactoryNative(bool $useDoh, string $expectedResolver)
    {
        $GLOBALS['sugar_config']['security']['use_doh'] = $useDoh;
        $resolver1 = ResolverFactory::getInstance();
        $resolver2 = ResolverFactory::getInstance();
        $this->assertEquals($resolver1, $resolver2);
        $this->assertInstanceOf(ResolverLogger::class, $resolver1);

        $resolver = self::getProperty($resolver1, 'resolver');
        $this->assertInstanceOf($expectedResolver, $resolver);
    }

    public static function getProperty($object, $property)
    {
        $reflectedClass = new \ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($property);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }
}

class AdministrationMock
{
    public $settings = [

    ];

    public function retrieveSettings($category = false, $clean = false)
    {
        return $this;
    }
}
