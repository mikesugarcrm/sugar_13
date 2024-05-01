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

require_once 'install/install_utils.php';
require_once 'modules/TimePeriods/TimePeriod.php';

class PopulateOppSeedDataTest extends TestCase
{
    private $createdOpportunities;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        global $current_user;
        SugarTestHelper::setUp('current_user');
        $current_user->is_admin = 1;
        $current_user->save();
        $GLOBALS['db']->query('UPDATE opportunities SET deleted = 1');
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProductUtilities::removeAllCreatedProducts();
        $GLOBALS['db']->query('UPDATE opportunities SET deleted = 0');
        if ($this->createdOpportunities) {
            $ids = "('" . implode("','", $this->createdOpportunities) . "')";
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN $ids");
            $GLOBALS['db']->query("DELETE FROM products WHERE opportunity_id IN $ids");
        }
    }

    public static function dataProviderMonthDelta()
    {
        $return = [];
        for ($m = 0; $m < 24; $m++) {
            $return[] = [$m];
        }

        return $return;
    }

    /**
     * @dataProvider dataProviderMonthDelta
     * @group opportunities
     */
    public function testCreatePastDate($monthDelta)
    {
        $now = new DateTime();
        $now->setTime(23, 59, 59);
        $date = OpportunitiesSeedData::createPastDate($monthDelta);
        $objDate = new DateTime($date);
        $this->assertLessThan($now->format('U'), $objDate->format('U'));
    }

    /**
     * @dataProvider dataProviderMonthDelta
     * @group opportunities
     */
    public function testCreateDate($monthDelta)
    {
        $now = new DateTime();
        $now->setTime(0, 0, 0);
        $date = OpportunitiesSeedData::createDate($monthDelta);
        $objDate = new DateTime($date);
        $this->assertGreaterThanOrEqual($now->format('U'), $objDate->format('U'));
    }
}
