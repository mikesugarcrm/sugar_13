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

/**
 * Test class for SugarVisibility.
 */
class SugarVisibilityTest extends TestCase
{
    /**
     * @var SugarVisibility
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = $this->getMockForAbstractClass('SugarVisibility', [$this->createMock(SugarBean::class)]);
    }

    /**
     * @covers SugarVisibility::addVisibilityFrom
     */
    public function testAddVisibilityFrom()
    {
        $query = 'from';
        $this->assertEquals($query, $this->object->addVisibilityFrom($query));
    }

    /**
     * @covers SugarVisibility::addVisibilityWhere
     */
    public function testAddVisibilityWhere()
    {
        $query = 'from';
        $this->assertEquals($query, $this->object->addVisibilityFrom($query));
    }

    /**
     * @covers SugarVisibility::getOption
     */
    public function testGetOption()
    {
        $this->assertEquals('default', $this->object->getOption('nonexisting_option', 'default'), 'returns default value');
        $this->assertNull($this->object->getOption('nonexisting_option'));
        $this->object->setOptions(['test' => 'yay']);
        $this->assertEquals('yay', $this->object->getOption('test'), 'returns option\'s value');
    }

    /**
     * @covers SugarVisibility::setOptions
     */
    public function testSetOptions()
    {
        $options = [
            'test1' => 'yay1',
            'test2' => 'yay2',
            'pirates' => 'attack!',
        ];

        $this->assertEquals($this->object, $this->object->setOptions($options), 'returns self');

        foreach ($options as $key => $value) {
            $this->assertEquals($value, $this->object->getOption($key));
        }
    }
}
