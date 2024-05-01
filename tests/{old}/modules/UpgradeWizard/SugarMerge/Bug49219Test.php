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

/**
 * Bug49219Test.php
 * @author Collin Lee
 *
 * This test will attempt to assert two things:
 * 1) That upgrade for Meetings quickcreatedefs.php correctly remove footerTpl and headerTpl metadata attributes from
 * custom quickcreatedefs.php files (since we removed them from code base)
 * 2) That the SubpanelQuickCreate changes done for this bug can correctly pick up metadata footerTpl and headerTpl
 * attributes
 */
require_once 'include/dir_inc.php';

class Bug49219Test extends TestCase
{
    public $merge;

    protected function setUp(): void
    {
        global $beanList, $beanFiles, $current_user;
        require 'include/modules.php';
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        SugarTestMergeUtilities::setupFiles(['Meetings'], ['quickcreatedefs'], 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files');
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMergeUtilities::teardownFiles();
        unset($current_user);
    }


    /**
     * testUpgradeMeetingsQuickCreate641
     * @outputBuffering enabled
     * This test asserts that the footerTpl and headerTpl form attributes are removed from quickcreatedefs.php when
     * upgrading to 641
     */
    public function testUpgradeMeetingsQuickCreate641()
    {
        $viewdefs = [];
        require 'custom/modules/Meetings/metadata/quickcreatedefs.php';
        $this->assertArrayHasKey('headerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'Unit test setup failed');
        $this->assertArrayHasKey('footerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'Unit test setup failed');
        $this->merge = new QuickCreateMerge();
        $this->merge->merge('Meetings', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/640/modules/Meetings/metadata/quickcreatedefs.php', 'modules/Meetings/metadata/quickcreatedefs.php', 'custom/modules/Meetings/metadata/quickcreatedefs.php');
        SugarAutoLoader::buildCache();
        require 'custom/modules/Meetings/metadata/quickcreatedefs.php';
        $this->assertArrayNotHasKey('headerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'SugarMerge code does not remove headerTpl from quickcreatedefs.php');
        $this->assertArrayNotHasKey('footerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'SugarMerge code does not remove footerTpl from quickcreatedefs.php');
    }


    /**
     * testSubpanelQuickCreate
     * @outputBuffering enabled
     * This test asserts that we can pick up the footerTpl and headerTpl attributes in the quickcreatedefs.php files
     */
    public function testSubpanelQuickCreate()
    {
        $quickCreate = new SubpanelQuickCreate('Meetings', 'QuickCreate', true);
        $this->assertEquals('modules/Meetings/tpls/header.tpl', $quickCreate->ev->defs['templateMeta']['form']['headerTpl'], 'SubpanelQuickCreate fails to pick up headerTpl attribute');
        $this->assertEquals('modules/Meetings/tpls/footer.tpl', $quickCreate->ev->defs['templateMeta']['form']['footerTpl'], 'SubpanelQuickCreate fails to pick up footerTpl attribute');
        $this->merge = new QuickCreateMerge();
        $this->merge->merge('Meetings', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/640/modules/Meetings/metadata/quickcreatedefs.php', 'modules/Meetings/metadata/quickcreatedefs.php', 'custom/modules/Meetings/metadata/quickcreatedefs.php');
        SugarAutoLoader::buildCache();
        $quickCreate = new SubpanelQuickCreate('Meetings', 'QuickCreate', true);
        $this->assertEquals('include/EditView/header.tpl', $quickCreate->ev->defs['templateMeta']['form']['headerTpl'], 'SubpanelQuickCreate fails to pick up default headerTpl attribute');
        $this->assertEquals('include/EditView/footer.tpl', $quickCreate->ev->defs['templateMeta']['form']['footerTpl'], 'SubpanelQuickCreate fails to pick up default footerTpl attribute');
    }
}
