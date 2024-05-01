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
class CurrentUserPortalApiTest extends TestCase
{
    /**
     * @var CurrentUserPortalApi
     */
    public static $currentUserApi;

    /**
     * @var Contact
     */
    public static $contact;

    /**
     * @var ServiceBase
     */
    public static $service;

    /**
     * @var nameFormat
     */
    public static $nameFormat;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        self::$nameFormat = $GLOBALS['sugar_config']['default_locale_name_format'];
        $GLOBALS['sugar_config']['default_locale_name_format'] = 's f l';

        SugarTestPortalUtilities::enablePortal();
        SugarTestPortalUtilities::storeOriginalUser();

        self::$contact = SugarTestContactUtilities::createContact(
            '',
            [
                'first_name' => 'testfirst',
                'last_name' => 'testlast',
                'picture' => 'testpicture',
                'portal_active' => 1,
                'portal_name' => 'testportal',
                'disable_custom_fields' => true,
            ]
        );

        $_SESSION['contact_id'] = self::$contact->id;

        self::$currentUserApi = new CurrentUserPortalApi();
        self::$currentUserApi->portal_contact = self::$contact;

        self::$service = SugarTestPortalUtilities::loginAsPortalUser(self::$contact->id);
    }

    public static function tearDownAfterClass(): void
    {
        $GLOBALS['sugar_config']['default_locale_name_format'] = self::$nameFormat;

        self::$service = null;
        self::$currentUserApi = null;
        self::$contact = null;

        SugarTestPortalUtilities::restoreOriginalUser();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestPortalUtilities::disablePortal();
        SugarTestHelper::tearDown();
    }

    /**
     * Test api method retrieveCurrentUser
     */
    public function testRetrieveCurrentUser()
    {
        $result = self::$currentUserApi->retrieveCurrentUser(self::$service, []);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('current_user', $result);
        $this->assertIsArray($result['current_user']);
        $this->assertArrayHasKey('preferences', $result['current_user']);
        $this->assertArrayHasKey('module_list', $result['current_user']);
        $this->assertArrayHasKey('type', $result['current_user']);
        $this->assertArrayHasKey('user_id', $result['current_user']);
        $this->assertArrayHasKey('user_name', $result['current_user']);
        $this->assertArrayHasKey('id', $result['current_user']);
        $this->assertArrayHasKey('full_name', $result['current_user']);
        $this->assertArrayHasKey('picture', $result['current_user']);
        $this->assertArrayHasKey('portal_name', $result['current_user']);
    }

    /**
     * @return array
     */
    public function updateCurrentUserDataProvider()
    {
        return [
            // portal name
            [
                [
                    'portal_name' => 'TEST_ME',
                ],
                [
                    'portal_name' => 'TEST_ME',
                ],
            ],
            // first/last names
            [
                [
                    'first_name' => 'test first name',
                    'last_name' => 'test last name',
                ],
                [
                    'full_name' => 'test first name test last name',
                ],
            ],
        ];
    }

    /**
     * Test api method updateCurrentUser
     *
     * @dataProvider updateCurrentUserDataProvider
     */
    public function testUpdateCurrentUser(array $args, array $expected)
    {
        $result = self::$currentUserApi->updateCurrentUser(self::$service, $args);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('current_user', $result);
        $this->assertIsArray($result['current_user']);
        $this->assertArrayHasKey('preferences', $result['current_user']);
        $this->assertArrayHasKey('module_list', $result['current_user']);
        $this->assertArrayHasKey('type', $result['current_user']);
        $this->assertArrayHasKey('user_id', $result['current_user']);
        $this->assertArrayHasKey('user_name', $result['current_user']);
        $this->assertArrayHasKey('id', $result['current_user']);
        $this->assertArrayHasKey('full_name', $result['current_user']);
        $this->assertArrayHasKey('picture', $result['current_user']);
        $this->assertArrayHasKey('portal_name', $result['current_user']);

        foreach ($expected as $k => $v) {
            $this->assertArrayHasKey($k, $result['current_user']);
            $this->assertEquals($v, $result['current_user'][$k]);
        }
    }

    /**
     * Tests current user for using picture from contact
     */
    public function testContactPicture()
    {
        $result = self::$currentUserApi->retrieveCurrentUser(self::$service, []);

        $this->assertArrayHasKey('picture', $result['current_user']);
        $this->assertNotEmpty($result['current_user']['picture']);
        $this->assertEquals('testpicture', $result['current_user']['picture']);
    }
}
