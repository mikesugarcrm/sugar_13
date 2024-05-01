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

class TrackerMonitorTest extends TestCase
{
    protected function setUp(): void
    {
        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unsetMonitors();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['app_strings']);
    }

    public function testValidMonitors()
    {
        $trackerManager = TrackerManager::getInstance();
        $exceptionThrown = false;
        try {
            $monitor = $trackerManager->getMonitor('tracker');
            $monitor2 = $trackerManager->getMonitor('tracker_queries');
            $monitor3 = $trackerManager->getMonitor('tracker_perf');
            $monitor4 = $trackerManager->getMonitor('tracker_sessions');
            $monitor5 = $trackerManager->getMonitor('tracker_tracker_queries');
        } catch (Exception $ex) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    public function testInvalidMonitors()
    {
        $trackerManager = TrackerManager::getInstance();
        $exceptionThrown = false;
        $monitor = $trackerManager->getMonitor('invalid_tracker');
        $this->assertTrue(get_class($monitor) == 'BlankMonitor');
    }

    public function testInvalidValue()
    {
        $trackerManager = TrackerManager::getInstance();
        $monitor = $trackerManager->getMonitor('tracker');
        $exceptionThrown = false;
        try {
            $monitor->setValue('invalid_column', 'foo');
        } catch (Exception $exception) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }
}
