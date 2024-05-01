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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\ResultParser;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\ResultParser
 */
class ResultParserTest extends TestCase
{
    /**
     * @covers ::parseSource
     * @covers ::addSourceRemap
     * @covers ::normalizeFieldName
     * @dataProvider providerTestParseSource
     */
    public function testParseSource(\Elastica\Result $result, array $remap, array $expected)
    {
        $rp = new ResultParser(new Highlighter());
        $rp->addSourceRemap($remap);
        $this->assertSame($expected, $rp->parseSource($result));
    }

    public function providerTestParseSource()
    {
        $hit1 = [
            '_source' => [
                'name' => 'foo',
                'desc' => 'bar',
                'SomeModule__email' => ['stuff'],
            ],
        ];

        return [
            // no source data
            [
                new \Elastica\Result([]),
                [],
                [],
            ],
            // normalize, no remap
            [
                new \Elastica\Result($hit1),
                [],
                [
                    'name' => 'foo',
                    'desc' => 'bar',
                    'email' => ['stuff'],
                ],
            ],
            // normalize, with remap
            [
                new \Elastica\Result($hit1),
                ['email' => 'remapped'],
                [
                    'name' => 'foo',
                    'desc' => 'bar',
                    'remapped' => ['stuff'],
                ],
            ],
        ];
    }

    /**
     * @covers ::parseHighlights
     * @covers ::addHighlightRemap
     * @covers ::normalizeFieldName
     * @covers ::getSubFieldName
     * @covers ::getPreTag
     * @covers ::getPostTag
     * @dataProvider providerTestParseHighlights
     */
    public function testParseHighlights(\Elastica\Result $result, array $remap, array $expected)
    {
        $rp = new ResultParser(new Highlighter());
        $rp->addHighlightRemap($remap);
        $this->assertSame($expected, $rp->parseHighlights($result));
    }

    public function providerTestParseHighlights()
    {
        $hit1 = [
            'highlight' => [
                'Contacts__first_name.gs_string' => [
                    'aaa <strong>bbb</strong>',
                ],
                'Contacts__last_name.gs_string' => [
                    '<strong>aaa</strong> bbb',
                ],
                'Contacts__last_name.gs_string_wildcard' => [
                    'aaa <strong>bbb</strong>',
                ],
                'Contacts__email_search.primary.gs_email_wildcard' => [
                    'xxx <strong>yyy</strong>',
                ],
                'Contacts__email_search.primary.gs_email' => [
                    'xxx <strong>yyy</strong>',
                ],
                'Contacts__email_search.secondary.gs_email_wildcard' => [
                    'xxx',
                ],
            ],
        ];
        return [
            // no highligts present
            [
                new \Elastica\Result([]),
                [],
                [],
            ],
            // highligts present with primary field, sub fields and duplicates, no remap
            [
                new \Elastica\Result($hit1),
                [],
                [
                    'first_name' => [
                        'aaa <strong>bbb</strong>',
                    ],
                    'last_name' => [
                        '<strong>aaa</strong> bbb',
                    ],
                    'email_search' => [
                        'primary' => [
                            'xxx <strong>yyy</strong>',
                        ],
                        'secondary' => [
                            '<strong>xxx</strong>',
                        ],
                    ],
                ],
            ],
            // highligts present with primary field, sub fields and duplicates, with remap
            [
                new \Elastica\Result($hit1),
                ['email_search' => 'email'],
                [
                    'first_name' => [
                        'aaa <strong>bbb</strong>',
                    ],
                    'last_name' => [
                        '<strong>aaa</strong> bbb',
                    ],
                    'email' => [
                        'primary' => [
                            'xxx <strong>yyy</strong>',
                        ],
                        'secondary' => [
                            '<strong>xxx</strong>',
                        ],
                    ],
                ],
            ],
        ];
    }
}
