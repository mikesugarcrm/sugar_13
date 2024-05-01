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

/**
 * @covers SugarCleaner
 */
class SugarCleanerTest extends TestCase
{
    /**
     * @dataProvider cleanHtmlProvider
     */
    public function testCleanHtml($args, $expected)
    {
        $actual = SugarCleaner::cleanHtml($args[0], $args[1]);

        $this->assertEquals($expected, $actual, 'Html did not get cleaned as expected');
    }

    public static function cleanHtmlProvider()
    {
        // script tags should be removed
        yield [
            [" World &lt;script&gt;alert('Hello');&lt;/script&gt;", true],
            ' World ',
        ];

        // double encoded script tags should be removed
        yield [
            ['Hello &amp;lt;script&amp;gt;alert(&#039;Hi&#039;);&amp;lt;/script&amp;gt; World', true],
            'Hello  World',
        ];

        // Non harmful tags like bold tags should be allowed
        yield [
            ['&lt;b&gt;Hello&lt;/b&gt;', true],
            '&lt;b&gt;Hello&lt;/b&gt;',
        ];

        // Normal text without html should pass through unchanged
        yield [
            ['Hello', true],
            'Hello',
        ];

        // Try one test with false i.e., Not entity encoded
        yield [
            ['<b>Hello</b>', false],
            '<b>Hello</b>',
        ];

        yield [
            [
                '<table><tbody><tr><td rowspan="4" width="210%"><img src="pdf_logo.jpg" alt="" /></td></tr></tbody></table>',
                false,
            ],
            '<table><tbody><tr><td rowspan="4" width="210%"><img src="pdf_logo.jpg" alt="" /></td></tr></tbody></table>',
        ];

        yield [
            [
                '<table><!--START_PRODUCT_LOOP--><tr><!--Comment--><td width="60%">{$product.quantity}</td></tr><!--END_PRODUCT_LOOP--></table>',
                false,
            ],
            '<table><!--START_PRODUCT_LOOP--><tr><td width="60%">{$product.quantity}</td></tr><!--END_PRODUCT_LOOP--></table>',
        ];

        // On Windows, DOMDocument handles whitespaces differently than the test expects
        if (is_windows()) {
            return;
        }

        // Try area/map tags and img usemap attribute
        yield [
            [
                '<map name="my_map"><area target="_blank" href="http://www.example.org/" shape="rect" coords="8, 10, 674, 423" /><area target="_blank" href="http://stores.ebay.com/MyStore/" shape="rect" coords="11, 464, 157, 746" /><area target="_blank" href="http://stores.ebay.com/MyOtherStore" shape="rect" coords="175, 459, 328, 750" /><area target="_blank" href="http://stores.ebay.com/MyThirdStore" shape="rect" coords="351, 466, 499, 748" /><area target="_blank" href="http://www.example2.org/" shape="rect" coords="521, 463, 675, 745" /><area href="test@example.org?subject=Test" shape="circle" coords="24, 790, 14" /></map><img border="0" src="http://www.example.org/Newsletter.jpg" width="676" height="838" usemap="#my_map" />',
                false,
            ],
            '<map name="my_map"><area target="_blank" href="http://www.example.org/" shape="rect" coords="8, 10, 674, 423" /><area target="_blank" href="http://stores.ebay.com/MyStore/" shape="rect" coords="11, 464, 157, 746" /><area target="_blank" href="http://stores.ebay.com/MyOtherStore" shape="rect" coords="175, 459, 328, 750" /><area target="_blank" href="http://stores.ebay.com/MyThirdStore" shape="rect" coords="351, 466, 499, 748" /><area target="_blank" href="http://www.example2.org/" shape="rect" coords="521, 463, 675, 745" /><area href="test@example.org?subject=Test" shape="circle" coords="24, 790, 14" /></map><img border="0" src="http://www.example.org/Newsletter.jpg" width="676" height="838" usemap="#my_map" alt="Newsletter.jpg" />',
        ];
    }

    /**
     * @dataProvider notScalarProvider
     * @return void
     */
    public function testNotScalar($html)
    {
        $result = SugarCleaner::cleanHtml($html);
        $this->assertSame($html, $result);
    }

    public static function notScalarProvider(): Generator
    {
        yield [
            new stdClass(),
        ];
        yield [
            ['foo' => 'bar',],
        ];
        yield [
            null,
        ];
    }
}
