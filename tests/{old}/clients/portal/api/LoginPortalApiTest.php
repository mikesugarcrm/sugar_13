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
 * @group ApiTests
 */
class LoginPortalApiTest extends TestCase
{
    /**
     * @var OAuth2Api
     */
    public static $OAuthApi;

    /**
     * @var Contact
     */
    public static $contact;
    public static $contactNoLogin;

    /**
     * @var token data
     */
    public static $tokenData;

    /**
     * @var ServiceBase
     */
    public static $service;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $tmp_user = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(false, 1);
        SugarTestPortalUtilities::enablePortal();
        $GLOBALS['current_user'] = $tmp_user;
        SugarTestPortalUtilities::storeOriginalUser();

        self::$contact = SugarTestContactUtilities::createContact(
            '',
            [
                'first_name' => 'Mike',
                'last_name' => 'Smith',
                'portal_active' => 1,
                'portal_name' => 'msmith',
                'portal_password' => User::getPasswordHash('msmith'),
            ]
        );

        self::$contactNoLogin = SugarTestContactUtilities::createContact(
            '',
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'portal_active' => 0,
                'portal_name' => 'jsmith',
                'portal_password' => User::getPasswordHash('jsmith'),
            ]
        );

        self::$OAuthApi = new OAuth2Api();
        self::$service = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        self::$service = null;
        self::$OAuthApi = null;
        self::$contact = null;
        self::$contactNoLogin = null;
        self::$tokenData = null;

        SugarTestPortalUtilities::restoreOriginalUser();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestPortalUtilities::disablePortal();
        SugarTestHelper::tearDown();
    }

    public function testLoginPortalUserNoAccess()
    {
        $this->expectException(SugarApiExceptionNeedLogin::class);

        $result = self::$OAuthApi->token(self::$service, [
            'grant_type' => 'password',
            'username' => self::$contactNoLogin->portal_name,
            'password' => self::$contactNoLogin->portal_name,
            'client_id' => 'support_portal',
            'platform' => 'portal',
            'client_secret' => '',
            'current_language' => 'en_us',
        ]);
    }

    public function testLoginPortalUserWrongPassword()
    {
        $this->expectException(SugarApiExceptionNeedLogin::class);

        $result = self::$OAuthApi->token(self::$service, [
            'grant_type' => 'password',
            'username' => self::$contact->portal_name,
            'password' => self::$contact->portal_name . 'wrong',
            'client_id' => 'support_portal',
            'platform' => 'portal',
            'client_secret' => '',
            'current_language' => 'en_us',
        ]);
    }

    // The OAuthApi creates a cookie for the download token. There seem to be no way to prevent that from happening, so we have to somehow ignore the errors from phpunit
    // Before we added a @ to ignore the errors from the token method, but now we changed it to instead set the error reporting for the test to 0 to suppress those errors

    public function testLoginPortalUser()
    {
        $this->iniSet('error_reporting', 0);
        $result = self::$OAuthApi->token(self::$service, [
            'grant_type' => 'password',
            'username' => self::$contact->portal_name,
            'password' => self::$contact->portal_name,
            'client_id' => 'support_portal',
            'platform' => 'portal',
            'client_secret' => '',
            'current_language' => 'en_us',
        ]);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertNotEmpty($result['access_token']);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('scope', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertNotEmpty($result['refresh_token']);
        $this->assertArrayHasKey('refresh_expires_in', $result);
        $this->assertArrayHasKey('download_token', $result);

        self::$tokenData = $result;
    }

    public function testLogoutPortalUser()
    {
        $this->iniSet('error_reporting', 0);
        $result = self::$OAuthApi->logout(self::$service, [
            'token' => self::$tokenData['token'],
            'refresh_token' => self::$tokenData['refresh_token'],
        ]);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertNotEmpty($result['success']);
    }
}
