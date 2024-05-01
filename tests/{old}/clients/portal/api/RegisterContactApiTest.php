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
 *  Prepare Register Contact Api.
 */
class RegisterContactApiTest extends TestCase
{
    private $admin;
    private $old_defaultUser;

    protected function setUp(): void
    {
        $this->admin = Administration::getSettings(false, true);
        $this->old_defaultUser = $this->admin->settings['portal_defaultUser'] ?? null;
    }

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
    }

    protected function tearDown(): void
    {
        if ($this->old_defaultUser !== null) {
            $this->admin->saveSetting('portal', 'defaultUser', $this->old_defaultUser, 'support');
        }
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test createContactRecord() to see if fields are set properly
     *
     * @param string|int $defaultUserId The default user Id
     * @dataProvider createContactProvider
     */
    public function testCreateContact($defaultUserId)
    {
        $api = new RegisterContactApi();
        $rest = SugarTestRestUtilities::getRestServiceMock();

        // a particular user is used for portal default user
        $this->admin->saveSetting('portal', 'defaultUser', $defaultUserId, 'support');
        $result = $api->createContactRecord(
            $rest,
            [
                'last_name' => 'lastName',
                'portal_user_company_name' => 'companyName',
                'email' => [
                    0 => ['email_address' => 'a@s.com'],
                ],
                'portal_name' => 'portalName',
                'portal_password' => 'Password123',
            ]
        );
        $this->assertNotEmpty($result);
        $bean = BeanFactory::getBean('Contacts', $result);
        $this->assertEquals('portalName', $bean->portal_name);
        $this->assertEquals(0, $bean->portal_active);
        $this->assertEquals('external', $bean->entry_source);
        $this->assertEquals($defaultUserId, $bean->assigned_user_id);
        $bean->mark_deleted($bean->id);
    }

    public function createContactProvider(): array
    {
        return [
            ['280ecfca-f9ae-11e9-9ca8-6c400895ea84'],
            [1], // admin id is saved as integer in config; other user ids are saved as strings
        ];
    }
}
