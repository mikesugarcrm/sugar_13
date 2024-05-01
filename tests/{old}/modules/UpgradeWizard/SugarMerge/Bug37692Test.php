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

class Bug37692Test extends TestCase
{
    public $merge;
    public $has_dir;
    public $modules;

    protected function setUp(): void
    {
        $this->modules = ['Project'];
        $this->has_dir = [];

        foreach ($this->modules as $module) {
            if (!file_exists("custom/modules/{$module}/metadata")) {
                mkdir_recursive("custom/modules/{$module}/metadata", true);
            }

            if (file_exists("custom/modules/{$module}")) {
                $this->has_dir[$module] = true;
            }

            $files = ['editviewdefs', 'detailviewdefs'];
            foreach ($files as $file) {
                if (file_exists("custom/modules/{$module}/metadata/{$file}")) {
                    copy("custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php.bak");
                }

                if (file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
                    copy("custom/modules/{$module}/metadata/{$file}.php.suback.php", "custom/modules/{$module}/metadata/{$file}.php.suback.bak");
                }

                if (file_exists("tests/{old}/modules/UpgradeWizard/SugarMerge/od_metadata_files/custom/modules/{$module}/metadata/{$file}.php")) {
                    copy("tests/{old}/modules/UpgradeWizard/SugarMerge/od_metadata_files/custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php");
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
                $files = ['editviewdefs', 'detailviewdefs'];

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

    public function test_project_merge()
    {
        $viewdefs = [];
        $sugar_merge = new SugarMerge('tests/{old}/modules/UpgradeWizard/SugarMerge/od_metadata_files/custom');
        $sugar_merge->mergeModule('Project');
        $this->assertTrue(file_exists('custom/modules/Project/metadata/detailviewdefs.php.suback.php'));
        $this->assertTrue(file_exists('custom/modules/Project/metadata/editviewdefs.php.suback.php'));
        require 'custom/modules/Project/metadata/detailviewdefs.php';
        $this->assertTrue(isset($viewdefs['Project']['DetailView']['panels']['lbl_panel_1']), 'Assert that the original panel index is preserved');
        require 'custom/modules/Project/metadata/editviewdefs.php';
        $this->assertTrue(isset($viewdefs['Project']['EditView']['panels']['default']), 'Assert that the original panel index is preserved');
    }
}
