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
 * This class is meant to test everything for InboundEmail
 */
class GroupFoldersTest extends TestCase
{
    /**
     * Create test user
     */
    protected function setUp(): void
    {
        global $groupfolder_id;
        if (empty($groupfolder_id)) {
            $this->setupTestUser();
        } // IF
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Emails');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['mod_strings']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
    }

    /**
     * Create a group folder
     */
    public function testCreateGroupFolder()
    {
        global $current_user, $groupfolder_id, $teamObject;
        $sugarFolder = new SugarFolder();
        $sugarFolder->name = 'UnitTestGroupFolder';
        $sugarFolder->parent_folder = '';
        $sugarFolder->has_child = 0;
        $sugarFolder->is_group = 1;
        $sugarFolder->assign_to_id = $current_user->id;
        $teamObject = SugarTestTeamUtilities::createAnonymousTeam();
        $sugarFolder->team_id = $teamObject->id;
        $sugarFolder->team_set_id = $teamObject->id;
        $status = $sugarFolder->save();
        $groupfolder_id = $sugarFolder->id;
        $this->assertTrue($status, 'group Folder can not be created');
    } // fn

    /**
     * Create an Email
     */
    public function testCreateEmail()
    {
        global $current_user, $teamObject, $email_id;

        $data = [
            'name' => 'Unittest',
            'description' => 'Unittest',
            'description_html' => '<b>Unittest</b>',
            'from_addr_name' => 'Unittest',
            'to_addrs_names' => 'Unittestto',
            'cc_addrs_names' => 'Unittestcc',
            'bcc_addrs_names' => 'Unittestbcc',
            'reply_to_addr' => 'Unittest@ubnittest.com',
            'from_addr' => 'ajaysales@sugarcrm.com',
            'to_addrs' => 'Unittest@unittest.com',
            'cc_addrs' => 'Unittest@unittest.com',
            'bcc_addrs' => 'Unittest@unittest.com',
            'message_id' => md5('Unittest - ' . random_int(0, mt_getrandmax())),
            'team_id' => $teamObject->id,
            'team_set_id' => $teamObject->id,
            'assigned_user_id' => $current_user->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email_id = $email->id;

        $this->assertTrue(!empty($email_id), 'testAssignEmailToGroupFolder failed');
    }

    /**
     * Assign this email to group folder
     */
    public function testAssignEmailToGroupFolder()
    {
        global $current_user, $groupfolder_id, $teamObject, $email_id;
        $email = new Email();
        $email->retrieve($email_id);
        $toSugarFolder = new SugarFolder();
        $toSugarFolder->retrieve($groupfolder_id);
        $status = $toSugarFolder->addBean($email);
        $this->assertTrue($status, 'testAssignEmailToGroupFolder failed');
    }

    /**
     * Retrieve Email for this folder
     */
    public function retrieveEmailForGroupFolder()
    {
        global $current_user, $groupfolder_id, $teamObject, $email_id;
        $toSugarFolder = new SugarFolder();
        $result = $toSugarFolder->getListItemsForEmailXML($groupfolder_id);
        $this->assertTrue((strlen($result['out']) == 1), 'retrieveEmailForGroupFolder failed');
    }

    /**
     * Delete this email
     */
    public function testDeleteEmailForGroupFolder()
    {
        global $current_user, $groupfolder_id, $teamObject, $email_id;
        $email = new Email();
        $email->delete($email_id);

        $this->assertNull($email->retrieve($email_id));
    }

    /**
     * Delete a folder
     */
    public function testDeleteGroupFolder()
    {
        global $groupfolder_id;
        $focus = $this->retrieveGroupFolder();
        $status = $focus->delete();
        if ($status) {
            $this->tearDownGroupFolder();
            $this->tearDownTestUser();
            unset($groupfolder_id);
        }
        $this->assertTrue($status, 'UnitTestGroupFolder can not be deleted');
    }

    /**
     * retrieve a group folder
     */
    private function retrieveGroupFolder()
    {
        global $groupfolder_id;
        $focus = new SugarFolder();
        $focus->retrieve($groupfolder_id);
        return $focus;
    } // fn

    /**
     * Delete this inbound account.
     */
    private function tearDownGroupFolder()
    {
        global $groupfolder_id;
        $GLOBALS['db']->query("delete from folders WHERE id = '{$groupfolder_id}'");
    }

    /**
     * Create a test user
     */
    private function setupTestUser()
    {
        global $current_user;
        $user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $user;
        $current_user = $user;
        $user->status = 'Active';
        $user->is_admin = 1;
        $user->save();
    }

    /**
     * Remove user created for test
     */
    private function tearDownTestUser()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
}
