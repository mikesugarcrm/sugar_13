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
 * Bug51242Test.php
 *
 * This test checks to see that the parsers may be properly loaded depending on the layout requested.
 */
class Bug51242Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function providerGetParser()
    {
        return [
            [MB_PORTALLISTVIEW, 'Cases', null, null, MB_PORTAL],
            ['portallayoutview', 'Cases', null, null, MB_PORTAL],
            [MB_PORTALLISTVIEW, 'Leads', null, null, MB_PORTAL],
            ['portallayoutview', 'Leads', null, null, MB_PORTAL],
            [MB_PORTALLISTVIEW, 'Bugs', null, null, MB_PORTAL],
            ['portallayoutview', 'Bugs', null, null, MB_PORTAL],
        ];
    }


    /**
     * @dataProvider providerGetParser
     * @param string $view String value of the view to load
     * @param string $module String value of the module name
     * @param string $package The name of the MB package
     * @param string $subpanel The subpanel
     * @param string $client The client for this parser
     */
    public function testGetParser($view, $module, $package, $subpanel, $client)
    {
        $parser = ParserFactory::getParser($view, $module, $package, $subpanel, $client);
        $this->assertNotEmpty($parser, 'Failed to retrieve parser instance');
    }
}
