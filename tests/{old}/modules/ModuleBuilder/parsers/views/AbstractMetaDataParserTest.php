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

class AbstractMetaDataParserTest extends TestCase
{
    /**
     * Test the the isTrue function works correctly for boolean and non-boolean values
     * @group Studio
     * @dataProvider isTrueProvider
     */
    public function testIsTrue($value, $expected)
    {
        $this->assertSame($expected, SugarTestReflection::callProtectedMethod(
            'AbstractMetaDataParser',
            'isTrue',
            [$value]
        ));
    }

    public static function isTrueProvider()
    {
        return [
            [true, true],
            [false, false],
            [0, false],
            ['', false],
            ['true', true],
            ['false', false],
            ['FALSE', false],
            ['0', false],
            ['something', true],
        ];
    }

    /**
     * Tests validation of studio defs for client and view specific rules
     *
     * @dataProvider studioValidationDefProvider
     * @param array $def Array of fields defs
     * @param string $view The view name to check defs for
     * @param string $client The client to check defs for
     * @param bool $expected The expected result of the validation call
     */
    public function testGetClientStudioValidation($def, $view, $client, $expected)
    {
        $actual = AbstractMetaDataParser::getClientStudioValidation($def, $view, $client);
        $this->assertEquals($expected, $actual);
    }

    public function studioValidationDefProvider()
    {
        return [
            // Test no client specific rule in the defs is null
            [
                'def' => [],
                'view' => 'list',
                'client' => 'base',
                'expected' => null,
            ],
            // Test no client passed is null
            [
                'def' => ['base' => []],
                'view' => 'list',
                'client' => '',
                'expected' => null,
            ],
            // Test def[client] is a string is null
            [
                'def' => ['base' => 'list'],
                'view' => 'list',
                'client' => 'base',
                'expected' => null,
            ],
            // Test no view passed is null
            [
                'def' => ['mobile' => []],
                'view' => '',
                'client' => 'mobile',
                'expected' => null,
            ],
            // Test def[client] is boolean returns the boolean
            [
                'def' => ['mobile' => true],
                'view' => 'list',
                'client' => 'mobile',
                'expected' => true,
            ],
            [
                'def' => ['mobile' => false],
                'view' => 'list',
                'client' => 'mobile',
                'expected' => false,
            ],
            // Test client and view specific rules are boolean
            [
                'def' => ['mobile' => ['list' => false]],
                'view' => 'list',
                'client' => 'mobile',
                'expected' => false,
            ],
            [
                'def' => ['custom' => ['record' => 'somestring']],
                'view' => 'record',
                'client' => 'custom',
                'expected' => true,
            ],
        ];
    }

    public function validFieldDataProvider()
    {
        return [
            [
                [
                    'name' => 'uploadfile',
                    'source' => 'non-db',
                    'type' => 'file',
                ],
                true,
            ],
            [
                [
                    'name' => 'uploadfile',
                    'source' => 'non-db',
                    'type' => 'varchar',
                ],
                false,
            ],
            'name-and-type-are-enough' => [
                [
                    'name' => 'name',
                    'type' => 'varchar',
                ],
                true,
            ],
            'json-is-invalid' => [
                [
                    'name' => 'data',
                    'type' => 'json',
                ],
                false,
            ],
            'json-can-be-enabled' => [
                [
                    'name' => 'data',
                    'type' => 'json',
                    'studio' => true,
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider validFieldDataProvider
     */
    public function testValidField(array $definition, $expected)
    {
        $this->assertEquals($expected, AbstractMetaDataParser::validField($definition));
    }

    /**
     * @covers ::setFielddefsProps
     * @dataProvider setFielddefsPropsDataProvider
     *
     * @param array $fielddefs fielddefs to be passed in constructor call
     * @param array $constructorArgs Array of parameters to be passed in constructor call
     * @param array $fieldNames Array of field names to which the given properties will be set
     * @param array $propertyList Array of 'property => value' to be set
     * @param array $expectation Expected fielddefs
     */
    public function testSetFielddefsProps(
        $fielddefs,
        $constructorArgs,
        $fieldNames,
        $propertyList,
        $expectation
    ) {

        $parser = SugarTestAbstractMetaDataParserUtilities::createGridLayoutParserWithFielddefs(
            $fielddefs,
            $constructorArgs
        );
        $parser->setFielddefsProps($fieldNames, $propertyList);
        $this->assertEquals($expectation, $parser->_fielddefs);

        // unsets the parser created above
        SugarTestAbstractMetaDataParserUtilities::removeCreatedParser($parser);
    }

    public function setFielddefsPropsDataProvider()
    {
        return [
            [
                // fielddefs
                [
                    'service_start_date' => [
                        'name' => 'service_start_date',
                    ],
                    'test_1' => [
                        'name' => 'test_1',
                    ],
                    'test_2' => [
                        'name' => 'test_2',
                        'testProperty' => 'testValue',
                    ],
                ],
                // contructorArgs
                ['editview', 'Opportunities', null, null, 'mobile'],
                // fieldNames
                [
                    'service_start_date',
                    'test_2',
                ],
                // propertyList
                [
                    'newProperty' => 'newValue',
                ],
                // expectation
                [
                    'service_start_date' => [
                        'name' => 'service_start_date',
                        'newProperty' => 'newValue',
                    ],
                    'test_1' => [
                        'name' => 'test_1',
                    ],
                    'test_2' => [
                        'name' => 'test_2',
                        'testProperty' => 'testValue',
                        'newProperty' => 'newValue',
                    ],
                ],
            ],
        ];
    }
}
