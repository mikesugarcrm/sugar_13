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

class ConnectorsFormatterTest extends Sugar_Connectors_TestCase
{
    public $parentFieldArray;
    public $vardef;
    public $displayParams;
    public $tabindex;
    public $ss;

    protected function setUp(): void
    {
        //Store original files
        if (!file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
            $the_string = <<<EOQ
<?php
\$modules_sources = array (
  'Accounts' =>
  array (
    'ext_rest_twitter' => 'ext_rest_twitter',
  ),
  'Opportunities' =>
  array (
    'ext_rest_twitter' => 'ext_rest_twitter',
  ),
  'Contacts' =>
  array (
  ),
);

EOQ;
            file_put_contents(CONNECTOR_DISPLAY_CONFIG_FILE, $the_string);
        }
        parent::setUp();

        if (file_exists('custom/modules/Connectors/connectors/sources/ext/rest/twitter/twitter.php')) {
            copy_recursive('custom/modules/Connectors/connectors/sources/ext/rest/twitter', 'custom/modules/Connectors/backup/connectors/sources/ext/rest/twitter_backup');
            ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/twitter');
        }

        //Setup the neccessary Smarty configurations
        $this->parentFieldArray = 'fields';
        VardefManager::loadVardef('Accounts', 'Account', true);
        require_once 'cache/modules/Accounts/Accountvardefs.php';
        $this->vardef = $GLOBALS['dictionary']['Account']['fields']['name'];
        $this->displayParams = ['sources' => ['ext_rest_twitter']];
        $this->tabindex = 0;
        $this->ss = new Sugar_Smarty();
        $this->ss->assign('parentFieldArray', $this->parentFieldArray);
        $this->ss->assign('vardef', $this->vardef);
        $this->ss->assign('displayParams', $this->displayParams);
        $this->ss->left_delimiter = '{{';
        $this->ss->right_delimiter = '}}';

        //Setup the mapping to guarantee that we have hover fields for the Accounts module
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;
        $_REQUEST['modify'] = true;
        $_REQUEST['action'] = 'SaveModifyMapping';
        $_REQUEST['mapping_values'] = '';
        $_REQUEST['mapping_sources'] = 'ext_rest_twitter';

        $controller = new ConnectorsController();
        $controller->action_SaveModifyMapping();

        FormatterFactory::$formatter_map = [];
        ConnectorFactory::$source_map = [];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists('custom/modules/Connectors/connectors/sources/ext/rest/twitter_backup/twitter.php')) {
            copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/rest/twitter_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/twitter');
            ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/twitter_backup');
        }
    }

    public function testHoverLinkForAccounts()
    {
        $enabled_sources = ConnectorUtils::getModuleConnectors('Accounts');
        $hover_sources = [];
        $displayParams = [];
        $displayParams['module'] = 'Accounts';
        $displayParams['enableConnectors'] = true;

        foreach ($enabled_sources as $id => $mapping) {
            $source = SourceFactory::getSource($id);
            if ($source->isEnabledInHover()) {
                $parts = preg_split('/_/', $id);
                $hover_sources[$parts[safeCount($parts) - 1]] = $id;
                $displayParams['connectors'][] = $id;
            }
        }

        $output = ConnectorUtils::getConnectorButtonScript($displayParams, $this->ss);
        preg_match_all('/<div[^\>]*?>/', $output, $matches);
        $this->assertTrue(isset($matches[0]));
    }

    public function testRemoveHoverLinksInViewDefs()
    {
        $module = 'Accounts';

        if (file_exists("custom/modules/{$module}/metadata/detailviewdefs.php")) {
            require "custom/modules/{$module}/metadata/detailviewdefs.php";
        } elseif (file_exists("modules/{$module}/metadata/detailviewdefs.php")) {
            require "modules/{$module}/metadata/detailviewdefs.php";
        }

        $this->assertTrue(!empty($viewdefs));

        //Remove hover fields
        ConnectorUtils::removeHoverField($viewdefs, $module);
        $foundHover = false;
        foreach ($viewdefs[$module]['DetailView']['panels'] as $panel_id => $panel) {
            foreach ($panel as $row_id => $row) {
                foreach ($row as $field_id => $field) {
                    if (is_array($field) && !empty($field['displayParams']['enableConnectors'])) {
                        $foundHover = true;
                    }
                } //foreach
            } //foreach
        } //foreach

        //There should have been no hover fields found
        $this->assertTrue(!$foundHover);
    }
}
