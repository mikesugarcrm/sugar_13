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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query\Parser;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SearchStringProcessor;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SearchStringProcessor
 */
class SearchStringProcessorTest extends TestCase
{
    /**
     * @covers ::parse
     * @covers ::getSubString
     *
     * @dataProvider providerParseTest
     */
    public function testParse($terms, $expectecd)
    {
        $result = SearchStringProcessor::parse($terms);
        $this->assertSame($result, $expectecd);
    }

    public function providerParseTest()
    {
        return [
            ['', []],
            ['a b or c', ['a', 'b', 'or', 'c']],
            ['(a b or c)', [['a', 'b', 'or', 'c']]],
            ['a b or c and d', ['a', 'b', 'or', 'c', 'and', 'd']],
            ['a (b or c) and d', ['a', ['b', 'or', 'c'], 'and', 'd']],
            ['(a or b) and (c or d)', [['a', 'or', 'b'], 'and', ['c', 'or', 'd']]],
            // nested structure
            ['a or (b and (c or d))', ['a', 'or', ['b', 'and', ['c', 'or', 'd']]]],
            // unbalanced braces, make a best guess
            ['a or (b and (c or d)', [['a', 'or'], 'b', 'and', ['c', 'or', 'd']]],
            ['a (b', [['a'], 'b']],
            ['a b) c', [['a', 'b'], 'c']],
            ['a b &) c', [['a', 'b', '&'], 'c']],
        ];
    }
}
