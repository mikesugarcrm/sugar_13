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
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Retrieve;

/**
 * @covers \Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Retrieve
 * @covers NormalizedTeamSecurity
 * @covers TeamSecurity
 */
class RetrieveVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        $GLOBALS['sugar_config']['perfProfile']['TeamSecurity']['default']['prefetch_for_retrieve'] = false;
        SugarConfig::getInstance()->clearCache();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testApplyToQuery()
    {
        $bean = SugarTestAccountUtilities::createAccount('test_id');
        SugarConfig::getInstance()->clearCache();
        $GLOBALS['sugar_config']['perfProfile']['TeamSecurity']['default']['prefetch_for_retrieve'] = true;
        $query = new \SugarQuery();
        $query->from($bean, [
            'add_deleted' => false,
            'team_security' => true,
            'erased_fields' => true,
            'action' => 'view',
            'bean_id' => 'test_id',
        ]);
        $query->where()->equals("$bean->table_name.id", 'test_id');

        $sql = $query->compile()->getSQL();

        $this->assertStringContainsString(
            'WHERE tst.team_set_id = ? GROUP BY tst.team_set_id',
            $sql
        );
    }

    public function testApplyToFrom()
    {
        global $current_user, $db;
        $this->expectException(\LogicException::class);

        $strategy = new Retrieve($current_user, 'test_id');

        $strategy->applyToFrom($db, 'SELECT 1 FROM DUAL', 'accounts');
    }

    public function testApplyToWhere()
    {
        global $current_user, $db;
        $this->expectException(\LogicException::class);

        $strategy = new Retrieve($current_user, 'test_id');

        $strategy->applyToWhere($db, 'SELECT 1 FROM DUAL', 'accounts');
    }
}
