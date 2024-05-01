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

class Bug36845Test extends TestCase
{
    public $has_custom_unified_search_modules_display = false;
    public $has_custom_unified_search_modules = false;
    public $module_dir = 'modules/clabc_Bug36845Test';
    public $module = 'clabc_Bug36845Test';

    protected function setUp(): void
    {
        global $beanFiles, $beanList;
        require 'include/modules.php';

        if (file_exists(sugar_cached('modules/unified_search_modules.php'))) {
            $this->has_custom_unified_search_modules = true;
            copy(sugar_cached('modules/unified_search_modules.php'), sugar_cached('modules/unified_search_modules.php.bak'));
        }

        if (file_exists('custom/modules/unified_search_modules_display.php')) {
            $this->has_custom_unified_search_modules_display = true;
            copy('custom/modules/unified_search_modules_display.php', 'custom/modules/unified_search_modules_display.php.bak');
        }

        if (file_exists($this->module_dir)) {
            rmdir_recursive($this->module_dir);
        }

        mkdir_recursive($this->module_dir . '/metadata');

        $the_string = <<<EOQ
<?php
\$module_name = "{$this->module}";
\$searchFields["{$this->module}"] =
    array (
        'name' => array( 'query_type'=>'default'),
        'account_type'=> array('query_type'=>'default', 'options' => 'account_type_dom', 'template_var' => 'ACCOUNT_TYPE_OPTIONS'),
        'industry'=> array('query_type'=>'default', 'options' => 'industry_dom', 'template_var' => 'INDUSTRY_OPTIONS'),
        'annual_revenue'=> array('query_type'=>'default'),
        'address_street'=> array('query_type'=>'default','db_field'=>array('billing_address_street','shipping_address_street')),
        'address_city'=> array('query_type'=>'default','db_field'=>array('billing_address_city','shipping_address_city')),
        'address_state'=> array('query_type'=>'default','db_field'=>array('billing_address_state','shipping_address_state')),
        'address_postalcode'=> array('query_type'=>'default','db_field'=>array('billing_address_postalcode','shipping_address_postalcode')),
        'address_country'=> array('query_type'=>'default','db_field'=>array('billing_address_country','shipping_address_country')),
        'rating'=> array('query_type'=>'default'),
        'phone'=> array('query_type'=>'default','db_field'=>array('phone_office')),
        'email'=> array('query_type'=>'default','db_field'=>array('email1','email2')),
        'website'=> array('query_type'=>'default'),
        'ownership'=> array('query_type'=>'default'),
        'employees'=> array('query_type'=>'default'),
        'ticker_symbol'=> array('query_type'=>'default'),
        'current_user_only'=> array('query_type'=>'default','db_field'=>array('assigned_user_id'),'my_items'=>true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        'assigned_user_id'=> array('query_type'=>'default'),
        'favorites_only' => array(
            'query_type'=>'format',
			'operator' => 'subquery',
			'subquery' => 'SELECT sugarfavorites.record_id FROM sugarfavorites
			                    WHERE sugarfavorites.deleted=0
			                        and sugarfavorites.module = \''.\$module_name.'\'
			                        and sugarfavorites.assigned_user_id = \'{0}\'',
			'db_field'=>array('id')),
	);
?>
EOQ;

        $fp = sugar_fopen($this->module_dir . '/metadata/SearchFields.php', 'w');
        fwrite($fp, $the_string);
        fclose($fp);
        $table_name = strtolower($this->module);
        $the_string = <<<EOQ
<?php
\$dictionary["{$this->module}"] = array(
    'table'=>"{$table_name}",
    'audited'=>true,
    'fields'=>array (
),
    'relationships'=>array (
),
    'optimistic_locking'=>true,
);
if (!class_exists('VardefManager')){
}
VardefManager::createVardef("{$this->module}","{$this->module}", array('basic','team_security','assignable','company'));
?>
EOQ;

        $fp = sugar_fopen($this->module_dir . '/vardefs.php', 'w');
        fwrite($fp, $the_string);
        fclose($fp);

        $the_string = <<<EOQ
<?php
class clabc_Bug36845Test extends Basic
{
}
?>
EOQ;
        $fp = sugar_fopen($this->module_dir . '/clabc_Bug36845Test.php', 'w');
        fwrite($fp, $the_string);
        fclose($fp);
        require 'include/modules.php';
        global $beanFiles, $beanList;

        $beanFiles['clabc_Bug36845Test'] = 'modules/clabc_Bug36845Test/clabc_Bug36845Test.php';
        $beanList['clabc_Bug36845Test'] = 'clabc_Bug36845Test';
    }

    protected function tearDown(): void
    {
        //Unset the clabc_Bug36845Test references
        unset($GLOBALS['beanList']['clabc_Bug36845Test']);
        unset($GLOBALS['beanFiles']['clabc_Bug36845Test']);

        if (file_exists(sugar_cached('modules/unified_search_modules.php'))) {
            unlink(sugar_cached('modules/unified_search_modules.php'));
        }

        if ($this->has_custom_unified_search_modules) {
            copy(sugar_cached('modules/unified_search_modules.php.bak'), sugar_cached('modules/unified_search_modules.php'));
            unlink(sugar_cached('modules/unified_search_modules.php.bak'));
        }

        if ($this->has_custom_unified_search_modules_display) {
            copy('custom/modules/unified_search_modules_display.php.bak', 'custom/modules/unified_search_modules_display.php');
            unlink('custom/modules/unified_search_modules_display.php.bak');
        } else {
            unlink('custom/modules/unified_search_modules_display.php');
        }

        if (file_exists("custom/{$this->module_dir}/metadata")) {
            rmdir_recursive("custom/{$this->module_dir}/metadata");
        }

        if (file_exists($this->module_dir)) {
            rmdir_recursive($this->module_dir);
        }
    }

    public function test_update_custom_vardefs()
    {
        $unified_search_modules = [];
        $this->assertTrue(file_exists("{$this->module_dir}/metadata/SearchFields.php"), 'Assert that we have a SearchFields.php file');
        $this->assertTrue(file_exists("{$this->module_dir}/vardefs.php"), 'Assert that we have a vardefs.php file');
        require_once 'modules/UpgradeWizard/uw_utils.php';
        add_unified_search_to_custom_modules_vardefs();
        $usa = new UnifiedSearchAdvanced();
        $usa->buildCache();
        $this->assertTrue(file_exists(sugar_cached('modules/unified_search_modules.php')), 'Assert that we have a unified_search_modules.php file');
        include sugar_cached('modules/unified_search_modules.php');
        $this->assertTrue(isset($unified_search_modules['clabc_Bug36845Test']), 'Assert that the custom module was added to unified_search_modules.php');
        $this->assertEquals(false, $unified_search_modules['clabc_Bug36845Test']['default'], 'Assert that the custom module was set to not be searched on by default');
    }


    public function test_update_custom_vardefs_without_searchfields()
    {
        $unified_search_modules = [];
        unlink("{$this->module_dir}/metadata/SearchFields.php");
        $this->assertTrue(!file_exists("{$this->module_dir}/metadata/SearchFields.php"), 'Assert that we have a SearchFields.php file');
        $this->assertTrue(file_exists("{$this->module_dir}/vardefs.php"), 'Assert that we have a vardefs.php file');
        require_once 'modules/UpgradeWizard/uw_utils.php';
        add_unified_search_to_custom_modules_vardefs();
        $usa = new UnifiedSearchAdvanced();
        $usa->buildCache();

        $this->assertTrue(file_exists(sugar_cached('modules/unified_search_modules.php')), 'Assert that we have a unified_search_modules.php file');
        include sugar_cached('modules/unified_search_modules.php');
        $this->assertTrue(empty($unified_search_modules['clabc_Bug36845Test']), 'Assert that the custom module was not added to unified_search_modules.php');
    }


    public function test_create_unified_search_modules_display()
    {
        if (file_exists('custom/modules/unified_search_modules_display.php')) {
            unlink('custom/modules/unified_search_modules_display.php');
        }

        require_once 'modules/UpgradeWizard/uw_utils.php';
        $usa = new UnifiedSearchAdvanced();
        $_REQUEST['enabled_modules'] = 'Accounts,Bug36845Test';
        $usa->saveGlobalSearchSettings();
        $this->assertTrue(file_exists('custom/modules/unified_search_modules_display.php'), 'Assert that unified_search_modules_display.php file was created');
    }
}
