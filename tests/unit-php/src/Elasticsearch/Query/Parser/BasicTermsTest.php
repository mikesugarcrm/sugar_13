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
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\QueryBuilderException;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\BasicTerms;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\BasicTerms
 */
class BasicTermsTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getTerms
     * @covers ::getOperator
     * @covers ::addTerm
     * @covers ::toArray
     * @covers ::hasTerm
     *
     * @dataProvider providerBasicTermsTest
     */
    public function testBasicTerms($operaor, array $terms, $additionalTerm, $expectecd)
    {
        $term = new BasicTerms($operaor, $terms);

        $this->assertSame(TermParserHelper::getOperator($operaor), $term->getOperator());
        $this->assertSame($terms, $term->getTerms());

        $term->addTerm($additionalTerm);
        $this->assertEquals($expectecd, $term->toArray());
    }

    public function providerBasicTermsTest()
    {
        return [
            // add no new term
            [
                'AND',
                ['James', 'Bond'],
                '',
                ['AND' => ['James', 'Bond']],
            ],
            // normal case, 'AND'
            [
                'AND',
                ['James', 'Bond'],
                'Movie',
                ['AND' => ['James', 'Bond', 'Movie']],
            ],
            // normal case, 'NOT'
            [
                'NOT',
                ['James', 'Bond'],
                'Movie',
                ['NOT' => ['James', 'Bond', 'Movie']],
            ],
            // normal case, 'OR'
            [
                'OR',
                ['James', 'Bond'],
                'Movie',
                ['OR' => ['James Bond Movie']],
            ],
            // 'AND', symbol operator
            [
                '&',
                ['James', 'Bond'],
                'Movie',
                ['AND' => ['James', 'Bond', 'Movie']],
            ],
            // 'NOT', symbol operator
            [
                '-',
                ['James', 'Bond'],
                'Movie',
                ['NOT' => ['James', 'Bond', 'Movie']],
            ],
            // symbol operator, 'OR' operator
            [
                '|',
                ['James', 'Bond'],
                'Movie',
                ['OR' => ['James Bond Movie']],
            ],
            // 'OR' operator, combine terms into a string
            [
                'OR',
                ['James', 'Bond'],
                'Movie',
                ['OR' => ['James Bond Movie']],
            ],
            // nexted terms operator, combine terms into a string
            [
                'OR',
                ['James', 'Bond'],
                new BasicTerms('AND', ['M6', '007']),
                ['OR' => [['AND' => ['M6', '007']], 'James Bond']],
            ],
            // NOT operator
            [
                'NOT',
                ['James', 'Bond'],
                new BasicTerms('AND', ['M6', '007']),
                ['NOT' => ['James', 'Bond', ['AND' => ['M6', '007']]]],
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @dataProvider providerBasicTermsTestException
     */
    public function testBasicTermsException($operator)
    {
        $this->expectException(QueryBuilderException::class);
        new BasicTerms($operator, ['abc']);
    }

    public function providerBasicTermsTestException()
    {
        return [
            ['ands'],
            ['&&'],
        ];
    }
}
