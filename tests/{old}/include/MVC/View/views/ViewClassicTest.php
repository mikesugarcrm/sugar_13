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

class ViewClassicTest extends TestCase
{
    public function testConstructor()
    {
        $view = new ViewClassic();

        $this->assertEquals('', $view->type);
    }

    public function testDisplayWithClassicView()
    {
        $view = $this->createPartialMock('ViewClassic', ['includeClassicFile']);

        $view->module = 'testmodule' . random_int(0, mt_getrandmax());
        $view->action = 'testaction' . random_int(0, mt_getrandmax());

        sugar_mkdir("modules/{$view->module}", null, true);
        sugar_touch("modules/{$view->module}/{$view->action}.php");

        $return = $view->display();

        rmdir_recursive("modules/{$view->module}");

        $this->assertTrue($return);
    }

    public function testDisplayWithClassicCustomView()
    {
        $view = $this->createPartialMock('ViewClassic', ['includeClassicFile']);

        $view->module = 'testmodule' . random_int(0, mt_getrandmax());
        $view->action = 'testaction' . random_int(0, mt_getrandmax());

        sugar_mkdir("custom/modules/{$view->module}", null, true);
        sugar_touch("custom/modules/{$view->module}/{$view->action}.php");

        $return = $view->display();

        rmdir_recursive("custom/modules/{$view->module}");

        $this->assertTrue($return);
    }

    public function testDisplayWithNoClassicView()
    {
        $view = $this->createPartialMock('ViewClassic', ['includeClassicFile']);

        $view->module = 'testmodule' . random_int(0, mt_getrandmax());
        $view->action = 'testaction' . random_int(0, mt_getrandmax());

        $this->assertFalse($view->display());
    }
}
