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
 * Bug #35413
 * Other character sets not displayed properly
 *
 * Bug #45059
 * Non UTF-8 Emails sent without Character Encoding are not translated properly
 */
class Bug35413Test extends TestCase
{
    private $localization;

    protected function setUp(): void
    {
        $this->localization = Localization::getObject();
    }

    public static function stringsProvider()
    {
        return [
            [
                '7cvU3iDI5d7L7O3TIOUg1cfU7N0g3c4g5ezR1NPd5eHU3csg287a',
                'يثشق بهقثىيس ه صاشىف فخ هىرشسفهلشفث غخع',
                'windows-1256',
            ],
            [
                '7cjT7cjU0+3IwcbExNE=',
                'يبسيبشسيبءئؤؤر',
                'windows-1256',
            ],
            [ // params related to 45059 ticket
                'GyRCJWYhPCU2TD4bKEI=',
                'ユーザ名',
                'ISO-2022-JP',
            ],
            [
                'RnJvbTog6eXh7CD55eTt',
                'From: יובל שוהם',
                'ISO-8859-8',
            ],
            [
                'srvSqtaxytPEsMn6yMu1xNHbvqYK',
                "不要直视陌生人的眼睛\n",
                'GB2312',
            ],
            //Not a good test case
            /*
            array ( // what happens when we post a dummy charset?
                base64_encode("12345"),
                "12345",
                " "
            )
            */
        ];
    }

    /**
     * Test convert base64 $source to string and convert string from $encoding to utf8. It has to return $utf8string.
     *
     * @dataProvider stringsProvider
     * @ticket 35413, 45059
     * @param string $source base64 encoded string in native charset
     * @param string $utf8string previous string in utf8
     * @param string $encoding encoding of native string
     */
    public function testEncodings($source, $utf8string, $encoding)
    {
        $source = base64_decode($source);
        $translateCharsetResult = $this->localization->translateCharset($source, $encoding, 'UTF-8');

        $this->assertEquals($utf8string, $translateCharsetResult, 'Strings have to be the same');
    }
}
