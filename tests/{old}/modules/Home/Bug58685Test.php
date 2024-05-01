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
 * Bug58685Test.php
 * This test tests that the error message is returned after an upload that isn't a true upload but just an empty post
 *
 * @ticket 58685
 */
class Bug58685Test extends TestCase
{
    /**
     * @var array<string, mixed>|mixed
     */
    public $oldPost;
    /**
     * @var mixed
     */
    public $oldRM;
    /**
     * @var mixed
     */
    public $oldCL;

    protected function setUp(): void
    {
        $this->oldPost = $_POST;
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->oldRM = $_SERVER['REQUEST_METHOD'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $this->oldCL = $_SERVER['CONTENT_LENGTH'];
        }
        SugarTestHelper::setUp('app_strings');
    }

    protected function tearDown(): void
    {
        $_POST = $this->oldPost;
        if (isset($this->oldRM)) {
            $_SERVER['REQUEST_METHOD'] = $this->oldRM;
        }
        if (isset($this->oldCL)) {
            $_SERVER['CONTENT_LENGTH'] = $this->oldCL;
        }
    }

    /**
     * testEmptyPostError
     */
    public function testSaveUploadErrorMessage()
    {
        //first lets test that no errors show up under normal conditions, clear out Post array just in case there is stale info
        $_POST = [];
        //now lets simulate that we are coming from a post, which along with the empty file and post array should trigger the error message
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_LENGTH'] = 10;
        $_FILES = [];
        $view = new HomeViewList();
        $view->suppressDisplayErrors = true;
        $view->processMaxPostErrors();
        $this->assertContains(
            'ERROR: There was an error during your upload, please contact an administrator for help.',
            $view->errors
        );
    }
}
