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

require_once 'include/utils/array_utils.php';

class SugarArrayTest extends TestCase
{
    public function testCanFindValueUsingDotNotation()
    {
        $random = random_int(100, 200);
        $array = [
            'foo' => [
                $random => [
                    'bar' => $random,
                ],
            ],
        ];

        $array = new SugarArray($array);
        $this->assertEquals($array->get("foo.{$random}.bar"), $random);
    }

    public function testReturnsDefaultValueWhenDoesNotContainRequestedValue()
    {
        $random = random_int(100, 200);
        $array = new SugarArray([]);
        $this->assertEquals($array->get('unknown', $random), $random);
    }

    public function testImplementsArrayAccess()
    {
        $reflection = new ReflectionClass('SugarArray');
        $this->assertTrue($reflection->implementsInterface('ArrayAccess'));
    }

    public function testImplementsCountable()
    {
        $reflection = new ReflectionClass('SugarArray');
        $this->assertTrue($reflection->implementsInterface('Countable'));
    }

    public function testStaticMethodCanTraverseProvidedArray()
    {
        $random = random_int(100, 200);
        $array = [
            'foo' => [
                $random => [
                    'bar' => $random,
                ],
            ],
        ];

        $this->assertEquals(SugarArray::staticGet($array, "foo.{$random}.bar"), $random);
    }

    public function testStaticMethodCanReturnDefaultOnUnknownValue()
    {
        $random = random_int(100, 200);
        $this->assertEquals(SugarArray::staticGet([123, 321], 'unknown', $random), $random);
    }

    public function testAdd_blank_option()
    {
        $options = 'noneArray';
        $expectedArray = ['' => ''];
        $result = add_blank_option($options);
        $this->assertEquals($result[''], $expectedArray['']);
        $options2 = ['mason' => 'unittest'];
        $expectedArray2 = ['' => '', 'mason' => 'unittest'];
        $result2 = add_blank_option($options2);
        $this->assertEquals($result2, $expectedArray2);
    }
}
