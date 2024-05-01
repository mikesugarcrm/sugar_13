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

class Bug46713Test extends TestCase
{
    private $hasExistingCustomSearchFields = false;
    public $searchForm;
    public $originalDbType;
    public $smartyTestFile;

    protected function setUp(): void
    {
        if (file_exists('custom/modules/Cases/metadata/SearchFields.php')) {
            $this->hasExistingCustomSearchFields = true;
            copy('custom/modules/Cases/metadata/SearchFields.php', 'custom/modules/Cases/metadata/SearchFields.php.bak');
            unlink('custom/modules/Cases/metadata/SearchFields.php');
        } elseif (!file_exists('custom/modules/Cases/metadata')) {
            mkdir_recursive('custom/modules/Cases/metadata');
        }

        //Setup Opportunities module and date_closed field
        $_REQUEST['view_module'] = 'Cases';
        $_REQUEST['name'] = 'date_closed';
        $templateDate = new TemplateDate();
        $templateDate->enable_range_search = true;
        $templateDate->populateFromPost();
        include 'custom/modules/Cases/metadata/SearchFields.php';

        //Prepare SearchForm
        $seed = new aCase();
        $module = 'Cases';
        $this->searchForm = new SearchForm($seed, $module);
        $this->searchForm->searchFields = [
            'range_case_number' => [
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
            unlink('custom/modules/Cases/metadata/SearchFields.php');
        }

        if (file_exists('custom/modules/Cases/metadata/SearchFields.php.bak')) {
            copy('custom/modules/Cases/metadata/SearchFields.php.bak', 'custom/modules/Cases/metadata/SearchFields.php');
            unlink('custom/modules/Cases/metadata/SearchFields.php.bak');
        }

        if (file_exists($this->smartyTestFile)) {
            unlink($this->smartyTestFile);
        }
    }

    public function testRangeNumberSearches()
    {
        $GLOBALS['db']->dbType = 'mysql';

        $this->searchForm->searchFields['range_case_number'] = [
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '0',
            'operator' => '=',
        ];

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals('cases.case_number = 0', $where_clauses[0]);
    }
}
