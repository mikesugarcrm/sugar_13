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
 * @coversDefaultClass \InboundEmailUtils
 */
class InboundEmailUtilsTest extends TestCase
{
    /**
     * @covers ::updateInlineImageHtml
     * @dataProvider updateInlineImageHtmlProvider
     * @param string $html the test HTML string
     * @param array $inlineImages the test mappings of {Old cid => New cid}
     * @param string $expected the expected result HTML
     */
    public function testUpdateInlineImageHtml($html, $inlineImages, $expected)
    {
        $result = InboundEmailUtils::updateInlineImageHtml($html, $inlineImages);
        $this->assertEquals($expected, $result);
    }

    public function updateInlineImageHtmlProvider()
    {
        return [
            [
                '<img class="fakeClass" width="50" src="cid:fakeOldCID" alt="fakeAlt">',
                [
                    'fakeOldCID' => 'fakeNewCID',
                ],
                '<img width="50" class="image" src="cid:fakeNewCID" alt="fakeAlt">',
            ],
            [
                '<img src="cid:fakeOldCID" width="50" class="fakeClass" alt="fakeAlt">',
                [
                    'fakeOldCID' => 'fakeNewCID',
                ],
                '<img class="image" src="cid:fakeNewCID" width="50" alt="fakeAlt">',
            ],
            [
                '<img src="cid:fakeOldCID" alt="fakeAlt">',
                [
                    'fakeOldCID' => 'fakeNewCID',
                ],
                '<img class="image" src="cid:fakeNewCID" alt="fakeAlt">',
            ],
            [
                '<img class="fakeClass" width="50" src="cid:fakeOldCID" alt="fakeAlt">' .
                '<img class="fakeClass" width="50" src="cid:fakeOldCID" alt="fakeAlt">',
                [
                    'fakeOldCID' => 'fakeNewCID',
                ],
                '<img width="50" class="image" src="cid:fakeNewCID" alt="fakeAlt">' .
                '<img width="50" class="image" src="cid:fakeNewCID" alt="fakeAlt">',
            ],
        ];
    }
}
