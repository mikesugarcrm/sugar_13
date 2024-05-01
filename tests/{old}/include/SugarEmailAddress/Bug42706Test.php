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
 * @ticket 42706
 */
class Bug42706Test extends TestCase
{
    public function providerGetPrimaryAddress()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @group bug42706
     * @dataProvider providerGetPrimaryAddress
     */
    public function testGetPrimaryAddress($invalid)
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        // Update the user's primary email address to be valid or invalid, according to the data provided.
        $email = $user->emailAddress->getPrimaryAddress($user);
        $user->emailAddress->AddUpdateEmailAddress($email, $invalid);

        if ($invalid) {
            $this->assertEmpty($user->emailAddress->getPrimaryAddress($user), 'Primary email should be empty');
        } else {
            $this->assertEquals($email, $user->emailAddress->getPrimaryAddress($user), 'Primary email should be ' . $email);
        }
    }
}
