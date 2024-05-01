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

class RestLoginTest extends RestTestBase
{
    public $contact;
    public $apiuser;

    protected function setUp(): void
    {
        // Start out with a fake auth token to prevent _restCall from auto logging in
        $this->authToken = 'LOGGING_IN';

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE id LIKE 'UNIT%'");
        $GLOBALS['db']->query("DELETE FROM oauth_tokens WHERE consumer LIKE '_unit_%'");
        if (isset($this->contact->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '" . $this->contact->id . "'");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c = '" . $this->contact->id . "'");
            }
        }
        if (isset($this->apiuser->id)) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '" . $this->apiuser->id . "'");
            if ($GLOBALS['db']->tableExists('users_cstm')) {
                $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c = '" . $this->apiuser->id . "'");
            }
        }

        $system_config = new Administration();
        $system_config->saveSetting('supportPortal', 'RegCreatedBy', '');
        $system_config->saveSetting('portal', 'on', 0);

        $GLOBALS['db']->commit();
    }

    /**
     * @group rest
     */
    public function testRestLoginUser()
    {
        $args = [
            'grant_type' => 'password',
            'username' => $this->user->user_name,
            'password' => $this->user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ];

        $reply = $this->restCall('oauth2/token', json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'], $reply['reply']['refresh_token']);
        $this->assertEquals('bearer', $reply['reply']['token_type']);

        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->restCall('ping');
        $this->assertEquals('pong', $replyPing['reply']);
    }

    /**
     * @group rest
     */
    public function testRestLoginUserInvalidGrant()
    {
        $args = [
            'grant_type' => 'password',
            'username' => $this->user->user_name,
            'password' => $this->user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ];

        $reply = $this->restCall('oauth2/token', json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'], $reply['reply']['refresh_token']);
        $this->assertEquals('bearer', $reply['reply']['token_type']);

        $this->authToken = 'this-is-not-a-token';
        $replyPing = $this->restCall('ping');

        $this->assertEquals($replyPing['reply']['error'], 'invalid_grant');
    }

    /**
     * @group rest
     */
    public function testRestOauthViaGet()
    {
        $args = [
            'grant_type' => 'password',
            'username' => $this->user->user_name,
            'password' => $this->user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ];

        $reply = $this->restCall('oauth2/token', json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'], $reply['reply']['refresh_token']);
        $this->assertEquals('bearer', $reply['reply']['token_type']);

        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->restCall('ping');
        $this->assertEquals('pong', $replyPing['reply']);

        $this->authToken = 'LOGGING_IN';
        $replyPing2 = $this->restCall('ping?oauth_token=' . $reply['reply']['access_token']);
        $this->assertEquals('pong', $replyPing2['reply']);
    }


    /**
     * @group rest
     */
    public function testRestLoginUserAutocreateKey()
    {
        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE c_key = 'sugar'");
        $GLOBALS['db']->commit();
        $args = [
            'grant_type' => 'password',
            'username' => $this->user->user_name,
            'password' => $this->user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
        ];

        $reply = $this->restCall('oauth2/token', json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'], $reply['reply']['refresh_token']);
        $this->assertEquals('bearer', $reply['reply']['token_type']);

        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->restCall('ping');
        $this->assertEquals('pong', $replyPing['reply']);
    }


    /**
     * @group rest
     */
    public function testRestLoginCustomIdUser()
    {
        // Create a unit test login ID
        $consumer = BeanFactory::newBean('OAuthKeys');
        $consumer->id = 'UNIT-TEST-regularlogin';
        $consumer->new_with_id = true;
        $consumer->c_key = '_unit_regularlogin';
        $consumer->c_secret = '';
        $consumer->oauth_type = 'oauth2';
        $consumer->client_type = 'user';
        $consumer->save();

        $GLOBALS['db']->commit();

        $args = [
            'grant_type' => 'password',
            'username' => $this->user->user_name,
            'password' => $this->user->user_name,
            'client_id' => $consumer->c_key,
            'client_secret' => '',
            'platform' => 'base',
        ];

        $reply = $this->restCall('oauth2/token', json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'], $reply['reply']['refresh_token']);
        $this->assertEquals('bearer', $reply['reply']['token_type']);

        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->restCall('ping');
        $this->assertEquals('pong', $replyPing['reply']);
    }

    /**
     * @group rest
     */
    public function testRestLoginRefresh()
    {
        $args = [
            'grant_type' => 'password',
            'username' => $this->user->user_name,
            'password' => $this->user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ];

        $reply = $this->restCall('oauth2/token', json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'], $reply['reply']['refresh_token']);
        $this->assertEquals('bearer', $reply['reply']['token_type']);

        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->restCall('ping');
        $this->assertEquals('pong', $replyPing['reply']);

        $refreshToken = $reply['reply']['refresh_token'];


        $args = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ];

        // Prevents _restCall from automatically logging in
        $this->authToken = 'LOGGING_IN';
        $reply2 = $this->restCall('oauth2/token', json_encode($args));
        // if ( empty($reply2['reply']['access_token']) ) { print_r($reply2); }
        $this->assertNotEmpty($reply2['reply']['access_token']);
        $this->assertNotEmpty($reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply2['reply']['access_token'], $reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'], $reply2['reply']['access_token']);
        $this->assertNotEquals($reply['reply']['refresh_token'], $reply2['reply']['refresh_token']);
        $this->assertEquals('bearer', $reply2['reply']['token_type']);

        $this->authToken = $reply2['reply']['access_token'];
        $replyPing = $this->restCall('ping');
        $this->assertEquals('pong', $replyPing['reply']);
    }

    /**
     * @group rest
     */
    public function testBadLogin()
    {
        $args = [
            'grant_type' => 'password',
            'username' => $this->user->user_name,
            'password' => 'this is not the correct password',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => 'base',
        ];

        $reply = $this->restCall('oauth2/token', json_encode($args));
        $this->assertNotEmpty($reply['reply']['error']);
        $this->assertEquals('need_login', $reply['reply']['error']);
    }
}
