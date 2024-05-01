<?php
/*
 * Your installation OR use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do NOT agree to all of the applicable terms OR do NOT have the
 * authority to bind the entity as an authorized representative, then do not
 * install OR use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query\Parser;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SimpleTermParser;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SimpleTermParser
 */
class SimpleTermParserTest extends TestCase
{
    /**
     * @covers ::parse
     * @covers ::preProcess
     * @covers ::compressTerms
     * @covers ::createAndNotTerms
     * @covers ::setDefaultOperator
     * @covers ::getDefaultOperator
     * @covers ::setUseShortcutOperator
     *
     * @dataProvider providerParseTest
     */
    public function testParse(string $terms, string $defaultOperator, bool $useShortcut, array $expectecd)
    {
        $parser = new SimpleTermParser();
        $parser->setDefaultOperator($defaultOperator);
        $parser->setUseShortcutOperator($useShortcut);
        if (TermParserHelper::getOperator($defaultOperator)) {
            $this->assertSame(TermParserHelper::getOperator($defaultOperator), $parser->getDefaultOperator());
        }

        $this->assertSame($expectecd, $parser->parse($terms));
    }

    public function providerParseTest()
    {
        return [
            // empty term always returns BasicTerm with 'OR'
            ['', 'OR', true, ['OR' => [' ']]],
            ['', '&', true, ['OR' => [' ']]],
            // to treat '-' as char if it is NOT leading by space
            ['a b-c d', 'OR', true, ['OR' => ['a b-c d']]],
            // leading 'NOT' operator
            ['-a b c', 'OR', true, ['OR' => [['NOT' => ['a']], 'b c']]],
            // combined cases without bracket
            ['a b OR c', 'OR', true, ['OR' => ['a b c']]],
            ['a b|c', 'OR', true, ['OR' => ['a b c']]],
            ['a b AND c', '&', true, ['AND' => ['a', 'b', 'c']]],
            ['a b&c', '&', true, ['AND' => ['a', 'b', 'c']]],
            [
                'a b NOT c',
                'OR',
                true,
                [
                    'OR' => [['AND' => [['AND' => ['b']], ['NOT' => ['c']]]], 'a'],
                ],
            ],
            [
                'a b -c',
                'OR',
                true,
                [
                    'OR' => [['AND' => [['AND' => ['b']], ['NOT' => ['c']]]], 'a'],
                ],
            ],
            [
                'a b OR c d',
                '&',
                true,
                ['OR' => [['AND' => ['a', 'b']], ['AND' => ['c', 'd']]]],
            ],
            [
                'a AND b OR c & d',
                '|',
                true,
                ['OR' => [['AND' => ['a', 'b']], ['AND' => ['c', 'd']]]],
            ],
            [
                'a b NOT c',
                'AND',
                true,
                ['AND' => [['AND' => ['a', 'b']], ['NOT' => ['c']]]],
            ],
            // expression with brackets
            ['(a b OR c)', 'OR', true, ['OR' => ['a b c']]],
            [
                'a (b OR c) AND d',
                'OR',
                true,
                ['OR' => [['AND' => [['OR' => ['b c']], 'd']], 'a']],
            ],
            [
                'a (b OR c) AND d',
                '&',
                true,
                ['AND' => ['a', ['OR' => ['b c']], 'd']],
            ],
            [
                '(a OR b) AND (c OR d)',
                'OR',
                true,
                ['AND' => [['OR' => ['a b']], ['OR' => ['c d']]]],
            ],
            // nested structure
            [
                'a OR (b AND (c OR d))',
                'OR',
                true,
                ['OR' => [['AND' => ['b', ['OR' => ['c d']]]], 'a']],
            ],
            // Not terms as a group
            [
                'a -(b c)',
                'OR',
                true,
                [
                    'AND' => [
                        ['AND' => ['a']],
                        ['NOT' => [['OR' => ['b c']]]],
                    ],
                ],
            ],
            // unbalanced braces, make a best guess
            [
                'a OR (b AND (c OR d)',
                'OR',
                true,
                [
                    'OR' => [
                        ['OR' => ['a']],
                        ['AND' => ['b', ['OR' => ['c d']]]],
                    ],
                ],
            ],
            // wrong parentheses
            ['a (b', 'OR', true, ['OR' => [['OR' => ['a']], 'b']]],
            ['a b) c', 'OR', true, ['OR' => [['OR' => ['a b']], 'c']]],
            ['a b &) c', 'OR', true, ['OR' => [['OR' => ['a b']], 'c']]],
            ['Johnson &) johnson', 'AND', true, ['AND' => [['OR' => ['Johnson']], 'johnson']]],
            ['Johnson & (johnson', 'AND', true, ['AND' => [['OR' => ['Johnson']], 'johnson']]],
            // end with operators
            ['a b c & AND OR | ', 'OR', true, ['OR' => ['a b c']]],
            // duplicated operators
            ['a b OR AND OR c &', 'OR', true, ['OR' => ['a b c']]],
            // phone number
            ['(128) 123-7944 text', 'OR', true, ['OR' => [['OR' => ['128']], '123-7944 text']]],
            ['(128) 123-7944 text', 'AND', true, ['AND' => [['OR' => ['128']], '123-7944', 'text']]],
            // using default operator
            ['(128) 123-7944 text', 'nt', true, ['AND' => [['OR' => ['128']], '123-7944', 'text']]],
            [
                'gmail.com (-kate OR -smith OR dean)',
                '&',
                true,
                [
                    'AND' => [
                        'gmail.com',
                        [
                            'OR' => [
                                ['NOT' => ['kate']],
                                ['NOT' => ['smith']],
                                'dean',
                            ],
                        ],
                    ],
                ],
            ],
            // test lower case 'and', 'or' and 'or', which are not operators
            ['a and b or c not d', 'OR', true, ['OR' => ['a and b or c not d']]],
            ['a Or AND and', '&', true, ['AND' => ['a', 'Or', 'and']]],
            // terms with spaces
            ['a   b   AND    and', '&', true, ['AND' => ['a', 'b', 'and']]],
            ['        ', '&', true, ['OR' => [' ']]],
            ['   AND     ', '&', true, ['OR' => [' ']]],
            // large terms, 'AND'
            [
                '111111111111111222222222222222333333333 AND 4444444444444445555555555555551',
                'AND',
                true,
                [
                    'AND' => [
                        [
                            'AND' => [
                                '111111111111111',
                                '222222222222222',
                                '333333333',
                            ],
                        ],
                        [
                            'AND' => [
                                '444444444444444',
                                '555555555555555',
                                '1',
                            ],
                        ],
                    ],
                ],
            ],
            // large terms, 'OR'
            [
                '111111111111111222222222222222333333333 444',
                'OR',
                true,
                [
                    'OR' => [
                        [
                            'AND' => [
                                '111111111111111',
                                '222222222222222',
                                '333333333',
                            ],
                        ],
                        '444',
                    ],
                ],
            ],
            [
                '111111111111111222222222222222333333333 NOT 444',
                'NOT',
                true,
                [
                    'AND' => [
                        [
                            'AND' => [
                                '111111111111111',
                                '222222222222222',
                                '333333333',
                            ],
                        ],
                        [
                            'NOT' => ['444'],
                        ],
                    ],
                ],
            ],

            // don't use shortcut
            // empty term always returns BasicTerm with 'OR'
            ['', 'OR', false, ['OR' => [' ']]],
            ['', '&', false, ['OR' => [' ']]],
            // to treat '-' as char if it is NOT leading by space
            ['a b-c d', 'OR', false, ['OR' => ['a b-c d']]],
            // leading 'NOT' operator
            ['-a b c', 'OR', false, ['OR' => ['-a b c']]],
            // combined cases without bracket
            ['a b OR c', 'OR', false, ['OR' => ['a b c']]],
            ['a b|c', 'OR', false, ['OR' => ['a b|c']]],
            ['a b AND c', '&', false, ['AND' => ['a', 'b', 'c']]],
            ['a b&c', '&', false, ['AND' => ['a', 'b&c']]],
            [
                'a b NOT c',
                'OR',
                false,
                [
                    'OR' => [['AND' => [['AND' => ['b']], ['NOT' => ['c']]]], 'a'],
                ],
            ],
            [
                'a b -c',
                'OR',
                false,
                [
                    'OR' => ['a b -c'],
                ],
            ],
            [
                'a b OR c d',
                '&',
                false,
                ['OR' => [['AND' => ['a', 'b']], ['AND' => ['c', 'd']]]],
            ],
            // special chars
            ['a !@#$ d', 'OR', true, ['OR' => ['a d']]],
            ['a !@#$ d', 'AND', true, ['AND' => ['a', 'd']]],
            ['a !@#$ d', 'OR', false, ['OR' => ['a d']]],
            ['a !@#$ d', 'AND', false, ['AND' => ['a', 'd']]],
            ["a ' d", 'AND', true, ['AND' => ['a', 'd']]],
        ];
    }
}
