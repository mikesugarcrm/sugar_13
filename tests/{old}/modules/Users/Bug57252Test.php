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
 * Bug57252Test.php
 * @author Matt Marum
 *
 * This unit test checks to make sure that we are pulling the Admin panel date/time format
 * preferences when a user does not have an existing date/time format preference.
 */
class Bug57252Test extends TestCase
{
    public $testUser;

    protected function setUp(): void
    {
        SugarTestHelper::setup('current_user');
        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
        $this->testUser->save();
    }

    protected function tearDown(): void
    {
        $this->testUser->resetPreferences();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * @group bug57252
     */
    public function testDefaultDateTimeFormatFromSystemConfig()
    {
        global $sugar_config;

        $this->assertEquals($this->testUser->getPreference('datef'), $sugar_config['default_date_format']);
        $this->assertEquals($this->testUser->getPreference('timef'), $sugar_config['default_time_format']);
    }

    /**
     * @group bug57252
     */
    public function testDefaultDateTimeFormatFromUserPref()
    {
        $this->testUser->setPreference('datef', 'd/m/Y', 0, 'global');
        $this->testUser->setPreference('timef', 'h.iA', 0, 'global');

        $this->assertEquals($this->testUser->getPreference('datef'), 'd/m/Y');
        $this->assertEquals($this->testUser->getPreference('timef'), 'h.iA');
    }
}
