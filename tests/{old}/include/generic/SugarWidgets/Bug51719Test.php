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
 * Bug #51719
 * [Oracle]: No data display in Summation with Detail report when used with Is Not Empty filter
 *
 * @author mgusev@sugarcrm.com
 * @ticked 51719
 */
class Bug51719Test extends TestCase
{
    /**
     * @var Account
     */
    protected $account = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Tries to assert that we got correct account by $where part for empty account_type
     *
     * @group 51719
     * @return void
     */
    public function testQueryFilterEmpty()
    {
        $this->account->account_type = '';
        $this->account->industry = '';
        $this->account->save();

        $layout_def = [
            'column_key' => 'self:account_type',
            'input_name0' => 'empty',
            'input_name1' => 'on',
            'name' => 'account_type',
            'qualifier_name' => 'empty',
            'runtime' => 1,
            'table_alias' => 'accounts',
            'tablekey' => 'self',
            'type' => 'enum',
        ];
        $report = new Report();
        $layoutManager = new LayoutManager();
        $layoutManager->setAttributePtr('reporter', $report);
        $sugarWidgetFieldEnum = new SugarWidgetFieldEnum($layoutManager);
        $where = $sugarWidgetFieldEnum->queryFilter($layout_def);
        if ($where != '') {
            $where .= " AND accounts.id = '" . $this->account->id . "'";
        }
        $query = $this->account->create_new_list_query('', $where, ['id']);
        $account = $GLOBALS['db']->fetchOne($query);

        $this->assertEquals($this->account->id, $account['id'], 'Returned incorrect account');
    }

    /**
     * Tries to assert that we got correct account by $where part for not empty account_type
     *
     * @group 51719
     * @return void
     */
    public function testQueryFilterNot_Empty()
    {
        $this->account->account_type = 'Analyst';
        $this->account->industry = 'Apparel';
        $this->account->save();

        $layout_def = [
            'column_key' => 'self:account_type',
            'input_name0' => 'not_empty',
            'input_name1' => 'on',
            'name' => 'account_type',
            'qualifier_name' => 'not_empty',
            'runtime' => 1,
            'table_alias' => 'accounts',
            'tablekey' => 'self',
            'type' => 'enum',
        ];
        $report = new Report();
        $layoutManager = new LayoutManager();
        $layoutManager->setAttributePtr('reporter', $report);
        $sugarWidgetFieldEnum = new SugarWidgetFieldEnum($layoutManager);
        $where = $sugarWidgetFieldEnum->queryFilter($layout_def);
        if ($where != '') {
            $where .= " AND accounts.id = '" . $this->account->id . "'";
        }
        $query = $this->account->create_new_list_query('', $where, ['id']);
        $account = $GLOBALS['db']->fetchOne($query);

        $this->assertEquals($this->account->id, $account['id'], 'Returned incorrect account');
    }
}
