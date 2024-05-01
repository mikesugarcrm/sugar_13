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
 * Bug51185Test.php
 * @author Collin Lee
 *
 * This unit tests checks to ensure the value returned from handleDateFormat in SchedulersJob.php is properly returned
 * depending on the arguments passed in.  By default, the handleDateFormat call should return a database formatted date
 * time value.
 */
class Bug51185Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        //Change the datef value in user preference so that it is not the default db format
        $current_user->setPreference('datef', 'd/m/Y', 0, 'global');
        $current_user->setPreference('timef', 'H:i', 0, 'global');
        $current_user->save();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * testSchedulersJobHandleDateFormatWithNow
     */
    public function testSchedulersJobHandleDateFormatWithNow()
    {
        global $current_user;

        $job = new SchedulersJob(false);
        $job->user = $current_user;

        $this->assertMatchesRegularExpression('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat('now'));
        $this->assertMatchesRegularExpression(
            '/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/',
            $job->handleDateFormat('now', $current_user, false)
        );
        $this->assertMatchesRegularExpression(
            '/^\d{1,2}\/\d{1,2}\/\d{4}\s\d{1,2}\:\d{2}$/',
            $job->handleDateFormat('now', $current_user, true)
        );
    }

    /**
     * testSchedulersJobHandleDateFormatWithoutNow
     */
    public function testSchedulersJobHandleDateFormatWithoutNow()
    {
        global $current_user;
        $job = new SchedulersJob(false);
        $job->user = $current_user;
        $this->assertMatchesRegularExpression('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat());
    }

    /**
     * testSchedulersJobHandleDateFormatWithOtherTime
     */
    public function testSchedulersJobHandleDateFormatWithOtherTime()
    {
        global $current_user;
        $job = new SchedulersJob(false);
        $job->user = $current_user;
        $this->assertMatchesRegularExpression('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat('+7 days'));
        $this->assertMatchesRegularExpression('/^\d{4}\-\d{2}\-\d{2}\s\d{1,2}\:\d{2}\:\d{2}$/', $job->handleDateFormat('+7 days', $current_user, false));
        $this->assertMatchesRegularExpression('/^\d{1,2}\/\d{1,2}\/\d{4}\s\d{1,2}\:\d{2}$/', $job->handleDateFormat('+7 days', $current_user, true));
    }
}
