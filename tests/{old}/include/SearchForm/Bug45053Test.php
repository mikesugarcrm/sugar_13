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

require_once 'include/SearchForm/SearchForm2.php';

class Bug45053Test extends TestCase
{
    public $opportunity = null;
    public $account = null;
    public $requestArray = null;
    public $searchForm = null;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->opportunity = new Opportunity();
        $this->opportunity->name = 'Bug45053Test ' . time();
        $this->opportunity->account_name = $this->account->name;
        $this->opportunity->amount = 500;
        $tomorrow = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
        $this->opportunity->date_closed = date('Y-m-d', $tomorrow);
        $this->opportunity->sales_stage = 'Prospecting';
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id='{$this->opportunity->id}'");
    }

    /**
     * @ticket 45053
     */
    public function testPopulateFromArrayFunctionInSearchForm2ForFieldDefsArrayRelateFields()
    {
        $searchFields = [];
        $searchdefs = [];
        //test that when request object has values for relate fields
        //and request is not coming from search button
        //then fieldDefs array has value for relate fields set from populateFromArray() function in SearchForm2.php

        //array to simulate REQUEST object
        $this->requestArray['module'] = 'Opportunities';
        $this->requestArray['action'] = 'index';
        $this->requestArray['searchFormTab'] = 'advanced_search';
        $this->requestArray['sales_stage'] = 'Prospecting'; //value of a relate field set in REQUEST object
        $this->requestArray['query'] = 'true';


        $this->searchForm = new SearchForm($this->opportunity, 'Opportunities');

        require 'modules/Opportunities/vardefs.php';
        require 'modules/Opportunities/metadata/SearchFields.php';
        require 'modules/Opportunities/metadata/searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->opportunity->getFieldDefinitions();
        $this->searchForm->populateFromArray($this->requestArray, 'advanced_search', false);
        $test_sales_stage = $this->searchForm->fieldDefs['sales_stage_advanced']['value'];
        $this->assertEquals($this->requestArray['sales_stage'], $test_sales_stage);
    }
}
