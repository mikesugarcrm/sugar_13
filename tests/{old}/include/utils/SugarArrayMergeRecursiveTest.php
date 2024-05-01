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

require_once 'include/utils.php';

class SugarArrayMergeRecursiveTest extends TestCase
{
    /**
     * @ticket 25280
     */
    public function testDeepArrayMerge()
    {
        $array1 = ['one' => ['two' => ['three' => ['some' => 'stuff']]]];
        $array2 = ['one' => ['two' => ['three' => ['more' => 'stuff']]]];
        $expected = ['one' => ['two' => ['three' => ['more' => 'stuff', 'some' => 'stuff']]]];
        $results = sugarArrayMergeRecursive($array1, $array2);
        $this->assertEquals($results, $expected);
    }

    /**
     * this one won't preserve order
     */
    public function testSubArrayKeysArePreserved()
    {
        $array1 = [
            'dog' => [
                'dog1' => 'dog1',
                'dog2' => 'dog2',
                'dog3' => 'dog3',
                'dog4' => 'dog4',
            ],
        ];

        $array2 = [
            'dog' => [
                'dog2' => 'dog2',
                'dog1' => 'dog1',
                'dog3' => 'dog3',
                'dog4' => 'dog4',
            ],
        ];

        $results = sugarArrayMergeRecursive($array1, $array2);

        $resultsKeys = array_keys($results['dog']);
        sort($resultsKeys);
        $array2Keys = array_keys($array2['dog']);
        sort($array2Keys);

        $this->assertEquals($resultsKeys, $array2Keys);
    }

    public function testSugarArrayMergeMergesTwoArraysWithLikeKeysOverwritingExistingKeys()
    {
        $foo = [
            'one' => 123,
            'two' => 123,
            'foo' => [
                'int' => 123,
                'foo' => 'bar',
            ],
        ];
        $bar = [
            'one' => 123,
            'two' => 321,
            'foo' => [
                'int' => 123,
                'bar' => 'foo',
            ],
        ];

        $expected = [
            'one' => 123,
            'two' => 321,
            'foo' => [
                'int' => 123,
                'foo' => 'bar',
                'bar' => 'foo',
            ],
        ];
        $this->assertEquals(sugarArrayMergeRecursive($foo, $bar), $expected);
        // insure that internal functions can't duplicate behavior
        $this->assertNotEquals(array_merge($foo, $bar), $expected);
        $this->assertNotEquals(array_merge_recursive($foo, $bar), $expected);
    }
}
