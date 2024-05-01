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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Mapping;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\ObjectProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\RawProperty;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping
 */
class MappingTest extends TestCase
{
    /**
     * @covers ::excludeFromSource
     * @covers ::getSourceExcludes
     */
    public function testExcludeFromSource()
    {
        $mapping = new Mapping('FooBar');
        $this->assertSame([], $mapping->getSourceExcludes());
        $mapping->excludeFromSource('field1');
        $mapping->excludeFromSource('field2');
        $mapping->excludeFromSource('field1');
        $this->assertSame(['field1', 'field2'], $mapping->getSourceExcludes());
    }

    /**
     * @covers ::getModule
     */
    public function testGetModule()
    {
        $mapping = new Mapping('ModuleName');
        $this->assertSame('ModuleName', $mapping->getModule());
    }

    /**
     * @covers ::hasProperty
     * @covers ::getProperty
     */
    public function testHasProperty()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('foobar'));

        $property = new RawProperty();
        $mapping->addRawProperty('foobar', $property);
        $this->assertTrue($mapping->hasProperty('foobar'));
        $this->assertSame($property, $mapping->getProperty('foobar'));
    }

    /**
     * @covers ::getProperty
     */
    public function testGetPropertyNotExist()
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Trying to get non-existing property 'fieldx' for 'FooBar'");

        $mapping = new Mapping('FooBar');
        $mapping->getProperty('fieldx');
    }

    /**
     * @covers ::addRawProperty
     * @covers ::addProperty
     */
    public function testAddRawProperty()
    {
        $mapping = new Mapping('FooBar');
        $property = new RawProperty();
        $mapping->addRawProperty('fieldx', $property);
        $this->assertSame($property, $mapping->getProperty('fieldx'));
    }

    /**
     * @covers ::addRawProperty
     * @covers ::addProperty
     */
    public function testAddRawPropertyExistingFailure()
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Cannot redeclare field 'fieldx' for module 'FooBar'");

        $mapping = new Mapping('FooBar');
        $mapping->addRawProperty('fieldx', new RawProperty());
        $mapping->addRawProperty('fieldx', new RawProperty());
    }

    /**
     * @covers ::addObjectProperty
     * @covers ::addProperty
     */
    public function testAddObjectProperty()
    {
        $mapping = new Mapping('FooBar');
        $property = new ObjectProperty();
        $mapping->addObjectProperty('fieldy', $property);
        $this->assertSame($property, $mapping->getProperty('fieldy'));
    }

    /**
     * @covers ::addObjectProperty
     * @covers ::addProperty
     */
    public function testAddObjectPropertyExistingFailure()
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Cannot redeclare field 'fieldy' for module 'FooBar'");

        $mapping = new Mapping('FooBar');
        $mapping->addRawProperty('fieldy', new ObjectProperty());
        $mapping->addRawProperty('fieldy', new ObjectProperty());
    }

    /**
     * Testing calling only addNotAnalyzedField
     * @covers ::addNotAnalyzedField
     * @covers ::createMultiFieldBase
     * @covers ::compile
     */
    public function testAddNotAnalyzedField()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('field1'));

        // add field1, no copyTo
        $mapping->addNotAnalyzedField('field1');
        $this->assertTrue($mapping->hasProperty('field1'));
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => true,
            ],
        ], $mapping->compile());

        // add same field again, with one copyTo
        $mapping->addNotAnalyzedField('field1', ['field1_copy1']);
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => true,
                'copy_to' => [
                    'field1_copy1',
                ],
            ],
        ], $mapping->compile());

        // add new field, with one copyTo
        $mapping->addNotAnalyzedField('field2', ['field2_copy1']);
        $this->assertTrue($mapping->hasProperty('field2'));
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => true,
                'copy_to' => [
                    'field1_copy1',
                ],
            ],
            'field2' => [
                'type' => 'keyword',
                'index' => true,
                'copy_to' => [
                    'field2_copy1',
                ],
            ],
        ], $mapping->compile());

        // add field1 again, with one more copyTo
        $mapping->addNotAnalyzedField('field1', ['field1_copy2']);
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => true,
                'copy_to' => [
                    'field1_copy1',
                    'field1_copy2',
                ],
            ],
            'field2' => [
                'type' => 'keyword',
                'index' => true,
                'copy_to' => [
                    'field2_copy1',
                ],
            ],
        ], $mapping->compile());
    }

    /**
     * Testing calling only addNotIndexedField
     * @covers ::addNotIndexedField
     * @covers ::createMultiFieldBase
     * @covers ::compile
     */
    public function testAddNotIndexedField()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('field1'));

        // add field1, no copyTo
        $mapping->addNotIndexedField('field1');
        $this->assertTrue($mapping->hasProperty('field1'));
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => false,
            ],
        ], $mapping->compile());

        // add same field again, with one copyTo
        $mapping->addNotIndexedField('field1', ['field1_copy1']);
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => false,
                'copy_to' => [
                    'field1_copy1',
                ],
            ],
        ], $mapping->compile());

        // add new field, with one copyTo
        $mapping->addNotIndexedField('field2', ['field2_copy1']);
        $this->assertTrue($mapping->hasProperty('field2'));
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => false,
                'copy_to' => [
                    'field1_copy1',
                ],
            ],
            'field2' => [
                'type' => 'keyword',
                'index' => false,
                'copy_to' => [
                    'field2_copy1',
                ],
            ],
        ], $mapping->compile());

        // add field1 again, with one more copyTo
        $mapping->addNotIndexedField('field1', ['field1_copy2']);
        $this->assertSame([
            'field1' => [
                'type' => 'keyword',
                'index' => false,
                'copy_to' => [
                    'field1_copy1',
                    'field1_copy2',
                ],
            ],
            'field2' => [
                'type' => 'keyword',
                'index' => false,
                'copy_to' => [
                    'field2_copy1',
                ],
            ],
        ], $mapping->compile());
    }

    /**
     * Testing calling only addMultiField
     * @covers ::addMultiField
     * @covers ::createMultiFieldBase
     * @covers ::compile
     */
    public function testAddMultiField()
    {
        $mapping = new Mapping('FooBar');
        $this->assertFalse($mapping->hasProperty('base1'));

        // add base1.field1
        $mapping->addMultiField('base1', 'field1', new MultiFieldProperty());
        $this->assertTrue($mapping->hasProperty('base1'));
        $this->assertSame([
            'base1' => [
                'type' => 'keyword',
                'index' => true,
                'fields' => [
                    'field1' => ['type' => 'text'],
                ],
            ],
        ], $mapping->compile());

        // add base1.field2
        $mapping->addMultiField('base1', 'field2', new MultiFieldProperty());
        $this->assertSame([
            'base1' => [
                'type' => 'keyword',
                'index' => true,
                'fields' => [
                    'field1' => ['type' => 'text'],
                    'field2' => ['type' => 'text'],
                ],
            ],
        ], $mapping->compile());
    }

    /**
     * Test addMultiField on a non-multifield base
     * @covers ::addMultiField
     * @covers ::createMultiFieldBase
     */
    public function testAddMultiFieldInvalidBase()
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Field 'field1' is not a multi field");

        $mapping = new Mapping('FooBar');
        $mapping->addRawProperty('field1', new RawProperty());
        $mapping->addMultiField('field1', 'multi1', new MultiFieldProperty());
    }

    /**
     * Test addMultiField on a non-multifield base
     * @covers ::addMultiField
     * @covers ::createMultiFieldBase
     */
    public function testAddMultiFieldDuplicateField()
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage("Field 'multi1' already exists as multi field");

        $mapping = new Mapping('FooBar');
        $property = new MultiFieldProperty();
        $mapping->addMultiField('field1', 'multi1', $property);
        $property->setType('keyword');
        $mapping->addMultiField('field1', 'multi1', $property);
    }

    /**
     * Combination testing: Create multi fields on top of not indexed base field
     * @coversNothing
     * @dataProvider providerTestAddMultiFieldCombination
     */
    public function testAddMultiFieldCombination($baseMethod, $expected)
    {
        $mapping = new Mapping('FooBar');

        // create base field
        call_user_func([$mapping, $baseMethod], 'base');

        // add base.field1
        $mapping->addMultiField('base', 'field1', new MultiFieldProperty());
        $this->assertTrue($mapping->hasProperty('base'));
        $this->assertSame($expected, $mapping->compile());
    }

    public function providerTestAddMultiFieldCombination()
    {
        return [
            [
                'addNotIndexedField',
                [
                    'base' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'field1' => ['type' => 'text'],
                        ],
                    ],
                ],
            ],
            [
                'addNotAnalyzedField',
                [
                    'base' => [
                        'type' => 'keyword',
                        'index' => true,
                        'fields' => [
                            'field1' => ['type' => 'text'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
