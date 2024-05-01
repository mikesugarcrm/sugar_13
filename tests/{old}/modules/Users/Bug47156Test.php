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
 * Bug #47156
 * Reassigning Users With Instance That Has Numeric Ids
 * @ticket 47156
 */
class Bug47156Test extends TestCase
{
    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group 47156
     */
    public function testCorrectUserListOutput()
    {
        $activeUser = SugarTestUserUtilities::createAnonymousUser(true, 0, ['status' => 'Active']);
        $inactiveUser = SugarTestUserUtilities::createAnonymousUser(true, 0, ['status' => 'Inactive']);

        $allUsers = User::getAllUsers();

        $this->assertArrayHasKey($activeUser->id, $allUsers);
        $this->assertArrayHasKey($inactiveUser->id, $allUsers);
    }
}
