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

class Bug50781Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', ['Administration']);
        SugarTestHelper::setUp('app_list_strings');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group 50781
     */
    public function testToggleClass()
    {
        $GLOBALS['module'] = 'Administration';
        $GLOBALS['action'] = 'ConfigureFTS';

        $view = new AdministrationViewGlobalsearchsettings();
        $view->init();
        $view->display();

        $this->expectOutputRegex('/.*class=\"shouldToggle\".*/');
    }
}
