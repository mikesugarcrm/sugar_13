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

namespace Sugarcrm\SugarcrmTestsUnit\src\Security\ModuleScanner;

use LoggerManager;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetInterface;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\ModuleScanner\SweetShield
 */
class SweetShieldTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['log'] = $this->createMock(LoggerManager::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
    }

    public function invalidPathDataProvider()
    {
        return [
            ['/absolute/path',],
            ['directory/../traversal',],
            ["null-byte\0",],
            ['file://data.txt',],
            ['php://filter/read=string.toupper/resource=data.txt',],
        ];
    }

    /**
     * @dataProvider invalidPathDataProvider
     * @return void
     */
    public function testValidPath(string $path)
    {
        $this->expectException(\RuntimeException::class);
        SweetShield::validPath($path);
    }

    public function testCallMethod()
    {
        $first = 42;
        $second = 'foobar';
        $method = 'save';
        $object = $this->createMock(\SugarBean::class);
        $object->expects($this->once())
            ->method($method)
            ->with($first, $second);
        SweetShield::callMethod($object, $method, $first, $second);
    }

    public function testCallFunction()
    {
        $one = ['a', 'b'];
        $two = ['c', 'd'];
        $result = SweetShield::callFunction('array_merge', $one, $two);
        $this->assertEquals(array_merge($one, $two), $result);
    }

    public function testIsInternalFunction()
    {
        $this->assertTrue(SweetShield::isInternalFunction('strlen'));
        $this->assertFalse(SweetShield::isInternalFunction('foo'));
    }

    public function testIsAllowedFunction()
    {
        $this->assertTrue(SweetShield::isAllowedFunction('strlen'));
        $this->assertTrue(SweetShield::isAllowedFunction(SweetShield::SWEET_PREFIX . 'anything'));
        $this->assertTrue(SweetShield::isAllowedFunction('custom_function'));
        $this->assertFalse(SweetShield::isAllowedFunction('eval'));
    }

    public function testIsAllowedMethod()
    {
        $class = new class () implements SweetInterface {
        };
        $this->assertTrue(SweetShield::isAllowedMethod($class, 'any-method'));
        $this->assertFalse(SweetShield::isAllowedMethod('reflection', 'any-method'));
        $this->assertFalse(SweetShield::isAllowedMethod('any-class', 'setlevel'));
        $this->assertFalse(SweetShield::isAllowedMethod('sugarautoloader', 'put'));
    }
}
