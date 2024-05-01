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

class Bug59825Test extends TestCase
{
    /**
     * The test module
     *
     * @var string
     */
    private static $module = 'Bugs';

    /**
     * Rather than setting up and tearing down for each iteration of the data
     * provider, set up once and tear down once, as these are used as-is throughout
     * each test.
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]); // Admin user
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests not null parsers for views
     *
     * @param string $type A type of view to get a parser for
     * @dataProvider layoutProvider
     */
    public function testParserIsNotNullForLayoutType($type)
    {
        $parser = ParserFactory::getParser($type, self::$module);
        $this->assertNotNull($parser, "Portal parser for $type in Bugs module is null");
    }

    /**
     * Gets a list of 'types' of metadata views to be used in the test. Includes
     * basic layouts from all OOTB and, where applicable, wireless and portal
     * layouts.
     *
     * @return array
     */
    public static function layoutProvider()
    {
        return [
            // Basic types for all OOTB installations
            // This simulates StudioModule::getViewMetadataSources()
            ['type' => MB_EDITVIEW],
            ['type' => MB_DETAILVIEW],
            ['type' => MB_LISTVIEW],
            ['type' => MB_BASICSEARCH],
            ['type' => MB_ADVANCEDSEARCH],
            ['type' => MB_POPUPLIST],
            ['type' => MB_QUICKCREATE],
            // Wireless types
            // This simulates StudioModule::getWirelessLayouts()
            ['type' => MB_WIRELESSEDITVIEW],
            ['type' => MB_WIRELESSDETAILVIEW],
            ['type' => MB_WIRELESSLISTVIEW],
            // Portal types not including search, which was the cause of the bug
            // This simulates StudioModule::getPortalLayoutSources()
            ['type' => MB_PORTALRECORDVIEW],
            ['type' => MB_PORTALLISTVIEW],
        ];
    }
}
