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

class RestTestPortalBase extends RestTestBase
{
    /**
     * @var \ACLRole|mixed
     */
    public $role;
    /**
     * @var \SugarBean|null
     */
    public $portalGuy;
    public $oldPortal;
    protected $currentPortalBean = null;
    protected $testConsumer = null;
    protected $originalSetting = [];

    /**
     * @var Contact
     */
    protected $contact;

    protected function setUp(): void
    {
        // Setup the original settings
        $system_config = Administration::getSettings();

        if (isset($system_config->settings['supportPortal_RegCreatedBy'])) {
            $this->originalSetting['portaluserid'] = $system_config->settings['supportPortal_RegCreatedBy'];
        }

        if (isset($system_config->settings['portal_on'])) {
            $this->originalSetting['portalon'] = $system_config->settings['portal_on'];
        }

        parent::setUp();

        // Make the current user a portal only user
        $this->user->portal_only = '1';
        $this->user->save();

        // Reset the support portal user id to the newly created user id
        $system_config->saveSetting('supportPortal', 'RegCreatedBy', $this->user->id);

        $portalConfig = new ParserModifyPortalConfig();
        $this->role = $portalConfig->getPortalACLRole();
        if (!($this->user->check_role_membership($this->role->name))) {
            $this->user->load_relationship('aclroles');
            $this->user->aclroles->add($this->role);
            $this->user->save();
        }

        // A little bit destructive, but necessary.
        $GLOBALS['db']->query("DELETE FROM contacts WHERE portal_name = 'unittestportal'");

        // Create the portal contact
        $this->contact = BeanFactory::newBean('Contacts');
        // Make the contact id unique-ish for test runs
        $this->contact->id = 'UNIT-TEST-' . create_guid_section(10);
        $this->contact->new_with_id = true;
        $this->contact->first_name = 'Little';
        $this->contact->last_name = 'Unittest';
        $this->contact->description = 'Little Unittest';
        $this->contact->portal_name = 'unittestportal';
        $this->contact->portal_active = '1';
        $this->contact->portal_password = User::getPasswordHash('unittest');
        $this->contact->assigned_user_id = $this->user->id;
        $this->contact->save();

        $this->portalGuy = $this->contact;

        // Adding it to the contacts array makes sure it gets deleted when done
        $this->contacts[] = $this->contact;

        // Add the support_portal oauth key
        $this->testConsumer = BeanFactory::newBean('OAuthKeys');

        // use consumer to find bean with client_type === support portal
        $this->currentPortalBean = BeanFactory::newBean('OAuthKeys');
        $this->currentPortalBean->getByKey('support_portal', 'oauth2');
        $this->currentPortalBean->new_with_id = true;

        $GLOBALS['db']->query('DELETE FROM ' . $this->testConsumer->table_name . " WHERE client_type = 'support_portal'");

        // Create a unit test login ID
        $this->testConsumer->id = 'UNIT-TEST-portallogin';
        $this->testConsumer->new_with_id = true;
        $this->testConsumer->c_key = 'support_portal';
        $this->testConsumer->c_secret = '';
        $this->testConsumer->oauth_type = 'oauth2';
        $this->testConsumer->client_type = 'support_portal';
        $this->testConsumer->save();

        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        global $db;
        // Re-enable the old portal users
        if (isset($this->oldPortal)) {
            $portalIds = "('" . implode("','", $this->oldPortal) . "')";
            $db->query("UPDATE users SET deleted = '0' WHERE id IN {$portalIds}");
        }


        // Delete test support_portal user
        $db->query('DELETE FROM ' . $this->testConsumer->table_name . " WHERE client_type = 'support_portal'");

        $this->cleanUpRecords();

        // Add back original support_portal user
        if (!empty($this->currentPortalBean->id)) {
            $this->currentPortalBean->save();
        }

        // reset the config table back to what it was originally, default if nothing was there
        $portalUserId = $this->originalSetting['portaluserid'] ?? '';
        $portalOn = empty($this->originalSetting['portalon']) ? '0' : '1';

        $system_config = new Administration();

        $system_config->saveSetting('supportPortal', 'RegCreatedBy', $portalUserId);
        $system_config->saveSetting('portal', 'on', $portalOn);

        $GLOBALS['db']->commit();
        parent::tearDown();
    }

    protected function restLogin($username = '', $password = '', $platform = 'base')
    {
        $args = [
            'grant_type' => 'password',
            'username' => 'unittestportal',
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        ];

        // Prevent an infinite loop, put a fake authtoken in here.
        $this->authToken = 'LOGGING_IN';

        $reply = $this->restCall('oauth2/token', json_encode($args));

        $this->assertNotEmpty(
            $reply['reply']['access_token'],
            'REST authentication failed, the response looked like: ' . var_export($reply, true)
        );

        $this->authToken = $reply['reply']['access_token'];
        $this->refreshToken = $reply['reply']['refresh_token'];
    }

    protected function restLogout()
    {
        if (!empty($this->authToken) && !empty($this->refreshToken)) {
            $args = [
                'token' => $this->authToken,
            ];

            $reply = $this->restCall('oauth2/logout', json_encode($args));
            if (!isset($reply['reply']['success'])) {
                throw new Exception('Rest logout failed, message looked like: ' . $reply['replyRaw']);
            }
        }
    }
}
