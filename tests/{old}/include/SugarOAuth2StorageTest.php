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

class SugarOAuth2StorageTest extends RestTestPortalBase
{
    public $previousPortalLicense;
    private $sessionType;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE c_key = 'support_portal'");
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

        parent::setUp();

        $admin = new Administration();

        if (!isset($admin->settings['license_num_portal_users'])) {
            $admin->settings['license_num_portal_users'] = 50;
            $admin->saveSetting('license', 'num_portal_users', '50');
        }


        $admin->retrieveSettings('system');
        if (!isset($admin->settings['system_session_timeout'])) {
            $session_timeout = abs(ini_get('session.gc_maxlifetime'));
            $admin->saveSetting('system', 'session_timeout', $session_timeout);
        }

        $admin->retrieveSettings('license');
        $admin->settings['license_enforce_portal_user_limit'] = '1';
        $admin->saveSetting('license', 'enforce_portal_user_limit', '1');

        $admin->retrieveSettings(false, true);
        sugar_cache_clear('admin_settings_cache');

        // When setting $_SESSION['platform'] to portal we are also, inadvertently
        // setting $_SESSION['type'] to 'support_portal' in the setPlatformStore()
        // call. This was lasting beyond this test and causing failure downstream
        // in full suite runs.
        if (!empty($_SESSION['type'])) {
            $this->sessionType = $_SESSION['type'];
        }
    }

    protected function tearDown(): void
    {
        // Handle session 'type' resetting
        if (!empty($this->sessionType)) {
            $_SESSION['type'] = $this->sessionType;
        } else {
            unset($_SESSION['type']);
        }

        // Reset the portal login license to previous numbers, if we have it
        if (isset($this->previousPortalLicense)) {
            $GLOBALS['db']->query("UPDATE config SET value = '" . $this->previousPortalLicense . "' WHERE name = 'num_portal_users'");
            sugar_cache_clear('admin_settings_cache');
        }

        $GLOBALS['db']->query('DELETE FROM session_active');

        SugarTestHelper::tearDown();

        parent::tearDown();
        $_SESSION = [];
    }

    /**
     * @group bug57572
     */
    public function testPortalInactiveErrorActive()
    {
        $contact1 = BeanFactory::newBean('Contacts');
        $contact1->first_name = 'UNIT';
        $contact1->last_name = 'UNIT1';
        $contact1->portal_active = true;
        $contact1->portal_name = 'unittestportal1';
        $contact1->portal_password = User::getPasswordHash('unittestportal1');
        $contact1->save();
        $this->contacts[] = $contact1;

        $storage = new SugarOAuth2Storage();
        $storage->setPlatform('portal');
        $res = $storage->checkUserCredentials('support_portal', 'unittestportal1', 'unittestportal1');
        $this->assertNotEmpty($res, 'Client credentials did not validate');
    }

    /**
     * @group bug57572
     */
    public function testPortalInactiveErrorInactive()
    {
        $contact2 = BeanFactory::newBean('Contacts');
        $contact2->first_name = 'portal';
        $contact2->last_name = 'inactive';
        $contact2->portal_active = false;
        $contact2->portal_name = 'unittestportal2';
        $contact2->portal_password = User::getPasswordHash('unittestportal2');
        $contact2->save();
        $this->contacts[] = $contact2;

        $storage = new SugarOAuth2Storage();
        $storage->setPlatform('portal');

        $this->expectException(SugarApiExceptionNeedLogin::class);
        $storage->checkUserCredentials('support_portal', 'unittestportal2', 'unittestportal2');
    }

    public function testTooManyUsers()
    {
        $ret = $GLOBALS['db']->query("SELECT value FROM config WHERE name = 'num_portal_users'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->previousPortalLicense = $row['value'];

        $contact1 = BeanFactory::newBean('Contacts');
        $contact1->first_name = 'UNIT';
        $contact1->last_name = 'TEST1';
        $contact1->portal_active = true;
        $contact1->portal_name = 'unittestportal1';
        $contact1->portal_password = User::getPasswordHash('unittestportal1');
        $contact1->save();
        $this->contacts[] = $contact1;

        $contact2 = BeanFactory::newBean('Contacts');
        $contact2->first_name = 'UNIT';
        $contact2->last_name = 'TEST2';
        $contact2->portal_active = true;
        $contact2->portal_name = 'unittestportal2';
        $contact2->portal_password = User::getPasswordHash('unittestportal2');
        $contact2->save();
        $this->contacts[] = $contact2;

        $contact3 = BeanFactory::newBean('Contacts');
        $contact3->first_name = 'UNIT';
        $contact3->last_name = 'TEST3';
        $contact3->portal_active = true;
        $contact3->portal_name = 'unittestportal3';
        $contact3->portal_password = User::getPasswordHash('unittestportal3');
        $contact3->save();
        $this->contacts[] = $contact3;

        $storage = new SugarOAuth2Storage();

        $GLOBALS['db']->query("UPDATE config SET value = '1' WHERE name = 'num_portal_users'");
        $admin = new Administration();

        if (!isset($admin->settings['license_num_portal_users'])) {
            $admin->settings['license_num_portal_users'] = 1;
            $admin->saveSetting('license', 'num_portal_users', '1');
        }

        sugar_cache_clear('admin_settings_cache');
        $admin->retrieveSettings(false, true);
        sugar_cache_clear('admin_settings_cache');

        // While we can be clever about this, for a unit test we're just going to act dumb, clear out all portal sessions to make sure
        // that we have an accurate test.
        $GLOBALS['db']->query('DELETE FROM session_active');

        // For some reason this really wants a remote address set
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // SESSION platform needs to be set because it is expected for portal
        // storage. This is usually set in the API before any calls to storage,
        // OR it is set by the storage once it reads the platform type
        //
        // NOTE: This will set $_SESSION['type'] to 'support_portal' in the
        // getPlatformStore() method. That was causing failures later on downstream.
        $_SESSION['platform'] = 'portal';

        // First login should work.
        $firstCheck = $storage->checkUserCredentials('support_portal', 'unittestportal1', 'unittestportal1');
        $storage->setAccessToken(create_guid(), 'support_portal', $contact1->id, time() + 30, null);

        // Second login is borderline, but we let them pass because we are nice.
        $firstCheck = $storage->checkUserCredentials('support_portal', 'unittestportal2', 'unittestportal2');
        $storage->setAccessToken(create_guid(), 'support_portal', $contact2->id, time() + 30, null);

        try {
            // Third login is time to fail
            $firstCheck = $storage->checkUserCredentials('support_portal', 'unittestportal3', 'unittestportal3');
            $storage->setAccessToken(create_guid(), 'support_portal', $contact3->id, time() + 30, null);


            $errorLabel = 'no_error';
        } catch (SugarApiException $e) {
            $errorLabel = $e->messageLabel;
        }

        // We need to make sure this errored out here
        $this->assertEquals('too_many_concurrent_connections', $errorLabel);
    }
}
