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

class Bug48496Test extends TestCase
{
    public $backup_config;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['module'] = 'Imports';
        $_REQUEST['module'] = 'Imports';
        $_REQUEST['import_module'] = 'Accounts';
        $_REQUEST['action'] = 'last';
        $_REQUEST['type'] = '';
        $_REQUEST['has_header'] = 'off';
        sugar_touch('upload/import/status_' . $GLOBALS['current_user']->id . '.csv');
    }

    protected function tearDown(): void
    {
        unlink('upload/import/status_' . $GLOBALS['current_user']->id . '.csv');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['module']);
        unset($_REQUEST['module']);
        unset($_REQUEST['import_module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['type']);
        unset($_REQUEST['has_header']);
    }

    public function testQueryDoesNotContainDuplicateUsersLastImportClauses()
    {
        global $current_user;

        $params = [
            'custom_from' => ', users_last_import',
            'custom_where' => " AND users_last_import.assigned_user_id = '{$current_user->id}'
                AND users_last_import.bean_type = 'Account'
                AND users_last_import.bean_id = accounts.id
                AND users_last_import.deleted = 0
                AND accounts.deleted = 0",
        ];

        $seed = BeanFactory::newBean('Accounts');

        $lvfMock = $this->getMockBuilder('ListViewFacade')->setMethods(['setup', 'display'])->setConstructorArgs([$seed, 'Accounts'])->getMock();

        $lvfMock->expects($this->once())
            ->method('setup')
            ->with(
                $this->anything(),
                '',
                $params,
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything()
            );

        $viewLast = new ImportViewLast();
        $viewLast->init($seed);
        $viewLast->lvf = $lvfMock;

        SugarTestReflection::callProtectedMethod($viewLast, 'getListViewResults');
    }
}
