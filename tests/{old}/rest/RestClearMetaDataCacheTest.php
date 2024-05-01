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

/**
 * Bug 59210 - Editing a modules field in Studio does not take affect immediately
 * in metadata API requests.
 *
 * This test confirms that when certain metadata containing elements are edited
 * in studio that they indeed clear the metadata cache. While bug 59210 is specific
 * to field edits, the same condition existed among all studio related editable
 * elements, which was the metadata cache was not being invalidated when edits
 * were made.
 *
 * NOTE: this will be a fairly long running test since it will be testing cache
 * clearing of the API metadata after certain UI tasks are carried out.
 */
class RestClearMetadataCacheTest extends RestTestBase
{
    /**
     * Collection of teardown methods to call after the test is run
     *
     * @var array
     */
    private $teardowns = [];

    /**
     * Holder for the current request array
     *
     * @var array
     */
    private $request = [];

    /**
     * Object containing various request arrays
     *
     * @var RestCacheClearRequestMock
     */
    private $requestMock;

    /**
     * Flag used in handling modListHeader global
     *
     * @var bool
     */
    private $modListHeaderSet = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestMock = new RestCacheClearRequestMock();

        // User needs to be an admin user
        $this->user->is_admin = 1;
        $this->user->save();

        // Backup the request
        $this->request = $_REQUEST;

        // Setup one GLOBAL for relationships
        if (!isset($GLOBALS['modListHeader'])) {
            $GLOBALS['modListHeader'] = query_module_access_list($this->user);
            $this->modListHeaderSet = true;
        }

        // Back up the current file if there is one
        if (file_exists($this->requestMock->ddlCustomFile)) {
            rename($this->requestMock->ddlCustomFile, $this->requestMock->ddlCustomFile . '.testbackup');
        }

        // Create an empty test custom file
        mkdir_recursive(dirname($this->requestMock->ddlCustomFile));
        sugar_file_put_contents($this->requestMock->ddlCustomFile, '<?php' . "\n");

        // Force a mobile platform
        $this->restLogin($this->user->user_name, $this->user->user_name, 'mobile');

        // Lets clear the metadata cache to make sure we are start with fresh data
        $this->clearMetadataCache();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->requestMock->ddlCustomFile . '.testbackup')) {
            rename($this->requestMock->ddlCustomFile . '.testbackup', $this->requestMock->ddlCustomFile);
        }
        // This should really only happen if the test suite doesn't pass completely
        foreach ($this->teardowns as $teardown) {
            $this->$teardown();
        }

        // Set the request back to what it was originally
        $_REQUEST = $this->request;

        // Clean up at the parent
        parent::tearDown();

        // Handle modListHeader
        if ($this->modListHeaderSet) {
            unset($GLOBALS['modListHeader']);
        }
    }

    /**
     * Tests relationship create, edit and delete reflect immediately in metadata
     * requests
     *
     * @group rest
     */
    public function testRelationshipChangesClearMetadataCache()
    {
        // Base private metadata manager
        $mm = MetaDataManager::getManager();
        $mm->rebuildCache();

        // Create a relationship
        $_REQUEST = $this->requestMock->createRelationshipRequestVars;
        $relationships = new DeployedRelationships($_REQUEST ['view_module']);
        // This should return the new relationship object
        $new = $relationships->addFromPost();
        // Get the new relationship name since we will need that in assertions
        $relName = $new->getName();

        // We also need it in our delete process, so set it there now
        $this->requestMock->createRelationshipRequestVars['relationship_name'] = $relName;

        // Finish the save now
        $relationships->save();
        $relationships->build();

        // Add to the teardown stack for catching failures
        $this->teardowns['r'] = 'tearDownRelationship';

        // Test relationship shows in metadata
        $data = $mm->getMetadata();
        $this->assertNotEmpty($data['relationships'][$relName], 'The created relationship was not found in the metadata response and it should have been');

        // Delete the relationship and remove the teardown method from the
        // teardown stack since at this point it will have cleaned itself up
        $this->tearDownRelationship();
        unset($this->teardowns['r']);

        // Test relationship no longer shows up
        $data = $mm->getMetadata();
        $this->assertFalse(isset($data['relationships'][$relName]), 'The created relationship was found in the metadata response and it should not have been');
    }

    private function tearDownRelationship()
    {
        $_REQUEST = $this->requestMock->createRelationshipRequestVars;
        $mb = new ModuleBuilderController();
        $mb->action_DeleteRelationship();
    }
}

/**
 * Mock collection object of various requests used in changing metadata elements
 */
class RestCacheClearRequestMock
{
    /**
     * Mock request for creating a field
     *
     * @var array
     */
    public $createFieldRequestVars = [
        'action' => 'saveField',
        'comments' => '',
        'default' => '',
        'dependency' => '',
        'dependency_display' => '',
        'duplicate_merge' => '0',
        'enforced' => 'false',
        'formula' => '',
        'formula_display' => '',
        'help' => '',
        'importable' => 'true',
        'is_update' => 'true',
        'labelValue' => 'Unit Testy',
        'label' => 'LBL_UNIT_TESTY',
        'new_dropdown' => '',
        'reportableCheckbox' => '1',
        'reportable' => '1',
        'to_pdf' => 'true',
        'type' => 'varchar',
        'name' => 'unit_testy',
        'module' => 'ModuleBuilder',
        'view_module' => 'Accounts',
    ];

    /**
     * Mock request for deleting a field
     *
     * @var array
     */
    public $deleteFieldRequestVars = [
        'action' => 'DeleteField',
        'labelValue' => 'Unit Testosterone',
        'label' => 'LBL_UNIT_TESTY',
        'to_pdf' => 'true',
        'type' => 'varchar',
        'name' => 'unit_testy_c',
        'module' => 'ModuleBuilder',
        'view_module' => 'Accounts',
    ];

    /**
     * Mock relationship request
     *
     * @var array
     */
    public $createRelationshipRequestVars = [
        'to_pdf' => '1',
        'module' => 'ModuleBuilder',
        'action' => 'SaveRelationship',
        'remove_tables' => 'true',
        'view_module' => 'Bugs',
        'relationship_name' => '',
        'lhs_module' => 'Bugs',
        'relationship_type' => 'one-to-one',
        'rhs_module' => 'Accounts',
    ];

    /**
     * Mock dropdown list items
     *
     * @var array
     */
    public $ddlItems = [
        ['jimmy', 'Jimmy'],
        ['jerry', 'Jerry'],
        ['jenny', 'Jenny'],
    ];

    /**
     * Mock dropdownlist request
     *
     * @var array
     */
    public $ddlFieldRequestVars = [
        'list_value' => '',
        'dropdown_lang' => 'en_us',
        'dropdown_name' => 'unit_test_dropdown',
        'view_package' => 'studio',
    ];

    /**
     * Custom file created by the dropdownlist save
     * @var string
     */
    public $ddlCustomFile = 'custom/include/language/en_us.lang.php';

    /**
     * Setup the dropdown list elements
     */
    public function __construct()
    {
        // Prepare the dropdownlist items
        $this->ddlFieldRequestVars['list_value'] = json_encode($this->ddlItems);
    }
}
