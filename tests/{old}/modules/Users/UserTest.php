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
use Sugarcrm\Sugarcrm\Entitlements\Subscription;

/**
 * @coversDefaultClass User
 */
class UserTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers ::save
     * @covers ::getUsersNameAndEmail
     */
    public function testSave_SystemOverrideConfigurationIsCreatedForTheUser()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        $oe = BeanFactory::newBean('OutboundEmail');

        // Create the user while this setting is 2 so the override configuration is not created yet.
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);
        $user = SugarTestUserUtilities::createAnonymousUser();

        $override = $oe->getUsersMailerForSystemOverride($user->id);
        $this->assertNull($override, 'The override configuration should not exist yet');

        // Update the user while this setting is 0 so the override configuration is created.
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
        $user->save();

        $override = $oe->getUsersMailerForSystemOverride($user->id);
        $userData = $user->getUsersNameAndEmail();
        $emailAddressId = $user->emailAddress->getGuid($userData['email']);
        $this->assertSame($userData['name'], $override->name, 'The names should match');
        $this->assertSame($userData['email'], $override->email_address, 'The email addresses should match');
        $this->assertSame($emailAddressId, $override->email_address_id, 'The email address IDs should match');

        OutboundEmailConfigurationTestHelper::tearDown();
    }

    /**
     * @covers ::save
     * @covers ::getUsersNameAndEmail
     */
    public function testSave_SystemOverrideConfigurationIsUpdatedForTheUser()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        $oe = BeanFactory::newBean('OutboundEmail');

        // Create the user while this setting is 0 so the override configuration exists.
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
        $user = SugarTestUserUtilities::createAnonymousUser();

        $override = $oe->getUsersMailerForSystemOverride($user->id);
        $userData = $user->getUsersNameAndEmail();
        $emailAddressId = $user->emailAddress->getGuid($userData['email']);
        $this->assertSame($userData['name'], $override->name, 'The names should match');
        $this->assertSame($userData['email'], $override->email_address, 'The email addresses should match');
        $this->assertSame($emailAddressId, $override->email_address_id, 'The email address IDs should match');

        // Change the user's name.
        $user->first_name = 'Bill';
        $user->last_name = 'Roth';

        // Change the user's primary email address.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $user->email1 = $address->email_address;
        $user->email2 = $userData['email'];

        // No matter what the setting, the user's override configuration will be updated if it exists.
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);
        $user->save();

        $override = $oe->getUsersMailerForSystemOverride($user->id);
        $userData = $user->getUsersNameAndEmail();
        $this->assertSame($userData['name'], $override->name, 'The names should match');
        $this->assertSame($address->email_address, $override->email_address, 'The email addresses should match');
        $this->assertSame($address->id, $override->email_address_id, 'The email address IDs should match');

        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
    }

    public function testSettingAUserPreference()
    {
        $this->user->setPreference('test_pref', 'dog');

        $this->assertEquals('dog', $this->user->getPreference('test_pref'));
    }

    public function testRemoveAUserPreference()
    {
        $this->user->setPreference('test_pref2', 'DeleteThis');

        $this->assertEquals('DeleteThis', $this->user->getPreference('test_pref2'));

        $this->user->removePreference('test_pref2');

        $this->assertEmpty($this->user->getPreference('test_pref2'));
    }

    public function testGettingSystemPreferenceWhenNoUserPreferenceExists()
    {
        $GLOBALS['sugar_config']['somewhackypreference'] = 'somewhackyvalue';

        $result = $this->user->getPreference('somewhackypreference');

        unset($GLOBALS['sugar_config']['somewhackypreference']);

        $this->assertEquals('somewhackyvalue', $result);
    }

    /**
     * @ticket 42667
     */
    public function testGettingSystemPreferenceWhenNoUserPreferenceExistsForEmailDefaultClient()
    {
        if (isset($GLOBALS['sugar_config']['email_default_client'])) {
            $oldvalue = $GLOBALS['sugar_config']['email_default_client'];
        }
        $GLOBALS['sugar_config']['email_default_client'] = 'somewhackyvalue';

        $result = $this->user->getPreference('email_link_type');

        if (isset($oldvalue)) {
            $GLOBALS['sugar_config']['email_default_client'] = $oldvalue;
        } else {
            unset($GLOBALS['sugar_config']['email_default_client']);
        }

        $this->assertEquals('somewhackyvalue', $result);
    }

    public function testResetingUserPreferences()
    {
        $this->user->setPreference('test_pref', 'dog');

        $this->user->resetPreferences();

        $this->assertNull($this->user->getPreference('test_pref'));
    }

    /**
     * @ticket 36657
     */
    public function testCertainPrefsAreNotResetWhenResetingUserPreferences()
    {
        $this->user->setPreference('ut', '1');
        $this->user->setPreference('timezone', 'GMT');

        $this->user->resetPreferences();

        $this->assertEquals('1', $this->user->getPreference('ut'));
        $this->assertEquals('GMT', $this->user->getPreference('timezone'));
    }

    public function testSavingToMultipleUserPreferenceCategories()
    {
        $this->user->setPreference('test_pref1', 'dog', 0, 'cat1');
        $this->user->setPreference('test_pref2', 'dog', 0, 'cat2');

        $this->user->savePreferencesToDB();

        $this->assertEquals(
            'cat1',
            $GLOBALS['db']->getOne(
                "SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->user->id}' AND category = 'cat1'"
            )
        );

        $this->assertEquals(
            'cat2',
            $GLOBALS['db']->getOne(
                "SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->user->id}' AND category = 'cat2'"
            )
        );
    }

    public function testDeleteUser()
    {
        SugarTestHelper::setUp('current_user', [true, true]);

        $createdUser = SugarTestUserUtilities::createAnonymousUser(true);

        $createdUser->mark_deleted($createdUser->id);

        // reload the user
        $createdUser->retrieve($createdUser->id, true, false);

        $this->assertEquals(1, $createdUser->deleted);
        $this->assertEquals('Inactive', $createdUser->status);
        $this->assertEquals('Terminated', $createdUser->employee_status);
    }

    public function testGetReporteesWithLeafCount()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $subManager2->id;
        $rep3->save();

        //get leaf arrays
        $managerReportees = User::getReporteesWithLeafCount($manager->id);
        $subManager1Reportees = User::getReporteesWithLeafCount($subManager1->id);

        //check normal scenario
        $this->assertEquals('1', $managerReportees[$subManager1->id], 'SubManager leaf count did not match');
        $this->assertEquals('0', $subManager1Reportees[$rep1->id], 'Rep leaf count did not match');

        //now delete one so we can check the delete code.
        $rep1->mark_deleted($rep1->id);
        $rep1->save();

        //first w/o deleted rows
        $managerReportees = User::getReporteesWithLeafCount($manager->id);
        $this->assertEquals(
            '0',
            $managerReportees[$subManager1->id],
            'SubManager leaf count did not match after delete'
        );
        //now with deleted rows
        $managerReportees = User::getReporteesWithLeafCount($manager->id, true);
        $this->assertEquals('1', $managerReportees[$subManager1->id], 'SubManager leaf count did not match');
    }

    /**
     * @group user
     */
    public function testGetReporteesWithLeafCountWithAdditionalFields()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $subManager2->id;
        $rep3->save();

        //get leaf arrays
        $managerReportees = User::getReporteesWithLeafCount($manager->id, false, ['first_name']);

        //check normal scenario
        $this->assertEquals('1', $managerReportees[$subManager1->id]['total'], 'SubManager leaf count did not match');
        $this->assertEquals($subManager1->first_name, $managerReportees[$subManager1->id]['first_name']);
    }

    public function testGetReporteeManagers()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $manager->id;
        $rep3->save();

        $managers = User::getReporteeManagers($manager->id);
        $this->assertEquals('2', count($managers), 'Submanager count did not match');
    }

    public function testGetReporteeReps()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();
        $rep4 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $manager->id;
        $rep2->save();
        $rep3->reports_to_id = $manager->id;
        $rep3->save();

        $rep4->status = 'Inactive';
        $rep4->reports_to_id = $manager->id;
        $rep4->save();

        $reps = User::getReporteeReps($manager->id);
        $this->assertEquals('2', count($reps), 'Rep count did not match');
    }

    public function datProviderForTestGetEmailClientPreference()
    {
        return [
            ['sugar', 'foo', 'sugar'],
            ['', 'foo', 'foo'],
        ];
    }

    /**
     * @dataProvider datProviderForTestGetEmailClientPreference
     */
    public function testGetEmailClientPreference($emailLinkType, $emailDefaultClient, $expected)
    {
        $oc = $this->backUpConfig('email_default_client'); // original client
        $op = $this->user->getPreference('email_link_type'); // original preference
        $os = $this->backUpSession('isMobile'); // original session

        $GLOBALS['sugar_config']['email_default_client'] = $emailDefaultClient;
        $this->user->setPreference('email_link_type', $emailLinkType);
        unset($_SESSION['isMobile']);

        $actual = $this->user->getEmailClientPreference();
        $this->assertEquals($expected, $actual);

        $this->restoreConfig('email_default_client', $oc);
        $this->user->setPreference('email_link_type', $op);
        $this->restoreSession('isMobile', $os);
    }

    public function testGetEmailClientPreference_SessionIsMobile()
    {
        $oc = $this->backUpConfig('email_default_client'); // original client
        $op = $this->user->getPreference('email_link_type'); // original preference
        $os = $this->backUpSession('isMobile'); // original session

        $GLOBALS['sugar_config']['email_default_client'] = 'sugar';
        $this->user->setPreference('email_link_type', 'sugar');
        $_SESSION['isMobile'] = true;

        $expected = 'other';
        $actual = $this->user->getEmailClientPreference();
        $this->assertEquals($expected, $actual, "Should have returned {$expected} when the session is mobile and PRO+");

        $this->restoreConfig('email_default_client', $oc);
        $this->user->setPreference('email_link_type', $op);
        $this->restoreSession('isMobile', $os);
    }

    public function testPrimaryEmailShouldBeCaseInsensitive()
    {
        $this->user->email1 = 'example@example.com';
        $this->assertTrue($this->user->isPrimaryEmail('EXAMPLE@example.com'));
    }

    public function testUserPictureIsEmptyWhenItDoesntExist()
    {
        $this->user->picture = 'thisdoesntexist';
        $this->user->save();

        $tuser = $this->user->retrieve($this->user->id);

        $this->assertEmpty($tuser->picture);
    }

    public function testUserPictureIsSetWhenFileExists()
    {
        touch('upload/test_user_picture');
        $this->user->picture = 'test_user_picture';
        $this->user->save();

        $tuser = $this->user->retrieve($this->user->id);

        $this->assertEquals('test_user_picture', $tuser->picture);

        unlink('upload/test_user_picture');
    }

    public function testSetUserTimezonePreference_GetUserTimeZone_CorrectTimezoneReturned()
    {
        $dateTime = new SugarDateTime('2015-01-01 12:00:00', new DateTimeZone('UTC'));
        $offsetGMT = $dateTime->getOffset() / 60;

        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->setPreference('timezone', 'America/New_York');
        $timezone = $user->getTimeZone();
        $dateTime->setTimeZone($timezone);
        $offsetNY = $dateTime->getOffset() / 60;

        $this->assertEquals(300, ($offsetGMT - $offsetNY), 'Unexpected Timezone returned for User');
    }

    /**
     * @param boolean $isWorkFlowModule The return value of User::isWorkFlowModule
     * @param array $modules Module list returned by getAdminModules
     * @param boolean $expected Expected return value
     *
     * @dataProvider isAdminOrDeveloperForModuleProvider
     * @covers       User::isAdminForModule
     */
    public function testIsAdminForModule($isWorkFlowModule, array $modules, $expected)
    {
        $this->isAdminOrDeveloperForModule(
            'isAdminForModule',
            'getAdminModules',
            $isWorkFlowModule,
            $modules,
            $expected
        );
    }

    /**
     * @return void
     * @throws \Doctrine\DBAL\Exception
     * @covers User::updateLastStates
     */
    public function testUpdateLastStates()
    {
        $platform = 'testPlatform';

        $userMock = $this->createPartialMock('User', ['retrieveLastStates']);
        $userMock->expects($this->once())->method('retrieveLastStates')->with($platform);

        $userMock->updateLastStates([], $platform);
    }

    /**
     * @param boolean $isWorkFlowModule The return value of User::isWorkFlowModule
     * @param array $modules Module list returned by getDeveloperModules
     * @param boolean $expected Expected return value
     *
     * @dataProvider isAdminOrDeveloperForModuleProvider
     * @covers       User::isDeveloperForModule
     */
    public function testIsDeveloperForModule($isWorkFlowModule, array $modules, $expected)
    {
        $this->isAdminOrDeveloperForModule(
            'isDeveloperForModule',
            'getDeveloperModules',
            $isWorkFlowModule,
            $modules,
            $expected
        );
    }

    /**
     * @covers ::getProductCodes
     * @dataProvider providerTestGetProductCodes
     * @param array $productsData the mock data from getProductsData
     * @param array $expected the expected array of product codes
     */
    public function testGetProductCodes(array $productsData, array $expected)
    {
        // Create a User mock
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getProductsData'])
            ->disableOriginalConstructor()
            ->getMock();

        // Mock the User's getProductsData to use our mock data
        $userMock->expects($this->once())
            ->method('getProductsData')
            ->willReturn($productsData);

        $result = $userMock->getProductCodes();
        $this->assertEquals($expected, $result);
    }

    /**
     * Provides various scenarios of data formatted from getProductsData
     *
     * @return array[]
     */
    public function providerTestGetProductCodes()
    {
        return [
            [
                [
                    'FAKE_PRODUCT' => [
                        'product_code' => 'FAKE',
                        'product_name' => 'Fake Product',
                    ],
                ],
                ['FAKE'],
            ],
            [
                [
                    'FAKE_PRODUCT' => [
                        'product_code' => 'FAKE',
                        'product_name' => 'Fake Product',
                    ],
                    'FAKE_PRODUCT_2' => [
                        'product_code' => 'FAKE_2',
                        'product_name' => 'Fake Product 2',
                    ],
                ],
                ['FAKE', 'FAKE_2'],
            ],
            [
                [
                    'FAKE_PRODUCT' => [
                        'product_code' => 'FAKE',
                        'product_name' => 'Fake Product',
                    ],
                    'FAKE_PRODUCT_2' => [
                        'product_code' => 'FAKE_2',
                        'product_name' => 'Fake Product 2',
                    ],
                    'DUPLICATE_FAKE_PRODUCT_2' => [
                        'product_code' => 'FAKE_2',
                        'product_name' => 'Fake Product 2',
                    ],
                ],
                ['FAKE', 'FAKE_2'],
            ],
        ];
    }

    /**
     * @covers ::getProductsData
     * @dataProvider dataProviderTestGetProductsData
     * @param $subscriptionKeys
     * @param $expected
     */
    public function testGetProductsData($subscriptionKeys, $expected)
    {
        global $current_user;

        // Mock the SubscriptionManager
        $smMock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Entitlements\SubscriptionManager::class)
            ->onlyMethods(['getTopLevelUserSubscriptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $smMock->expects($this->once())
            ->method('getTopLevelUserSubscriptions')
            ->willReturn($subscriptionKeys);

        // Mock the User to use our SubscriptionManager mock
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getSubscriptionManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $userMock->expects($this->once())
            ->method('getSubscriptionManager')
            ->willReturn($smMock);

        $result = SugarTestReflection::callProtectedMethod($userMock, 'getProductsData', [$current_user]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Provides various license scenarios for testGetProducts
     *
     * @return array[]
     */
    public function dataProviderTestGetProductsData()
    {
        global $sugar_flavor;

        return [
            // Basic license
            [
                [
                    Subscription::SUGAR_BASIC_KEY,
                ],
                [
                    Subscription::SUGAR_BASIC_KEY => [
                        'product_code' => $sugar_flavor,
                        'product_name' => User::getLicenseTypeDescription(Subscription::SUGAR_BASIC_KEY),
                    ],
                ],
            ],
            // Sell Premier + Serve licenses
            [
                [
                    Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY,
                    Subscription::SUGAR_SERVE_KEY,
                ],
                [
                    Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY => [
                        'product_code' => Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY,
                        'product_name' => User::getLicenseTypeDescription(Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY),
                    ],
                    Subscription::SUGAR_SERVE_KEY => [
                        'product_code' => Subscription::SUGAR_SERVE_KEY,
                        'product_name' => User::getLicenseTypeDescription(Subscription::SUGAR_SERVE_KEY),
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function hashesAreUnique()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $this->assertNotEquals($user1->getUserMDHash(), $user2->getUserMDHash());
    }

    /**
     * @param string $testMethod Method to be tested
     * @param string $getModules Method that returns module list
     * @param boolean $isWorkFlowModule The return value of User::isWorkFlowModule
     * @param array $modules Module list returned by $getModules
     * @param boolean $expected Expected return value
     */
    private function isAdminOrDeveloperForModule($testMethod, $getModules, $isWorkFlowModule, array $modules, $expected)
    {
        /** @var User|MockObject $user */
        $user = $this->getMockBuilder('User')
            ->setMethods([$getModules, 'isWorkFlowModule'])
            ->disableOriginalConstructor()
            ->getMock();
        $user->id = 'TEST';

        $user->expects($this->any())
            ->method($getModules)
            ->will($this->returnValue($modules));

        $module = 'SomeModule';
        $user->expects($this->any())
            ->method('isWorkFlowModule')
            ->with($module)
            ->will($this->returnValue($isWorkFlowModule));

        $this->assertEquals($expected, $user->$testMethod($module));
    }

    /**
     * @group BR-1721
     */
    public function testUpdateLastLogin()
    {
        $now = TimeDate::getInstance()->nowDb();

        $last_login = $this->user->updateLastLogin();

        $this->assertEquals($now, $last_login);
    }

    public function isAdminOrDeveloperForModuleProvider()
    {
        return [
            // current module is a workflow module, but there are no developer or admin modules
            [
                true,
                [],
                false,
            ],
            // there are developer or admin modules, but current module is not a workflow module
            [
                false,
                ['Accounts'],
                false,
            ],
            // current module is a workflow module, and there are developer or admin modules
            [
                true,
                ['Accounts'],
                true,
            ],
        ];
    }

    public function testSetPreferenceFieldDefaults()
    {
        $mockFields = [
            'field1' => [
                'name' => 'field1',
                'user_preference' => 'true',
            ],
            'field2' => [
                'name' => 'field2',
                'user_preference' => 'true',
                'default' => 'already set!',
            ],
            'field3' => [
                'name' => 'field3',
            ],
        ];

        $mockUser = $this->getMockBuilder(User::class)
            ->onlyMethods(['getPreferenceHelper'])
            ->getMock();

        $mockPreferenceHelper = $this->getMockBuilder(UserPreferenceFieldsHelper::class)
            ->onlyMethods(['getPreferenceField'])
            ->getMock();
        $mockPreferenceHelper->method('getPreferenceField')
            ->willReturnMap([
                [$mockUser, 'field1', 'field1Value'],
            ]);
        $mockUser->method('getPreferenceHelper')->willReturn($mockPreferenceHelper);

        $actual = $mockUser->setPreferenceFieldDefaults($mockFields);
        $this->assertEquals('field1Value', $actual['field1']['default']);
        $this->assertEquals('already set!', $actual['field2']['default']);
        $this->assertTrue(!isset($actual['field3']['default']));
    }

    /**
     * @covers ::patchVardefs
     */
    public function testPatchVardefs()
    {
        global $current_user;

        $vardefs = [
            'fields' => [
                'mail_credentials' => [
                    'name' => 'mail_credentials',
                ],
            ],
        ];

        $outboundEmailConfiguration = $this->getMockBuilder(OutboundSmtpEmailConfiguration::class)
            ->setConstructorArgs([$current_user])
            ->onlyMethods([])
            ->getMock();
        $outboundEmailConfiguration->setHost('fakeHost');
        $outboundEmailConfiguration->setAuthType('oauth2');
        $outboundEmailConfiguration->setSmtpType('exchange_online');

        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setPreferenceFieldDefaults', 'getSystemMailerConfiguration', 'systemOverrideMailCredentialsRequired'])
            ->getMock();
        $userMock->method('getSystemMailerConfiguration')
            ->willReturn($outboundEmailConfiguration);
        $userMock->method('systemOverrideMailCredentialsRequired')
            ->willReturn(true);
        $userMock->expects($this->once())
            ->method('setPreferenceFieldDefaults')->willReturnCallback(function ($vardefs) {
                return $vardefs;
            });

        $result = $userMock->patchVardefs($vardefs);

        error_log(print_r($result, true));

        $this->assertEquals('fakeHost', $result['fields']['mail_credentials']['default']['mail_smtpserver']);
        $this->assertEquals('oauth2', $result['fields']['mail_credentials']['default']['mail_authtype']);
        $this->assertEquals('exchange_online', $result['fields']['mail_credentials']['default']['mail_smtptype']);
    }

    private function backUpConfig($name)
    {
        $config = null;

        if (isset($GLOBALS['sugar_config'][$name])) {
            $config = $GLOBALS['sugar_config'][$name];
        }

        return $config;
    }

    private function restoreConfig($name, $value = null)
    {
        if (!is_null($value)) {
            $GLOBALS['sugar_config'][$name] = $value;
        } else {
            unset($GLOBALS['sugar_config'][$name]);
        }
    }

    private function backUpSession($name)
    {
        $session = null;

        if (isset($_SESSION[$name])) {
            $session = $_SESSION[$name];
        }

        return $session;
    }

    private function restoreSession($name, $value = null)
    {
        if (!is_null($value)) {
            $_SESSION[$name] = $value;
        } else {
            unset($_SESSION[$name]);
        }
    }

    /**
     *
     * @covers ::getEmailLinkDropdown
     * @dataProvider providerTestGetEmailLinkDropdown
     * @param bool $systemEmailIsConfigured
     * @return void
     */
    public function testGetEmailLinkDropdown(bool $systemEmailIsConfigured)
    {
        // Create a User mock
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['isSystemEmailConfigured'])
            ->disableOriginalConstructor()
            ->getMock();

        $userMock->expects($this->any())
            ->method('isSystemEmailConfigured')
            ->willReturn($systemEmailIsConfigured);

        $dropdown = $userMock->getEmailLinkDropdown();

        $result = array_key_exists('sugar', $dropdown);

        self::assertEquals($systemEmailIsConfigured, $result);
    }

    /**
     * Data provider for testGetEmailLinkDropdown
     */
    public function providerTestGetEmailLinkDropdown()
    {
        return [
            'system email is configured' => [true],
            'system email is not configured' => [false],
        ];
    }
}
