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
 * This class moves test metadata files into legacy locations to test the upgrade
 * routine. Will back up any existing legacy and sidecar files, and restore them,
 * as needed.
 */
class SidecarMetaDataFileBuilder
{
    /**
     * The files created for testing.
     *
     * @var array
     */
    private $created = [];

    /**
     * The file suffix to use when creating a backup
     *
     * @var string
     */
    private $backupSuffix = '_unittest.bak';

    /**
     * The list of test files to make
     *
     * @var array
     */
    private $filesToMake = [
        [
            'module' => 'Accounts', 'view' => 'edit', 'type' => 'mobile',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Accountswirelessedit.php',
            'legacypath' => 'custom/history/modules/Accounts/metadata/wireless.editviewdefs.php_1341122961',
            'sidecarpath' => 'custom/history/modules/Accounts/clients/mobile/views/edit/edit.php_1341122961',
        ],
        [
            'module' => 'Accounts', 'view' => 'detail', 'type' => 'mobile',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Accountswirelessdetail.php',
            'legacypath' => 'custom/working/modules/Accounts/metadata/wireless.detailviewdefs.php',
            'sidecarpath' => 'custom/working/modules/Accounts/clients/mobile/views/detail/detail.php',
        ],
        [
            'module' => 'Bugs', 'view' => 'list', 'type' => 'mobile',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Bugswirelesslist.php',
            'legacypath' => 'custom/modules/Bugs/metadata/wireless.listviewdefs.php',
            'sidecarpath' => 'custom/modules/Bugs/clients/mobile/views/list/list.php',
        ],
        [
            'module' => 'Bugs', 'view' => 'search', 'type' => 'mobile',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Bugswirelesssearch.php',
            'legacypath' => 'custom/working/modules/Bugs/metadata/wireless.searchdefs.php',
            'sidecarpath' => 'custom/working/modules/Bugs/clients/mobile/views/search/search.php',
        ],
        [
            'module' => 'Bugs', 'view' => 'list', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Bugslist.php',
            'legacypath' => 'custom/modules/Bugs/metadata/listviewdefs.php',
            'sidecarpath' => 'custom/modules/Bugs/clients/base/views/list/list.php',
        ],
        [
            'module' => 'Accounts', 'view' => 'edit', 'type' => 'portal',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Accountswirelessedit.php',
            'legacypath' => 'custom/history/modules/Accounts/clients/portal/views/edit/edit.php_1341122961',
            // history is deleted for portal/edit
            'sidecarpath' => '',
        ],
        [
            'module' => 'Bugs', 'view' => 'list', 'type' => 'portal',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Bugsportallist.php',
            'legacypath' => 'custom/portal/modules/Bugs/metadata/listviewdefs.php',
            'sidecarpath' => 'custom/modules/Bugs/clients/portal/views/list/list.php',
        ],
        [
            'module' => 'Bugs', 'view' => 'search', 'type' => 'portal',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Bugsportalsearch.php',
            'legacypath' => 'custom/portal/modules/Bugs/metadata/searchformdefs.php',
            'sidecarpath' => 'custom/modules/Bugs/clients/portal/views/search/search.php',
        ],
        // tabs/panels
        [
            'module' => 'Opportunities', 'view' => 'record', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Oppdetail.php',
            'legacypath' => 'custom/modules/Opportunities/metadata/detailviewdefs.php',
            'sidecarpath' => 'custom/modules/Opportunities/clients/base/views/record/record.php',
        ],
        // portal record view
        [
            'module' => 'Cases', 'view' => 'record', 'type' => 'portal',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Cases67portaledit.php',
            'legacypath' => 'custom/modules/Cases/clients/portal/views/edit/edit.php',
            'sidecarpath' => 'custom/modules/Cases/clients/portal/views/record/record.php',
        ],
        [
            'module' => 'Cases', 'view' => 'record', 'type' => 'portal',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Casesportaledit.php',
            'legacypath' => 'custom/working/portal/modules/Cases/metadata/editviewdefs.php',
            'sidecarpath' => 'custom/working/modules/Cases/clients/portal/views/record/record.php',
        ],
        // Record view, and merge combo fields and special fields
        [
            'module' => 'Accounts', 'view' => 'record', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Accountsedit.php',
            'legacypath' => 'custom/modules/Accounts/metadata/editviewdefs.php',
            'sidecarpath' => 'custom/modules/Accounts/clients/base/views/record/record.php',
        ],
        [
            'module' => 'Contacts', 'view' => 'record', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Contactsdetail.php',
            'legacypath' => 'custom/modules/Contacts/metadata/detailviewdefs.php',
            'sidecarpath' => 'custom/modules/Contacts/clients/base/views/record/record.php',
        ],
        [
            'module' => 'Contacts', 'view' => 'record', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Contactsedit.php',
            'legacypath' => 'custom/modules/Contacts/metadata/editviewdefs.php',
            'sidecarpath' => 'custom/modules/Contacts/clients/base/views/record/record.php',
        ],
        [
            'module' => 'Leads', 'view' => 'record', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Leadsdetail.php',
            'legacypath' => 'custom/modules/Leads/metadata/detailviewdefs.php',
            'sidecarpath' => 'custom/modules/Leads/clients/base/views/record/record.php',
        ],
        // Search defs to filter
        [
            'module' => 'Accounts', 'view' => 'filter', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Accountsearchdefs.php',
            'legacypath' => 'custom/modules/Accounts/metadata/searchdefs.php',
            'sidecarpath' => 'custom/modules/Accounts/clients/base/filters/default/default.php',
        ],
        // Menu files
        [
            'module' => 'Bugs', 'view' => 'menu', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Bugsmenudefs.php',
            'legacypath' => 'custom/Extension/modules/Bugs/Ext/Menus/test1.php',
            'sidecarpath' => 'custom/Extension/modules/Bugs/Ext/clients/base/menus/header/test1.php',
        ],
        [
            'module' => 'Accounts', 'view' => 'menu', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Accountsmenudefs.php',
            'legacypath' => 'custom/modules/Accounts/Menu.php',
            'sidecarpath' => 'custom/modules/Accounts/clients/base/menus/header/header.php',
        ],
        [
            'module' => 'Cases', 'view' => 'quickmenu', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/Casesquickdefs.php',
            'legacypath' => 'custom/modules/Cases/clients/base/menus/quickcreate/quickcreate.php',
            'sidecarpath' => 'custom/modules/Cases/clients/base/menus/quickcreate/quickcreate.php',
        ],

        [
            'module' => 'ProductTemplates', 'view' => 'record', 'type' => 'base',
            'testpath' => 'tests/{old}/modules/UpgradeWizard/metadata/ProductTemplatesedit.php',
            'legacypath' => 'custom/modules/ProductTemplates/metadata/editviewdefs.php',
            'sidecarpath' => 'custom/modules/ProductTemplates/clients/base/views/record/record.php',
        ],
    ];

    /**
     * Builds the test files by moving them into their legacy locations. Will also
     * back up any existing files that need to be backed up
     */
    public function buildFiles()
    {
        foreach ($this->filesToMake as $filedata) {
            SugarTestHelper::saveFile($filedata['legacypath']);
            SugarTestHelper::saveFile($filedata['sidecarpath']);
            $this->installTestFile($filedata);
        }
    }

    /**
     * Tears down the test files that were created, the sidecar files that were
     * made during the upgrade and restores all backed up files
     */
    public function teardownFiles()
    {
        foreach ($this->created as $file) {
            // Kill the file we made for testing
            unlink($file);
        }

        // Kill the sidecar files that were created in testing
        foreach ($this->filesToMake as $file) {
            if (file_exists($file['sidecarpath'])) {
                unlink($file['sidecarpath']);
            }
        }
    }

    /**
     * Gets files of type $path that are made by this object. Used in the unit test
     * for checking existence of sidecar files after the upgrade and for getting
     * the list of legacy files that were converted.
     *
     * If $path is null, will return the array of files to make
     *
     * @param string|null $path
     * @param bool $asArrays If true, returns each filename as an array
     * @return array
     */
    public function getFilesToMake($path = null, $asArrays = false)
    {
        if (!$path) {
            return $this->filesToMake;
        }
        $return = [];
        $index = $path . 'path';
        foreach ($this->filesToMake as $filedata) {
            if (isset($filedata[$index])) {
                $return[] = $asArrays ? [$filedata[$index]] : $filedata[$index];
            }
        }

        return $return;
    }

    /**
     * Used in the unit test for metadata upgrading, builds a list of files for
     * a given module, view and type
     *
     * @param array|string $view If an array, gets all files of view type in the array
     * @param string $path The path type to get
     * @param array $modules List of modules to get views for
     * @return array
     */
    public function getFilesToMakeByView($view, $path = 'sidecar', $modules = [])
    {
        $return = [];
        $index = $path . 'path';
        foreach ($this->filesToMake as $file) {
            if (is_array($view) && in_array($file['view'], $view) && (empty($modules) || in_array($file['module'], $modules))) {
                $return[] = ['module' => $file['module'], 'view' => $file['view'], 'type' => $file['type'], 'filepath' => $file[$index]];
            } else {
                if ($file['view'] == $view && (empty($modules) || in_array($file['module'], $modules))) {
                    $return[] = ['module' => $file['module'], 'view' => $file['view'], 'type' => $file['type'], 'filepath' => $file[$index]];
                }
            }
        }

        return $return;
    }

    /**
     * Utility method to actually install a test file
     *
     * @param array $filedata
     */
    private function installTestFile($filedata)
    {
        if (file_exists($filedata['testpath'])) {
            $dir = dirname($filedata['legacypath']);
            if (!is_dir($dir)) {
                mkdir_recursive($dir);
            }
            SugarTestHelper::saveFile($filedata['legacypath']);
            if (copy($filedata['testpath'], $filedata['legacypath'])) {
                $this->created[] = $filedata['legacypath'];
            }
        } else {
            var_dump($filedata);
        }
    }
}
