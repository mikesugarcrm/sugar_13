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

/**
 * Test checks if SearchDef with 'force_unifiedsearch' => true concatenates the db_field array properly,
 * when the search value is a multiple word term (contains space between the words)
 *
 * @author snigam@sugarcrm.com, avucinic@sugarcrm.com
 */
class Bug45709_53785_Test extends TestCase
{
    public $task = null;
    public $contact = null;
    public $team = null;
    public $requestArray = null;
    public $searchForm = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->task = SugarTestTaskUtilities::createTask();
        $this->task->contact_id = $this->contact->id;
        $this->task->save();
        $this->team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->team->name = '45709';
        $this->team->name_2 = '53785';
        $this->team->save();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
    }

    /**
     * @ticket 45709
     */
    public function testGenerateSearchWhereForFieldsWhenFullContactNameGiven()
    {
        $searchFields = [];
        $searchdefs = [];
        // Array to simulate REQUEST object
        $this->requestArray['module'] = 'Tasks';
        $this->requestArray['action'] = 'index';
        $this->requestArray['searchFormTab'] = 'advanced_search';
        $this->requestArray['contact_name_advanced'] = $this->contact->first_name . ' ' . $this->contact->last_name; //value of a contact name field set in REQUEST object
        $this->requestArray['query'] = 'true';

        // Initialize search form
        $this->searchForm = new SearchForm($this->task, 'Tasks');

        // Load the vardefs and search metadata
        require 'modules/Tasks/vardefs.php';
        require 'modules/Tasks/metadata/SearchFields.php';
        require 'modules/Tasks/metadata/searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->task->getFieldDefinitions();

        // Fill the data from the array we are using to simulate REQUEST
        $this->searchForm->populateFromArray($this->requestArray, 'advanced_search', false);

        // Get the generated search clause
        $whereArray = $this->searchForm->generateSearchWhere(true, $this->task->module_dir);

        // And use it to load the contact created
        $test_query = 'SELECT id FROM contacts WHERE ' . $whereArray[0];
        $result = $GLOBALS['db']->query($test_query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        // Check if the contact was successfully loaded
        $this->assertEquals($this->contact->id, $row['id'], "Didn't find the correct contact id");

        // Load the task using the contact_id we got from the previous query
        $result2 = $GLOBALS['db']->query("SELECT * FROM tasks WHERE tasks.contact_id='{$this->task->contact_id}'");
        $row2 = $GLOBALS['db']->fetchByAssoc($result2);

        // Check if the task is loaded properly
        $this->assertEquals($this->task->id, $row2['id'], "Couldn't find the expected related task");
    }

    /**
     * @ticket 53785
     */
    public function testGenerateSearchWhereForFieldsWhenFullTeamNameGiven()
    {
        $searchFields = [];
        $searchdefs = [];
        // Array to simulate REQUEST object
        $this->requestArray['module'] = 'Teams';
        $this->requestArray['action'] = 'index';
        $this->requestArray['searchFormTab'] = 'basic_search';
        $this->requestArray['name_basic'] = $this->team->name . ' ' . $this->team->name_2; //value of team name field set in REQUEST object
        $this->requestArray['query'] = 'true';

        // Initialize search form
        $this->searchForm = new SearchForm($this->team, 'Teams');

        // Load the vardefs and search metadata
        require 'modules/Teams/vardefs.php';
        require 'modules/Teams/metadata/SearchFields.php';
        require 'modules/Teams/metadata/searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->team->getFieldDefinitions();

        // Fill the data from the array we are using to simulate REQUEST
        $this->searchForm->populateFromArray($this->requestArray, 'basic_search', false);

        // Get the generated search clause
        $whereArray = $this->searchForm->generateSearchWhere(true, $this->team->module_dir);

        // And use it to load the team created
        $test_query = 'SELECT id FROM teams WHERE ' . $whereArray[0];
        $result = $GLOBALS['db']->query($test_query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        // Check if the team was successfully loaded
        $this->assertEquals($this->team->id, $row['id'], "Didn't find the correct team id");
    }
}
