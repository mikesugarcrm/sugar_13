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
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * User profile Save tests
 */
class SaveTest extends TestCase
{
    protected $tabs;
    protected $currUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currUser = $GLOBALS['current_user'];

        SugarTestHelper::setUp('current_user', [true, 1]);
        $this->tabs = new TabController();

        $_POST['record'] = $this->currUser->id;
        $_POST['multi_1_c'] = ['Customer', 'Integrator'];
        $_POST['multi_1_c_multiselect'] = true;
    }

    protected function tearDown(): void
    {
        $GLOBALS['current_user'] = $this->currUser;

        unset($_POST['record']);
        unset($_POST['multi_1_c']);
        unset($_POST['multi_1_c_multiselect']);

        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        parent::tearDown();
    }

    /**
     * Home always needs to be first display tab
     */
    public function testAddHomeToDisplayTabsOnSave()
    {
        $focus = null;
        $current_user = $GLOBALS['current_user'];
        $_POST['record'] = $current_user->id;
        $_REQUEST['display_tabs_def'] = 'display_tabs[]=Leads';  //Save only included Leads
        include 'modules/Users/Save.php';
        //Home was prepended
        $this->assertEquals(['Home' => 'Home', 'Leads' => 'Leads'], $this->tabs->get_user_tabs($focus));
    }

    public function testSaveOfOutboundEmailSystemOverrideConfiguration()
    {
        $current_user = $GLOBALS['current_user'];
        OutboundEmailConfigurationTestHelper::setUp();
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
        OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration($current_user->id);

        $_POST['record'] = $current_user->id;
        $_POST['first_name'] = 'Julia';
        $_POST['last_name'] = 'Watkins';
        $_POST['mail_smtpuser'] = $_REQUEST['mail_smtpuser'] = 'julia';
        $_POST['mail_smtppass'] = $_REQUEST['mail_smtppass'] = 'B5rz71Kg';

        include 'modules/Users/Save.php';

        unset($_POST['record']);
        unset($_POST['mail_smtpuser']);
        unset($_REQUEST['mail_smtpuser']);
        unset($_POST['mail_smtppass']);
        unset($_REQUEST['mail_smtppass']);

        $current_user->retrieve($current_user->id);
        $userData = $current_user->getUsersNameAndEmail();
        $emailAddressId = $current_user->emailAddress->getGuid($userData['email']);
        $oe = BeanFactory::newBean('OutboundEmail');
        $override = $oe->getUsersMailerForSystemOverride($current_user->id);

        $this->assertSame($userData['name'], $override->name, 'The names should match');
        $this->assertSame($current_user->id, $override->user_id, 'The current user should be the owner');
        $this->assertSame($userData['email'], $override->email_address, 'The email addresses should match');
        $this->assertSame($emailAddressId, $override->email_address_id, 'The email address IDs should match');
        $this->assertSame('julia', $override->mail_smtpuser, 'The usernames should match');
        $this->assertSame('B5rz71Kg', $override->mail_smtppass, 'The passwords should not match');

        OutboundEmailConfigurationTestHelper::tearDown();
    }

    /**
     * @covers User::save
     * @covers Person::save
     * @covers SugarEmailAddress::handleLegacySave
     * @covers SugarEmailAddress::populateAddresses
     * @covers SugarEmailAddress::addAddress
     * @covers SugarEmailAddress::getEmailGUID
     * @covers SugarEmailAddress::getGuid
     */
    // TODO: This test needs to be re-written in the future and will be handled by SS-560
    /*
    public function testSaveReplacesTheEmailAddressForTheCurrentUserWithoutAffectingTheOtherUser()
    {
        $address1 = Uuid::uuid4() . '@example.com';
        $ea = SugarTestEmailAddressUtilities::createEmailAddress($address1);

        $address2 = Uuid::uuid4() . '@example.com';

        SugarTestHelper::setUp('current_user', [true, 1]);
        $current_user = $GLOBALS['current_user'];
        SugarTestEmailAddressUtilities::addAddressToPerson($current_user, $ea);

        $user2 = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson($user2, $ea);

        $_POST['record'] = $current_user->id;
        $_POST['Users_email_widget_id'] = $_REQUEST['Users_email_widget_id'] = 0;
        $_POST['emailAddressWidget'] = $_REQUEST['emailAddressWidget'] = 1;
        $_POST['useEmailWidget'] = $_REQUEST['useEmailWidget'] = true;

        // Save the current user's primary email address so that it isn't removed.
        $_POST['Users0emailAddress0'] = $_REQUEST['Users0emailAddress0'] = $current_user->email1;
        $_POST['Users0emailAddressId0'] = $_REQUEST['Users0emailAddressId0'] = $ea->getGuid($current_user->email1);
        $_POST['Users0emailAddressVerifiedFlag0'] = $_REQUEST['Users0emailAddressVerifiedFlag0'] = true;
        $_POST['Users0emailAddressVerifiedValue0'] = $_REQUEST['Users0emailAddressVerifiedValue0'] = $current_user->email1;

        // Change the current user's secondary email address.
        // The ID and email address are not in sync. The address is different. The ID is still passed but not used when
        // saving the changes for the current user.
        $_POST['Users0emailAddress1'] = $_REQUEST['Users0emailAddress1'] = $address2;
        $_POST['Users0emailAddressId1'] = $_REQUEST['Users0emailAddressId1'] = $ea->id;
        // Mark the new email address invalid.
        $_POST['Users0emailAddressInvalidFlag'] = $_REQUEST['Users0emailAddressInvalidFlag'] = ['Users0emailAddress1'];
        $_POST['Users0emailAddressVerifiedFlag1'] = $_REQUEST['Users0emailAddressVerifiedFlag1'] = true;
        $_POST['Users0emailAddressVerifiedValue1'] = $_REQUEST['Users0emailAddressVerifiedValue1'] = $address2;

        include 'modules/Users/Save.php';

        unset($_POST['record']);
        unset($_POST['Users_email_widget_id']);
        unset($_REQUEST['Users_email_widget_id']);
        unset($_POST['emailAddressWidget']);
        unset($_REQUEST['emailAddressWidget']);
        unset($_POST['useEmailWidget']);
        unset($_REQUEST['useEmailWidget']);
        unset($_POST['Users0emailAddress0']);
        unset($_REQUEST['Users0emailAddress0']);
        unset($_POST['Users0emailAddressId0']);
        unset($_REQUEST['Users0emailAddressId0']);
        unset($_POST['Users0emailAddressVerifiedFlag0']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag0']);
        unset($_POST['Users0emailAddressVerifiedValue0']);
        unset($_REQUEST['Users0emailAddressVerifiedValue0']);
        unset($_POST['Users0emailAddress1']);
        unset($_REQUEST['Users0emailAddress1']);
        unset($_POST['Users0emailAddressId1']);
        unset($_REQUEST['Users0emailAddressId1']);
        unset($_POST['Users0emailAddressInvalidFlag']);
        unset($_REQUEST['Users0emailAddressInvalidFlag']);
        unset($_POST['Users0emailAddressVerifiedFlag1']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag1']);
        unset($_POST['Users0emailAddressVerifiedValue1']);
        unset($_REQUEST['Users0emailAddressVerifiedValue1']);

        // Make sure we can clean up the new email address.
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress($address2);

        $current_user->retrieve($current_user->id);
        $user2->retrieve($user2->id);

        $currentUserIndex = ($ea->id == $current_user->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;
        $user2Index = ($ea->id == $user2->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;

        $this->assertCount(
            2,
            $current_user->emailAddress->addresses,
            'The current user should have two email addresses'
        );
        $this->assertEquals(
            1,
            $current_user->emailAddress->addresses[$currentUserIndex]['invalid_email'],
            'The email address should be have been marked invalid'
        );

        // None of the current user's email addresses should be address1.
        foreach ($current_user->emailAddress->addresses as $address) {
            $this->assertNotEquals(
                $ea->id,
                $address['email_address_id'],
                'The current user should not be linked to the same email address as user2'
            );
            $this->assertNotEquals(
                $address1,
                $address['email_address'],
                'The current user should not have the same email address as user2'
            );
        }

        $this->assertCount(
            2,
            $user2->emailAddress->addresses,
            'user2 should have two email addresses'
        );
        $this->assertEquals(
            0,
            $user2->emailAddress->addresses[$user2Index]['invalid_email'],
            'The email address should not have been changed'
        );
        $this->assertEquals(
            $ea->id,
            $user2->emailAddress->addresses[$user2Index]['email_address_id'],
            'user2 should still be linked to address1'
        );
        $this->assertEquals(
            $address1,
            $user2->emailAddress->addresses[$user2Index]['email_address'],
            'user2 should still have address1'
        );

        // None of user2's email addresses should be address2.
        foreach ($user2->emailAddress->addresses as $address) {
            $this->assertNotEquals(
                $current_user->emailAddress->addresses[$user2Index]['email_address_id'],
                $address['email_address_id'],
                "user2 should not be linked to {$address2}"
            );
            $this->assertNotEquals(
                $address2,
                $address['email_address'],
                "user2 should not have {$address2}"
            );
        }
    }
    */

    /**
     * @covers User::save
     * @covers Person::save
     * @covers SugarEmailAddress::handleLegacySave
     * @covers SugarEmailAddress::populateAddresses
     * @covers SugarEmailAddress::addAddress
     * @covers SugarEmailAddress::getEmailGUID
     * @covers SugarEmailAddress::getGuid
     */
    public function testSaveUpdatesTheEmailAddressForBothUsers()
    {
        $address = Uuid::uuid4() . '@example.com';
        $ea = SugarTestEmailAddressUtilities::createEmailAddress($address);

        $current_user = $GLOBALS['current_user'];
        SugarTestEmailAddressUtilities::addAddressToPerson($current_user, $ea);

        $user2 = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson($user2, $ea);

        $_POST['record'] = $current_user->id;
        $_POST['Users_email_widget_id'] = $_REQUEST['Users_email_widget_id'] = 0;
        $_POST['emailAddressWidget'] = $_REQUEST['emailAddressWidget'] = 1;
        $_POST['useEmailWidget'] = $_REQUEST['useEmailWidget'] = true;

        // Save the current user's primary email address so that it isn't removed.
        $_POST['Users0emailAddress0'] = $_REQUEST['Users0emailAddress0'] = $current_user->email1;
        $_POST['Users0emailAddressId0'] = $_REQUEST['Users0emailAddressId0'] = $ea->getGuid($current_user->email1);
        $_POST['Users0emailAddressVerifiedFlag0'] = $_REQUEST['Users0emailAddressVerifiedFlag0'] = true;
        $_POST['Users0emailAddressVerifiedValue0'] = $_REQUEST['Users0emailAddressVerifiedValue0'] = $current_user->email1;

        // Change the email address without creating a new one.
        $_POST['Users0emailAddress1'] = $_REQUEST['Users0emailAddress1'] = $address;
        $_POST['Users0emailAddressId1'] = $_REQUEST['Users0emailAddressId1'] = $ea->id;
        // Mark the email address invalid.
        $_POST['Users0emailAddressInvalidFlag'] = $_REQUEST['Users0emailAddressInvalidFlag'] = ['Users0emailAddress1'];
        $_POST['Users0emailAddressVerifiedFlag1'] = $_REQUEST['Users0emailAddressVerifiedFlag1'] = true;
        $_POST['Users0emailAddressVerifiedValue1'] = $_REQUEST['Users0emailAddressVerifiedValue1'] = $address;

        include 'modules/Users/Save.php';

        unset($_POST['record']);
        unset($_POST['Users_email_widget_id']);
        unset($_REQUEST['Users_email_widget_id']);
        unset($_POST['emailAddressWidget']);
        unset($_REQUEST['emailAddressWidget']);
        unset($_POST['useEmailWidget']);
        unset($_REQUEST['useEmailWidget']);
        unset($_POST['Users0emailAddress0']);
        unset($_REQUEST['Users0emailAddress0']);
        unset($_POST['Users0emailAddressId0']);
        unset($_REQUEST['Users0emailAddressId0']);
        unset($_POST['Users0emailAddressVerifiedFlag0']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag0']);
        unset($_POST['Users0emailAddressVerifiedValue0']);
        unset($_REQUEST['Users0emailAddressVerifiedValue0']);
        unset($_POST['Users0emailAddress1']);
        unset($_REQUEST['Users0emailAddress1']);
        unset($_POST['Users0emailAddressId1']);
        unset($_REQUEST['Users0emailAddressId1']);
        unset($_POST['Users0emailAddressInvalidFlag']);
        unset($_REQUEST['Users0emailAddressInvalidFlag']);
        unset($_POST['Users0emailAddressVerifiedFlag1']);
        unset($_REQUEST['Users0emailAddressVerifiedFlag1']);
        unset($_POST['Users0emailAddressVerifiedValue1']);
        unset($_REQUEST['Users0emailAddressVerifiedValue1']);

        $current_user->retrieve($current_user->id);
        $user2->retrieve($user2->id);

        $currentUserIndex = ($ea->id == $current_user->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;
        $user2Index = ($ea->id == $user2->emailAddress->addresses[0]['email_address_id']) ? 0 : 1;

        $this->assertCount(
            2,
            $current_user->emailAddress->addresses,
            'The current user should have two email addresses'
        );
        $this->assertEquals(
            $ea->id,
            $current_user->emailAddress->addresses[$currentUserIndex]['email_address_id'],
            'The current user should still be linked to address'
        );
        $this->assertEquals(
            $address,
            $current_user->emailAddress->addresses[$currentUserIndex]['email_address'],
            'The current user should still have address'
        );
        $this->assertEquals(
            1,
            $current_user->emailAddress->addresses[$currentUserIndex]['invalid_email'],
            'The email address should be have been marked invalid for the current user'
        );

        $this->assertCount(
            2,
            $user2->emailAddress->addresses,
            'user2 should have two email addresses'
        );
        $this->assertEquals(
            $ea->id,
            $user2->emailAddress->addresses[$user2Index]['email_address_id'],
            'user2 should still be linked to address'
        );
        $this->assertEquals(
            $address,
            $user2->emailAddress->addresses[$user2Index]['email_address'],
            'user2 should still have address'
        );
        $this->assertEquals(
            1,
            $user2->emailAddress->addresses[$user2Index]['invalid_email'],
            'The email address should be have been marked invalid for user2'
        );
    }

    /**
     * @dataProvider saveLicenseTypeProvider
     */
    public function testSetLicenseType(array $licenseType, bool $isAdmin, array $expected)
    {
        $current_user = SugarTestHelper::setUp('current_user', [true, $isAdmin]);

        $_POST['record'] = $current_user->id;
        $_POST['LicenseTypes'] = $licenseType;
        include 'modules/Users/Save.php';

        $record = $su = BeanFactory::retrieveBean('Users', $current_user->id, ['use_cache' => false]);

        $this->assertEquals($expected, $record->getTopLevelLicenseTypes());
    }

    public function saveLicenseTypeProvider()
    {
        return [
            [['CURRENT'], true, ['CURRENT']],
            [[], false, []],
        ];
    }

    public function testNonAdminSetLicenseTypeException()
    {
        // setup non-admin user
        $current_user = SugarTestHelper::setUp('current_user');

        $_POST['record'] = $current_user->id;
        $_POST['LicenseTypes'] = ['SUGAR_SERVE'];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        include 'modules/Users/Save.php';
    }

    public function testSetLicenseTypeInvalidException()
    {
        // setup admin user
        $current_user = SugarTestHelper::setUp('current_user', [true, true]);

        $_POST['record'] = $current_user->id;
        $_POST['LicenseTypes'] = ['INVALID_TYPE'];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        include 'modules/Users/Save.php';
    }

    /**
     * @param array $licenseType
     * @param bool $unlinked
     * @dataProvider unlinkReportCacheProvider
     */
    public function testUnlinkReportCache(array $licenseType, bool $unlinked)
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser(
            true,
            1,
            [
                'license_type' => '["CURRENT"]',
                'preferred_language' => 'en_us',
            ]
        );

        $cacheFile = sugar_cached('modules/modules_def_' . $current_user->preferred_language . '_' .
            md5($current_user->id) . '.js');
        if (!file_exists($cacheFile)) {
            sugar_file_put_contents($cacheFile, 'test content');
        }

        $_POST['record'] = $current_user->id;
        $_POST['LicenseTypes'] = $licenseType;
        include 'modules/Users/Save.php';

        if ($unlinked) {
            $this->assertFalse(file_exists($cacheFile));
        } else {
            $this->assertTrue(file_exists($cacheFile));
        }
    }

    public function unlinkReportCacheProvider()
    {
        return [
            [['CURRENT'], false],
            [['SUGAR_SERVE'], true],
        ];
    }

    /**
     * Test Field Name Placement preference is set
     */
    public function testSetFieldNamePlacementPref()
    {
        $current_user = SugarTestHelper::setUp('current_user', [true, true]);

        $_POST['record'] = $current_user->id;
        $_POST['field_name_placement'] = 'field_on_side';
        include 'modules/Users/Save.php';

        unset($_POST['record']);
        unset($_POST['field_name_placement']);

        $record = BeanFactory::retrieveBean('Users', $current_user->id, ['use_cache' => false]);

        $this->assertEquals('field_on_side', $record->getPreference('field_name_placement'));
    }

    /**
     * Test Field type MultiSelect can be saved
     */
    public function testAddMultiSelect()
    {
        $current_user = SugarTestHelper::setUp('current_user', [true, true]);
        $_POST['record'] = $current_user->id;

        $data = [
            'name' => 'multi_1',
            'module' => 'Users',
            'type' => 'multienum',
            'label' => 'LBL_MULTISELECT_FIELD',
            'options' => 'account_type_dom',
            'default_value' => 'Analyst',
        ];
        $this->addCustomField($data);
        $bean = BeanFactory::getBean('Users', $current_user->id);
        if (array_key_exists('multi_1_c', $bean->field_defs) &&
            isset($bean->multi_1_c)) {
            include 'modules/Users/Save.php';
            $this->assertEquals('^Customer^,^Integrator^', $bean->multi_1_c);

            $dyField = new DynamicField();
            $dyField->bean = BeanFactory::getBean('Users');
            $dyField->module = 'Users';
            $dyField->deleteField('multi_1_c');
        }
    }

    /**
     * @dataProvider providerTestNumberPinnedModules
     * @param int|null $pinnedPref The user's current number pinned preference
     * @param int|null $pinnedConfig The system's current number pinned config
     * @param bool $userAllowed Whether a user is allowed to configure pins
     * @param string|null $pinnedPost The number pinned POST request value
     * @param bool $expectedPref The user's expected number pinned preference
     */
    public function testNumberPinnedModules($pinnedPref, $pinnedConfig, $userAllowed, $pinnedPost, $expectedPref)
    {
        global $current_user;

        $current_user->setPreference('number_pinned_modules', $pinnedPref);
        $this->tabs->set_number_pinned_modules($pinnedConfig);
        $this->tabs->set_users_pinned_modules($userAllowed);

        $_POST['record'] = $current_user->id;
        $_POST['number_pinned_modules'] = $pinnedPost;

        include 'modules/Users/Save.php';
        $this->assertEquals($expectedPref, $current_user->getPreference('number_pinned_modules'));
    }

    /**
     * @return array Test data for testNumberPinnedModules
     */
    public function providerTestNumberPinnedModules()
    {
        return [
            [3, 4, true, '5', 5,],
            [null, 4, false, '5', null,],
            [null, 4, true, '4', null,],
        ];
    }

    /**
     * Adds a custom field
     *
     * @param array $data
     */
    protected function addCustomField(array $data)
    {
        SugarAutoLoader::requireWithCustom('ModuleInstall/ModuleInstaller.php');
        $moduleInstallerClass = SugarAutoLoader::customClass('ModuleInstaller');
        $moduleInstaller = new $moduleInstallerClass();
        $moduleInstaller->install_custom_fields([$data]);

        $objName = BeanFactory::getObjectName('Users');
        VardefManager::loadVardef('Users', $objName, true);
    }
}
