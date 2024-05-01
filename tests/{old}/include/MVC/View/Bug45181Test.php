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
 * Bug 45181: Please remove "Log Memory Usage" if useless
 * @ticket 45181
 */
class Bug45181 extends TestCase
{
    private $sugar_config;
    private $sugarView;

    protected function setUp(): void
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        global $sugar_config;
        $this->sugar_config = $sugar_config;
        $this->sugarView = new Bug45181TestSugarViewMock();
        $this->sugarView->module = 'Contacts';
        $this->sugarView->action = 'EditView';
        if (is_file('memory_usage.log')) {
            unlink('memory_usage.log');
        }
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        if (is_file('memory_usage.log')) {
            unlink('memory_usage.log');
        }
        $sugar_config = $this->sugar_config;
        unset($this->sugar_config);
        unset($GLOBALS['app_strings']);
    }


    /**
     * testLogMemoryUsageOn
     * This test asserts that when log_memory_usage is set to true we receive a log message from the function
     * call and the memory_usage.log file is created.
     *
     * @outputBuffering enabled
     */
    public function testLogMemoryUsageOn()
    {
        global $sugar_config;
        $sugar_config['log_memory_usage'] = true;
        $output = $this->sugarView->logMemoryStatisticsTest("\n");
        $this->assertNotEmpty($output, 'Failed to recognize log_memory_usage = true setting');
        $this->assertFileExists('memory_usage.log', 'Unable to create memory_usage.log file');
    }

    /**
     * testLogMemoryUsageOff
     * This test asserts that when log_memory_usage is set to false we do not receive a log message from the function
     * call nor is the memory_usage.log file created.
     *
     * @outputBuffering enabled
     */
    public function testLogMemoryUsageOff()
    {
        if (!function_exists('memory_get_usage') || !function_exists('memory_get_peak_usage')) {
            $this->markTestSkipped('Skipping test since memory_get_usage and memory_get_peak_usage function are unavailable');
            return;
        }
        global $sugar_config;
        $sugar_config['log_memory_usage'] = false;
        $output = $this->sugarView->logMemoryStatisticsTest("\n");
        $this->assertEmpty($output, 'Failed to recognize log_memory_usage = false setting');
        $this->assertFileDoesNotExist('memory_usage.log');
    }
}

class Bug45181TestSugarViewMock extends SugarView
{
    public function logMemoryStatisticsTest($newline)
    {
        return $this->logMemoryStatistics($newline);
    }
}
