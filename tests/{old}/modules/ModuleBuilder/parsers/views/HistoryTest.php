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

class HistoryTest extends TestCase
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var History
     */
    private $history;

    protected function setUp(): void
    {
        $this->path = sugar_cached('/history/' . time());
        sugar_mkdir($this->getHistoryDir());
        $this->history = new History($this->path);
    }

    protected function tearDown(): void
    {
        // Clean all temporary files created
        $files = glob($this->getHistoryDir() . '/*');
        foreach ($files as $file) {
            @unlink($file);
        }
        @rmdir($this->getHistoryDir());
    }

    public function testConstructor()
    {
        $this->assertTrue(is_dir($this->getHistoryDir()), 'History dir not created');
    }

    /**
     * Append a file to the history, check if it's properly added, restore it, and check if it's there
     */
    public function testAppendRestoreUndo()
    {
        $tempFile = tempnam($this->getHistoryDir(), 'history');

        $time = $this->history->append($tempFile);
        $this->assertTrue(file_exists($this->history->getFileByTimestamp($time)), "Didn't create history file");
        $this->assertEquals($this->history->restoreByTimestamp($time), $time, 'Restore returns incorrect timestamp');

        $this->assertTrue(file_exists($this->path), 'Preview file not created');
        $this->assertFileEquals($tempFile, $this->path, 'Restored file incorrect');

        $this->history->undoRestore();
        $this->assertFalse(file_exists($this->path), 'Preview file not removed');
    }

    /**
     * Add several files to history, test getter functions for the history list
     */
    public function testPositioning()
    {
        // Pause for a second in between each append for different timestamps
        $el1 = $this->history->append(tempnam($this->getHistoryDir(), 'history'));
        $el2 = $this->history->append(tempnam($this->getHistoryDir(), 'history'));
        $el3 = $this->history->append(tempnam($this->getHistoryDir(), 'history'));

        // Grab our values for testing
        $getFirst = $this->history->getFirst();
        $getLast = $this->history->getLast();
        $getNth1 = $this->history->getNth(1);
        $getNext = $this->history->getNext();

        // Assertions
        $this->assertEquals($el3, $getFirst, "$el3 was not the timestamp returned by getFirst() [$getFirst]");
        $this->assertEquals($el1, $getLast, "$el1 was not the timestamp returned by getLast() [$getLast]");
        $this->assertEquals($el2, $getNth1, "$el2 was not the timestamp returned by getNth(1) [$getNth1]");
        $this->assertEquals($el1, $getNext, "$el1 was not the timestamp returned by getNext() [$getNext]");

        // Last assertion
        $getNext = $this->history->getNext();
        $this->assertFalse($getNext, "Expected getNext() [$getNext] to return false");
    }

    private function getHistoryDir()
    {
        return dirname($this->path);
    }
}
