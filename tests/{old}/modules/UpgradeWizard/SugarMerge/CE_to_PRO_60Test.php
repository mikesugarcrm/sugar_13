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

require_once 'include/dir_inc.php';

class CE_to_PRO_60Test extends TestCase
{
    public $merge;
    public $has_dir;
    public $modules;

    protected function setUp(): void
    {
        $this->modules = ['Contacts'];
        $this->has_dir = [];

        foreach ($this->modules as $module) {
            if (!file_exists("custom/modules/{$module}/metadata")) {
                mkdir_recursive("custom/modules/{$module}/metadata", true);
            }

            if (file_exists("custom/modules/{$module}")) {
                $this->has_dir[$module] = true;
            }

            $files = ['editviewdefs', 'detailviewdefs', 'listviewdefs'];
            foreach ($files as $file) {
                if (file_exists("custom/modules/{$module}/metadata/{$file}")) {
                    copy("custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php.bak");
                }

                if (file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
                    copy("custom/modules/{$module}/metadata/{$file}.php.suback.php", "custom/modules/{$module}/metadata/{$file}.php.suback.bak");
                }

                if (file_exists("tests/{old}/modules/UpgradeWizard/SugarMerge/ce_metadata_files/custom/modules/{$module}/metadata/{$file}.php")) {
                    copy("tests/{old}/modules/UpgradeWizard/SugarMerge/ce_metadata_files/custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php");
                }
            } //foreach
        } //foreach
    }

    protected function tearDown(): void
    {
        foreach ($this->modules as $module) {
            if (!$this->has_dir[$module]) {
                rmdir_recursive("custom/modules/{$module}");
            } else {
                $files = ['editviewdefs', 'detailviewdefs', 'listviewdefs'];

                foreach ($files as $file) {
                    if (file_exists("custom/modules/{$module}/metadata/{$file}.php.bak")) {
                        copy(
                            "custom/modules/{$module}/metadata/{$file}.php.bak",
                            "custom/modules/{$module}/metadata/{$file}.php"
                        );
                        unlink("custom/modules/{$module}/metadata/{$file}.php.bak");
                    } elseif (file_exists("custom/modules/{$module}/metadata/{$file}.php")) {
                        unlink("custom/modules/{$module}/metadata/{$file}.php");
                    }

                    if (file_exists("custom/modules/{$module}/metadata/{$module}.php.suback.bak")) {
                        copy(
                            "custom/modules/{$module}/metadata/{$file}.php.suback.bak",
                            "custom/modules/{$module}/metadata/{$file}.php.suback.php"
                        );
                        unlink("custom/modules/{$module}/metadata/{$file}.php.suback.bak");
                    } elseif (file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
                        unlink("custom/modules/{$module}/metadata/{$file}.php.suback.php");
                    }
                }
            }
        }
    }

    public function test_contacts_detailview_merge()
    {
        $this->merge = new DetailViewMerge();
        $this->merge->merge('Contacts', 'tests/{old}/modules/UpgradeWizard/SugarMerge/ce_metadata_files/600/modules/Contacts/metadata/detailviewdefs.php', 'modules/Contacts/metadata/detailviewdefs.php', 'custom/modules/Contacts/metadata/detailviewdefs.php');
        $this->assertTrue(file_exists('custom/modules/Contacts/metadata/detailviewdefs.php.suback.php'));
        require 'custom/modules/Contacts/metadata/detailviewdefs.php';
        $fields = [];
        $panels = [];

        //echo var_export($viewdefs['Contacts']['DetailView']['panels'], true);
        $columns_sanitized = true;
        foreach ($viewdefs['Contacts']['DetailView']['panels'] as $panel_key => $panel) {
            $panels[$panel_key] = $panel_key;
            foreach ($panel as $r => $row) {
                $new_row = true;
                foreach ($row as $col_key => $col) {
                    if ($new_row && $col_key != 0) {
                        $columns_sanitized = false;
                    }

                    $new_row = false;

                    $id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
                    if (!empty($id) && !is_array($id)) {
                        $fields[$id] = $col;
                    }
                }
            }
        }

        //echo var_export($viewdefs['Contacts']['DetailView']['panels'], true);

        $this->assertTrue(count($panels) == 4, 'Assert that there are 4 panels matching the custom Contacts DetailView layout');
        $this->assertTrue(isset($panels['LBL_PANEL_ASSIGNMENT']), "Assert that 'LBL_PANEL_ASSIGNMENT' panel id is present");
        $this->assertTrue(isset($panels['LBL_PANEL_ADVANCED']), "Assert that 'LBL_PANEL_ADVANCED' panel id is present");
        $this->assertTrue(isset($panels['lbl_detailview_panel1']), "Assert that 'lbl_detailview_panel1' panel id is present");
        $this->assertTrue(isset($panels['lbl_contact_information']), "Assert that 'lbl_contact_information' panel id is present");

        $this->assertTrue(isset($fields['team_name']), 'Assert that team_name field is added');

        $found_test_c = false;
        $found_test2_c = false;

        foreach ($viewdefs['Contacts']['DetailView']['panels']['lbl_detailview_panel1'] as $row) {
            foreach ($row as $col_key => $col) {
                $id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
                if ($id == 'test_c') {
                    $found_test_c = true;
                } elseif ($id == 'test2_c') {
                    $found_test2_c = true;
                }
            }
        }

        $this->assertTrue($found_test_c, 'Assert that test_c custom field is preserved');
        $this->assertTrue($found_test2_c, 'Assert that test2_c custom field is preserved');
    }


    public function test_contacts_editview_merge()
    {
        $this->merge = new EditViewMerge();
        $this->merge->merge('Contacts', 'tests/{old}/modules/UpgradeWizard/SugarMerge/ce_metadata_files/600/modules/Contacts/metadata/editviewdefs.php', 'modules/Contacts/metadata/editviewdefs.php', 'custom/modules/Contacts/metadata/editviewdefs.php');
        $this->assertTrue(file_exists('custom/modules/Contacts/metadata/editviewdefs.php.suback.php'));
        require 'custom/modules/Contacts/metadata/editviewdefs.php';
        $fields = [];
        $panels = [];

        //echo var_export($viewdefs['Contacts']['EditView']['panels'], true);
        $columns_sanitized = true;
        foreach ($viewdefs['Contacts']['EditView']['panels'] as $panel_key => $panel) {
            $panels[$panel_key] = $panel_key;
            foreach ($panel as $r => $row) {
                $new_row = true;
                foreach ($row as $col_key => $col) {
                    if ($new_row && $col_key != 0) {
                        $columns_sanitized = false;
                    }

                    $new_row = false;

                    $id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
                    if (!empty($id) && !is_array($id)) {
                        $fields[$id] = $col;
                    }
                }
            }
        }


        //echo var_export($viewdefs['Contacts']['EditView']['panels'], true);

        //$this->assertTrue(count($panels) == 4, "Assert that there are 4 panels matching the custom Contacts EditView layout");
        $this->assertTrue(isset($panels['LBL_PANEL_ASSIGNMENT']), "Assert that 'LBL_PANEL_ASSIGNMENT' panel id is present");
        $this->assertTrue(isset($panels['LBL_PANEL_ADVANCED']), "Assert that 'LBL_PANEL_ADVANCED' panel id is present");
        $this->assertTrue(isset($panels['lbl_editview_panel1']), "Assert that 'lbl_editview_panel1' panel id is present");
        $this->assertTrue(isset($panels['lbl_contact_information']), "Assert that 'lbl_contact_information' panel id is present");

        $this->assertTrue(isset($fields['team_name']), 'Assert that team_name field is added');

        $found_test_c = false;
        $found_test2_c = false;

        foreach ($viewdefs['Contacts']['EditView']['panels']['lbl_editview_panel1'] as $row) {
            foreach ($row as $col_key => $col) {
                $id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
                if ($id == 'test_c') {
                    $found_test_c = true;
                } elseif ($id == 'test2_c') {
                    $found_test2_c = true;
                }
            }
        }

        $this->assertTrue($found_test_c, 'Assert that test_c custom field is preserved');
        $this->assertTrue($found_test2_c, 'Assert that test2_c custom field is preserved');
    }


    public function test_contacts_listview_merge()
    {
        $listViewDefs = [];
        $this->merge = new ListViewMerge();
        $this->merge->merge('Contacts', 'tests/{old}/modules/UpgradeWizard/SugarMerge/ce_metadata_files/600/modules/Contacts/metadata/listviewdefs.php', 'modules/Contacts/metadata/listviewdefs.php', 'custom/modules/Contacts/metadata/listviewdefs.php');
        $this->assertTrue(file_exists('custom/modules/Contacts/metadata/listviewdefs.php.suback.php'));
        require 'custom/modules/Contacts/metadata/listviewdefs.php';

        $this->assertTrue(isset($listViewDefs ['Contacts']['TEST_C']), 'Assert that TEST_C field is preserved');
        $this->assertTrue(isset($listViewDefs ['Contacts']['TEST2_C']), 'Assert that TEST2_C field is preserved');
        $this->assertTrue($listViewDefs ['Contacts']['TEST_C']['default'], 'Assert that TEST_C field is shown');
        $this->assertTrue($listViewDefs ['Contacts']['TEST2_C']['default'], 'Assert that TEST2_C field is shown');
    }
}
