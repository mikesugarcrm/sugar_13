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
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper
 */
class TermParserHelperTest extends TestCase
{
    /**
     * @covers ::isOperator
     * @dataProvider providerIsOperatorTest
     */
    public function testIsOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isOperator($operaor), $expectecd);
    }

    public function providerIsOperatorTest()
    {
        return [
            ['AND', true],
            ['OR', true],
            ['NOT', true],
            ['|', true],
            ['&', true],
            ['-', true],
            ['', false],
            ['notor', false],
            ['and', false],
            ['or', false],
            ['not', false],
            ['And', false],
            ['Or', false],
            ['Not', false],
            [['a'], false],
        ];
    }

    /**
     * @covers ::isAndOperator
     * @dataProvider providerIsAndOperatorTest
     */
    public function testIsAndOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isAndOperator($operaor), $expectecd);
    }

    public function providerIsAndOperatorTest()
    {
        return [
            ['AND', true],
            ['OR', false],
            ['NOT', false],
            ['|', false],
            ['&', true],
            ['-', false],
            ['', false],
        ];
    }

    /**
     * @covers ::isOrOperator
     * @dataProvider providerIsOrOperatorTest
     */
    public function testIsOrOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isOrOperator($operaor), $expectecd);
    }

    public function providerIsOrOperatorTest()
    {
        return [
            ['AND', false],
            ['OR', true],
            ['NOT', false],
            ['|', true],
            ['&', false],
            ['-', false],
            ['', false],
        ];
    }

    /**
     * @covers ::isNotOperator
     * @dataProvider providerIsNotOperatorTest
     */
    public function testIsNotOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isNotOperator($operaor), $expectecd);
    }

    public function providerIsNotOperatorTest()
    {
        return [
            ['AND', false],
            ['OR', false],
            ['NOT', true],
            ['|', false],
            ['&', false],
            ['-', true],
            ['', false],
        ];
    }

    /**
     * @covers ::getOperator
     * @dataProvider providerGetOperatorTest
     */
    public function testGetOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::getOperator($operaor), $expectecd);
    }

    public function providerGetOperatorTest()
    {
        return [
            ['AND', 'AND'],
            ['OR', 'OR'],
            ['NOT', 'NOT'],
            ['|', 'OR'],
            ['&', 'AND'],
            ['-', 'NOT'],
            // empty string
            ['', false],
            // operator is case-sensitive
            ['and', false],
            ['or', false],
            ['not', false],
        ];
    }
}
