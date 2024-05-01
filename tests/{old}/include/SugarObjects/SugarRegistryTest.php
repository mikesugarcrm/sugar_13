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

use PHPUnit\Framework\TestCase;

class SugarRegistryTest extends TestCase
{
    public function testGetInstanceReturnsAnInstanceOfSugarRegistry()
    {
        $this->assertTrue(SugarRegistry::getInstance() instanceof SugarRegistry, 'Returned object is not a SugarRegistry instance');
    }

    public function testGetInstanceReturnsSameObject()
    {
        $one = SugarRegistry::getInstance();
        $two = SugarRegistry::getInstance();
        $this->assertSame($one, $two);
    }

    public function testParameterPassedToGetInstanceSpecifiesInstanceName()
    {
        $foo1 = SugarRegistry::getInstance('foo');
        $foo2 = SugarRegistry::getInstance('foo');
        $this->assertSame($foo1, $foo2);

        $bar = SugarRegistry::getInstance('bar');
        $this->assertNotSame($foo1, $bar);
    }

    public function testCanSetAndGetValues()
    {
        $random = random_int(100, 200);
        $r = SugarRegistry::getInstance();
        $r->integer = $random;
        $this->assertEquals($random, $r->integer);
        $this->assertEquals($random, SugarRegistry::getInstance()->integer);
    }

    public function testIssetReturnsTrueFalse()
    {
        $r = SugarRegistry::getInstance();
        $this->assertFalse(isset($r->foo));
        $this->assertFalse(isset(SugarRegistry::getInstance()->foo));

        $r->foo = 'bar';
        $this->assertTrue(isset($r->foo));
        $this->assertTrue(isset(SugarRegistry::getInstance()->foo));
    }

    public function testUnsetRemovesValueFromRegistry()
    {
        $r = SugarRegistry::getInstance();
        $r->foo = 'bar';
        unset($r->foo);
        $this->assertFalse(isset($r->foo));
        $this->assertFalse(isset(SugarRegistry::getInstance()->foo));
    }

    public function testReturnsNullOnAnyUnknownValue()
    {
        $r = SugarRegistry::getInstance();
        $this->assertNull($r->unknown);
        $this->assertNull(SugarRegistry::getInstance()->unknown);
    }

    public function testAddToGlobalsPutsRefsToAllRegistryObjectsInGlobalSpace()
    {
        $r = SugarRegistry::getInstance();
        $r->foo = 'bar';

        $this->assertFalse(isset($GLOBALS['foo']), 'sanity check');
        $r->addToGlobals();
        $this->assertTrue(isset($GLOBALS['foo']));
    }
}
