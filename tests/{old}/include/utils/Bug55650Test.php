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
 * Check if getTrackerSubstring() utils function returns a html decoded value
 * which is also chopped to the tracker_max_display_length parameter
 *
 * @ticket 55650
 * @author avucinic@sugarcrm.com
 */
class Bug55650Test extends TestCase
{
    /**
     * @dataProvider providerTestGetTrackerSubstring
     */
    public function testGetTrackerSubstring($value, $expected)
    {
        // Setup some helper values
        $add = '';
        $cut = $GLOBALS['sugar_config']['tracker_max_display_length'];
        // If the length is longer then the set tracker_max_display_length, the substring length for asserting equal will be
        // -3 the length of the tracker_max_display_length, and we should add ...
        if (strlen($expected) > $GLOBALS['sugar_config']['tracker_max_display_length']) {
            $add = '...';
            $cut = $cut - 3;
        }

        // Test if the values got converted, and if the original string was chopped to the expected string
        $this->assertEquals(getTrackerSubstring($value), substr($expected, 0, $cut) . $add, '');
    }

    public static function providerTestGetTrackerSubstring()
    {
        return [
            0 => ['A lot of quotes &#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;', "A lot of quotes '''''''''''''''"],
            1 => ["A lot of quotes <>'\" &#34; &#62; &#60; &#8364; &euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;", "A lot of quotes <>'\" \" > < € €€€€€€€€"],
            2 => ['A lot of quotes &amp;', 'A lot of quotes &'],
        ];
    }
}
