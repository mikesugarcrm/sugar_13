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
 * Bug 59273 - Field name in viewdefs has different char case as in vardefs
 */
class Bug59273Test extends TestCase
{
    private $viewFile = 'custom/modulebuilder/packages/test/modules/test/clients/mobile/views/list/list.php';
    private $request = [];
    private $mbc;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

        $this->request = $_REQUEST;

        $_REQUEST['name'] = 'test';
        $_REQUEST['view_package'] = 'test';
        $_REQUEST['view_module'] = 'test';

        $this->mbc = new ModuleBuilderController();
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';
        $_REQUEST['readme'] = '';
        $_REQUEST['label'] = 'test';
        $_REQUEST['key'] = 'test';
        $this->mbc->action_SavePackage();

        $_REQUEST['type'] = 'issue';
        $this->mbc->action_SaveModule();
        unset($_REQUEST);
    }

    protected function tearDown(): void
    {
        $_REQUEST['package'] = 'test';
        $_REQUEST['module'] = 'test';
        $_REQUEST['view_module'] = 'test';
        $_REQUEST['view_package'] = 'test';
        $this->mbc->action_DeleteModule();
        unset($_REQUEST['view_module']);
        unset($_REQUEST['module']);
        $this->mbc->action_DeletePackage();

        $_REQUEST = $this->request;

        SugarTestHelper::tearDown();
    }

    /**
     * Tests field name casing for mobile list views
     *
     * @group Bug59273
     */
    public function testCustomModuleListViewDefsUseCorrectCase()
    {
        $viewdefs = [];
        $this->assertFileExists($this->viewFile, "Custom module list view file {$this->viewFile} was not found");

        include $this->viewFile;

        $this->assertTrue(isset($viewdefs['test_test']['mobile']['view']['list']['panels']), 'Cannot find the panels in the mobile list view defs');
        $panels = $viewdefs['test_test']['mobile']['view']['list']['panels'];
        $this->assertTrue(isset($panels[0]['fields'][0]), 'First member of the fields array not found in the mobile list view defs');
        $test = $this->hasField('test_test_number', $panels[0]['fields']);
        $this->assertTrue($test, 'Lowercase test_test_number not found in the fields array');
    }

    /**
     * Simple field searcher
     *
     * @param string $field The field to look for
     * @param array $fields The fields array to search in
     * @return bool
     */
    private function hasField($field, $fields)
    {
        foreach ($fields as $f) {
            if (isset($f['name']) && $f['name'] == $field) {
                return true;
            }
        }

        return false;
    }
}
