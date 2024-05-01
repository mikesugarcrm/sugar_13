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
 * Bug49982Test.php
 * This test tests that the error message is returned after an upload that exceeds post_max_size
 *
 * @ticket 49982
 */
class Bug49982Test extends TestCase
{
    public $doc = null;
    public $contract = null;

    protected function setUp(): void
    {
        $_POST = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = null;
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        $_POST = [];
    }

    /**
     * testUploadSizeError
     * We want to simulate uploading a file that is bigger than the post max size. However the $_FILES global array cannot be overwritten
     * without triggering php errors so we can't trigger the error codes directly.
     * In the scenario we are trying to simulate, the post AND files array are returned empty by php, so let's simulate that
     * in order to test the error message from home page
     */
    public function testSaveUploadError()
    {
        //first lets test that no errors show up under normal conditions, clear out Post array just in case there is stale info
        $sv = new SugarView();
        $this->assertFalse($sv->checkPostMaxSizeError(), 'Sugar view indicated an upload error when there should be none.');

        //now lets simulate that we are coming from a post, which along with the empty file and post array should trigger the error message
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue($sv->checkPostMaxSizeError(), 'Sugar view list did not return an error, however conditions dictate that an upload with a file exceeding post_max_size has occurred.');
    }
}
