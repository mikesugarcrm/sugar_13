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

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class SugarFoldersTest extends TestCase
{
    private $folder = null;
    private $additionalFolders = null;
    private $user = null;
    private $emails = null;
    private $toDelete = [];

    protected function setUp(): void
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->user;
        $this->folder = new SugarFolder();
        $this->additionalFolders = [];
        $this->emails = [];
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Emails');
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->user->id}'");
        $this->clearFolder($this->folder->id);

        foreach ($this->additionalFolders as $additionalID) {
            $this->clearFolder($additionalID);
        }

        foreach ($this->emails as $emailID) {
            $GLOBALS['db']->query("DELETE FROM emails WHERE id='$emailID'");
        }

        unset($this->folder);

        $this->cleanupCreatedRecords();

        SugarTestHelper::tearDown();
    }

    /**
     * Test the Set Folder method.
     */
    public function testSetFolder()
    {
        //Set folder
        $this->folder->id = create_guid();
        $this->folder->new_with_id = true;

        $fields = [
            'name' => 'TEST_FOLDER',
            'parent_folder' => 'PRNT_FOLDER',
            'team_id' => create_guid(),
            'team_set_id' => create_guid(),
        ];

        $this->folder->setFolder($fields);

        //Retrieve newly created folder
        $error_message = 'Unable to set folder.';
        $this->folder->retrieve($this->folder->id);

        $this->assertEquals($fields['name'], $this->folder->name, $error_message);
        $this->assertEquals($fields['parent_folder'], $this->folder->parent_folder, $error_message);
        $this->assertEquals($fields['team_id'], $this->folder->team_id, $error_message);
        $this->assertEquals($fields['team_set_id'], $this->folder->team_set_id, $error_message);
        $this->assertEquals($this->user->id, $this->folder->assign_to_id, $error_message);

        //Check for folder subscriptions create for global user
        $sub_ids = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(1, safeCount($sub_ids), $error_message);
        $this->assertEquals($this->folder->id, $sub_ids[0], $error_message);
    }

    /**
     * Test sugar folder subscriptions: create, clear, insert, clear specific folder.
     */
    public function testFolderSubscriptions()
    {
        $this->createNewSugarFolder();
        $error_message = 'Unable to create|insert|delete sugar folder subscriptions.';

        //Clear subscriptions
        $this->folder->clearSubscriptions();
        $subs = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(0, safeCount($subs), $error_message);

        //Add a subscription
        $this->folder->insertFolderSubscription($this->folder->id, $GLOBALS['current_user']->id);
        $subs = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(1, safeCount($subs), $error_message);

        //Clear subscriptions for a paricular folder
        $this->folder->clearSubscriptionsForFolder($this->folder->id);
        $subs = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(0, safeCount($subs), $error_message);
    }

    /**
     * Test the getParentIDRecursive function which is used to find a grouping of folders.
     */
    public function testgetParentIDRecursive()
    {
        $f1 = new SugarFolder();
        $f12 = new SugarFolder();
        $f3 = new SugarFolder();

        $f1->id = create_guid();
        $f1->new_with_id = true;

        $f12->id = create_guid();
        $f12->new_with_id = true;

        $f3->id = create_guid();
        $f3->new_with_id = true;

        $f12->parent_folder = $f1->id;
        $f1->save();
        $f12->save();
        $f3->save();

        $this->additionalFolders[] = $f1->id;
        $this->additionalFolders[] = $f12->id;
        $this->additionalFolders[] = $f3->id;


        $parentIDs = $this->folder->getParentIDRecursive($f12->id); //Includes itself in the return list.
        $this->assertEquals(2, safeCount($parentIDs), 'Unable to retrieve parent ids recursively');

        $parentIDs = $this->folder->getParentIDRecursive($f3->id); //Includes itself in the return list.
        $this->assertEquals(1, safeCount($parentIDs), 'Unable to retrieve parent ids recursively');

        //Find the children by going the other way.
        $childrenArray = [];
        $this->folder->findAllChildren($f1->id, $childrenArray);
        $this->assertEquals(1, count($childrenArray), 'Unable to retrieve child ids recursively');

        $childrenArray = [];
        $this->folder->findAllChildren($f3->id, $childrenArray);
        $this->assertEquals(0, count($childrenArray), 'Unable to retrieve child ids recursively');
    }

    /**
     * Test to ensure that for a new user, the My Email, My Drafts, Sent Email, etc. folders can be retrieved.
     */
    public function testGetUserFolders()
    {
        $emailUI = new EmailUI();
        $emailUI->preflightUser($GLOBALS['current_user']);
        $error_message = 'Unable to get user folders';
        $rootNode = new ExtNode('', '');

        $folderOpenState = '';
        $ret = $this->folder->getUserFolders($rootNode, $folderOpenState, $GLOBALS['current_user'], true);

        $this->assertEquals(1, safeCount($ret), $error_message);
        $this->assertEquals($GLOBALS['mod_strings']['LNK_MY_INBOX'], $ret[0]['text'], $error_message);
        //Should contain 'My Drafts', 'My Sent Mail', 'My Archive'
        $this->assertEquals(3, safeCount($ret[0]['children']), $error_message);
    }

    /**
     * Tests sugar folder methods that deal with emails.
     */
    public function testFolderEmailMethods()
    {
        $emailParams = ['status' => 'read'];
        $email = $this->createEmailObject($emailParams);
        $this->emails[] = $email->id;

        $this->createNewSugarFolder();
        $this->folder->addBean($email, $GLOBALS['current_user']);

        $emailExists = $this->folder->checkEmailExistForFolder($email->id);
        $this->assertTrue($emailExists, 'Unable to check for emails with a specific folder');

        //Remove the specific email from our folder.

        $this->folder->deleteEmailFromFolder($email->id);
        $emailExists = $this->folder->checkEmailExistForFolder($email->id);
        $this->assertFalse($emailExists, 'Unable to check for emails with a specific folder.');

        //Move the Email bean from one folder to another
        $f3 = new SugarFolder();
        $f3->id = create_guid();
        $f3->new_with_id = true;
        $f3->save();
        $this->additionalFolders[] = $f3->id;

        $this->folder->addBean($email, $GLOBALS['current_user']);

        $emailExists = $f3->checkEmailExistForFolder($email->id);
        $this->assertFalse($emailExists);

        $this->folder->move($this->folder->id, $f3->id, $email->id);
        $emailExists = $f3->checkEmailExistForFolder($email->id);
        $this->assertTrue($emailExists, 'Unable to move Emails bean to a different sugar folder');
    }


    /**
     * "a" is used for the admin user's ID because "1" has special meaning to other tests downstream. Using "1" for the
     * ID impacts the application state that is expected for those tests and causes them to fail. It is not important to
     * test for "1" because the root cause of the issue being tested is that the `created_by` and `id` fields contain
     * values that are shorter than 36 characters. In production, this scenario only presents itself when the user is
     * the admin.
     *
     * @group db2
     */
    public function testRetrieveFoldersForProcessing_UsingDB2_CurrentUserIsAdmin()
    {
        SugarTestHelper::setUp('current_user');
        global $current_user;

        $sf = new SugarFolder();
        $sf->folder_type = 'inbound';
        $sf->save();
        $this->additionalFolders[] = $sf->id;

        $folders = $sf->retrieveFoldersForProcessing($current_user);

        $this->assertEquals($sf->id, $folders[0]['id']);
    }


    public function testDeleteChildrenCascadeCountQueries()
    {
        $id = create_guid();
        $guid = create_guid();
        $sf = new SugarFolder();
        $result = $sf->deleteChildrenCascade($id);
        $this->assertTrue($result);

        $sf->db->getConnection()->insert('inbound_email', [
            'id' => $guid,
            'groupfolder_id' => $id,
            'deleted' => 0,
        ]);
        $this->toDelete['inbound_email'] = ['id' => $guid];
        $result = $sf->deleteChildrenCascade($id);
        $this->assertFalse($result);
        $this->cleanupCreatedRecords();

        $sf->db->getConnection()->insert('folders_rel', [
            'id' => $guid,
            'polymorphic_module' => 'Emails',
            'polymorphic_id' => 'foo',
            'folder_id' => $id,
        ]);
        $this->toDelete['folders_rel'] = ['id' => $guid];
        $result = $sf->deleteChildrenCascade($id);
        $this->assertFalse($result);
    }

    public function testUpdateFolderCountQueries()
    {
        $id = create_guid();
        $sf = new SugarFolder();
        $fields = [
            'record' => '',
            'name' => '',
            'parent_folder' => '',
            'team_id' => '',
            'team_set_id' => '',
        ];
        $sf->has_child = false;
        $sf->parent_folder = $id;

        $sf->db->getConnection()->insert('folders', [
            'id' => $id,
            'parent_folder' => 'none',
            'deleted' => 0,
            'created_by' => 'now()',
            'modified_by' => 'now()',
            'has_child' => 0,
        ]);
        $this->toDelete['folders'] = ['id' => $id];

        $sf->updateFolder($fields);
        $result = $sf->db->getConnection()
            ->executeQuery('SELECT has_child FROM folders WHERE id = ?', [$id])
            ->fetchOne();
        $this->assertEquals(0, $result);

        $guid = create_guid();
        $sf->db->getConnection()->insert('folders', [
            'id' => $guid,
            'parent_folder' => $id,
            'deleted' => 0,
            'created_by' => 'now()',
            'modified_by' => 'now()',
            'has_child' => 1,
        ]);
        $this->toDelete['folders'] = ['id' => $guid];

        $sf->parent_folder = $id;
        $sf->updateFolder($fields);
        $result = $sf->db->getConnection()
            ->executeQuery('SELECT has_child FROM folders WHERE id = ?', [$id])
            ->fetchOne();
        $this->assertEquals(0, $result);
    }

    private function createEmailObject($additionalParams = [])
    {
        global $timedate;

        $em = new Email();
        $em->name = 'tst_' . uniqid();
        $em->type = 'inbound';
        $em->intent = 'pick';
        //Two days from today
        $em->date_sent = $timedate->to_display_date_time(gmdate('Y-m-d H:i:s', (time() + (3600 * 24 * 2))));

        foreach ($additionalParams as $k => $v) {
            $em->$k = $v;
        }

        $em->save();

        return $em;
    }

    private function createNewSugarFolder()
    {
        $this->folder->id = create_guid();
        $this->folder->new_with_id = true;
        $this->folder->name = 'UNIT TEST';
        $this->folder->save();
    }

    private function clearFolder($folder_id)
    {
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->user->id}'");
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$folder_id}'");
        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$folder_id}'");
    }

    private function cleanupCreatedRecords()
    {
        /** @var Connection $conn */
        $conn = $GLOBALS['db']->getConnection();
        // delete previously created records
        foreach ($this->toDelete as $table => $fields) {
            $conn->delete($table, $fields);
            unset($this->toDelete[$table]);
        }
    }
}
