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

namespace Sugarcrm\SugarcrmTests\ProcessManager\Registry;

use Sugarcrm\Sugarcrm\ProcessManager\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    public function testSetAndGet()
    {
        $reg = Registry\Registry::getInstance();
        // Basic setting
        $reg->set('t1', 'test');
        $reg->set('t2', 'bark');
        $this->assertEquals('test', $reg->get('t1'));
        $this->assertEquals('bark', $reg->get('t2'));

        // Assert getting unset value no default
        $this->assertNull($reg->get('foo'));

        // Assert getting unset value with default
        $this->assertEquals('bar', $reg->get('foo', 'bar'));

        // Override testing
        $reg->set('t1', 'TEST', true);
        $reg->set('t1', 'foo');
        $this->assertEquals('TEST', $reg->get('t1'));

        // Additional override testing
        $reg->set('t1', 'bar', true);
        $this->assertEquals('bar', $reg->get('t1'));
    }

    public function testSetAndHas()
    {
        $reg = Registry\Registry::getInstance();
        $reg->set('t3', 'test');
        $reg->set('t4', 'test');

        $this->assertTrue($reg->has('t3'));
        $this->assertTrue($reg->has('t4'));
        $this->assertFalse($reg->has('t5'));
    }

    public function testSetAndDrop()
    {
        $reg = Registry\Registry::getInstance();
        $reg->set('t6', 'test');
        $reg->set('t7', 'test');

        $reg->drop('t6');

        $this->assertFalse($reg->has('t6'));
        $this->assertTrue($reg->has('t7'));
    }

    public function testGetChanges()
    {
        $reg = Registry\Registry::getInstance();
        $reg->set('t10', 'test');
        $reg->set('t20', 'test');
        $reg->set('t10', 'TEST', true);
        $reg->set('t10', 'foo');
        $reg->set('t10', 'bar', true);

        // Test we get the right value
        $this->assertEquals('bar', $reg->get('t10'));

        // Assertion on changes
        $changes = $reg->getChanges('t10');
        $this->assertCount(2, $changes);

        // Assertion on no changes
        $changes = $reg->getChanges('t20');
        $this->assertCount(0, $changes);
    }

    public function testReset()
    {
        $reg = Registry\Registry::getInstance();

        // Verify existence of all registered items to this point
        $this->assertTrue($reg->has('t1'));
        $this->assertTrue($reg->has('t4'));
        $this->assertTrue($reg->has('t7'));
        $this->assertTrue($reg->has('t10'));

        $reg->reset();

        // Verify we are all clear
        $this->assertFalse($reg->has('t1'));
        $this->assertFalse($reg->has('t4'));
        $this->assertFalse($reg->has('t7'));
        $this->assertFalse($reg->has('t10'));
    }
}
