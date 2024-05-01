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

class Bug51105Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testCheckEditViewHeaderTpl()
    {
        $view = 'QuickCreate';
        $error = 'Unexpected headerTpl value';
        $default = 'include/EditView/header.tpl';

        // Test modules, known case Meetings is default
        $module = 'Meetings';
        $sqc = new SubpanelQuickCreate($module, $view, true);
        $this->assertEquals($default, $sqc->ev->defs['templateMeta']['form']['headerTpl'], $error);

        // Employees has a defined headerTpl
        $module = 'Employees';
        $sqc = new SubpanelQuickCreate($module, $view, true);
        $this->assertEquals('modules/Users/tpls/EditViewHeader.tpl', $sqc->ev->defs['templateMeta']['form']['headerTpl'], $error);
    }
}
