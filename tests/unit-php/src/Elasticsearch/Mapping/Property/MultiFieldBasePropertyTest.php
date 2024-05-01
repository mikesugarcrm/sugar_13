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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Mapping\Property;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldBaseProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldBaseProperty
 */
class MultiFieldBasePropertyTest extends TestCase
{
    /**
     * Default mapping
     * @var array
     */
    protected $defaultMapping = [
        'type' => 'keyword',
        'index' => false,
    ];

    /**
     * @covers ::getMapping
     */
    public function testGetMapping()
    {
        $field = new MultiFieldBaseProperty();
        $this->assertSame($this->defaultMapping, $field->getMapping());
    }

    /**
     * @covers ::addField
     * @covers ::getMapping
     * @dataProvider providerTestAddField
     */
    public function testAddField($fields, $expected)
    {
        $field = new MultiFieldBaseProperty();
        foreach ($fields as $name => $property) {
            $field->addField($name, $property);
        }
        $this->assertSame($expected, $field->getMapping());
    }

    public function providerTestAddField()
    {
        return [

            // one field
            [
                [
                    'name' => new MultiFieldProperty(),
                ],
                [
                    'type' => 'keyword',
                    'index' => false,
                    'fields' => [
                        'name' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],

            // multiple fields
            [
                [
                    'name' => new MultiFieldProperty(),
                    'descr' => new MultiFieldProperty(),
                ],
                [
                    'type' => 'keyword',
                    'index' => false,
                    'fields' => [
                        'name' => [
                            'type' => 'text',
                        ],
                        'descr' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::addField
     */
    public function testAddFieldFailureOnExistingField()
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Field 'foobar' already exists as multi field");

        $field = new MultiFieldBaseProperty();
        $property = new MultiFieldProperty();
        $field->addField('foobar', $property);
        $property->setType('keyword');
        $field->addField('foobar', $property);
    }

    /**
     * @covers ::addField
     * @covers ::getMapping
     * @dataProvider providerTestAddField
     */
    public function testAddFieldOnExistingFieldWithSameProperties($fields, $expected)
    {
        $field = new MultiFieldBaseProperty();
        foreach ($fields as $name => $property) {
            $field->addField($name, $property);
            $field->addField($name, $property);
        }
        $this->assertSame($expected, $field->getMapping());
    }
}
