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
 * @ticket 61011
 */
class Bug61011Test extends TestCase
{
    protected $has_disable_count_query_enabled;

    protected function setUp(): void
    {
        global $sugar_config;
        $this->has_disable_count_query_enabled = !empty($sugar_config['disable_count_query']);
        $sugar_config['disable_count_query'] = true;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        global $sugar_config;
        if ($this->has_disable_count_query_enabled) {
            $sugar_config['disable_count_query'] = true;
        } else {
            unset($sugar_config['disable_count_query']);
        }
        SugarTestHelper::tearDown();
    }

    public function testAddDistinctIgnoresAggregates()
    {
        $query = <<<END
SELECT sum(IFNULL(t6_test61011.sum1,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum1,sum(IFNULL(t6_test61011.sum2,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum2,sum(IFNULL(t6_test61011.sum3,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum3,sum(IFNULL(t6_test61011.sum4,0)/IFNULL(t6_test61011_currencies.conversion_rate,1))*1 t6_test61011_sum_sum4
FROM t6_test61011
 INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id
                                        AND team_memberships.user_id = 'seed_max_id'
                                        AND team_memberships.deleted=0 group by tst.team_set_id) t6_test61011_tf on t6_test61011_tf.team_set_id  = t6_test61011.team_set_id LEFT JOIN currencies t6_test61011_currencies ON t6_test61011.currency_id=t6_test61011_currencies.id AND t6_test61011_currencies.deleted=0
 WHERE ((((t6_test61011.name IS NOT NULL AND t6_test61011.name <> ''))))
AND  t6_test61011.deleted=0
END;
        SugarTestReflection::callProtectedMethod($GLOBALS['db'], 'addDistinctClause', [&$query]);
        $this->assertStringContainsString('SELECT sum(', $query);
        $this->assertStringContainsString('group by tst.team_set_id', $query);
    }
}
