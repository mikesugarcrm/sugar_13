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
 * Test class for BeanVisibility.
 */
class BeanVisibilityTest extends TestCase
{
    /**
     * @var BeanVisibility
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new BeanVisibility(
            $this->createMock(SugarBean::class),
            []
        );
    }

    private function getStrategy($query = '')
    {
        $mock = new MockSugarStrategy(
            $this->createMock(SugarBean::class)
        );
        $mock->query = $query;
        return $mock;
    }

    /**
     * @covers BeanVisibility::addStrategy
     */
    public function testAddStrategy()
    {
        $this->assertNull($this->object->addStrategy($this->getStrategy(), ['pirates' => 'yay']));
    }

    /**
     * @covers BeanVisibility::addVisibilityFrom
     */
    public function testAddVisibilityFrom()
    {
        $this->object->addStrategy('MockSugarStrategy', ['query' => 'testingFrom']);
        $query = 'from';
        $this->assertEquals($this->object->addVisibilityFrom($query), 'from testingFrom');
    }

    /**
     * @covers BeanVisibility::addVisibilityWhere
     */
    public function testAddVisibilityWhere()
    {
        $this->object->addStrategy('MockSugarStrategy', ['query' => 'testingWhere']);
        $query = 'where';
        $this->assertEquals($this->object->addVisibilityWhere($query), 'where testingWhere');
    }
}

class MockSugarStrategy extends SugarVisibility
{
    public $query;

    public function __construct($bean, $data = [])
    {
        parent::__construct($bean, $data);
        if (!empty($data['query'])) {
            $this->query = $data['query'];
        }
    }

    public function addVisibilityFrom(&$query)
    {
        $query .= " {$this->query}";
        return $query;
    }

    public function addVisibilityWhere(&$query)
    {
        $query .= " {$this->query}";
        return $query;
    }
}
