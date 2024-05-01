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

//This test is based off of Bug45709Text.php
require_once 'include/SearchForm/SearchForm2.php';

class Bug47735Test extends TestCase
{
    public $user = null;
    public $requestArray = null;
    public $searchForm = null;

    protected function setUp(): void
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
    }

    /**
     *verify that Users search metadata are set up correctly to create a concatenated search on full name from the
     * GenerateSearchWhere function in SearchForm2.php
     */
    public function testGenerateSearchWhereForUsesConcatenatedFullName()
    {
        $searchFields = [];
        $searchdefs = [];
        require 'modules/Users/vardefs.php';
        require 'modules/Users/metadata/SearchFields.php';
        require 'modules/Users/metadata/searchdefs.php';

        //array to simulate REQUEST object, this simulates a basic search using both the first and
        //last name of the newly created anonymous user
        $this->requestArray['module'] = 'Users';
        $this->requestArray['action'] = 'index';
        $this->requestArray['searchFormTab'] = 'basic_search';
        $this->requestArray['search_name_basic'] = $this->user->first_name . ' ' . $this->user->last_name;
        $this->requestArray['query'] = 'true';

        //create new searchform. populate it's values and generate query
        $this->searchForm = new SearchForm($this->user, 'Users');
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->user->getFieldDefinitions();
        $this->searchForm->populateFromArray($this->requestArray, 'basic_search', false);
        $whereArray = $this->searchForm->generateSearchWhere(true, $this->user->module_dir);

        //use the where query to search for the user
        $test_query = 'SELECT id FROM users WHERE ' . $whereArray[0];
        $result = $GLOBALS['db']->query($test_query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        //make sure row is not empty
        $this->assertEquals($this->user->id, $row['id'], 'Did not retrieve any users using the following query: ' . $test_query);

        //make sure retrieved correct user
        $this->assertEquals($this->user->id, $row['id'], 'The query returned records but not the correct one: ' . $test_query);
    }
}
