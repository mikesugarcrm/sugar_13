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

class AdministrationControllerTest extends TestCase
{
    protected $savedTabs;
    protected $tabs;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('app_list_strings');
        $this->tabs = new TabController();
        $this->savedTabs = $this->tabs->get_system_tabs();
    }

    protected function tearDown(): void
    {
        $this->tabs->set_system_tabs($this->savedTabs);
        SugarTestHelper::tearDown();
    }

    /**
     * Home always needs to be first display tab
     */
    public function testAddHomeTabOnSave()
    {
        $_REQUEST['enabled_tabs'] = '["Leads"]';  //Save only included Leads
        $admin = new AdministrationController();
        $admin->action_savetabs();
        $tabs = new TabController();
        //Home tab should be prepended
        $this->assertEquals(['Home' => 'Home', 'Leads' => 'Leads'], $tabs->get_system_tabs());
    }
}
