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
 * Class SugarSystemInfoTest
 * @group BR-1722
 */
class SugarSystemInfoTest extends TestCase
{
    /**
     * @var SugarSystemInfo
     */
    protected $sysInfo;

    protected function setUp(): void
    {
        SugarTestTrackerUtility::setup();
        $bean = new Account();
        $user = SugarTestUserUtilities::createAnonymousUser(true, true);
        $user->updateLastLogin();
        SugarTestTrackerUtility::insertTrackerEntry($bean, 'editview');
        $this->sysInfo = SugarSystemInfo::getInstance();

        //add more users to test
        $user1 = SugarTestUserUtilities::createAnonymousUser(true, false);
        $user2 = SugarTestUserUtilities::createAnonymousUser(true, false);
        $user2->updateLastLogin();

        $user3 = SugarTestUserUtilities::createAnonymousUser(true, false, ['deleted' => 1]);
        $user4 = SugarTestUserUtilities::createAnonymousUser(true, false, ['status' => 'Inactive']);
        $user4->updateLastLogin();

        $user5 = SugarTestUserUtilities::createAnonymousUser(true, false, ['portal_only' => 1]);
        $user6 = SugarTestUserUtilities::createAnonymousUser(true, false, ['is_group' => 1]);
    }

    protected function tearDown(): void
    {
        SugarTestTrackerUtility::restore();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTrackerUtility::removeAllTrackerEntries();
    }

    /**
     * @return SugarSystemInfo
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('SugarSystemInfo', SugarSystemInfo::getInstance());
    }


    public function testGetInfo()
    {
        $this->assertArrayHasKey('sugar_flavor', $this->sysInfo->getInfo());
    }

    public function testGetDistroInfo()
    {
        $distro_name = time();
        file_put_contents('distro.php', "<?php \$distro_name = '$distro_name';");
        $info = $this->sysInfo->getDistroInfo();
        $this->assertArrayHasKey('distro_name', $info);
        $this->assertEquals($distro_name, $info['distro_name']);
        @unlink('distro.php');
    }

    public function testGetBaseInfo()
    {
        $this->assertArrayHasKey('auth_level', $this->sysInfo->getBaseInfo());
    }

    public function testGetUsersInfo()
    {
        $this->assertArrayHasKey('users', $this->sysInfo->getUsersInfo());
        $this->assertArrayHasKey('registered_users', $this->sysInfo->getUsersInfo());
        $this->assertArrayHasKey('admin_users', $this->sysInfo->getUsersInfo());
        $this->assertArrayHasKey('users_active_30_days', $this->sysInfo->getUsersInfo());
    }

    public function testGetActiveUsersXDaysCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->sysInfo->getActiveUsersXDaysCount(30));
    }

    public function testGetAdminCount()
    {
        $this->assertGreaterThanOrEqual(1, $this->sysInfo->getAdminCount());
    }

    public function testGetUsersCount()
    {
        $this->assertGreaterThanOrEqual(6, $this->sysInfo->getUsersCount());
    }

    public function testGetActiveUsersCount()
    {
        $this->assertGreaterThanOrEqual(3, $this->sysInfo->getActiveUsersCount());
    }

    public function testGetSystemName()
    {
        $sql = "SELECT value FROM config WHERE category = 'system' AND name = 'name'";
        $systemName = $GLOBALS['db']->getOne($sql);
        $this->assertNotEmpty($systemName);
        $this->assertEquals($systemName, $this->sysInfo->getSystemName());
    }

    public function testGetSystemNameInfo()
    {
        $this->assertArrayHasKey('system_name', $this->sysInfo->getSystemNameInfo());
    }

    public function testGetLicenseInfo()
    {
        $this->assertArrayHasKey('license_users', $this->sysInfo->getLicenseInfo());
    }

    public function testGetLicensePortalInfo()
    {
        $info = $this->sysInfo->getLicensePortalInfo();
        $this->assertArrayHasKey('license_portal_max', $info);
        $this->assertGreaterThan(-1, $info['license_portal_max']);
    }

    public function testGetEnvInfo()
    {
        $this->assertArrayHasKey('php_version', $this->sysInfo->getEnvInfo());
    }

    public function testGetAppInfo()
    {
        $this->assertArrayHasKey('auth_level', $this->sysInfo->getAppInfo());
    }

    public function testGetApplicationKeyInfo()
    {
        $this->assertArrayHasKey('application_key', $this->sysInfo->getApplicationKeyInfo());
    }

    public function testGetLatestTrackerIdInfo()
    {
        $info = $this->sysInfo->getLatestTrackerIdInfo();
        $this->assertArrayHasKey('latest_tracker_id', $info);
        $this->assertGreaterThan(0, $info['latest_tracker_id']);
    }

    public function testGetLicenseKey()
    {
        $this->assertNotEmpty($this->sysInfo->getLicenseKey());
    }
}
