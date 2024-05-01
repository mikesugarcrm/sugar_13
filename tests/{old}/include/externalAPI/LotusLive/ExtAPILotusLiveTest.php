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
 * ExtAPILotusLiveTest.php
 *
 * This test is for the ExtAPILotusLive.php class and the related functionality towards the Lotus Live web service
 *
 * @author Collin Lee
 */
class ExtAPILotusLiveTest extends TestCase
{
    protected function setUp(): void
    {
        global $app_strings;
        $app_strings = return_application_language('en_us');
    }

    /**
     * testUploadDocConflictErrorMessage
     *
     * This method tests that we get a unique error message for duplicate upload document conflicts
     */
    public function testUploadDocConflictErrorMessage()
    {
        $externalAPILotusLiveMock = new ExtAPILotusLiveMock();
        $msg = $externalAPILotusLiveMock->getErrorStringFromCode('Conflict');
        $this->assertEquals('A file with the same name already exists in the system.', $msg);

        $msg = $externalAPILotusLiveMock->getErrorStringFromCode();
        $this->assertEquals('An error occurred when trying to save to the external account.', $msg);

        $msg = $externalAPILotusLiveMock->getErrorStringFromCode([]);
        $this->assertEquals('An error occurred when trying to save to the external account.', $msg);
    }

    /**
     *  This method tests if a cache file was included without issues
     * @covers ExtAPILotusLive::loadDocCache
     */
    public function testLotusLoadDocCache()
    {
        $current_user = $GLOBALS['current_user'];

        $GLOBALS['current_user'] = $this->createMock('User');
        $GLOBALS['current_user']->id = 'LOTUS_TEST';

        $testDir = 'cache/include/externalAPI/';
        $testFile = $testDir . 'docCache_LOTUS_TEST_LotusLiveDirect.php';
        $testData = '<?php
            $docCache = array(
                "loadTime" => ' . time() . ',
                "results" => "LotusLiveTest",
            );';

        sugar_mkdir($testDir, null, true);
        file_put_contents($testFile, $testData);

        $externalAPILotusLiveMock = new ExtAPILotusLiveMock();
        $actual = $externalAPILotusLiveMock->loadDocCache();

        $this->assertEquals('LotusLiveTest', $actual);

        $GLOBALS['current_user'] = $current_user;
        unlink($testFile);
        rmdir($testDir);
    }
}
