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

require_once 'upgrade/scripts/post/2_NullifyEmptyDurationMinutes.php';

/**
 * Test for nullifying duration_minutes field on the database during upgrade
 */
class SugarUpgradeNullifyEmptyDurationMinutesTest extends UpgradeTestCase
{
    /**
     * Tests checking the column type
     * @param string $type
     * @param boolean $expect
     * @dataProvider columnTypeIsIntProvider
     */
    public function testColumnTypeIsInt($type, $expect)
    {
        $mock = $this->getUpgraderMock();

        $actual = SugarTestReflection::callProtectedMethod($mock, 'columnTypeIsInt', [$type]);
        $this->assertEquals($actual, $expect);
    }

    /**
     * Tests getting the correct column type
     * @param array $columns
     * @param string $expect
     * @dataProvider getColumnTypeProvider
     */
    public function testGetColumnType($columns, $expect)
    {
        $mock = $this->getUpgraderMock(['getColumns']);

        $mock->expects($this->any())
            ->method('getColumns')
            ->will($this->returnValue($columns));

        $actual = SugarTestReflection::callProtectedMethod($mock, 'getColumnType', []);
        $this->assertEquals($actual, $expect);
    }

    /**
     * Tests getting the correct field def type
     * @param array $defs
     * @param string $expect
     * @dataProvider getFielddefTypeProvider
     */
    public function testGetFielddefType($defs, $expect)
    {
        $mock = $this->getUpgraderMock(['getFielddefsFromBean']);

        $mock->expects($this->any())
            ->method('getFielddefsFromBean')
            ->will($this->returnValue($defs));

        $actual = SugarTestReflection::callProtectedMethod($mock, 'getFielddefType', []);
        $this->assertEquals($actual, $expect);
    }

    /**
     * Tests whether the upgrader needs to run
     * @param array $columns
     * @param array $defs
     * @param boolean $expect
     * @dataProvider needsUpdatingProvider
     */
    public function testNeedsUpdating($columns, $defs, $expect)
    {
        $mock = $this->getUpgraderMock(['getColumns', 'getFielddefsFromBean']);

        $mock->expects($this->any())
            ->method('getColumns')
            ->will($this->returnValue($columns));

        $mock->expects($this->any())
            ->method('getFielddefsFromBean')
            ->will($this->returnValue($defs));

        $actual = SugarTestReflection::callProtectedMethod($mock, 'needsUpdating', []);
        $this->assertEquals($actual, $expect);
    }

    public function columnTypeIsIntProvider()
    {
        return [
            // Tests basic expectation
            [
                'type' => 'int',
                'expect' => true,
            ],
            // Tests uppercase expectation
            [
                'type' => 'INT',
                'expect' => true,
            ],
            // Tests Oracle expectation
            [
                'type' => 'number',
                'expect' => true,
            ],
            // Tests Oracle UPPERCASE expectation
            [
                'type' => 'NUMBER',
                'expect' => true,
            ],
            // Tests IBMDB2 expectation
            [
                'type' => 'integer',
                'expect' => true,
            ],
            // Tests IBMDB2 UPPERCASE expectation
            [
                'type' => 'INTEGER',
                'expect' => true,
            ],
            // Tests non-expectation in SQL SERVER (should be int)
            [
                'type' => 'NUMERIC',
                'expect' => false,
            ],
            // Tests non-expectation
            [
                'type' => 'enum',
                'expect' => false,
            ],
            // Tests column not on table
            [
                'type' => '',
                'expect' => true,
            ],
            // Tests column not on table
            [
                'type' => null,
                'expect' => true,
            ],
        ];
    }

    public function getColumnTypeProvider()
    {
        return [
            [
                'columns' => [
                    'duration_minutes' => [
                        'type' => 'int',
                    ],
                ],
                'expect' => 'int',
            ],
            [
                'columns' => [
                    'duration_minutes' => [
                        'type' => 'char',
                    ],
                ],
                'expect' => 'char',
            ],
            [
                'columns' => [
                    'duration_minutes' => [
                        'custom_type' => 'int',
                    ],
                ],
                'expect' => null,
            ],
        ];
    }

    public function getFielddefTypeProvider()
    {
        return [
            // Test type is set to something
            [
                'defs' => [
                    'duration_minutes' => [
                        'type' => 'int',
                    ],
                ],
                'expect' => 'int',
            ],
            // Test dbType is set to something
            [
                'defs' => [
                    'duration_minutes' => [
                        'dbType' => 'char',
                    ],
                ],
                'expect' => 'char',
            ],
            // Test field not set returns null
            [
                'defs' => [
                    'duration' => [
                        'type' => 'int',
                    ],
                ],
                'expect' => null,
            ],
            // Test type not set returns null
            [
                'defs' => [
                    'duration_minutes' => [
                        'len' => 20,
                    ],
                ],
                'expect' => null,
            ],
        ];
    }

    public function needsUpdatingProvider()
    {
        return [
            // Test colType is int returns false
            [
                'columns' => [
                    'duration_minutes' => [
                        'type' => 'int',
                    ],
                ],
                'defs' => [
                    'duration_minutes' => [
                        'type' => 'int',
                    ],
                ],
                'expect' => false,
            ],
            // Test colType is null return false
            [
                'columns' => [
                    'duration_minutes' => [
                        'custom_type' => 'int',
                    ],
                ],
                'defs' => [
                    'duration_minutes' => [
                        'type' => 'int',
                    ],
                ],
                'expect' => false,
            ],
            // Test fieldType is int returns true
            [
                'columns' => [
                    'duration_minutes' => [
                        'type' => 'char',
                    ],
                ],
                'defs' => [
                    'duration_minutes' => [
                        'type' => 'int',
                    ],
                ],
                'expect' => true,
            ],
            // Test fieldType is char (not int) returns false
            [
                'columns' => [
                    'duration_minutes' => [
                        'type' => 'char',
                    ],
                ],
                'defs' => [
                    'duration_minutes' => [
                        'type' => 'char',
                    ],
                ],
                'expect' => false,
            ],
            // Test fieldType is null (not int) returns false
            [
                'columns' => [
                    'duration_minutes' => [
                        'type' => 'char',
                    ],
                ],
                'defs' => [
                    'duration_minutes' => [
                        'custom_type' => 'tinyint',
                    ],
                ],
                'expect' => false,
            ],
        ];
    }

    public function getUpgraderMock($methods = null)
    {
        return $this->getMockBuilder('SugarUpgradeNullifyEmptyDurationMinutes')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
