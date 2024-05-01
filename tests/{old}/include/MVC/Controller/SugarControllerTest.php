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

use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use PHPUnit\Framework\TestCase;

class SugarControllerTest extends TestCase
{
    /**
     * @var mixed[]
     */
    public $rmdir;
    protected $module_name;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('moduleList');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->module_name = null;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        if ($this->module_name) {
            rmdir_recursive("modules/{$this->module_name}");
        }
        SugarTestHelper::tearDown();
    }

    public function testSetup()
    {
        $controller = new SugarControllerMock();
        $controller->setup();

        $this->assertEquals('Home', $controller->module);
        $this->assertNull($controller->target_module);
    }

    public function testSetupSpecifyModule()
    {
        $controller = new SugarControllerMock();
        $controller->setup('foo');

        $this->assertEquals('foo', $controller->module);
        $this->assertNull($controller->target_module);
    }

    public function testSetupUseRequestVars()
    {
        $GLOBALS['moduleList'][] = 'dog33434';
        $GLOBALS['moduleList'][] = 'dog121255';
        $GLOBALS['moduleList'][] = 'dog1312';
        Validator::clearValidatorsCache();

        $_REQUEST = [
            'module' => 'dog33434',
            'target_module' => 'dog121255',
            'action' => 'dog3232',
            'record' => 'dog5656',
            'view' => 'dog4343',
            'return_module' => 'dog1312',
            'return_action' => 'dog1212',
            'return_id' => '11212',
        ];
        $controller = new SugarControllerMock();
        $controller->setup();

        $this->assertEquals($_REQUEST['module'], $controller->module);
        $this->assertEquals($_REQUEST['target_module'], $controller->target_module);
        $this->assertEquals($_REQUEST['action'], $controller->action);
        $this->assertEquals($_REQUEST['record'], $controller->record);
        $this->assertEquals($_REQUEST['view'], $controller->view);
        $this->assertEquals($_REQUEST['return_module'], $controller->return_module);
        $this->assertEquals($_REQUEST['return_action'], $controller->return_action);
        $this->assertEquals($_REQUEST['return_id'], $controller->return_id);
    }


    protected function touch($filename)
    {
        sugar_touch($filename);
    }

    protected function mkdir($filename)
    {
        $this->rmdir[] = $filename;
        sugar_mkdir($filename, null, true);
    }

    public function testSetModule()
    {
        $controller = new SugarControllerMock();
        $controller->setModule('cat');

        $this->assertEquals('cat', $controller->module);
    }

    public function testCallLegacyCodeIfLegacyDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("modules/$module_name/", null, true);
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';
        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic', $controller->view);
    }

    public function testCallLegacyCodeIfNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("modules/$module_name/views", null, true);
        $this->touch("modules/$module_name/views/view.detail.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list', $controller->view);
    }


    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("modules/$module_name/views", null, true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list', $controller->view);
    }

    public function testCallLegacyCodeIfCustomLegacyDetailViewAndNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("modules/$module_name/views", null, true);
        $this->touch("modules/$module_name/views/view.detail.php");
        sugar_mkdir("custom/modules/$module_name", null, true);
        $this->touch("custom/modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic', $controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndCustomNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("custom/modules/$module_name/views", null, true);
        $this->touch("custom/modules/$module_name/views/view.detail.php");
        sugar_mkdir("modules/$module_name", null, true);
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';
        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic', $controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFoundAndCustomLegacyDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("modules/$module_name/views", null, true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");
        sugar_mkdir("custom/modules/$module_name", null, true);
        $this->touch("custom/modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic', $controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFoundAndCustomNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("custom/modules/$module_name/views", null, true);
        $this->touch("custom/modules/$module_name/views/view.detail.php");
        sugar_mkdir("modules/$module_name/views", null, true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';
        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list', $controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFoundAndCustomLegacyDetailViewFoundAndCustomNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule' . random_int(0, mt_getrandmax());
        sugar_mkdir("custom/modules/$module_name/views", null, true);
        $this->touch("custom/modules/$module_name/views/view.detail.php");
        $this->touch("custom/modules/$module_name/DetailView.php");
        sugar_mkdir("modules/$module_name/views", null, true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock();
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list', $controller->view);
    }

    public function testPostDelete()
    {
        $_REQUEST['return_module'] = 'foo';
        $_REQUEST['return_action'] = 'bar';
        $_REQUEST['return_id'] = '123';

        $controller = new SugarControllerMock();
        $controller->post_delete();

        unset($_REQUEST['return_module']);
        unset($_REQUEST['return_action']);
        unset($_REQUEST['return_id']);

        $this->assertEquals('index.php?module=foo&action=bar&record=123', $controller->redirect_url);
    }

    /**
     * @ticket 23816
     */
    public function testPostDeleteWithOffset()
    {
        $_REQUEST['return_module'] = 'foo';
        $_REQUEST['return_action'] = 'bar';
        $_REQUEST['return_id'] = '123';
        $_REQUEST['offset'] = '2';

        $controller = new SugarControllerMock();
        $controller->post_delete();

        unset($_REQUEST['return_module']);
        unset($_REQUEST['return_action']);
        unset($_REQUEST['return_id']);
        unset($_REQUEST['offset']);

        $this->assertEquals('index.php?module=foo&action=bar&record=123&offset=2', $controller->redirect_url);
    }

    /**
     * @ticket 23816
     */
    public function testPostDeleteWithOffsetAndDuplicateSave()
    {
        $_REQUEST['return_module'] = 'foo';
        $_REQUEST['return_action'] = 'bar';
        $_REQUEST['return_id'] = '123';
        $_REQUEST['offset'] = '2';
        $_REQUEST['duplicateSave'] = true;

        $controller = new SugarControllerMock();
        $controller->post_delete();

        unset($_REQUEST['return_module']);
        unset($_REQUEST['return_action']);
        unset($_REQUEST['return_id']);
        unset($_REQUEST['offset']);
        unset($_REQUEST['duplicateSave']);

        $this->assertEquals('index.php?module=foo&action=bar&record=123', $controller->redirect_url);
    }

    public function testPostDeleteWithDefaultValues()
    {
        $backupDefaultModule = $GLOBALS['sugar_config']['default_module'];
        $backupDefaultAction = $GLOBALS['sugar_config']['default_action'];

        $GLOBALS['sugar_config']['default_module'] = 'yuck';
        $GLOBALS['sugar_config']['default_action'] = 'yuckyuck';

        $controller = new SugarControllerMock();
        $controller->post_delete();

        $GLOBALS['sugar_config']['default_module'] = $backupDefaultModule;
        $GLOBALS['sugar_config']['default_action'] = $backupDefaultAction;

        $this->assertEquals('index.php?module=yuck&action=yuckyuck&record=', $controller->redirect_url);
    }

    public function testExecuteException()
    {
        $controller = $this->createPartialMock('SugarController', ['process', 'handleException']);
        $controller->expects($this->once())
            ->method('process')
            ->will($this->throwException(new Exception('test')));
        $controller->expects($this->once())
            ->method('handleException');
        $controller->execute();
    }
}

class SugarControllerMock extends SugarController
{
    public string $do_action;

    public function callLegacyCode()
    {
        return parent::callLegacyCode();
    }

    public function post_delete()
    {
        parent::post_delete();
    }
}
