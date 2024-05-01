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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine\PMSEHandlers;

use PHPUnit\Framework\TestCase;
use PMSEBeanHandler;

/**
 * @coversDefaultClass \PMSEBeanHandler
 */
class PMSEBeanHandlerTest extends TestCase
{
    /**
     * The PMSEBeanHandler object
     * @var PMSEBeanHandler
     */
    protected static $bh;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        static::$bh = new PMSEBeanHandler();
    }

    /**
     * Data Provider for the parseString test method
     * @return array
     */
    public function parseStringDataProvider()
    {
        return [
            // No content to parse
            [
                'template' => 'Hello world',
                'base_module' => 'Foo',
                'expect' => [
                    'Foo' => [],
                ],
            ],
            // Module field
            [
                'template' => 'Foo {::Accounts::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_future' => [
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'value_type' => 'future',
                            'original' => '{::Accounts::name::}',
                        ],
                    ],
                ],
            ],
            // Module field old value
            [
                'template' => 'Foo {::Accounts::name::old::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_old' => [
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'value_type' => 'old',
                            'original' => '{::Accounts::name::old::}',
                        ],
                    ],
                ],
            ],
            // Target record link
            [
                'template' => 'Foo {::href_link::Accounts::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'name_href_link' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::name::}',
                        ],
                    ],
                ],
            ],
            // Related record link
            [
                'template' => '{::href_link::Accounts::campaign_accounts::name::}',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [],
                    'campaign_accounts' => [
                        'name' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::campaign_accounts::name::}',
                            'type' => 'relate',
                            'rel_module' => 'campaign_accounts',
                        ],
                    ],
                ],
            ],
            // Legacy workflow module field old value
            [
                'template' => 'Foo {::past::Accounts::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_old' => [
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'value_type' => 'old',
                            'original' => '{::past::Accounts::name::}',
                        ],
                    ],
                ],
            ],
            // Legacy workflow module field new value
            [
                'template' => 'Foo {::future::Accounts::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_future' => [
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'value_type' => 'future',
                            'original' => '{::future::Accounts::name::}',
                        ],
                    ],
                ],
            ],
            // Legacy workflow related module field old value
            [
                'template' => 'Foo {::past::Accounts::member_of::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'member_of_name_old' => [
                            'filter' => 'member_of',
                            'name' => 'name',
                            'value_type' => 'old',
                            'original' => '{::past::Accounts::member_of::name::}',
                        ],
                    ],
                ],
            ],
            // Legacy workflow related module field new value
            [
                'template' => 'Foo {::future::Accounts::member_of::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'member_of_name_future' => [
                            'filter' => 'member_of',
                            'name' => 'name',
                            'value_type' => 'future',
                            'original' => '{::future::Accounts::member_of::name::}',
                        ],
                    ],
                ],
            ],
            // Legacy workflow record link
            [
                'template' => '{::href_link::Accounts::href_link::}',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'name_href_link' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::href_link::}',
                        ],
                    ],
                ],
            ],
            // Legacy workflow related record link
            [
                'template' => '{::href_link::Accounts::campaign_accounts::href_link::}',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [],
                    'campaign_accounts' => [
                        'name' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::campaign_accounts::href_link::}',
                            'type' => 'relate',
                            'rel_module' => 'campaign_accounts',
                        ],
                    ],
                ],
            ],
            // All parsed parts in a template
            [
                'template' => $this->getFullTemplate(),
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_future' => [
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'value_type' => 'future',
                            'original' => '{::Accounts::name::}',
                        ],
                        'Accounts_name_old' => [
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'value_type' => 'old',
                            'original' => '{::Accounts::name::old::}',
                        ],
                        'campaign_accounts_name_future' => [
                            'filter' => 'campaign_accounts',
                            'name' => 'name',
                            'value_type' => 'future',
                            'original' => '{::campaign_accounts::name::}',
                        ],
                        'name_href_link' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::name::}',
                        ],
                    ],
                    'campaign_accounts' => [
                        'name' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::campaign_accounts::name::}',
                            'type' => 'relate',
                            'rel_module' => 'campaign_accounts',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getFullTemplate()
    {
        return <<<TMP
Hello {::Accounts::name::},

You recently updated your name from {::Accounts::name::old::}. Your {::campaign_accounts::name::} has also been updated.

Your record: {::href_link::Accounts::name::}
Your campaign: {::href_link::Accounts::campaign_accounts::name::}
TMP;
    }

    /**
     * Tests parseString
     * @param string $template String to parse.
     * @param string $base_module name of the module.
     * @param array $expect Expectations
     * @dataProvider parseStringDataProvider
     * @covers ::parseString
     */
    public function testParseString($template, $base_module, $expect)
    {
        $result = static::$bh->parseString($template, $base_module);
        $this->assertSame($expect, $result);
    }

    /**
     * Record link type checker data provider
     * @return array
     */
    public function isRecordLinkTypeDataProvider()
    {
        return [
            // Legacy workflow record link
            [
                'parts' => [
                    'href_link',
                    'Accounts',
                    'href_link',
                ],
                'expect' => true,
            ],
            // Legacy workflow related record link
            [
                'parts' => [
                    'href_link',
                    'Accounts',
                    'member_of',
                    'href_link',
                ],
                'expect' => true,
            ],
            // Advanced workflow record link
            [
                'parts' => [
                    'href_link',
                    'Accounts',
                    'name',
                ],
                'expect' => true,
            ],
            // Advanced workflow related record link
            [
                'parts' => [
                    'href_link',
                    'Accounts',
                    'member_of',
                    'name',
                ],
                'expect' => true,
            ],
            // False expectation #1
            [
                'parts' => [
                    'href_link',
                    'Accounts',
                    'type',
                ],
                'expect' => false,
            ],
            // False expectation #2
            [
                'parts' => [
                    'Accounts',
                    'member_of',
                    'href_link',
                ],
                'expect' => false,
            ],
        ];
    }

    /**
     * Tests if a parts array is a record link placeholder
     * @param array $parts The array of placeholder parts
     * @param boolean $expect Expectation
     * @dataProvider isRecordLinkTypeDataProvider
     * @covers ::isRecordLinkType
     */
    public function testIsRecordLinkType($parts, $expect)
    {
        $result = static::$bh->isRecordLinkType($parts);
        $this->assertSame($expect, $result);
    }

    public function getLastArrayTestDataProvider()
    {
        return [
            [
                'array' => ['a', 'b', 'c', 'd'],
                'key' => 3,
                'val' => 'd',
            ],
            [
                'array' => ['a', 'b', 'c'],
                'key' => 2,
                'val' => 'c',
            ],
            [
                'array' => ['A', 'B', 'C', 'D', 'E', 'F'],
                'key' => 5,
                'val' => 'F',
            ],
            [
                'array' => ['A'],
                'key' => 0,
                'val' => 'A',
            ],
        ];
    }

    /**
     * Tests the methods that get the last key and last value of an array
     * @param array $array Array of values to test
     * @param int $key Expected key
     * @param string $val Expected value
     * @dataProvider getLastArrayTestDataProvider
     * @covers ::getLastArrayKey
     * @covers ::getLastArrayValue
     */
    public function testGetLastArrayMethods($array, $key, $val)
    {
        $k = static::$bh->getLastArrayKey($array);
        $this->assertSame($key, $k);

        $v = static::$bh->getLastArrayValue($array);
        $this->assertSame($val, $v);
    }
}
