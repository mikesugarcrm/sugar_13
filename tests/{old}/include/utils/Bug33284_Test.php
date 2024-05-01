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

class Bug33284_Test extends TestCase
{
    public $max_display_set = false;
    public $max_display_length;

    protected function setUp(): void
    {
        $sugar_config = [];
        if (isset($sugar_config['tracker_max_display_length'])) {
            $this->max_display_set = true;
            $this->max_display_length = $sugar_config['tracker_max_display_length'];
        }
    }

    protected function tearDown(): void
    {
        if ($this->max_display_set) {
            global $sugar_config;
            $sugar_config['tracker_max_display_length'] = $this->max_display_length;
        }
    }

    public function test_get_tracker_substring1()
    {
        global $sugar_config;

        $default_length = 30;

        $sugar_config['tracker_max_display_length'] = $default_length;

        $test_string = 'The quick brown fox jumps over lazy dogs';
        $display_string = getTrackerSubstring($test_string);
        $this->assertEquals(strlen(from_html($display_string)), $default_length, 'Assert that the string length is equal to ' . $default_length . ' characters');
    }


    public function test_get_tracker_substring2()
    {
        global $sugar_config;
        $test_string = '"Hello There How Are You? " This has quotes too';

        $default_length = 30;

        $sugar_config['tracker_max_display_length'] = $default_length;

        $display_string = getTrackerSubstring($test_string);
        $this->assertEquals(strlen(from_html($display_string)), $default_length, 'Assert that the string length is equal to ' . $default_length . ' characters (default)');

        $test_string = '早前於美國完成民族音樂學博士學位回港後在大專院校的音樂系任教123456789';
        $display_string = getTrackerSubstring($test_string);

        $this->assertEquals(mb_strlen(from_html($display_string), 'UTF-8'), $default_length, 'Assert that the string length is equal to ' . $default_length . ' characters (default)');
    }
}
