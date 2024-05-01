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

class Bug44472Test extends TestCase
{
    /**
     * @var \EditViewMerge|mixed|\EditViewMergeMock
     */
    public $merge;

    protected function setUp(): void
    {
        SugarTestMergeUtilities::setupFiles(['Cases'], ['editviewdefs'], 'tests/{old}/modules/UpgradeWizard/SugarMerge/od_metadata_files/610');
    }

    protected function tearDown(): void
    {
        SugarTestMergeUtilities::teardownFiles();
    }


    public function test620TemplateMetaMergeOnCases()
    {
        $viewdefs = [];
        $this->merge = new EditViewMerge();
        $this->merge->merge('Cases', 'tests/{old}/modules/UpgradeWizard/SugarMerge/od_metadata_files/610/oob/modules/Cases/metadata/editviewdefs.php', 'modules/Cases/metadata/editviewdefs.php', 'custom/modules/Cases/metadata/editviewdefs.php');
        $this->assertTrue(file_exists('custom/modules/Cases/metadata/editviewdefs.php.suback.php'));
        require 'custom/modules/Cases/metadata/editviewdefs.php';
        $this->assertFalse(isset($viewdefs['Cases']['EditView']['templateMeta']['form']), 'Assert that the templateMeta is pulled from the upgraded view rather than the customized view');
    }

    public function test620TemplateMetaMergeOnMeetings()
    {
        $this->merge = new EditViewMergeMock();
        $this->merge->setModule('Meetings');
        $data = [];
        $data['Meetings'] = ['EditView' => ['templateMeta' => ['form']]];
        $this->merge->setCustomData($data);
        $newData = [];
        $newData['Meetings'] = ['EditView' => ['templateMeta' => []]];
        $this->merge->setNewData($newData);
        $this->merge->testMergeTemplateMeta();
        $newData = $this->merge->getNewData();
        $this->assertTrue(!isset($newData['Meetings']['EditView']['templateMeta']['form']), 'Assert that we do not take customized templateMeta section for Meetings');
    }

    public function test620TemplateMetaMergeOnCalls()
    {
        $this->merge = new EditViewMergeMock();
        $this->merge->setModule('Calls');
        $data = [];
        $data['Calls'] = ['EditView' => ['templateMeta' => ['form']]];
        $this->merge->setCustomData($data);
        $newData = [];
        $newData['Calls'] = ['EditView' => ['templateMeta' => []]];
        $this->merge->setNewData($newData);
        $this->merge->testMergeTemplateMeta();

        $newData = $this->merge->getNewData();
        $this->assertTrue(!isset($newData['Calls']['EditView']['templateMeta']['form']), 'Assert that we do not take customized templateMeta section for Calls');
    }
}

class EditViewMergeMock extends EditViewMerge
{
    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setCustomData($data)
    {
        $this->customData = $data;
    }

    public function setNewData($data)
    {
        $this->newData = $data;
    }

    public function getNewData()
    {
        return $this->newData;
    }

    public function testMergeTemplateMeta()
    {
        $this->mergeTemplateMeta();
    }
}
