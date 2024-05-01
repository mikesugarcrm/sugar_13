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

class Bug40247Test extends TestCase
{
    public $has_custom_connectors_file;
    public $has_custom_display_config_file;
    public $has_custom_accounts_detailviewdefs_file;
    public $has_custom_leads_detailviewdefs_file;
    public $has_custom_contacts_detailviewdefs_file;

    protected function setUp(): void
    {
        if (file_exists('custom/modules/connectors/metadata/connectors.php')) {
            $this->has_custom_connectors_file = true;
            copy('custom/modules/connectors/metadata/connectors.php', 'custom/modules/connectors/metadata/connectors.php.bak');
            unlink('custom/modules/connectors/metadata/connectors.php');
        }

        if (file_exists('custom/modules/connectors/metadata/display_config.php')) {
            $this->has_custom_display_config_file = true;
            copy('custom/modules/connectors/metadata/display_config.php', 'custom/modules/connectors/metadata/display_config.php.bak');
            unlink('custom/modules/connectors/metadata/display_config.php');
        }

        if (file_exists('custom/modules/accounts/metadata/detailviewdefs.php')) {
            $this->has_custom_accounts_detailviewdefs_file = true;
            copy('custom/modules/accounts/metadata/detailviewdefs.php', 'custom/modules/accounts/metadata/detailviewdefs.php.bak');
            unlink('custom/modules/accounts/metadata/detailviewdefs.php');
        }

        if (file_exists('custom/modules/contactss/metadata/detailviewdefs.php')) {
            $this->has_custom_contacts_detailviewdefs_file = true;
            copy('custom/modules/contacts/metadata/detailviewdefs.php', 'custom/modules/contacts/metadata/detailviewdefs.php.bak');
            unlink('custom/modules/contacts/metadata/detailviewdefs.php');
        }

        if (file_exists('custom/modules/accounts/metadata/detailviewdefs.php')) {
            $this->has_custom_leads_detailviewdefs_file = true;
            copy('custom/modules/leads/metadata/detailviewdefs.php', 'custom/modules/leads/metadata/detailviewdefs.php.bak');
            unlink('custom/modules/leads/metadata/detailviewdefs.php');
        }

        if (file_exists('custom/modules/Connectors/metadata/mergeviewdefs.php')) {
            unlink('custom/modules/Connectors/metadata/mergeviewdefs.php');
        }
    }

    protected function tearDown(): void
    {
        if ($this->has_custom_connectors_file) {
            copy('custom/modules/connectors/metadata/connectors.php.bak', 'custom/modules/connectors/metadata/connectors.php');
            unlink('custom/modules/connectors/metadata/connectors.php.bak');
        }

        if ($this->has_custom_display_config_file) {
            copy('custom/modules/connectors/metadata/display_config.php.bak', 'custom/modules/connectors/metadata/display_config.php');
            unlink('custom/modules/connectors/metadata/display_config.php.bak');
        }

        if ($this->has_custom_accounts_detailviewdefs_file) {
            copy('custom/modules/accounts/metadata/detailviewdefs.php.bak', 'custom/modules/accounts/metadata/detailviewdefs.php');
            unlink('custom/modules/accounts/metadata/detailviewdefs.php.bak');
        }

        if ($this->has_custom_contacts_detailviewdefs_file) {
            copy('custom/modules/contacts/metadata/detailviewdefs.php.bak', 'custom/modules/contacts/metadata/detailviewdefs.php');
            unlink('custom/modules/contacts/metadata/detailviewdefs.php.bak');
        }

        if ($this->has_custom_leads_detailviewdefs_file) {
            copy('custom/modules/leads/metadata/detailviewdefs.php.bak', 'custom/modules/leads/metadata/detailviewdefs.php');
            unlink('custom/modules/leads/metadata/detailviewdefs.php.bak');
        }
        SugarAutoLoader::buildCache();
    }

    public function test_default_pro_connectors()
    {
        $this->install_connectors();
        if (!file_exists('custom/modules/connectors/metadata/display_config.php')) {
            $this->markTestSkipped('Mark test skipped.  Likely no permission to write to custom directory.');
        }

        $viewdefs = [];

        require 'modules/Accounts/metadata/detailviewdefs.php';
        $this->assertTrue(in_array('CONNECTOR', $viewdefs['Accounts']['DetailView']['templateMeta']['form']['buttons']), 'Assert that the Get Data button is added to Accounts detailviewdefs.php file.');

        $twitter_hover_link_set = false;

        foreach ($viewdefs['Accounts']['DetailView']['panels'] as $panels) {
            foreach ($panels as $panel) {
                foreach ($panel as $row => $col) {
                    if (empty($col)) {
                        continue;
                    }

                    if (is_array($col) && $col['name'] == 'name') {
                        if (isset($col['displayParams']) && isset($col['displayParams']['connectors'])) {
                            foreach ($col['displayParams']['connectors'] as $entry) {
                                if ($entry == 'ext_rest_twitter') {
                                    $twitter_hover_link_set = true;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }

        $this->assertTrue($twitter_hover_link_set, 'Assert that the Twitter hover link is properly set for Accounts.');

        $mod = 'Contacts';

        require "modules/{$mod}/metadata/detailviewdefs.php";
        $twitter_hover_link_set = false;

        foreach ($viewdefs["{$mod}"]['DetailView']['panels'] as $panels) {
            foreach ($panels as $panel) {
                foreach ($panel as $row => $col) {
                    if (empty($col)) {
                        continue;
                    }

                    if (is_array($col) && $col['name'] == 'full_name') {
                        if (isset($col['displayParams']) && isset($col['displayParams']['connectors'])) {
                            foreach ($col['displayParams']['connectors'] as $entry) {
                                if ($entry == 'ext_rest_twitter') {
                                    $twitter_hover_link_set = true;
                                }
                            }
                        }
                    }
                }
            }

            $this->assertTrue($twitter_hover_link_set, "Assert that the Twitter hover link is properly set for {$mod}.");
        }
    }

    private function install_connectors()
    {
        require 'modules/Connectors/InstallDefaultConnectors.php';
    }
}
