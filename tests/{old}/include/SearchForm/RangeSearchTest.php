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

class RangeSearchTest extends TestCase
{
    private $hasExistingCustomSearchFields = false;
    public $searchForm;
    public $originalDbType;
    public $smartyTestFile;

    protected function setUp(): void
    {
        if (file_exists('custom/modules/Opportunities/metadata/SearchFields.php')) {
            $this->hasExistingCustomSearchFields = true;
            copy('custom/modules/Opportunities/metadata/SearchFields.php', 'custom/modules/Opportunities/metadata/SearchFields.php.bak');
            unlink('custom/modules/Opportunities/metadata/SearchFields.php');
        } elseif (!file_exists('custom/modules/Opportunities/metadata')) {
            SugarAutoLoader::ensureDir('custom/modules/Opportunities/metadata');
        }

        //Setup Opportunities module and date_closed field
        $_REQUEST['view_module'] = 'Opportunities';
        $_REQUEST['name'] = 'date_closed';
        $templateDate = new TemplateDate();
        $templateDate->enable_range_search = true;
        $templateDate->populateFromPost();
        include 'custom/modules/Opportunities/metadata/SearchFields.php';

        //Prepare SearchForm
        $seed = new Opportunity();
        $module = 'Opportunities';
        $this->searchForm = new SearchForm($seed, $module);
        $this->searchForm->searchFields = [
            'range_date_closed' => [
                'query_type' => 'default',
                'enable_range_search' => 1,
                'is_date_field' => 1,
                'value' => '[this_year]',
                'operator' => 'this_year',
            ],
            'start_range_date_closed' => [
                'query_type' => 'default',
                'enable_range_search' => 1,
                'is_date_field' => 1,
            ],
            'end_range_date_closed' => [
                'query_type' => 'default',
                'enable_range_search' => 1,
                'is_date_field' => 1,
            ],
            'range_amount' => [
                'query_type' => 'default',
                'enable_range_search' => true,
            ],
            'start_range_amount' => [
                'query_type' => 'default',
                'enable_range_search' => true,
            ],
            'end_range_amount' => [
                'query_type' => 'default',
                'enable_range_search' => true,
            ],
        ];

        $this->originalDbType = $GLOBALS['db']->dbType;
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->dbType = $this->originalDbType;

        if (!$this->hasExistingCustomSearchFields) {
            unlink('custom/modules/Opportunities/metadata/SearchFields.php');
        }

        if (file_exists('custom/modules/Opportunities/metadata/SearchFields.php.bak')) {
            copy('custom/modules/Opportunities/metadata/SearchFields.php.bak', 'custom/modules/Opportunities/metadata/SearchFields.php');
            unlink('custom/modules/Opportunities/metadata/SearchFields.php.bak');
        }

        if (file_exists($this->smartyTestFile)) {
            unlink($this->smartyTestFile);
        }
    }

    public function testRangeNumberSearches()
    {
        $GLOBALS['db']->dbType = 'mysql';
        unset($this->searchForm->searchFields['range_date_closed']);
        $this->searchForm->searchFields['range_amount'] = [
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '10000',
            'operator' => 'greater_than',
        ];

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals('opportunities.amount > 10000', $where_clauses[0]);

        $this->searchForm->searchFields['range_amount'] = [
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '10000',
            'operator' => 'less_than',
        ];

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals('opportunities.amount < 10000', $where_clauses[0]);

        $this->searchForm->searchFields['range_amount'] = [
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '10000',
            'operator' => 'greater_than_equals',
        ];

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals('opportunities.amount >= 10000', $where_clauses[0]);

        $this->searchForm->searchFields['range_amount'] = [
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '10000',
            'operator' => 'less_than_equals',
        ];

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals('opportunities.amount <= 10000', $where_clauses[0]);

        $this->searchForm->searchFields['range_amount'] = [
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '10000',
            'operator' => 'not_equal',
        ];

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals('(opportunities.amount IS NULL OR opportunities.amount != 10000)', $where_clauses[0]);

        $this->searchForm->searchFields['range_amount'] = [
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '10000',
            'operator' => '=',

        ];

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals('(opportunities.amount >= 9999.99 AND opportunities.amount <= 10000.01)', $where_clauses[0]);
    }

    /**
     * testRangeSearchWithSavedReportValues
     * This test attempts to simulate testing what would happen should a saved report be invoked against
     * a range search field
     */
    public function testRangeSearchWithSavedReportValues()
    {
        $parentFieldArray = 'fields';

        $vardef = [];
        $vardef['name'] = 'date_closed_advanced';
        $vardef['vname'] = 'LBL_DATE_CLOSED';

        $opportunity = new Opportunity();
        $vardef = $opportunity->field_defs['date_closed'];
        $vardef['name'] = 'date_closed_advanced';
        $vardef['options'] =
            [
                '=' => 'Equals',
                'not_equal' => 'Not On',
                'greater_than' => ' After',
                'less_than' => ' Before',
                'last_7_days' => ' Last 7 Days',
                'next_7_days' => ' Next 7 Days',
                'last_30_days' => ' Last 30 Days',
                'next_30_days' => ' Next 30 Days',
                'last_month' => ' Last Month',
                'this_month' => ' This Month',
                'next_month' => ' Next Month',
                'last_year' => ' Last Year',
                'this_year' => ' This Year',
                'next_year' => ' Next Year',
                'between' => ' Is Between',
            ];


        $displayParams = ['labelSpan' => '', 'fieldSpan' => ''];
        $tabindex = '';

        $sugarFieldDatetime = new SugarFieldDatetime('Datetime');

        $_REQUEST['action'] = 'SearchForm';
        $html = $sugarFieldDatetime->getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);

        //Write this widget's contents to a file
        $this->smartyTestFile = 'tests/{old}/include/SearchForm/RangeSearchTest.tpl';
        $handle = sugar_fopen($this->smartyTestFile, 'wb');
        fwrite($handle, $html);

        //Check that the template exists before we proceed with further tests
        $this->assertTrue(file_exists($this->smartyTestFile));

        //Stuff Smarty variables
        $vardef['value'] = '';
        $fields = [];
        $fields['date_closed_advanced'] = $vardef;

        //Create Smarty instance
        $ss = new Sugar_Smarty();

        //Assign Smarty variables
        $ss->assign('fields', $fields);
        $ss->assign('APP', $GLOBALS['app_strings']);
        $ss->assign('CALENDAR_FORMAT', 'm-d-Y');

        //Simulate the request with saved report value
        $_REQUEST['date_closed_advanced'] = '07-03-2009';

        $output = $ss->fetch($this->smartyTestFile);
        $this->assertMatchesRegularExpression('/range_date_closed_advanced\"\s+?value\s*?\=s*?\'07\-03\-2009\'/', $output);

        //Simulate the request with range search value
        $_REQUEST['range_date_closed_advanced'] = '07-04-2009';

        $output = $ss->fetch($this->smartyTestFile);
        $this->assertMatchesRegularExpression('/range_date_closed_advanced\"\s+?value\s*?\=s*?\'07\-04\-2009\'/', $output);
    }
}
