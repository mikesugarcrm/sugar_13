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

class Bug45573Test extends TestCase
{
    public $hasCustomSearchFields;

    protected function setUp(): void
    {
        $beanList = null;
        $beanFiles = null;
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = true;

        if (file_exists('custom/modules/Cases/metadata/SearchFields.php')) {
            $this->hasCustomSearchFields = true;
            copy('custom/modules/Cases/metadata/SearchFields.php', 'custom/modules/Cases/metadata/SearchFields.php.bak');
            unlink('custom/modules/Cases/metadata/SearchFields.php');
        }
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        if ($this->hasCustomSearchFields && file_exists('custom/modules/Cases/metadata/SearchFields.php.bak')) {
            copy('custom/modules/Cases/metadata/SearchFields.php.bak', 'custom/modules/Cases/metadata/SearchFields.php');
            unlink('custom/modules/Cases/metadata/SearchFields.php.bak');
        } elseif (!$this->hasCustomSearchFields && file_exists('custom/modules/Cases/metadata/SearchFields.php')) {
            unlink('custom/modules/Cases/metadata/SearchFields.php');
        }

        //Refresh vardefs for Cases to reset
        VardefManager::loadVardef('Cases', 'aCase', true);
    }

    /**
     * testActionAdvancedSearchViewSave
     * This method tests to ensure that custom SearchFields are created or updated when a search layout change is made
     */
    public function testActionAdvancedSearchViewSave()
    {
        $searchFields = [];
        $mbController = new ModuleBuilderController();
        $_REQUEST['view_module'] = 'Cases';
        $_REQUEST['view'] = 'advanced_search';
        $mbController->action_searchViewSave();
        $this->assertTrue(file_exists('custom/modules/Cases/metadata/SearchFields.php'));

        require 'custom/modules/Cases/metadata/SearchFields.php';
        $this->assertTrue(isset($searchFields['Cases']['range_date_entered']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_entered']['enable_range_search']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_modified']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_modified']['enable_range_search']));
    }

    /**
     * testActionBasicSearchViewSave
     * This method tests to ensure that custom SearchFields are created or updated when a search layout change is made
     */
    public function testActionBasicSearchViewSave()
    {
        $searchFields = [];
        $mbController = new ModuleBuilderController();
        $_REQUEST['view_module'] = 'Cases';
        $_REQUEST['view'] = 'basic_search';
        $mbController->action_searchViewSave();
        $this->assertTrue(file_exists('custom/modules/Cases/metadata/SearchFields.php'));

        require 'custom/modules/Cases/metadata/SearchFields.php';
        $this->assertTrue(isset($searchFields['Cases']['range_date_entered']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_entered']['enable_range_search']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_modified']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_modified']['enable_range_search']));
    }


    /**
     * testActionAdvancedSearchSaveWithoutAnyRangeSearchFields
     * One last test to check what would happen if we had a module that did not have any range search fields enabled
     */
    public function testActionAdvancedSearchSaveWithoutAnyRangeSearchFields()
    {
        $searchFields = [];
        //Load the vardefs for the module to pass to TemplateRange
        VardefManager::loadVardef('Cases', 'aCase', true);
        global $dictionary;
        $vardefs = $dictionary['Case']['fields'];
        foreach ($vardefs as $key => $def) {
            if (!empty($def['enable_range_search'])) {
                unset($vardefs[$key]['enable_range_search']);
            }
        }

        TemplateRange::repairCustomSearchFields($vardefs, 'Cases');

        //In this case there would be no custom SearchFields.php file created
        $this->assertTrue(!file_exists('custom/modules/Cases/metadata/SearchFields.php'));

        //Yet we have the defaults set still in out of box settings
        require 'modules/Cases/metadata/SearchFields.php';
        $this->assertTrue(isset($searchFields['Cases']['range_date_entered']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_entered']['enable_range_search']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_modified']));
        $this->assertTrue(isset($searchFields['Cases']['range_date_modified']['enable_range_search']));
    }
}
