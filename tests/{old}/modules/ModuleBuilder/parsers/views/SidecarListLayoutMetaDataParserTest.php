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

class SidecarListLayoutMetaDataParserTest extends TestCase
{
    /**
     * @var SidecarListLayoutMetaDataParser
     */
    protected $parser;

    protected function setUp(): void
    {
        $this->parser = $this->createPartialMock('SidecarListLayoutMetaDataParser', ['handleSave']);

        $implementation = $this->createPartialMock(
            'DeployedMetaDataImplementation',
            ['getPanelDefsPath']
        );

        $implementation->expects($this->any())
            ->method('getPanelDefsPath')
            ->will(
                $this->returnValue(
                    ['unittest', 'view', 'test']
                )
            );

        SugarTestReflection::setProtectedValue($this->parser, 'implementation', $implementation);

        $this->parser->client = 'unittest';
    }

    protected function tearDown(): void
    {
        unset($this->parser);
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsFalseWhenViewDefsAreNotDefined()
    {
        $this->assertEmpty($this->parser->_viewdefs);
        $this->assertFalse(SugarTestReflection::callProtectedMethod($this->parser, 'getPanel'));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsFalseWhenViewDefsAreDefinedButEmpty()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [],
                    ],
                ],
            ],
        ];

        $this->assertFalse(SugarTestReflection::callProtectedMethod($this->parser, 'getPanel'));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsArrayWhenPanelIsDefined()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [
                            [
                                'unit' => 'test',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [['unit' => 'test']];

        $this->assertSame($expected, SugarTestReflection::callProtectedMethod($this->parser, 'getPanel'));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsSpecificPanelIfItExists()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [
                            [
                                'dummy' => 'panel',
                            ],
                            [
                                'unit' => 'test',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = ['unit' => 'test'];

        $this->assertSame($expected, SugarTestReflection::callProtectedMethod($this->parser, 'getPanel', [1]));
    }

    /**
     * @convert SidecarListLayoutMetaDataParser::getPanel()
     */
    public function testGetPanelReturnsAllPanelsIfSpecificPanelDoesNotExists()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [
                            [
                                'dummy' => 'panel',
                            ],
                            [
                                'unit' => 'test',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            [
                'dummy' => 'panel',
            ],
            [
                'unit' => 'test',
            ],
        ];

        $this->assertSame($expected, SugarTestReflection::callProtectedMethod($this->parser, 'getPanel', [2]));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser::addField
     */
    public function testAddFieldAddsToEndOfPanel()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [
                            [
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->parser->_fielddefs = [
            'test_field' => [
                'label' => 'LBL_FIELD',
                'type' => 'text',
            ],
        ];

        $this->parser->addField('test_field');

        $panel = SugarTestReflection::callProtectedMethod($this->parser, 'getPanel');

        $this->assertEquals(2, safeCount($panel[0]['fields']));

        $expected = [
            'name' => 'test_field',
            'label' => 'LBL_FIELD',
            'enabled' => true,
            'default' => true,
            'sortable' => false,
        ];
        $this->assertSame($expected, array_pop($panel[0]['fields']));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser::addField
     */
    public function testAddFieldToStartOfPanel()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [
                            [
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->parser->_fielddefs = [
            'test_field' => [
                'label' => 'LBL_FIELD',
                'type' => 'text',
            ],
        ];

        $this->parser->addField('test_field', [], 0, 0);

        $panel = SugarTestReflection::callProtectedMethod($this->parser, 'getPanel');

        $this->assertEquals(2, safeCount($panel[0]['fields']));

        $expected = [
            'name' => 'test_field',
            'label' => 'LBL_FIELD',
            'enabled' => true,
            'default' => true,
            'sortable' => false,
        ];
        $this->assertSame($expected, array_shift($panel[0]['fields']));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser::addField
     */
    public function testAddFieldAddsToMiddleOfPanel()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [
                            [
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ],
                                    [
                                        'name' => 'address',
                                        'enabled' => true,
                                        'default' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->parser->_fielddefs = [
            'test_field' => [
                'label' => 'LBL_FIELD',
                'type' => 'text',
            ],
        ];

        $this->parser->addField('test_field', [], 1, 0);

        $panel = SugarTestReflection::callProtectedMethod($this->parser, 'getPanel');

        $this->assertEquals(3, safeCount($panel[0]['fields']));

        $expected = [
            'name' => 'test_field',
            'label' => 'LBL_FIELD',
            'enabled' => true,
            'default' => true,
            'sortable' => false,
        ];
        $addedField = array_splice($panel[0]['fields'], 1, 1);
        $this->assertSame($expected, reset($addedField));
    }

    /**
     * @covers SidecarListLayoutMetaDataParser::resetPanelFields
     */
    public function testResetPanelFieldsRemovesAllFields()
    {
        $this->parser->_viewdefs = [
            'unittest' => [
                'view' => [
                    'test' => [
                        'panels' => [
                            [
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'enabled' => true,
                                        'default' => true,
                                    ],
                                    [
                                        'name' => 'address',
                                        'enabled' => true,
                                        'default' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->parser->resetPanelFields();

        $this->assertEmpty($this->parser->_viewdefs['unittest']['view']['test']['panels'][0]['fields']);
    }


    /**
     * Tests parsing of readonly properties of field defs
     *
     * @dataProvider readonlyPropTestProvider
     * @param array $defs Mock array of vardefs to trim
     * @param boolean $expectation Assertion to test
     */
    public function testReadonlyPropertyIsParsed($defs, $expectation)
    {
        $result = SidecarListLayoutMetaDataParser::_trimFieldDefs($defs);
        $actual = !empty($result['readonly']);
        $this->assertEquals($expectation, $actual, 'Assertion of readonly property existence failed');
    }

    public function readonlyPropTestProvider()
    {
        return [
            ['defs' => ['name' => 'test1', 'vname' => 'LBL_TEST1', 'readonly' => true], 'expectation' => true],
            ['defs' => ['name' => 'test2', 'vname' => 'LBL_TEST2'], 'expectation' => false],
        ];
    }

    /**
     * @dataProvider isDefaultFieldProvider
     */
    public function testIsDefaultField(array $field, $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->parser, 'isDefaultField', [$field]);
        $this->assertEquals($expected, $actual);
    }

    public static function isDefaultFieldProvider()
    {
        return [
            'true-by-default' => [
                [],
                true,
            ],
            'must-be-enabled' => [
                [
                    'enabled' => false,
                ],
                false,
            ],
            'must-be-default' => [
                [
                    'default' => false,
                ],
                false,
            ],
            'must-be-enabled-for-studio' => [
                [
                    'studio' => false,
                ],
                false,
            ],
            'studio-with-visible-value' => [
                [
                    'studio' => 'visible',
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider isAdditionalFieldProvider
     */
    public function testIsAdditionalField(array $field, $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->parser, 'isAdditionalField', [$field]);
        $this->assertEquals($expected, $actual);
    }

    public static function isAdditionalFieldProvider()
    {
        return [
            'false-by-default' => [
                [],
                false,
            ],
            'must-be-non-default' => [
                [
                    'default' => false,
                ],
                true,
            ],
            'must-be-enabled' => [
                [
                    'default' => false,
                    'enabled' => false,
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderSetDefSortable
     */
    public function testSetDefSortable($field_data, $expected)
    {
        $this->parser->_fielddefs = [
            'test_field' => $field_data,
        ];

        $results = $this->parser->setDefSortable('test_field', $field_data);

        if ($expected) {
            $this->assertArrayNotHasKey('sortable', $results);
        } else {
            $this->assertArrayHasKey('sortable', $results);
            $this->assertFalse($results['sortable']);
        }
    }

    public static function dataProviderSetDefSortable()
    {
        return [
            [
                [
                    'type' => 'parent',
                ],
                false,
            ],
            [
                // relate with no sort_on or rname field
                [
                    'type' => 'relate',
                ],
                false,
            ],
            [
                // relate with sort_on field
                [
                    'type' => 'relate',
                    'sort_on' => ['name'],
                ],
                true,
            ],
            [
                // relate with just rname field should not be sortable
                [
                    'type' => 'relate',
                    'rname' => 'name',
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderSetDefSortableKeepSortableSet
     * @param $field_data
     */
    public function testSetDefSortableKeepSortableSet($field_data)
    {
        $this->parser->_fielddefs = [
            'test_field' => $field_data,
        ];

        $results = $this->parser->setDefSortable('test_field', $field_data);

        $this->assertEquals($field_data['sortable'], $results['sortable']);
    }

    public static function dataProviderSetDefSortableKeepSortableSet()
    {
        return [
            [
                // invalid type should still keep the sortable flag
                [
                    'type' => 'parent',
                    'sortable' => true,
                ],
            ],
            [
                [
                    'type' => 'relate',
                    'sort_on' => ['name'],
                    'sortable' => false,
                ],
            ],
            [
                [
                    'type' => 'relate',
                    'rname' => 'name',
                    'sortable' => true,
                ],
            ],
        ];
    }
}
