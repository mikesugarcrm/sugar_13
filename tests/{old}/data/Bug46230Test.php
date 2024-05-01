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
 * Bug #46230
 * Dependent Field values are not refreshed in subpanels & listviews
 * @ticket 49878
 */
class Bug46230Test extends TestCase
{
    private $account;
    private $stored_service_object;
    private $account2;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $this->account = SugarTestAccountUtilities::createAccount();

        //Unset global service_object variable so that the code in updateDependencyBean is run in SugarBean.php
        if (isset($GLOBALS['service_object'])) {
            $this->stored_service_object = $GLOBALS['service_object'];
            unset($GLOBALS['service_object']);
        }

        $this->account2 = SugarTestAccountUtilities::createAccount();
        $this->account2->account_type = 'Analyst';
        $this->account2->industry = 'Energy';
        $this->account2->field_defs['industry']['dependency'] = 'or(equal($account_type,"Analyst"),equal($account_type,"Customer"))';
        $this->account2->save();
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        if (!empty($this->stored_service_object)) {
            $GLOBALS['service_object'] = $this->stored_service_object;
        }
    }

    public function providerData()
    {
        return [
            ['Partner', 'Banking', '1'],
            ['Analyst', 'Energy', '0'],
            ['Customer', 'Education', '0'],
        ];
    }

    /**
     * @dataProvider providerData
     * @group 46230
     */
    public function testGetListViewArray($type, $industry, $is_industry_hidden)
    {
        $this->account->account_type = $type;
        $this->account->industry = $industry;
        $dependency = 'or(equal($account_type,"Analyst"),equal($account_type,"Customer"))';
        $this->account->field_defs['industry']['dependency'] = $dependency;

        $this->account->updateDependentFieldForListView();

        $res = $this->account->get_list_view_array();

        if ($is_industry_hidden == '1') {
            $this->assertEmpty($res['INDUSTRY']);
        } else {
            $this->assertNotEmpty($res['INDUSTRY']);
        }

        $this->account->updateDependentField();

        if ($is_industry_hidden == '1') {
            $this->assertEmpty($res['INDUSTRY']);
        } else {
            $this->assertNotEmpty($res['INDUSTRY']);
        }
    }

    /**
     * @group 54042
     */
    public function testRetrieveBeanUpdateDependentFields()
    {
        $this->account->retrieve($this->account2->id);
        $res = $this->account->get_list_view_array();
        $this->assertNotEmpty($res['INDUSTRY']);
    }

    /**
     * @group 54042
     */
    public function testRetrieveByStringFieldsBeanUpdateDependentFields()
    {
        $this->account->retrieve_by_string_fields(['id' => $this->account2->id]);
        $res = $this->account->get_list_view_array();
        $this->assertNotEmpty($res['INDUSTRY']);
    }
}
