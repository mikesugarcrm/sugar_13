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
 * @covers ::parseShorthandBytes
 */
class ParseShorthandBytesTest extends TestCase
{
    /**
     * @dataProvider shorthandBytesProvider
     */
    public function testParseShorthandBytes($string, $expected)
    {
        $actual = parseShorthandBytes($string);
        $this->assertSame($expected, $actual);
    }

    public static function shorthandBytesProvider()
    {
        return [
            ['1048576', 1048576],
            ['100K', 102400],
            ['8m', 8388608],
            ['1G', 1073741824],
            ['20X', 20],
            ['-1', null],
            ['-1K', null],
        ];
    }
}
