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

class Bug49955Test extends TestCase
{
    public $merge;

    protected function setUp(): void
    {
        SugarTestMergeUtilities::setupFiles(['Notes'], ['editviewdefs'], 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files');

        if (file_exists('custom/backup/modules/DocumentRevisions')) {
            rmdir_recursive('custom/backup/modules/DocumentRevisions');
        }

        file_put_contents('modules/DocumentRevisions/EditView.html', 'test');
        file_put_contents('modules/DocumentRevisions/DetailView.html', 'test');
        file_put_contents('modules/DocumentRevisions/EditView.php', 'test');
        file_put_contents('modules/DocumentRevisions/DetailView.php', 'test');
    }

    protected function tearDown(): void
    {
        SugarTestMergeUtilities::teardownFiles();

        if (file_exists('custom/backup/modules/DocumentRevisions')) {
            rmdir_recursive('custom/backup/modules/DocumentRevisions');
        }

        if (file_exists('modules/DocumentRevisions/EditView.html')) {
            unlink('modules/DocumentRevisions/EditView.html');
        }

        if (file_exists('modules/DocumentRevisions/DetailView.html')) {
            unlink('modules/DocumentRevisions/DetailView.html');
        }

        if (file_exists('modules/DocumentRevisions/EditView.php')) {
            unlink('modules/DocumentRevisions/EditView.php');
        }

        if (file_exists('modules/DocumentRevisions/DetailView.php')) {
            unlink('modules/DocumentRevisions/DetailView.php');
        }
    }

    public function test_filename_convert_merge()
    {
        $this->merge = new EditViewMerge();
        $this->merge->merge('Notes', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Notes/metadata/editviewdefs.php', 'modules/Notes/metadata/editviewdefs.php', 'custom/modules/Notes/metadata/editviewdefs.php');
        require 'custom/modules/Notes/metadata/editviewdefs.php';

        $foundFilename = 0;
        $fileField = '';

        foreach ($viewdefs['Notes']['EditView']['panels'] as $panel) {
            foreach ($panel as $row) {
                foreach ($row as $col) {
                    if (is_array($col)) {
                        $fieldName = $col['name'];
                    } else {
                        $fieldName = $col;
                    }

                    if ($fieldName == 'filename') {
                        $fileField = $col;
                        break;
                    }
                }
            }
        }

        $this->assertNotEmpty($fileField, 'Filename field doesn\'t exit, it should');
        $this->assertTrue(is_string($fileField) && $fileField == 'filename', 'Filename field not converted to string');

        if (file_exists('custom/modules/Notes/metadata/editviewdefs-testback.php')) {
            copy('custom/modules/Notes/metadata/editviewdefs-testback.php', 'custom/modules/Notes/metadata/editviewdefs.php');
            unlink('custom/modules/Notes/metadata/editviewdefs-testback.php');
        }

        //Now test the DocumentRevisions cleanup
        $instance = new UpgradeRemoval49955Mock();
        $instance->processFilesToRemove($instance->getFilesToRemove(624));
        $this->assertTrue(!file_exists('modules/DocumentRevisions/EditView.html'));
        $this->assertTrue(!file_exists('modules/DocumentRevisions/DetaillView.html'));
        $this->assertTrue(!file_exists('modules/DocumentRevisions/EditView.php'));
        $this->assertTrue(!file_exists('modules/DocumentRevisions/DetailView.html'));
    }
}

class UpgradeRemoval49955Mock extends UpgradeRemoval
{
    public function getFilesToRemove($version)
    {
        return [
            'modules/DocumentRevisions/EditView.html',
            'modules/DocumentRevisions/DetailView.html',
            'modules/DocumentRevisions/EditView.php',
            'modules/DocumentRevisions/DetailView.php',
        ];
    }
}
