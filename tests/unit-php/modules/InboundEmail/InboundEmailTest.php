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

namespace Sugarcrm\SugarcrmTestsUnit\modules\InboundEmail;

use PHPUnit\Framework\TestCase;
use SugarNullLogger;

/**
 * @coversDefaultClass \InboundEmail
 */
class InboundEmailTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['log'] = new SugarNullLogger();

        $GLOBALS['locale'] = \Localization::getObject();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
        unset($GLOBALS['locale']);
    }

    public function decodeHeaderProvider()
    {
        return [
            [
                'Content-Type: text/html; charset="utf-8"',
                [
                    'Content-Type' => [
                        'type' => 'text/html',
                        'charset' => 'utf-8',
                    ],
                ],
            ],
            [
                'Content-Type: text/html; charset=utf-8',
                [
                    'Content-Type' => [
                        'type' => 'text/html',
                        'charset' => 'utf-8',
                    ],
                ],
            ],
            [
                'Content-Type: text/html; charset=    utf-8',
                [
                    'Content-Type' => [
                        'type' => 'text/html',
                        'charset' => 'utf-8',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider decodeHeaderProvider
     * @covers ::decodeHeader
     */
    public function testDecodeHeader($header, $expected)
    {
        $ie = $this->createPartialMock('\\InboundEmail', []);
        $actual = $ie->decodeHeader($header);

        $this->assertEquals($expected, $actual);
    }

    public function convertToUtf8Provider()
    {
        return [
            // Commenting out windows-1256, since PHP doesn't have an easy way to detect this encoding.
//            'windows-1256' => [
//                '7cvU3iDI5d7L7O3TIOUg1cfU7N0g3c4g5ezR1NPd5eHU3csg287a',
//                'يثشق بهقثىيس ه صاشىف فخ هىرشسفهلشفث غخع',
//            ],
//            'another windows-1256' => [
//                '7cjT7cjU0+3IwcbExNE=',
//                'يبسيبشسيبءئؤؤر',
//            ],
            'ISO-2022-JP (params related to 45059 ticket)' => [
                'GyRCJWYhPCU2TD4bKEI=',
                'ユーザ名',
            ],
            'utf-8' => [
                '5LiN6KaB55u06KeG6ZmM55Sf5Lq655qE55y8552b',
                '不要直视陌生人的眼睛',
            ],
        ];
    }

    /**
     * SI Bug 45059
     *
     * @dataProvider convertToUtf8Provider
     * @covers ::convertToUtf8
     *
     * @requires extension mbstring
     */
    public function testConvertToUtf8($inputText, $expected)
    {
        $ie = $this->createPartialMock('\\InboundEmail', []);
        $inputText = base64_decode($inputText);
        $actual = $ie->convertToUtf8($inputText);

        $this->assertSame($expected, $actual, 'We should be able to convert to UTF-8');
    }

    public function handleEncodedFilenameProvider()
    {
        return [
            'name is encoded using RFC2047' => [
                '=?utf-8?B?QmVzdGlsbGluZ3Nza2plbWEgLSBNb2JpbGFib25uZW1lbnQgLSBFbGtqw7hw?= ' .
                '=?utf-8?B?w7bDtmzDpGzDpGxwbMOkcHDDpGzDpHBsLnhsc3g=?=',
                'Bestillingsskjema - Mobilabonnement - Elkjøpöölälälpläppäläpl.xlsx',
            ],
            "name is encoded using [encoding]''[filename]" => [
                "utf-8''qwerty.docx",
                'qwerty.docx',
            ],
            'name is not encoded and falls back to utf-8' => [
                'Bestillingsskjema - Mobilabonne.xlsx',
                'Bestillingsskjema - Mobilabonne.xlsx',
            ],
            'only part of the name is encoded' => [
                '=?ISO-8859-1?Q?Keld_J=F8rn?= Simonsen',
                'Keld Jørn Simonsen',
            ],
            'name is encoded with multiple charsets' => [
                '=?ISO-8859-1?Q?Keld_J=F8rn?= Simonsen ' .
                '=?utf-8?B?QmVzdGlsbGluZ3Nza2plbWEgLSBNb2JpbGFib25uZW1lbnQgLSBFbGtqw7hw?= ' .
                '=?utf-8?B?w7bDtmzDpGzDpGxwbMOkcHDDpGzDpHBsLnhsc3g=?=',
                'Keld Jørn Simonsen Bestillingsskjema - Mobilabonnement - Elkjøpöölälälpläppäläpl.xlsx',
            ],
            // Note: The space before and after "Simonsen" is removed because of the way that [encoding]''[filename] is
            // handled.
            "name is encoded using RFC2047 and [encoding]''[filename]" => [
                "=?ISO-8859-1?Q?Keld_J=F8rn?= utf-8''Simonsen " .
                '=?utf-8?B?QmVzdGlsbGluZ3Nza2plbWEgLSBNb2JpbGFib25uZW1lbnQgLSBFbGtqw7hw?= ' .
                '=?utf-8?B?w7bDtmzDpGzDpGxwbMOkcHDDpGzDpHBsLnhsc3g=?=',
                'Keld JørnSimonsenBestillingsskjema - Mobilabonnement - Elkjøpöölälälpläppäläpl.xlsx',
            ],
        ];
    }

    /**
     * @covers ::handleEncodedFilename
     * @dataProvider handleEncodedFilenameProvider
     */
    public function testHandleEncodedFilename($encodedName, $expected)
    {
        $ie = $this->createPartialMock('\\InboundEmail', []);
        $actual = $ie->handleEncodedFilename($encodedName);

        $this->assertSame($expected, $actual);
    }
}
