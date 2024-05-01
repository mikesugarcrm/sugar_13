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

namespace Sugarcrm\SugarcrmTestsUnit\Util\Arrays\TrackableArray;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions;
use Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray\TrackableArray;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray\TrackableArray
 */
class TrackableArrayTest extends TestCase
{
    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::getChangedKeys
     * @covers ::enableTracking
     */
    public function testModificationsAreTracked()
    {
        $arr = new TrackableArray();
        $arr['a'] = 1;
        $arr['b'] = 'string';
        $arr['c'] = ['data'];
        $arr['d'] = 'not changed';
        $arr->enableTracking();
        $arr['a'] += 1;
        $arr['b'] = 'new value';
        $arr['c']['new'] = 'value';

        $changedKeys = $arr->getChangedKeys();
        sort($changedKeys);
        $this->assertSame(['a', 'b', 'c'], $changedKeys);
    }

    /**
     * Tests that values appended to the TrackableArray are tracked.
     * @covers ::getChangedKeys
     * @covers ::enableTracking
     * @covers ::offsetSet
     * @covers ::offsetGet
     */
    public function testValuesAppendedAreTracked()
    {
        $arr = new TrackableArray();
        $arr[] = 'zero';
        $arr->enableTracking();
        $arr[] = 'one';
        $this->assertSame([1], $arr->getChangedKeys());
        $arr[] = 'two';
        $this->assertSame([1, 2], $arr->getChangedKeys());
        $arr['4'] = 'four';
        $arr[] = 'five';
        $this->assertSame([1, 2, 4, 5], $arr->getChangedKeys());
    }


    /**
     * Tests that a values unset from the TrackableArray are tracked.
     * @covers ::getChangedKeys
     * @covers ::enableTracking
     * @covers ::offsetSet
     * @covers ::offsetGet
     */
    public function testUnsetsAreTracked()
    {
        $arr = new TrackableArray();
        $arr['a'] = 'foo';
        $arr['b'] = 'bar';
        $arr->enableTracking();
        unset($arr['a']);
        $this->assertSame(['a'], $arr->getChangedKeys());
    }


    /**
     * Tests that the array values of a TrackableArray are identical to a native array.
     * @covers ::getArrayCopy
     * @covers ::offsetGet
     * @covers ::offsetSet
     * @covers ::offsetUnset
     */
    public function testArrayValuesAreIdentical()
    {
        $array = [];
        $tracked = new TrackableArray();

        //Appending value to empty array
        $array[] = 'a';
        $tracked[] = 'a';

        //Adding value by key
        $array['zz'] = 'z';
        $tracked['zz'] = 'z';

        //Appending value to non-empty array
        $array[] = 'b';
        $tracked[] = 'b';

        //Adding value by numeric key
        $array[5] = 'five';
        $tracked[5] = 'five';

        //Appending value to non-empty array with out of order numeric key
        $array[] = 'c';
        $tracked[] = 'c';

        //Nested array creation by reference
        $array['arr']['a'] = 'A';
        $tracked['arr']['a'] = 'A';

        //unset values
        $array['gone'] = 'not here';
        $tracked['gone'] = 'not here';
        unset($array['gone']);
        unset($tracked['gone']);

        //unset nested values
        $array['arr']['c'] = 'c';
        $tracked['arr']['c'] = 'c';
        unset($array['arr']['c']);
        unset($tracked['arr']['c']);

        $this->assertSame($array, $tracked->getArrayCopy());
    }

    /**
     * Test validating that a new TrackableArray still passes empty checks.
     * @coversNothing
     */
    public function testTrackableArrayRegistersEmpty()
    {
        $this->assertEmpty(new TrackableArray());
    }


    /**
     * Tests that changes can be applied correctly to another array.
     * @covers ::applyTrackedChangesToArray
     */
    public function testApplyChangesToArray()
    {
        $tracked = new TrackableArray();
        $tracked['toBeUnset'] = true;
        $tracked['tobeChanged'] = true;
        $tracked['deep']['a'] = 1;
        $tracked['deep']['unchanged'] = true;
        $tracked->enableTracking();
        $tracked['tobeAdded'] = true;
        unset($tracked['toBeUnset']);
        $tracked['deep']['a'] = 2;
        $tracked['new_deep']['a'] = 1;
        $tracked['tobeChanged'] = 'changed';
        $tracked['nested'] = ['one' => 1, 'some' => ['a', 'b']];
        $arr = [
            'toBeUnset' => true,
            'tobeChanged' => 1,
            'deep' => ['a' => 1],
        ];
        $tracked->applyTrackedChangesToArray($arr);
        $this->assertSame([
            'tobeChanged' => 'changed',
            'deep' => [
                'a' => 2,
            ],
            'new_deep' => [
                'a' => 1,
            ],
            'nested' => ['some' => ['a', 'b'], 'one' => 1],
            'tobeAdded' => true,
        ], $arr);
    }

    /**
     * Tests that a new TrackableArray can be populated from an existing array.
     * @covers ::populateFromArray
     * @covers ::__construct
     */
    public function testPopulateFromArray()
    {
        $arr = [
            'a' => 1,
            'deep' => ['b' => 2],
        ];
        $trackable = new TrackableArray($arr);


        //$this->assertInstanceOf('Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray\TrackableArray', $trackable['deep']);
        $this->assertEquals(1, $trackable['a']);
        $this->assertSame($arr, $trackable->getArrayCopy());

        //Verify Tracking for deep arrays created this way
        $trackable->enableTracking();
        $trackable['deep']['b'] = 3;
        $trackable->applyTrackedChangesToArray($arr);

        $this->assertSame($arr, $trackable->getArrayCopy());

        $trackable = new TrackableArray($arr);
        $trackable->populateFromArray($arr);

        $this->assertSame($arr, $trackable->getArrayCopy());
    }

    /**
     * Tests that in_array_access works with TrackableArray
     * @covers \Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions::in_array_access
     */
    public function testInArrayAccess()
    {
        $track = new TrackableArray(['red', 'green', 'blue']);

        $this->assertTrue(ArrayFunctions::in_array_access('red', $track));
        $this->assertFalse(ArrayFunctions::in_array_access('yellow', $track));
    }

    /**
     * Tests that array_access_merge works with TrackableArray
     * @covers \Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions::array_access_merge
     */
    public function testArrayAccessMerge()
    {
        $track = new TrackableArray(['red', 'green', 'blue']);
        $toMerge = ['yellow'];

        $this->assertSame(
            ['red', 'green', 'blue', 'yellow'],
            ArrayFunctions::array_access_merge($track, $toMerge)
        );
    }


    /**
     * Tests that array_access_keys works with TrackableArray
     * @covers \Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions::array_access_keys
     */
    public function testArrayAccessKeys()
    {
        $arr = ['red' => 'RED!', 'green', 'blue'];
        $track = new TrackableArray($arr);

        $this->assertSame(array_keys($arr), ArrayFunctions::array_access_keys($track));
    }


    /**
     * Tests that array_access_keys works with TrackableArray
     * @covers \Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions::array_access_keys
     */
    public function testIsArrayAccess()
    {
        $arr = ['nested' => []];
        $track = new TrackableArray($arr);

        $this->assertFalse(is_array($track['nested']));
        $this->assertTrue(ArrayFunctions::is_array_access($track['nested']));
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray\TrackableArray::class, $track['nested']);
    }
}
