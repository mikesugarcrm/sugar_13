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

namespace Sugarcrm\SugarcrmTestsUnit\inc;

use LoggerManager;
use PHPUnit\Framework\TestCase;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \Configurator
 */
class UtilsTest extends TestCase
{
    /**
     * @var mixed|mixed[]
     */
    public $sugar_config_bak;

    /**
     * @var array
     */
    protected $savedModuleStrings;

    /**
     * @var array
     */
    protected $savedAppListStrings;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['log'] = $this->createMock(LoggerManager::class);

        $this->savedModuleStrings = $GLOBALS['mod_strings'] ?? null;

        if (array_key_exists('mod_strings', $GLOBALS) && is_array($GLOBALS['mod_strings'])) {
            $GLOBALS['mod_strings']['LBL_BASIC'] = 'Basic';
        } else {
            $GLOBALS['mod_strings'] = [
                'LBL_BASIC' => 'Basic',
            ];
        }

        $this->sugar_config_bak = $GLOBALS['sugar_config'] ?? [];
        $GLOBALS['sugar_config']['languages'] = [
            'en_us' => 'English (US)',
        ];

        $this->savedAppListStrings = $GLOBALS['app_list_strings'] ?? null;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us', false);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['log']);

        if ($this->savedAppListStrings) {
            $GLOBALS['app_list_strings'] = $this->savedAppListStrings;
        }
        if ($this->savedModuleStrings) {
            $GLOBALS['mod_strings'] = $this->savedModuleStrings;
        }

        $GLOBALS['sugar_config'] = $this->sugar_config_bak;
        parent::tearDown();
    }

    /**
     * Provider for testSugarArrayMergeRecursive
     */
    public function providerTestSugarArrayMergeRecursive()
    {
        return [
            'arrays are sequential' => [
                ['test' => [1, 2, 3]],
                ['test' => [5, 6]],
                ['test' => [5, 6]],
            ],
            'arrays are associative' => [
                [
                    'full_text_engine' => [
                        'Elastic' => [
                            'host' => 'localhost',
                            'port' => '9200',
                        ],
                    ],
                ],
                [
                    'full_text_engine' => [
                        'Elastic' => [
                            'port' => '9201',
                        ],
                    ],
                ],
                [
                    'full_text_engine' => [
                        'Elastic' => [
                            'host' => 'localhost',
                            'port' => '9201',
                        ],
                    ],
                ],
            ],
            'one array is sequential and another is associative' => [
                ['test' => [1, 2, 3]],
                ['test' => ['key' => 'value']],
                ['test' => [1, 2, 3, 'key' => 'value']],
            ],
            'left array is empty and another is associative' => [
                ['test' => []],
                ['test' => ['key' => 'value']],
                ['test' => ['key' => 'value']],
            ],
            'right array is empty and another is associative' => [
                ['test' => ['key' => 'value']],
                ['test' => []],
                ['test' => ['key' => 'value']],
            ],
            'left array is empty and another is sequential' => [
                ['test' => []],
                ['test' => [1, 2, 3]],
                ['test' => [1, 2, 3]],
            ],
            'right array is empty and another is sequential' => [
                ['test' => [1, 2, 3]],
                ['test' => []],
                ['test' => []],
            ],
        ];
    }

    /**
     * @covers       \sugarArrayMergeRecursive
     * @param array $target
     * @param array $override
     * @param array $result
     * @dataProvider providerTestSugarArrayMergeRecursive
     */
    public function testSugarArrayMergeRecursive($target, $override, $result)
    {
        $this->assertEquals($result, \sugarArrayMergeRecursive($target, $override));
    }

    /**
     * @covers \getValueFromConfig
     */
    public function testGetValueFromConfig()
    {
        $GLOBALS['sugar_config']['berry'] = true;

        $this->assertEquals(true, \getValueFromConfig('berry'));
    }

    /**
     * @dataProvider isFalsyDataProvider
     * @covers       \isFalsy
     * @param mixed $value param to pass
     * @param bool $expected expected return
     */
    public function testIsFalsy($value, $expected)
    {
        $this->assertEquals($expected, \isFalsy($value));
    }

    public function isFalsyDataProvider()
    {
        return [
            ['value' => false, 'expected' => true],
            ['value' => 'false', 'expected' => true],
            ['value' => 0, 'expected' => true],
            ['value' => '0', 'expected' => true],
            ['value' => 'off', 'expected' => true],
            ['value' => true, 'expected' => false],
            ['value' => 'banana', 'expected' => false],
            ['value' => -1, 'expected' => false],
        ];
    }

    /**
     * @dataProvider sugarStrToUpperProvider
     * @covers       \sugarStrToUpper
     * @param string $string string to convert
     * @param string $expected expected result of string conversion
     */
    public function testSugarStrToUpper($string, $expected)
    {
        $this->assertEquals($expected, sugarStrToUpper($string));
    }

    public function sugarStrToUpperProvider()
    {
        // {string} string
        // {string} expected result
        return [
            ['', ''],
            ['hello', 'HELLO'],
            ['hElLo', 'HELLO'],
            ['HELLO', 'HELLO'],
            ['母老虎@家里.睡觉', '母老虎@家里.睡觉'],
            ['die Wörter', 'DIE WÖRTER'],
        ];
    }

    /**
     * @dataProvider sugarStrToLowerProvider
     * @covers       \sugarStrToLower
     * @param string $string string to convert
     * @param string $expected expected result of string conversion
     */
    public function testSugarStrToLower($string, $expected)
    {
        $this->assertEquals($expected, sugarStrToLower($string));
    }

    public function sugarStrToLowerProvider()
    {
        // {string} string
        // {string} expected result
        return [
            ['', ''],
            ['hello', 'hello'],
            ['hElLo', 'hello'],
            ['HELLO', 'hello'],
            ['母老虎@家里.睡觉', '母老虎@家里.睡觉'],
            ['DIE WÖRTER', 'die wörter'],
        ];
    }

    /**
     * @dataProvider sugarSubstrProvider
     * @covers       \sugarSubstr
     * @param string $string string to extract from
     * @param int|null $start index to start extraction from
     * @param int|null $length length of the substring to extract
     * @param string $expected expected result of substring extraction
     */
    public function testSugarSubstr($string, $start, $length, $expected)
    {
        $this->assertEquals($expected, sugarSubstr($string, $start, $length));
    }

    public function sugarSubstrProvider()
    {
        // {string} string
        // {int} starting index
        // {int} length
        // {string} expected result
        return [
            ['', 1, 3, ''],
            ['hello', 1, 3, 'ell'],
            ['hello', 1, null, 'ello'],
            ['hello', null, 3, 'hel'],
            ['母老虎', 2, 1, '虎'],
            ['die Wörter', 4, null, 'Wörter'],
        ];
    }

    /**
     * @dataProvider sugarStrlenProvider
     * @covers       \sugarStrlen
     * @param string $string string to count characters of
     * @param int $expected expected result number of characters
     */
    public function testSugarStrlen($string, $expected)
    {
        $this->assertEquals($expected, sugarStrlen($string));
    }

    public function sugarStrlenProvider()
    {
        // {string} string
        // {int} expected result
        return [
            ['', 0],
            ['a', 1],
            ['hello', 5],
            ['母老虎母老虎', 6],
            ['die Wörter', 10],
        ];
    }

    /**
     * @dataProvider providerTestSugarStrPos
     * @covers       \sugarStrpos
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param int|false $expected
     */
    public function testSugarStrPos(string $haystack, string $needle, int $offset, $expected)
    {
        $this->assertEquals($expected, sugarStrpos($haystack, $needle, $offset));
    }

    /**
     * @return array[] Test values for testSugarStrPos
     */
    public function providerTestSugarStrPos()
    {
        return [
            ['test', 't', 0, 0],
            ['이것은 모듈입니다', '이', 0, 0],
            ['test', 's', 0, 2],
            ['이것은 모듈입니다', '은', 0, 2],
            ['test offset', 'f', 8, false],
            ['이것은 모듈입니다', '은', 3, false],
            ['test space', ' ', 0, 4],
            ['이것은 모듈입니다', ' ', 0, 3],
        ];
    }

    /**
     * @dataProvider providerTestTranslate
     * @covers       \translate
     * @param any $string
     * @param any $mod
     * @param any $selectedValue
     * @param any $expected
     */
    public function testTranslate($string, $mod, $selectedValue, $expected)
    {
        require_once 'include/SugarCache/SugarCache.php';
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us', false);
        $this->assertEquals($expected, translate($string, $mod, $selectedValue));
    }

    /**
     * @return array[] Test values for translate
     */
    public function providerTestTranslate()
    {
        return [
            ['account_type_dom', '', 'Analyst', 'Analyst'],
            ['LBL_BASIC', '', '', 'Basic'],
            ['LBL_ACCOUNT_NAME', 'Accounts', '', 'Account Name:'],
            [[], '', '', []],
            [['test'], 'Accounts', '', ['test']],
            [[1 => 'test'], 'Contacts', 'test1', [1 => 'test']],
            [123, '', '', 123],
        ];
    }

    public function testSafeIsIterable()
    {
        self::assertTrue(safeIsIterable([1, 'a' => 2]));
        self::assertTrue(safeIsIterable(new \stdClass()));
        self::assertTrue(safeIsIterable(new \ArrayIterator([])));
        $generator = function () {
            yield 1;
        };
        self::assertTrue(safeIsIterable($generator()));
        self::assertFalse(safeIsIterable(1));
        self::assertFalse(safeIsIterable(new \DateTime()));
        self::assertFalse(safeIsIterable(null));
        self::assertFalse(safeIsIterable(true));
    }
    
    /**
     * @dataProvider providerTestReindexArray
     * @covers \reindexArray
     * @param array $array
     * @param bool $sort
     * @param array $expected
     */
    public function testReindexArray(array $array, bool $sort, array $expected)
    {
        $this->assertEquals($expected, reindexArray($array, $sort));
    }

    /**
     * @return array[] Test values for testReindexArray
     */
    public function providerTestReindexArray()
    {
        return [
            [[1 => 'a', 3 => 'b'], false, ['a', 'b']],
            [['a' => [1 => 'c', 3 => 'd'], 'b'], false, ['a'  => ['c', 'd'], 'b']],
            [[1 => 'a', 'c' => 'd', 3 => 'b'], false, [1 => 'a', 'c' => 'd', 3 => 'b']],
            [[1 => 'a', 'c' => 'd', 3 => 'b'], true, [1 => 'a', 3 => 'b', 'c' => 'd']],
        ];
    }
}
