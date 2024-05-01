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
 * @ticket 4236
 */
class Bug4236Test extends TestCase
{
    protected function setUp(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testFirstDayOfWeek()
    {
        global $timedate, $current_user;

        // No FDOW selected (0 is the default). I expect Calendar Month View to render starting on Sunday

        $fdow = $current_user->get_first_day_of_week();
        // Expect that the first day in slices_arr is Sunday
        $this->assertEquals($fdow, 0);

        // Set 0 (Sunday) as FDOW. I expect Calendar Month View to render starting on Sunday
        $current_user->setPreference('fdow', 0, 0, 'global');
        $fdow = $current_user->get_first_day_of_week();
        // Expect that the first day in slices_arr is Sunday
        $this->assertEquals($fdow, 0);

        // Set 1 (Monday) as FDOW. I expect Calendar Month View to render starting on Monday
        $current_user->setPreference('fdow', 1, 0, 'global');
        $fdow = $current_user->get_first_day_of_week();
        $this->assertEquals($fdow, 1);
    }
}
