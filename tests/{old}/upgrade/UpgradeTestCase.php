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

abstract class UpgradeTestCase extends TestCase
{
    /**
     * @var TestUpgrader
     */
    protected $upgrader;

    /**
     * admin user
     * @var User
     */
    protected static $admin;

    public static function setUpBeforeClass(): void
    {
        // create admin user
        self::$admin = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user'] = static::$admin;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function setUp(): void
    {
        // make sure current_user is not flushed out by SugarTestHelper::tearDown()
        $GLOBALS['current_user'] = static::$admin;
        $this->upgrader = new TestUpgrader(self::$admin);
        SugarTestHelper::setUp('files');
    }

    protected function tearDown(): void
    {
        $this->upgrader->cleanState();
        $this->upgrader->cleanDir($this->upgrader->getTempDir());
        SugarTestHelper::tearDown();
    }
}
