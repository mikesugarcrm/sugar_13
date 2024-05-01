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

use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;

/**
 * @group ApiTests
 */
class PortalStandardFunctionalityApiTest extends TestCase
{
    public static $service;

    public static $forbiddenSaveRecordsIds = [
        'Contacts' => 'portal-contact-not-saved',
        'Accounts' => 'portal-account-not-saved',
        'KBContents' => 'portal-kb-not-saved',
        'Others' => 'portal-generic-not-saved',
    ];

    protected static $originalPortalRecords = [];

    public static $accountIds = [
        'a1' => 'portal-account-1', // account of b2b-contact-1
        'a2' => 'portal-account-2', // account of b2b-contact-2
        'a3' => 'portal-account-3', // account without contacts
    ];

    public static $contactIds = [
        'b2b-account-1' => 'portal-b2b-contact-1',
        'b2b-account-2' => 'portal-b2b-contact-2',
        'b2c' => 'portal-b2c-contact-1',
    ];

    public static $bugIds = [
        'visible1' => 'portal-bug-1',
        'visible2' => 'portal-bug-2',
        'invisible1' => 'portal-bug-3',
    ];

    public static $categoryIds = [
        'visible1' => 'portal-category-1', // visible, first or condition
        'visible2' => 'portal-category-2', // visible, second or condition
        'invisible1' => 'portal-category-3', // none of the or conditions
    ];

    public static $kbIds = [
        'visible1' => 'portal-kb-1', // visible, with no category
        'visible2' => 'portal-kb-2', // visible, with visible category
        'invisible1' => 'portal-kb-4', // invisible, missing first and condition
        'invisible2' => 'portal-kb-5', // invisible, missing second and condition
        'invisible3' => 'portal-kb-6', // invisible, missing third and condition
    ];

    public static $caseIds = [
        'visible-b2b-account-1' => 'portal-case-1', // visible for b2b account 1 contacts
        'visible-b2b-account-2' => 'portal-case-2', // visible for b2b account 2 contacts
        'visible-b2b-account-3' => 'portal-case-3', // visible for b2b account 2 contacts and b2c contact
        'visible-b2b-account-4' => 'portal-case-4', // no contacts, so it is not visible to anyone for our purposes
        'invisible-b2b-account-1' => 'portal-case-8', // visible for b2b account 1 contacts by default, not visible if the option is disbaled
        'visible-b2c' => 'portal-case-5', // b2c contact
        'invisible1' => 'portal-case-6', // no relationship with accounts, no with contacts, but it is marked as visible
        'invisible2' => 'portal-case-7', // relationship to all accounts and contacts, but not marked as portal visible
    ];

    public static $noteIds = [
        'visible-b2c-1' => 'portal-note-1', // related directly to contact, no relationship to anything else and marked with portal flag
        'visible-b2c-2' => 'portal-note-2', // related directly to contact and to case visible-b2c and marked with portal flag
        'invisible-b2c-1' => 'portal-note-3', // related directly to contact and to case visible-b2c but not marked with portal flag
        'visible-all-1' => 'portal-note-4', // related to a kb and marked with portal flag
        'visible-all-2' => 'portal-note-5', // related to the visible bug and marked with portal flag
        'visible-b2b-1' => 'portal-note-6', // related to viewable case visible-b2b-account-1 with portal flag
        'invisible-b2b-1' => 'portal-note-8', // related to invisible case invisible-b2b-account-1
        'invisible-all-1' => 'portal-note-7', // not marked with portal flag but related to visible-b2b-account-3 that can be viewed by both the b2c contact and by b2b account 2
    ];

    protected $caseVisibility = 'all';

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]); // admin
        SugarTestPortalUtilities::enablePortal();
        self::createOriginalTestRecords();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestPortalUtilities::restoreOriginalUser();
        self::deleteOriginalTestRecords();
        SugarTestPortalUtilities::disablePortal();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        sugar_cache_clear('admin_settings_cache');
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        if ($this->caseVisibility !== 'all') {
            $admin = Administration::getSettings(false, true);
            $admin->saveSetting('portal', 'caseVisibility', 'all', 'support');
            PortalFactory::removeCache();
            $this->caseVisibility = 'all';
        }
        parent::tearDown();
    }

    protected function configCaseVisibility()
    {
        $admin = Administration::getSettings(false, true);
        $admin->saveSetting('portal', 'caseVisibility', 'related_contacts', 'support');
        PortalFactory::removeCache();
        $this->caseVisibility = 'related_contacts';
    }

    public static function addRecordToDelete(string $module, string $id)
    {
        self::$originalPortalRecords[$module][] = $id;
    }

    public static function deleteOriginalTestRecords()
    {
        if (!empty(self::$originalPortalRecords)) {
            foreach (self::$originalPortalRecords as $module => $records) {
                if (!empty($records)) {
                    foreach ($records as $id) {
                        SugarTestPortalUtilities::deleteSingleRecord($module, $id);
                    }
                }
            }
        }
    }

    public static function createOriginalTestRecords()
    {
        // accounts
        $id = self::$accountIds['a1'];
        $b = SugarTestPortalUtilities::createBasicObject('Accounts', $id);
        $b->name = $id;
        $b->save();
        self::addRecordToDelete('Accounts', $id);

        $id = self::$accountIds['a2'];
        $b = SugarTestPortalUtilities::createBasicObject('Accounts', $id);
        $b->name = $id;
        $b->save();
        self::addRecordToDelete('Accounts', $id);

        $id = self::$accountIds['a3'];
        $b = SugarTestPortalUtilities::createBasicObject('Accounts', $id);
        $b->name = $id;
        $b->save();
        self::addRecordToDelete('Accounts', $id);

        // b2b contact account 1
        $id = self::$contactIds['b2b-account-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Contacts', $id);
        $b->first_name = $id;
        $b->last_name = $id;
        $b->portal_name = $id;
        $b->portal_password = User::getPasswordHash($id);
        $b->portal_active = 1;
        $b->account_id = self::$accountIds['a1'];
        $b->save();
        self::addRecordToDelete('Contacts', $id);

        // b2b contact account 2
        $id = self::$contactIds['b2b-account-2'];
        $b = SugarTestPortalUtilities::createBasicObject('Contacts', $id);
        $b->first_name = $id;
        $b->last_name = $id;
        $b->portal_name = $id;
        $b->portal_password = User::getPasswordHash($id);
        $b->portal_active = 1;
        $b->account_id = self::$accountIds['a2'];
        $b->save();
        self::addRecordToDelete('Contacts', $id);

        // b2c contact
        $id = self::$contactIds['b2c'];
        $b = SugarTestPortalUtilities::createBasicObject('Contacts', $id);
        $b->first_name = $id;
        $b->last_name = $id;
        $b->portal_name = $id;
        $b->portal_password = User::getPasswordHash($id);
        $b->portal_active = 1;
        $b->save();
        self::addRecordToDelete('Contacts', $id);

        // bug portal visible
        $id = self::$bugIds['visible1'];
        $b = SugarTestPortalUtilities::createBasicObject('Bugs', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        self::addRecordToDelete('Bugs', $id);

        // bug portal visible, related to both account and contact
        $id = self::$bugIds['visible2'];
        $b = SugarTestPortalUtilities::createBasicObject('Bugs', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('contacts');
        $b->load_relationship('accounts');
        $b->contacts->add(self::$contactIds['b2b-account-1']);
        $b->accounts->add(self::$accountIds['a1']);
        self::addRecordToDelete('Bugs', $id);

        // bug portal invisible
        $id = self::$bugIds['invisible1'];
        $b = SugarTestPortalUtilities::createBasicObject('Bugs', $id);
        $b->name = $id;
        $b->portal_viewable = 0;
        $b->save();
        self::addRecordToDelete('Bugs', $id);

        // categories
        $kb = BeanFactory::newBean('KBContents');
        $categoryRoot = $kb->getCategoryRoot();

        $id = self::$categoryIds['visible1'];
        $b = SugarTestPortalUtilities::createBasicObject('Categories', $id);
        $b->name = $id;
        $b->is_external = 1;
        $b->lvl = 1;
        $b->lft = 2;
        $b->rgt = 4;
        $b->root = $categoryRoot;
        $b->save();
        self::addRecordToDelete('Categories', $id);

        $id = self::$categoryIds['visible2'];
        $b = SugarTestPortalUtilities::createBasicObject('Categories', $id);
        $b->name = $id;
        $b->lvl = 0;
        $b->lft = 2;
        $b->rgt = 4;
        $b->root = $categoryRoot;
        $b->save();
        self::addRecordToDelete('Categories', $id);

        // this cannot be related to any kb or it will become external
        $id = self::$categoryIds['invisible1'];
        $b = SugarTestPortalUtilities::createBasicObject('Categories', $id);
        $b->name = $id;
        $b->is_external = 0;
        $b->lvl = 1;
        $b->lft = 4;
        $b->rgt = 4;
        $b->root = $categoryRoot;
        $b->save();
        self::addRecordToDelete('Categories', $id);

        // Knowledgebase
        $id = self::$kbIds['visible1'];
        $b = SugarTestPortalUtilities::createBasicObject('KBContents', $id);
        $b->name = $id;
        $b->active_rev = 1;
        $b->is_external = 1;
        $b->status = \KBContent::ST_PUBLISHED;
        $b->save();
        self::addRecordToDelete('KBContents', $id);

        $id = self::$kbIds['visible2'];
        $b = SugarTestPortalUtilities::createBasicObject('KBContents', $id);
        $b->name = $id;
        $b->active_rev = 1;
        $b->is_external = 1;
        $b->status = \KBContent::ST_PUBLISHED;
        $b->category_id = self::$categoryIds['visible1'];
        $b->save();
        self::addRecordToDelete('KBContents', $id);

        $id = self::$kbIds['invisible1'];
        $b = SugarTestPortalUtilities::createBasicObject('KBContents', $id);
        $b->name = $id;
        $b->is_external = 1;
        $b->status = \KBContent::ST_PUBLISHED;
        $b->save();
        $b->active_rev = 0;
        $b->save();
        self::addRecordToDelete('KBContents', $id);

        $id = self::$kbIds['invisible2'];
        $b = SugarTestPortalUtilities::createBasicObject('KBContents', $id);
        $b->name = $id;
        $b->active_rev = 1;
        $b->is_external = 0;
        $b->status = \KBContent::ST_PUBLISHED;
        $b->save();
        self::addRecordToDelete('KBContents', $id);

        $id = self::$kbIds['invisible3'];
        $b = SugarTestPortalUtilities::createBasicObject('KBContents', $id);
        $b->name = $id;
        $b->active_rev = 1;
        $b->is_external = 1;
        $b->status = 'Draft';
        $b->save();
        self::addRecordToDelete('KBContents', $id);

        // Cases
        $id = self::$caseIds['visible-b2b-account-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('accounts');
        $b->accounts->add(self::$accountIds['a1']);
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['invisible-b2b-account-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('accounts');
        $b->accounts->add(self::$accountIds['a1']);
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['visible-b2b-account-2'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('accounts');
        $b->accounts->add(self::$accountIds['a2']);
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['visible-b2b-account-3'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('accounts');
        $b->accounts->add(self::$accountIds['a2']);
        $b->load_relationship('contacts');
        $b->contacts->add(self::$contactIds['b2c']);
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['visible-b2b-account-4'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('accounts');
        $b->accounts->add(self::$accountIds['a3']);
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['visible-b2c'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('contacts');
        $b->contacts->add(self::$contactIds['b2c']);
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['visible-b2c'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        $b->load_relationship('contacts');
        $b->contacts->add(self::$contactIds['b2c']);
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['invisible1'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 1;
        $b->save();
        self::addRecordToDelete('Cases', $id);

        $id = self::$caseIds['invisible2'];
        $b = SugarTestPortalUtilities::createBasicObject('Cases', $id);
        $b->name = $id;
        $b->portal_viewable = 0;
        $b->save();
        $b->load_relationship('accounts');
        $b->accounts->add(self::$accountIds['a1']);
        $b->accounts->add(self::$accountIds['a2']);
        $b->accounts->add(self::$accountIds['a3']);
        $b->load_relationship('contacts');
        $b->contacts->add(self::$contactIds['b2c']);
        self::addRecordToDelete('Cases', $id);

        // Notes
        $id = self::$noteIds['visible-b2c-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 1;
        $b->contact_id = self::$contactIds['b2c'];
        $b->save();
        self::addRecordToDelete('Notes', $id);

        $id = self::$noteIds['visible-b2c-2'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 1;
        $b->contact_id = self::$contactIds['b2c'];
        $b->parent_type = 'Cases';
        $b->parent_id = self::$caseIds['visible-b2c'];
        $b->save();
        self::addRecordToDelete('Notes', $id);

        $id = self::$noteIds['invisible-b2c-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 0;
        $b->contact_id = self::$contactIds['b2c'];
        $b->parent_type = 'Cases';
        $b->parent_id = self::$caseIds['visible-b2c'];
        $b->save();
        self::addRecordToDelete('Notes', $id);

        $id = self::$noteIds['visible-all-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 1;
        $b->parent_type = 'KBContents';
        $b->parent_id = self::$kbIds['visible2'];
        $b->save();
        self::addRecordToDelete('Notes', $id);

        $id = self::$noteIds['visible-all-2'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 1;
        $b->parent_type = 'Bugs';
        $b->parent_id = self::$bugIds['visible1'];
        $b->save();
        self::addRecordToDelete('Notes', $id);

        $id = self::$noteIds['visible-b2b-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 1;
        $b->parent_type = 'Cases';
        $b->parent_id = self::$caseIds['visible-b2b-account-1'];
        $b->save();
        self::addRecordToDelete('Notes', $id);

        $id = self::$noteIds['invisible-b2b-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 1;
        $b->parent_type = 'Cases';
        $b->parent_id = self::$caseIds['invisible-b2b-account-1'];
        $b->save();
        self::addRecordToDelete('Notes', $id);

        $id = self::$noteIds['invisible-all-1'];
        $b = SugarTestPortalUtilities::createBasicObject('Notes', $id);
        $b->name = $id;
        $b->portal_flag = 0;
        $b->parent_type = 'Cases';
        $b->contact_id = self::$contactIds['b2c'];
        $b->parent_id = self::$caseIds['visible-b2b-account-3'];
        $b->save();
        self::addRecordToDelete('Notes', $id);
    }

    public function testCantCreateContact()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Contacts',
            'id' => self::$forbiddenSaveRecordsIds['Contacts'],
            'new_with_id' => true,
            'last_name' => 'Jones',
            'portal_active' => true,
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new ModulePortalApi())->createRecord(self::$service, $args);
    }

    /*
        The test below, in theory should not be needed, as the previous test (that tests the opposite scenario) should succeed when encountering an exception.
        It was found out that in some occasions exceptions were thrown and the records were still created, and that's why these tests have been added.

        It appears that the issue is due to the fact of having the same exception while saving a record, for both the "save" logic and for the "reload saved record" logic
        By not having a rollback functionality, if something fails during "reload", it looks like the record did not "save", while instead it did.
        This comment applies to all the "testDidntCreate*" tests below as well
    */

    public function testDidntCreateContact()
    {
        // login as normal user to attempt to retrieve the record
        SugarTestPortalUtilities::restoreNormalUser();
        $b = BeanFactory::retrieveBean('Contacts', self::$forbiddenSaveRecordsIds['Contacts']);
        $this->assertTrue(empty($b));
    }

    public function testCantCreateAccount()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Accounts',
            'id' => self::$forbiddenSaveRecordsIds['Accounts'],
            'new_with_id' => true,
            'name' => 'Dummy',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new ModulePortalApi())->createRecord(self::$service, $args);
    }

    public function testDidntCreateAccount()
    {
        // login as normal user to attempt to retrieve the record
        SugarTestPortalUtilities::restoreNormalUser();
        $b = BeanFactory::retrieveBean('Accounts', self::$forbiddenSaveRecordsIds['Accounts']);
        $this->assertTrue(empty($b));
    }

    public function testCantCreateKnowledgebase()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'KBContents',
            'id' => self::$forbiddenSaveRecordsIds['KBContents'],
            'new_with_id' => true,
            'name' => 'Dummy',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new ModulePortalApi())->createRecord(self::$service, $args);
    }

    public function testDidntCreateKnowledgebase()
    {
        // login as normal user to attempt to retrieve the record
        SugarTestPortalUtilities::restoreNormalUser();
        $b = BeanFactory::retrieveBean('KBContents', self::$forbiddenSaveRecordsIds['KBContents']);
        $this->assertTrue(empty($b));
    }

    public function testCantCreateAnyOtherModule()
    {
        $currentUser = SugarTestPortalUtilities::getPortalCurrentUser(self::$contactIds['b2b-account-1']);

        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        foreach ($currentUser['current_user']['acl'] as $module => $perm) {
            if (!in_array($module, SugarTestPortalUtilities::$modulesCreationAllowed)) {
                $args = [
                    'module' => $module,
                    'id' => self::$forbiddenSaveRecordsIds['Others'],
                    'new_with_id' => true,
                    'name' => 'Dummy',
                ];

                $this->expectException(SugarApiExceptionNotAuthorized::class);
                $response = (new ModulePortalApi())->createRecord(self::$service, $args);
            }
        }
    }

    public function testDidntCreateAnyOtherModule()
    {
        $currentUser = SugarTestPortalUtilities::getPortalCurrentUser(self::$contactIds['b2b-account-1']);
        // login as normal user to attempt to retrieve the record
        SugarTestPortalUtilities::restoreNormalUser();
        foreach ($currentUser['current_user']['acl'] as $module => $perm) {
            // workaround to skip reports, as it breaks things
            // TODO, understand the real problem behind this
            if ($module === 'Reports') {
                break;
            }

            $b = BeanFactory::retrieveBean($module, self::$forbiddenSaveRecordsIds['Others']);

            if (!empty($b->id) && $b->id !== self::$forbiddenSaveRecordsIds['Others']) {
                // added this exception of the id check, because Currencies always returns the record with id -99
            } else {
                $this->assertTrue(empty($b));
            }
        }
    }

    public function testCreateBugB2C()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);

        $args = [
            'module' => 'Bugs',
            'name' => 'Dummy B2C',
            'portal_viewable' => 1,
        ];

        $response = (new ModulePortalApi())->createRecord(self::$service, $args);
        $bugId = $response['id'];

        SugarTestPortalUtilities::restoreNormalUser();
        $bugRetrieved = BeanFactory::retrieveBean('Bugs', $bugId);

        if (!empty($bugRetrieved)) {
            // retrieve and compare the contact
            $bugRetrieved->load_relationship('contacts');
            $contactIds = array_map('trim', $bugRetrieved->contacts->get());
            if (in_array(self::$contactIds['b2c'], $contactIds)) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }

            // cleanup the bug
            //SugarTestPortalUtilities::addRecordToDelete('Bugs', $bugId);
            SugarTestPortalUtilities::deleteSingleRecord('Bugs', $bugId);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testCreateBugB2B()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Bugs',
            'name' => 'Dummy B2B',
            'portal_viewable' => 1,
        ];

        $response = (new ModulePortalApi())->createRecord(self::$service, $args);
        $bugId = $response['id'];
        $bugRetrieved = BeanFactory::retrieveBean('Bugs', $bugId);

        $this->assertTrue(!empty($bugRetrieved));

        if (!empty($bugRetrieved)) {
            // retrieve and compare the contact
            $bugRetrieved->load_relationship('contacts');
            $contactIds = array_map('trim', $bugRetrieved->contacts->get());
            $this->assertSame(
                array_intersect(
                    [self::$contactIds['b2b-account-1']],
                    $contactIds
                ),
                [self::$contactIds['b2b-account-1']]
            );

            // retrieve the contact, to get the account
            $contact = BeanFactory::retrieveBean('Contacts', self::$contactIds['b2b-account-1']);
            $this->assertTrue(!empty($contact));
            $this->assertTrue(!empty($contact->account_id));

            // retrieve and compare the account
            if (!empty($contact) && !empty($contact->account_id)) {
                $bugRetrieved->load_relationship('accounts');
                $accountIds = array_map('trim', $bugRetrieved->accounts->get());
                $this->assertSame(
                    array_intersect(
                        [trim($contact->account_id)],
                        $accountIds
                    ),
                    [trim($contact->account_id)]
                );
            }

            // cleanup the bug
            //SugarTestPortalUtilities::addRecordToDelete('Bugs', $bugId);
            SugarTestPortalUtilities::deleteSingleRecord('Bugs', $bugId);
        }
    }

    public function testCreateCaseB2C()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);

        $args = [
            'module' => 'Cases',
            'name' => 'Dummy B2C',
            'portal_viewable' => 1,
            'priority' => 'P1',
            'status' => 'New',
            'type' => 'Administration',
        ];

        $response = (new ModulePortalApi())->createRecord(self::$service, $args);
        $caseId = $response['id'];
        $caseRetrieved = BeanFactory::retrieveBean('Cases', $caseId);

        if (!empty($caseRetrieved)) {
            // retrieve and compare the contact
            $caseRetrieved->load_relationship('contacts');
            $contactIds = array_map('trim', $caseRetrieved->contacts->get());
            if (in_array(self::$contactIds['b2c'], $contactIds)) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }

            // cleanup the case
            //SugarTestPortalUtilities::addRecordToDelete('Cases', $caseId);
            SugarTestPortalUtilities::deleteSingleRecord('Cases', $caseId);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testCreateCaseB2B()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Cases',
            'name' => 'Dummy B2B',
            'portal_viewable' => 1,
            'priority' => 'P1',
            'status' => 'New',
            'type' => 'Administration',
        ];

        $response = (new ModulePortalApi())->createRecord(self::$service, $args);
        $caseId = $response['id'];
        $caseRetrieved = BeanFactory::retrieveBean('Cases', $caseId);

        $this->assertTrue(!empty($caseRetrieved));

        if (!empty($caseRetrieved)) {
            // retrieve and compare the contact
            $caseRetrieved->load_relationship('contacts');
            $contactIds = array_map('trim', $caseRetrieved->contacts->get());
            $this->assertContains(self::$contactIds['b2b-account-1'], $contactIds);

            // retrieve the contact, to get the account
            $contact = BeanFactory::retrieveBean('Contacts', self::$contactIds['b2b-account-1']);
            $this->assertTrue(!empty($contact));
            $this->assertTrue(!empty($contact->account_id));

            // retrieve and compare the account
            if (!empty($contact) && !empty($contact->account_id)) {
                $caseRetrieved->load_relationship('accounts');
                $accountIds = array_map('trim', $caseRetrieved->accounts->get());
                $this->assertContains(trim($contact->account_id), $accountIds);
            }

            // cleanup the case
            //SugarTestPortalUtilities::addRecordToDelete('Cases', $caseId);
            SugarTestPortalUtilities::deleteSingleRecord('Cases', $caseId);
        }
    }

    public function testEditOtherContact()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);
        $args = [
            'module' => 'Contacts',
            'record' => self::$contactIds['b2b-account-2'],
            'last_name' => 'Smith',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModuleApi())->updateRecord(self::$service, $args);
    }

    public function testVerifyCantEditOtherContact()
    {
        SugarTestPortalUtilities::restoreNormalUser();
        $bean = BeanFactory::getBean('Contacts', self::$contactIds['b2b-account-2']);
        $this->assertNotEquals($bean->last_name, 'Smith');
    }

    public function testEditMyContact()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);
        $args = [
            'module' => 'Contacts',
            'record' => self::$contactIds['b2b-account-1'],
            'last_name' => 'Smith',
        ];

        $response = (new ModuleApi())->updateRecord(self::$service, $args);

        $bean = BeanFactory::retrieveBean('Contacts', self::$contactIds['b2b-account-1']);
        $this->assertEquals($bean->last_name, 'Smith');
    }

    public function testEditMyAccount()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);
        $args = [
            'module' => 'Accounts',
            'record' => self::$accountIds['a1'],
            'name' => 'Acme Inc.',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new ModuleApi())->updateRecord(self::$service, $args);
    }

    public function testVerifyCantEditMyAccount()
    {
        SugarTestPortalUtilities::restoreNormalUser();
        $bean = BeanFactory::retrieveBean('Accounts', self::$accountIds['a1']);
        $this->assertNotEquals($bean->name, 'Acme Inc.');
    }

    public function testEditMyCase()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);
        $args = [
            'module' => 'Cases',
            'record' => self::$caseIds['visible-b2b-account-1'],
            'name' => 'Problem',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new ModuleApi())->updateRecord(self::$service, $args);
    }

    public function testVerifyCantEditMyCase()
    {
        SugarTestPortalUtilities::restoreNormalUser();
        $bean = BeanFactory::retrieveBean('Cases', self::$caseIds['visible-b2b-account-1']);
        $this->assertNotEquals($bean->name, 'Problem');
    }

    public function testEditMyBug()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);
        $args = [
            'module' => 'Bugs',
            'record' => self::$bugIds['visible2'],
            'name' => 'Problem',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new ModuleApi())->updateRecord(self::$service, $args);
    }

    public function testVerifyCantEditMyBug()
    {
        SugarTestPortalUtilities::restoreNormalUser();
        $bean = BeanFactory::retrieveBean('Bugs', self::$bugIds['visible2']);
        $this->assertNotEquals($bean->name, 'Problem');
    }

    public function testEditMyNote()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);
        $args = [
            'module' => 'Notes',
            'record' => self::$noteIds['visible-b2c-1'],
            'name' => 'Problem',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new ModuleApi())->updateRecord(self::$service, $args);
    }

    public function testVerifyCantEditMyNote()
    {
        SugarTestPortalUtilities::restoreNormalUser();
        $bean = BeanFactory::retrieveBean('Notes', self::$noteIds['visible-b2c-1']);
        $this->assertNotEquals($bean->name, 'Problem');
    }

    public function testListviewContacts()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Contacts',
            'view' => 'list',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new FilterApi())->filterList(self::$service, $args);
    }

    public function testListviewAccounts()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Accounts',
            'view' => 'list',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new FilterApi())->filterList(self::$service, $args);
    }

    // apparently this is listed as a module that can be retrieved records from, and it fails
    public function testListviewManufacturers()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Manufacturers',
            'view' => 'list',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $response = (new FilterApi())->filterList(self::$service, $args);
    }

    public function testListviewCases()
    {
        $users = [
            'b2c' => [
                'portal-case-3',
                'portal-case-5',
            ],
            'b2b-account-1' => [
                'portal-case-1',
                'portal-case-8',
            ],
            'b2b-account-2' => [
                'portal-case-2',
                'portal-case-3',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            $args = [
                'module' => 'Cases',
                'view' => 'list',
            ];

            $response = (new FilterApi())->filterList(self::$service, $args);
            $this->assertNotEmpty($response);
            $this->assertIsArray($response);
            $this->assertArrayHasKey('next_offset', $response);
            $this->assertArrayHasKey('records', $response);
            $this->assertIsArray($response['records']);
            $this->assertSameSize($response['records'], $recordIdList);
            foreach ($response['records'] as $record) {
                $this->assertContains($record['id'], $recordIdList);
            }
        }
    }

    public function testListviewBugs()
    {
        $users = [
            'b2c' => [
                'portal-bug-1',
                'portal-bug-2',
            ],
            'b2b-account-1' => [
                'portal-bug-1',
                'portal-bug-2',
            ],
            'b2b-account-2' => [
                'portal-bug-1',
                'portal-bug-2',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            $args = [
                'module' => 'Bugs',
                'view' => 'list',
            ];

            $response = (new FilterApi())->filterList(self::$service, $args);
            $this->assertNotEmpty($response);
            $this->assertIsArray($response);
            $this->assertArrayHasKey('next_offset', $response);
            $this->assertArrayHasKey('records', $response);
            $this->assertIsArray($response['records']);
            $this->assertSameSize($response['records'], $recordIdList);
            foreach ($response['records'] as $record) {
                $this->assertContains($record['id'], $recordIdList);
            }
        }
    }

    public function testListviewKnowledgebases()
    {
        $users = [
            'b2c' => [
                'portal-kb-1',
                'portal-kb-2',
            ],
            'b2b-account-1' => [
                'portal-kb-1',
                'portal-kb-2',
            ],
            'b2b-account-2' => [
                'portal-kb-1',
                'portal-kb-2',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            $args = [
                'module' => 'KBContents',
                'view' => 'list',
            ];

            $response = (new FilterApi())->filterList(self::$service, $args);
            $this->assertNotEmpty($response);
            $this->assertIsArray($response);
            $this->assertArrayHasKey('next_offset', $response);
            $this->assertArrayHasKey('records', $response);
            $this->assertIsArray($response['records']);
            $this->assertSameSize($response['records'], $recordIdList);
            foreach ($response['records'] as $record) {
                $this->assertContains($record['id'], $recordIdList);
            }
        }
    }

    public function testListviewNotes()
    {
        $users = [
            'b2c' => [
                'portal-note-1',
                'portal-note-2',
                'portal-note-4',
                'portal-note-5',
            ],
            'b2b-account-1' => [
                'portal-note-4',
                'portal-note-5',
                'portal-note-6',
                'portal-note-8',
            ],
            'b2b-account-2' => [
                'portal-note-4',
                'portal-note-5',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            $args = [
                'module' => 'Notes',
                'view' => 'list',
            ];

            $response = (new FilterApi())->filterList(self::$service, $args);
            $this->assertNotEmpty($response);
            $this->assertIsArray($response);
            $this->assertArrayHasKey('next_offset', $response);
            $this->assertArrayHasKey('records', $response);
            $this->assertIsArray($response['records']);
            $this->assertSameSize($response['records'], $recordIdList);
            foreach ($response['records'] as $record) {
                $this->assertContains($record['id'], $recordIdList);
            }
        }
    }

    public function testListviewCategories()
    {
        // find the root system category
        $kb = BeanFactory::newBean('KBContents');
        $kbroot = $kb->getCategoryRoot();

        $users = [
            'b2c' => [
                $kbroot,
                'portal-category-1',
                'portal-category-2',
            ],
            'b2b-account-1' => [
                $kbroot,
                'portal-category-1',
                'portal-category-2',
            ],
            'b2b-account-2' => [
                $kbroot,
                'portal-category-1',
                'portal-category-2',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            $args = [
                'module' => 'Categories',
                'view' => 'list',
            ];

            $response = (new FilterApi())->filterList(self::$service, $args);
            $this->assertNotEmpty($response);
            $this->assertIsArray($response);
            $this->assertArrayHasKey('next_offset', $response);
            $this->assertArrayHasKey('records', $response);
            $this->assertIsArray($response['records']);
            $this->assertSameSize($response['records'], $recordIdList);
            foreach ($response['records'] as $record) {
                $this->assertContains($record['id'], $recordIdList);
            }
        }
    }

    public function testListviewDashboards()
    {
        // get the id we are supposed to see, based on the license used during the test run
        // use the same logic as the visibility
        if (PortalFactory::getInstance('Settings')->isServe()) {
            $dashboardId = '0ca2d773-0bb3-4bf3-ae43-68569968af57';
        } else {
            $this->markTestSkipped('Test is not valid on ENT-only instances');
        }

        $users = [
            'b2c' => [$dashboardId],
            'b2b-account-1' => [$dashboardId],
            'b2b-account-2' => [$dashboardId],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            $args = [
                'module' => 'Dashboards',
                'view' => 'list',
            ];

            $response = (new FilterApi())->filterList(self::$service, $args);
            $this->assertNotEmpty($response);
            $this->assertIsArray($response);
            $this->assertArrayHasKey('next_offset', $response);
            $this->assertArrayHasKey('records', $response);
            $this->assertIsArray($response['records']);
            $this->assertCount(count($recordIdList), $response['records']);
            foreach ($response['records'] as $record) {
                $this->assertContains($record['id'], $recordIdList);
            }
        }
    }

    public function testListviewAnyOtherModule()
    {
        $currentUser = SugarTestPortalUtilities::getPortalCurrentUser(self::$contactIds['b2b-account-1']);

        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);
        $aclMock = $this->createMock(SugarACLPortal::class);
        $modulesToIgnoreForListView = \Sugarcrm\SugarcrmTestsUnit\TestReflection::getProtectedValue($aclMock, 'modulesToDenyPortalList');

        foreach ($currentUser['current_user']['acl'] as $module => $perm) {
            if (!in_array(
                $module,
                array_merge(
                    SugarTestPortalUtilities::$modulesListviewsAllowed,
                    SugarTestPortalUtilities::$modulesToIgnore,
                    $modulesToIgnoreForListView
                )
            )
                && (empty($perm['access']) || $perm['access'] !== 'no')
                && (empty($perm['list']) || $perm['list'] !== 'no')
            ) {
                $args = [
                    'module' => $module,
                    'view' => 'list',
                ];

                $response = (new FilterApi())->filterList(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('next_offset', $response);
                $this->assertArrayHasKey('records', $response);
                $this->assertIsArray($response['records']);
                $this->assertCount(0, $response['records']);
            }
        }
    }

    public function testCantViewOtherContact()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Contacts',
            'record' => self::$contactIds['b2b-account-2'],
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCanViewOwnContact()
    {
        $users = [
            'b2c',
            'b2b-account-1',
            'b2b-account-2',
        ];

        foreach ($users as $userKey) {
            $recordId = self::$contactIds[$userKey];

            self::$service = SugarTestPortalUtilities::loginAsPortalUser($recordId);

            $args = [
                'module' => 'Contacts',
                'record' => $recordId,
            ];

            $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
            $this->assertNotEmpty($response);
            $this->assertIsArray($response);
            $this->assertArrayHasKey('id', $response);
            $this->assertEquals($response['id'], $recordId);
        }
    }

    public function testCanViewAllowedCases()
    {
        $users = [
            'b2c' => [
                'portal-case-3',
                'portal-case-5',
            ],
            'b2b-account-1' => [
                'portal-case-1',
                'portal-case-8',
            ],
            'b2b-account-2' => [
                'portal-case-2',
                'portal-case-3',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordIdList as $recordId) {
                $args = [
                    'module' => 'Cases',
                    'record' => $recordId,
                ];

                $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('id', $response);
                $this->assertEquals($response['id'], $recordId);
            }
        }
    }

    public function testCantViewNotAllowedCaseB2C()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);

        $args = [
            'module' => 'Cases',
            'record' => 'portal-case-1',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCantViewNotAllowedCaseB2B()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Cases',
            'record' => 'portal-case-2',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCantViewNotAllowedCaseB2BIfDisabled()
    {
        $this->configCaseVisibility();
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Cases',
            'record' => 'portal-case-8',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCanViewAllowedBugs()
    {
        $users = [
            'b2c' => [
                'portal-bug-1',
                'portal-bug-2',
            ],
            'b2b-account-1' => [
                'portal-bug-1',
                'portal-bug-2',
            ],
            'b2b-account-2' => [
                'portal-bug-1',
                'portal-bug-2',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordIdList as $recordId) {
                $args = [
                    'module' => 'Bugs',
                    'record' => $recordId,
                ];

                $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('id', $response);
                $this->assertEquals($response['id'], $recordId);
            }
        }
    }

    public function testCantViewNotAllowedBugB2C()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);

        $args = [
            'module' => 'Bugs',
            'record' => 'portal-bug-3',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCantViewNotAllowedBugB2B()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Bugs',
            'record' => 'portal-bug-3',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCanViewAllowedKnowledgebases()
    {
        $users = [
            'b2c' => [
                'portal-kb-1',
                'portal-kb-2',
            ],
            'b2b-account-1' => [
                'portal-kb-1',
                'portal-kb-2',
            ],
            'b2b-account-2' => [
                'portal-kb-1',
                'portal-kb-2',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordIdList as $recordId) {
                $args = [
                    'module' => 'KBContents',
                    'record' => $recordId,
                ];

                $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('id', $response);
                $this->assertEquals($response['id'], $recordId);
            }
        }
    }

    public function testCantViewNotAllowedKnowledgebaseB2C()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);

        $args = [
            'module' => 'KBContents',
            'record' => 'portal-kb-4',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCantViewNotAllowedKnowledgebaseB2B()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'KBContents',
            'record' => 'portal-kb-5',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCanViewAllowedNotes()
    {
        $users = [
            'b2c' => [
                'portal-note-1',
                'portal-note-2',
                'portal-note-4',
                'portal-note-5',
            ],
            'b2b-account-1' => [
                'portal-note-4',
                'portal-note-5',
                'portal-note-6',
                'portal-note-8',
            ],
            'b2b-account-2' => [
                'portal-note-4',
                'portal-note-5',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordIdList as $recordId) {
                $args = [
                    'module' => 'Notes',
                    'record' => $recordId,
                ];

                $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('id', $response);
                $this->assertEquals($response['id'], $recordId);
            }
        }
    }

    public function testCantViewNotAllowedNoteB2C()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);

        $args = [
            'module' => 'Notes',
            'record' => 'portal-note-3',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCantViewNotAllowedNoteB2B()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Notes',
            'record' => 'portal-note-2',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCantViewNotAllowedNoteB2BIfDisabled()
    {
        $this->configCaseVisibility();
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Notes',
            'record' => 'portal-note-8',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCanViewAllowedCategories()
    {
        // find the root system category
        $kb = BeanFactory::newBean('KBContents');
        $kbroot = $kb->getCategoryRoot();

        $users = [
            'b2c' => [
                $kbroot,
                'portal-category-1',
                'portal-category-2',
            ],
            'b2b-account-1' => [
                $kbroot,
                'portal-category-1',
                'portal-category-2',
            ],
            'b2b-account-2' => [
                $kbroot,
                'portal-category-1',
                'portal-category-2',
            ],
        ];

        foreach ($users as $userKey => $recordIdList) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordIdList as $recordId) {
                $args = [
                    'module' => 'Categories',
                    'record' => $recordId,
                ];

                $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('id', $response);
                $this->assertEquals($response['id'], $recordId);
            }
        }
    }

    public function testCantViewNotAllowedCategoryB2C()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2c']);

        $args = [
            'module' => 'Categories',
            'record' => 'portal-category-3',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testCantViewNotAllowedCategoryB2B()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Categories',
            'record' => 'portal-category-3',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new ModulePortalApi())->retrieveRecord(self::$service, $args);
    }

    public function testNotesRelateApiVisibleBugs()
    {
        $users = [
            'b2c' => [
                'portal-bug-1' => [
                    'portal-note-5',
                ],
                'portal-bug-2' => [],
            ],
            'b2b-account-1' => [
                'portal-bug-1' => [
                    'portal-note-5',
                ],
                'portal-bug-2' => [],
            ],
            'b2b-account-2' => [
                'portal-bug-1' => [
                    'portal-note-5',
                ],
                'portal-bug-2' => [],
            ],
        ];

        foreach ($users as $userKey => $recordData) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordData as $recordId => $relatedNotesIds) {
                $args = [
                    'module' => 'Bugs',
                    'record' => $recordId,
                    'link_name' => 'notes',
                ];

                $response = (new RelateApi())->filterRelated(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('next_offset', $response);
                $this->assertArrayHasKey('records', $response);
                $this->assertIsArray($response['records']);
                $this->assertSameSize($response['records'], $relatedNotesIds);
                foreach ($response['records'] as $record) {
                    $this->assertContains($record['id'], $relatedNotesIds);
                }
            }
        }
    }

    public function testNotesRelateApiNotVisibleBugs()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Bugs',
            'record' => 'portal-bug-3',
            'link_name' => 'notes',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new RelateApi())->filterRelated(self::$service, $args);
    }

    public function testNotesRelateApiVisibleCases()
    {
        $users = [
            'b2c' => [
                'portal-case-3' => [],
                'portal-case-5' => [
                    'portal-note-2',
                ],
            ],
            'b2b-account-1' => [
                'portal-case-1' => [
                    'portal-note-6',
                ],
            ],
            'b2b-account-2' => [
                'portal-case-2' => [],
                'portal-case-3' => [],
            ],
        ];

        foreach ($users as $userKey => $recordData) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordData as $recordId => $relatedNotesIds) {
                $args = [
                    'module' => 'Cases',
                    'record' => $recordId,
                    'link_name' => 'notes',
                ];

                $response = (new RelateApi())->filterRelated(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('next_offset', $response);
                $this->assertArrayHasKey('records', $response);
                $this->assertIsArray($response['records']);
                $this->assertSameSize($response['records'], $relatedNotesIds);
                foreach ($response['records'] as $record) {
                    $this->assertContains($record['id'], $relatedNotesIds);
                }
            }
        }
    }

    public function testNotesRelateApiNotVisibleCases()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'Cases',
            'record' => 'visible-b2c-1',
            'link_name' => 'notes',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new RelateApi())->filterRelated(self::$service, $args);
    }

    public function testNotesRelateApiVisibleKnowledgebases()
    {
        $users = [
            'b2c' => [
                'portal-kb-1' => [],
                'portal-kb-2' => [
                    'portal-note-4',
                ],
            ],
            'b2b-account-1' => [
                'portal-kb-1' => [],
                'portal-kb-2' => [
                    'portal-note-4',
                ],
            ],
            'b2b-account-2' => [
                'portal-kb-1' => [],
                'portal-kb-2' => [
                    'portal-note-4',
                ],
            ],
        ];

        foreach ($users as $userKey => $recordData) {
            self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds[$userKey]);

            foreach ($recordData as $recordId => $relatedNotesIds) {
                $args = [
                    'module' => 'KBContents',
                    'record' => $recordId,
                    'link_name' => 'notes',
                ];

                $response = (new RelateApi())->filterRelated(self::$service, $args);
                $this->assertNotEmpty($response);
                $this->assertIsArray($response);
                $this->assertArrayHasKey('next_offset', $response);
                $this->assertArrayHasKey('records', $response);
                $this->assertIsArray($response['records']);
                $this->assertSameSize($response['records'], $relatedNotesIds);
                foreach ($response['records'] as $record) {
                    $this->assertContains($record['id'], $relatedNotesIds);
                }
            }
        }
    }

    public function testNotesRelateApiNotVisibleKnowledgebases()
    {
        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contactIds['b2b-account-1']);

        $args = [
            'module' => 'KBContents',
            'record' => 'portal-kb-4',
            'link_name' => 'notes',
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $response = (new RelateApi())->filterRelated(self::$service, $args);
    }
}
