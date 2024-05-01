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


class RestCurrentUserPortalTest extends RestTestPortalBase
{
    /**
     * @group rest
     */
    public function testRetrieve()
    {
        $restReply = $this->restCall('me');
        $this->assertNotEmpty($restReply['reply']['current_user']['id']);
        $this->assertEquals($this->portalGuy->id, $restReply['reply']['current_user']['id']);
        $this->assertEquals($this->user->id, $restReply['reply']['current_user']['user_id']);
        $this->assertEquals('support_portal', $restReply['reply']['current_user']['type']);
    }

    /**
     * @group rest
     */
    public function testAcls()
    {
        // FIXME TY-1312: investigate why this test fails
        $allowedModules = [
            'Accounts' => ['edit' => 'no', 'create' => 'no'],
            'Bugs' => ['edit' => 'no', 'create' => 'yes'],
            'Cases' => ['edit' => 'no', 'create' => 'yes'],
            'Notes' => ['edit' => 'no', 'create' => 'yes'],
            // edit is yes because they can edit themselves
            'Contacts' => ['edit' => 'yes', 'create' => 'yes'],
        ];

        $restReply = $this->restCall('me');
        $user_acls = $restReply['reply']['current_user']['acl'];
        foreach ($allowedModules as $module => $acls) {
            foreach ($acls as $action => $access) {
                $this->assertEquals($user_acls[$module][$action], $access, "{$module} - {$action} Did not have the correct access");
            }
        }
    }

    /**
     * @group rest
     */
    public function testUpdate()
    {
        // FIXME TY-1312: investigate why this test fails
        $restReply = $this->restCall('me', json_encode(['first_name' => 'UNIT TEST - AFTER']), 'PUT');
        $this->assertNotEquals(stripos($restReply['reply']['current_user']['full_name'], 'UNIT TEST - AFTER'), false);
    }

    /**
     * @group rest
     */
    public function testPasswordUpdate()
    {
        $this->restLogin();
        // Change password twice to be sure working as expected
        $reply = $this->restCall(
            'me/password',
            json_encode(['new_password' => 'fubar', 'old_password' => 'unittest']),
            'PUT'
        );

        $this->assertEquals($reply['reply']['valid'], true, 'Part One');
        $reply = $this->restCall(
            'me/password',
            json_encode(['new_password' => 'newernew', 'old_password' => 'fubar']),
            'PUT'
        );
        $this->assertEquals($reply['reply']['valid'], true, 'Part Deux');
        // Now use an incorrect old_password .. this should return valid:false
        $reply = $this->restCall(
            'me/password',
            json_encode(['new_password' => 'hello', 'old_password' => 'nope']),
            'PUT'
        );
        $this->assertEquals($reply['reply']['valid'], false, 'Part Three - With a Vengence');
    }

    /**
     * @group rest
     */
    public function testPasswordVerification()
    {
        $reply = $this->restCall(
            'me/password',
            json_encode(['password_to_verify' => 'unittest']),
            'POST'
        );
        $this->assertEquals($reply['reply']['valid'], true);
        $reply = $this->restCall(
            'me/password',
            json_encode(['password_to_verify' => 'noway']),
            'POST'
        );
        $this->assertEquals($reply['reply']['valid'], false);
    }
}
