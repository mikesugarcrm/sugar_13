<?php

use PHPUnit\Framework\TestCase;

require_once 'vendor/tcpdf/tcpdf.php';

class TCPDFTest extends TestCase
{
    public function tagsDataProvider()
    {
        return [
            ['table border="0" cellspacing="2"', 'table', false],
            ['img src="./themes/default/images/pdf_logo.jpg" alt="" /', 'img', true],
            ['img src="./themes/default/images/pdf_logo.jpg" ', 'img', true],
            ['marker style="font-size:0"/', 'marker', true],
        ];
    }

    /**
     * @dataProvider tagsDataProvider
     * @param string $element
     * @param string $tagname
     * @param bool $expected
     * @return void
     */
    public function testIsSelfClosingTag(string $element, string $tagname, bool $expected)
    {
        $result = TCPDF::isSelfClosingTag($element, $tagname);
        $this->assertEquals($expected, $result);
    }
}
