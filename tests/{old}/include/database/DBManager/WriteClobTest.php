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

class DBManager_WriteClobTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testInsert()
    {
        /** @var Account $account */
        $account = BeanFactory::newBean('Accounts');
        $account->id = create_guid();
        $account->new_with_id = true;
        SugarTestAccountUtilities::setCreatedAccount([$account->id]);

        $description = str_repeat('A', 65535);
        $account->description = $description;
        $account->save();

        /** @var Account $reloaded */
        $reloaded = BeanFactory::getBean($account->module_name, $account->id, [
            'use_cache' => false,
        ]);
        $this->assertEquals($description, $reloaded->description);

        return $account;
    }

    /**
     * @depends testInsert
     */
    public function testUpdate(Account $account)
    {
        $description = str_repeat('B', 65535);
        $account->description = $description;
        $account->save();

        /** @var Account $reloaded */
        $reloaded = BeanFactory::getBean($account->module_name, $account->id, [
            'use_cache' => false,
        ]);
        $this->assertEquals($description, $reloaded->description);
    }
}
