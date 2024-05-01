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
 * @ticket 41557
 */
class Bug41557Test extends TestCase
{
    public function providerGetPrimaryAddress()
    {
        return [
            ['old1@test.com', 'new1@test.com', false, 2],
            ['old2@test.com', 'new2@test.com', true, 1],
        ];
    }

    /**
     * @group bug41557
     * @dataProvider providerGetPrimaryAddress
     */
    public function testGetPrimaryAddress($oldemail, $newemail, $conversion, $primary_count)
    {
        if ($conversion) {
            $_REQUEST['action'] = 'ConvertLead';
        }

        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, ['email' => $oldemail]);

        $this->assertEquals($oldemail, $user->emailAddress->getPrimaryAddress($user), 'Primary email should be ' . $oldemail);

        // second email
        $user->emailAddress->addAddress($newemail, true, false);

        // simulate lead conversion mode
        if ($conversion) {
            $_REQUEST['action'] = 'ConvertLead';
        }
        $user->emailAddress->save($user->id, $user->module_dir);

        $query = "select count(*) as cnt from email_addr_bean_rel eabr WHERE eabr.bean_id = '{$user->id}' AND eabr.bean_module = 'Users' and primary_address = 1 and eabr.deleted=0";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($primary_count, $count['cnt'], 'Incorrect primary email count');

        // cleanup
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}
