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

class UpgradeCustomTemplateMetaTest extends TestCase
{
    public $merge;

    protected function setUp(): void
    {
        SugarTestMergeUtilities::setupFiles(['Calls', 'Meetings', 'Notes'], ['editviewdefs'], 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files');
    }

    protected function tearDown(): void
    {
        SugarTestMergeUtilities::teardownFiles();
    }

    /**
     * @group SugarMerge
     */
    public function testMergeCallsEditviewdefsFor611()
    {
        $viewdefs = [];
        $this->merge = new EditViewMerge();
        $this->merge->merge('Calls', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Calls/metadata/editviewdefs.php', 'modules/Calls/metadata/editviewdefs.php', 'custom/modules/Calls/metadata/editviewdefs.php');

        //Load file
        require 'custom/modules/Calls/metadata/editviewdefs.php';

        $this->assertStringNotContainsString(
            'forms[0]',
            $viewdefs['Calls']['EditView']['templateMeta']['form']['buttons'][0]['customCode']
        );
    }

    /**
     * @group SugarMerge
     */
    public function testMergeMeetingsEditviewdefsFor611()
    {
        $viewdefs = [];
        $this->merge = new EditViewMerge();
        $this->merge->merge('Meetings', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Meetings/metadata/editviewdefs.php', 'modules/Meetings/metadata/editviewdefs.php', 'custom/modules/Meetings/metadata/editviewdefs.php');

        //Load file
        require 'custom/modules/Meetings/metadata/editviewdefs.php';

        $this->assertStringNotContainsString(
            'this.form.',
            $viewdefs['Meetings']['EditView']['templateMeta']['form']['buttons'][0]['customCode']
        );
    }


    /**
     * Custom button definitions should not be kept during upgrade
     * @group SugarMerge
     */
    public function testMergeCustomButtonsAndStudioChanges()
    {
        $viewdefs = [];
        $this->merge = new EditViewMerge();
        $this->merge->merge('Notes', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Notes/metadata/editviewdefs.php', 'modules/Notes/metadata/editviewdefs.php', 'custom/modules/Notes/metadata/editviewdefs.php');

        //Load file
        require 'custom/modules/Notes/metadata/editviewdefs.php';

        //Assert that custom Buttons are not kept
        $this->assertArrayNotHasKey('buttons', $viewdefs['Notes']['EditView']['templateMeta']['form'], 'Buttons array picked up from custom file');

        //Assert that studio possible changes are retained
        $this->assertArrayHasKey('useTabs', $viewdefs['Notes']['EditView']['templateMeta']);
        $this->assertArrayHasKey('tabDefs', $viewdefs['Notes']['EditView']['templateMeta']);
        $this->assertArrayHasKey('syncDetailEditViews', $viewdefs['Notes']['EditView']['templateMeta']);
    }
}
