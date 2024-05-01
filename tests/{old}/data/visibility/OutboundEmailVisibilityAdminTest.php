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
 * @coversDefaultClass OutboundEmailVisibility
 * @group email
 */
class OutboundEmailVisibilityAdminTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // create an admin user
        SugarTestHelper::setUp('current_user', [true, true]);

        // Create a system account.
        OutboundEmailConfigurationTestHelper::setUp();

        // Create two user accounts for the current user.
        OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);

        // Create a system override account for the current user.
        OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
    }

    public static function tearDownAfterClass(): void
    {
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        OutboundEmailConfigurationTestHelper::restoreAllowDefaultOutbound();
    }

    public function addVisibilityProvider()
    {
        return [
            [0, 4, true], // default outbound NOT allowed, admin sees system + user + own system_override accounts
            [1, 4, true], // default outbound NOT allowed, admin sees system + user + own system_override accounts
            [2, 3, true], // default outbound allowed, admin sees system + user accounts, no system_override account
        ];
    }

    /**
     * @covers ::addVisibilityWhereQuery
     * @dataProvider addVisibilityProvider
     * @param int $allowDefaultOutbound The notify_allow_default_outbound setting.
     * @param int $expectedCount The number of accounts that should be returned.
     * @param bool $shouldIncludeSystemAccount Should it return system account?
     */
    public function testAddVisibilityWhereQuery($allowDefaultOutbound, $expectedCount, $shouldIncludeSystemAccount)
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound($allowDefaultOutbound);

        $bean = BeanFactory::newBean('OutboundEmail');

        $q = new SugarQuery();
        $q->select('id');
        $q->from($bean);
        $accounts = $bean->fetchFromQuery($q);

        $this->assertCount($expectedCount, $accounts, "{$expectedCount} accounts should have been returned");

        $hasSystemAccount = false;
        foreach ($accounts as $account) {
            if ($account->type === OutboundEmail::TYPE_SYSTEM) {
                $hasSystemAccount = true;
                break;
            }
        }

        $this->assertSame($shouldIncludeSystemAccount, $hasSystemAccount);
    }
}
