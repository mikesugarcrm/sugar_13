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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine\PMSEPreProcessor;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\ProcessManager\Registry;

/**
 * @coversDefaultClass \PMSETerminateValidator
 */
class PMSEBaseValidatorTest extends TestCase
{
    private static $regKey = 'process-validators-Base';

    private static $keyStack = [];

    private static function getCacheKey($name)
    {
        return static::$regKey . '-' . $name;
    }

    public static function setUpBeforeClass(): void
    {
        // Test data to use
        static::$keyStack['foo'] = static::getCacheKey('foo');
        static::$keyStack['baz'] = static::getCacheKey('baz');

        // Set some sample data onto the registry
        $reg = Registry\Registry::getInstance();
        $reg->set(static::$keyStack['foo'], 'bar');
        $reg->set(static::$keyStack['baz'], 'zim');

        // Set the base key value to start off with, which mimics the addCacheValue
        // logic
        $reg->set(
            static::$regKey,
            [
                static::$keyStack['foo'],
                static::$keyStack['baz'],
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        // Reset the internal stack to empty... this is just good business
        static::$keyStack = [];

        // Reset the registry. This is VERY destructive, but in the context of a
        // suite of tests, this should be done.
        Registry\Registry::getInstance()->reset();
    }

    public function hasCacheValueProvider()
    {
        return [
            [
                'index' => 'foo',
                'expect' => true,
            ],
            [
                'index' => 'baz',
                'expect' => true,
            ],
            [
                'index' => 'wut',
                'expect' => false,
            ],
        ];
    }

    /**
     * Tests if a value exists in the cache for an index
     * @covers ::hasCacheValue
     * @param string $index The index name
     * @param boolean $expect The expectation
     * @dataProvider hasCacheValueProvider
     */
    public function testHasCacheValue($index, $expect)
    {
        $bv = new \PMSEBaseValidator();
        $actual = $bv->hasCacheValue($index);
        $this->assertSame($expect, $actual);
    }

    public function getCacheValueProvider()
    {
        return [
            [
                'index' => 'foo',
                'expect' => 'bar',
            ],
            [
                'index' => 'baz',
                'expect' => 'zim',
            ],
            [
                'index' => 'wut',
                'expect' => null,
            ],
        ];
    }

    /**
     * Tests if a value exists in the cache for an index
     * @covers ::getCacheValue
     * @param string $index The index name
     * @param string $expect The expectation
     * @dataProvider getCacheValueProvider
     */
    public function testGetCacheValue($index, $expect)
    {
        $bv = new \PMSEBaseValidator();
        $actual = $bv->getCacheValue($index);
        $this->assertSame($expect, $actual);
    }

    /**
     * Tests the addition of a value to the cache
     * @covers ::addCacheValue
     */
    public function testAddCacheValue()
    {
        // Needed for another test later
        static::$keyStack['push'] = static::getCacheKey('push');

        $bv = new \PMSEBaseValidator();

        // Add a value to the cache
        $bv->addCacheValue('push', 'pull');
        $actual = $bv->getCacheValue('push');
        $this->assertSame('pull', $actual);

        // Try to add a different value to the cache knowing it won't
        $bv->addCacheValue('push', 'move');
        $actual = $bv->getCacheValue('push');
        $this->assertSame('pull', $actual);

        // Now test the stack of key values, knowing that the last key was added
        // to the key stack twice - THIS NEEDS TO BE FIXED
        $actual = Registry\Registry::getInstance()->get(static::$regKey);
        $expect = [
            static::$keyStack['foo'],
            static::$keyStack['baz'],
            static::$keyStack['push'],
            static::$keyStack['push'],
        ];
        $this->assertSame($expect, $actual);
    }

    /**
     * Tests clearing the cache value
     * @covers ::clearCache
     */
    public function testClearCache()
    {
        $bv = new \PMSEBaseValidator();
        $bv->clearCache();

        $reg = Registry\Registry::getInstance();

        // Make sure none of the keys created still have value
        foreach (static::$keyStack as $key) {
            $this->assertFalse($reg->has($key));
        }

        // Lastly, test that the validator class key returns false for has
        $this->assertFalse($reg->has(static::$regKey));
    }

    public function isChangeOperationProvider()
    {
        return [
            // Tests operator field not set
            [
                'field' => (object)['noOp' => 'foo'],
                'expect' => false,
            ],
            // Tests operator field not a change
            [
                'field' => (object)['expOperator' => 'foo'],
                'expect' => false,
            ],
            // Tests operator field is a change - case #1
            [
                'field' => (object)['expOperator' => 'changes'],
                'expect' => true,
            ],
            // Tests operator field is a change - case #2
            [
                'field' => (object)['expOperator' => 'changes_from'],
                'expect' => true,
            ],
            // Tests operator field is a change - case #3
            [
                'field' => (object)['expOperator' => 'changes_to'],
                'expect' => true,
            ],
        ];
    }

    /**
     * @covers ::isChangeOperation
     * @param stdClass $field Mock criteria field object
     * @param boolean $expect Expectation
     * @dataProvider isChangeOperationProvider
     */
    public function testIsChangeOperation($field, $expect)
    {
        $bv = new \PMSEBaseValidator();
        $actual = $bv->isChangeOperation($field);
        $this->assertSame($expect, $actual);
    }

    public function getDecodedCriteriaProvider()
    {
        return [
            [
                'criteria' => '"test string"',
                'expect' => 'test string',
            ],
            [
                'criteria' => '[{"expOperator":"foo"},{"expOperator":"changes"}]',
                'expect' => [
                    (object)['expOperator' => 'foo'],
                    (object)['expOperator' => 'changes'],
                ],
            ],
        ];
    }

    /**
     * @covers ::getDecodedCriteria
     * @param string $criteria JSON string to decode
     * @param mixed $expect Expected decoded value
     * @dataProvider getDecodedCriteriaProvider
     */
    public function testGetDecodedCriteria($criteria, $expect)
    {
        $bv = new \PMSEBaseValidator();
        $actual = $bv->getDecodedCriteria($criteria);
        $this->assertEquals($expect, $actual);
    }

    /**
     * @covers ::getEncodedCriteria
     * @param string $expect Expected encoded value
     * @param mixed $criteria Input to encode
     * @dataProvider getDecodedCriteriaProvider
     */
    public function testGetEncodeCriteria($expect, $criteria)
    {
        $bv = new \PMSEBaseValidator();
        $actual = $bv->getEncodedCriteria($criteria);
        $this->assertSame($expect, $actual);
    }

    public function validateUpdateStateProvider()
    {
        return [
            // Test no change because of criteria
            [
                'criteria' => '[{"expOperator":"foo"}]',
                'args' => ['isUpdate' => true],
                'expect' => '[{"expOperator":"foo"}]',
            ],
            // Test no change because of args
            [
                'criteria' => '[{"expOperator":"foo"}]',
                'args' => [],
                'expect' => '[{"expOperator":"foo"}]',
            ],
            // Test change one object
            [
                'criteria' => '[{"expOperator":"changes"}]',
                'args' => ['isUpdate' => true],
                'expect' => '[{"expOperator":"changes","isUpdate":true}]',
            ],
            // Test change second of two objects
            [
                'criteria' => '[{"expOperator":"foo"},{"expOperator":"changes_from"}]',
                'args' => ['isUpdate' => true],
                'expect' => '[{"expOperator":"foo"},{"expOperator":"changes_from","isUpdate":true}]',
            ],
        ];
    }

    /**
     * @covers ::validateUpdateState
     * @param string $criteria Input criteria
     * @param array $args Request arguments
     * @param string $expect Expectation
     * @dataProvider validateUpdateStateProvider
     */
    public function testValidateUpdateState($criteria, $args, $expect)
    {
        $bv = new \PMSEBaseValidator();
        $actual = $bv->validateUpdateState($criteria, $args);
        $this->assertSame($expect, $actual);
    }

    public function hasAnyOrAllTypeOperationProvider()
    {
        return [
            // No criteria
            [
                'criteria' => '',
                'expect' => false,
            ],
            // Empty criteria
            [
                'criteria' => '[]',
                'expect' => false,
            ],
            // Non matching criteria #1
            [
                'criteria' => '[{"foo":"Any"}]',
                'expect' => false,
            ],
            // Non matching criteria #2
            [
                'criteria' => '[{"expRel":"Foo"}]',
                'expect' => false,
            ],
            // Match All #1
            [
                'criteria' => '[{"expRel":"All"}]',
                'expect' => true,
            ],
            // Match All #2
            [
                'criteria' => '[{"foo":"Any"},{"expRel":"Foo"},{"expRel":"All"}]',
                'expect' => true,
            ],
            // Match Any #1
            [
                'criteria' => '[{"expRel":"Any"}]',
                'expect' => true,
            ],
            // Match Any #2
            [
                'criteria' => '[{"foo":"All"},{"expRel":"Any"},{"expRel":"Foo"}]',
                'expect' => true,
            ],
        ];
    }

    /**
     * @covers ::hasAnyOrAllTypeOperation
     * @param string $criteria Input criteria
     * @param bool $expect Expectation
     * @dataProvider hasAnyOrAllTypeOperationProvider
     */
    public function testHasAnyOrAllTypeOperation(string $criteria, bool $expect)
    {
        $bv = new \PMSEBaseValidator();
        $actual = $bv->hasAnyOrAllTypeOperation($criteria);
        $this->assertSame($expect, $actual);
    }
}
