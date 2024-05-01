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
 * Bug 55003
 *
 * Notes showing up in portal browser and portal layout editor when Notes is not
 * a portal module
 */
class Bug55003Test extends TestCase
{
    /**
     * SugarPortalBrowser object
     *
     * @var SugarPortalBrowser
     */
    protected $portalBrowser;

    protected function setUp(): void
    {
        $this->portalBrowser = new SugarPortalBrowser();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');

        // Mod strings is required for Portal Browser, must use ModuleBuilder
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        unset($this->portalBrowser);
    }

    /**
     * Tests known portal modules
     *
     * @dataProvider getKnownPortalModules
     * @param string $module
     */
    public function testKnownPortalModulesPassPortalModuleCheck($module)
    {
        $assert = $this->portalBrowser->isPortalModule($module);
        $this->assertTrue($assert, "$module is a known portal module but was not found to be one in SugarPortalBrowser");
    }

    /**
     * Tests Notes not being a portal module
     */
    public function testNotesDoeNotPassPortalModuleCheck()
    {
        $assert = $this->portalBrowser->isPortalModule('Notes');
        $this->assertFalse($assert, 'Notes is not a known portal module but was found to be one in SugarPortalBrowser');
    }

    /**
     * Data provider for the known modules test
     *
     * @return array
     */
    public function getKnownPortalModules()
    {
        return [
            ['module' => 'Bugs'],
            ['module' => 'Cases'],
            ['module' => 'Contacts'],
            ['module' => 'KBContents'],
        ];
    }
}
