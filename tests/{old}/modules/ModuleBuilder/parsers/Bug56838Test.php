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
 * Bug55154Test.php
 *
 * Tests KBOLDDocuments Module 'keywords' field is not available in any layout.
 *
 * Using the parser factory delegates including necessary parser files at construct
 * time as opposed to loading all required files per fixture.
 */
class Bug56838Test extends TestCase
{
    protected static $testModule = 'Cases';

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('beanFiles');
        SugarTestHelper::setup('app_list_strings');
        SugarTestHelper::setup('mod_strings', [self::$testModule]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug56838
     */
    public function testMobileEditViewPanelLabelIsCorrect()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSEDITVIEW, self::$testModule, null, null, MB_WIRELESS);

        // Current layout
        $layout = $parser->getLayout();
        $this->assertArrayNotHasKey('LBL_PANEL_1', $layout, 'Layout still shows LBL_PANEL_1 as the default label on mobile edit views');
        $this->assertArrayHasKey('LBL_PANEL_DEFAULT', $layout, "'LBL_PANEL_DEFAULT' was not found as the default panel label on mobile edit views");
    }

    /**
     * @group Bug56838
     */
    public function testMobileDetailViewPanelLabelIsCorrect()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSDETAILVIEW, self::$testModule, null, null, MB_WIRELESS);

        // Current layout
        $layout = $parser->getLayout();
        $this->assertArrayNotHasKey('LBL_PANEL_1', $layout, 'Layout still shows LBL_PANEL_1 as the default label on mobile detail views');
        $this->assertArrayHasKey('LBL_PANEL_DEFAULT', $layout, "'LBL_PANEL_DEFAULT' was not found as the default panel label on mobile detail views");
    }

    /**
     * @group Bug56838
     */
    public function testMobileListViewPanelLabelIsCorrect()
    {
        // SidecarListLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSLISTVIEW, self::$testModule, null, null, MB_WIRELESS);

        // List panel defs
        $paneldefs = $parser->getPanelDefs();
        $this->assertNotEmpty($paneldefs, 'Panel defs are empty for mobile list view');
        $this->assertTrue(is_array($paneldefs), 'Panel defs for mobile list view are not an array');
        $this->assertTrue(isset($paneldefs[0]['label']), 'There is no label for mobile list view defs');
        $this->assertEquals($paneldefs[0]['label'], 'LBL_PANEL_DEFAULT', "Expected mobile list view panel label to be 'LBL_PANEL_DEFAULT' but got '{$paneldefs[0]['label']}'");
    }


    /**
     * @group Bug56838
     */
    public function testPortalRecordViewLabelIsCorrect()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_PORTALRECORDVIEW, self::$testModule, null, null, MB_PORTAL);

        // Current layout
        $layout = $parser->getLayout();
        $this->assertArrayNotHasKey('LBL_PANEL_1', $layout, 'Layout still shows LBL_PANEL_1 as the default label on portal record views');
        $this->assertArrayHasKey('LBL_RECORD_BODY', $layout, "'LBL_RECORD_BODY' was not found as the default panel label on portal record views");
    }

    /**
     * Does not test additional fields as OOTB instances do not have an additional
     * fields list. Should that change in the future, add the following:
     * <code>
     * $fields = $parser->getAdditionalFields();
     * $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the additional fields list");
     * </code>
     */
    /**
     * @group Bug56838
     */
    public function testPortalListViewLabelIsCorrect()
    {
        // SidecarListLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_PORTALLISTVIEW, self::$testModule, null, null, MB_PORTAL);

        // List panel defs
        $paneldefs = $parser->getPanelDefs();
        $this->assertNotEmpty($paneldefs, 'Panel defs are empty for portal list view');
        $this->assertTrue(is_array($paneldefs), 'Panel defs for portal list view are not an array');
        $this->assertTrue(isset($paneldefs[0]['label']), 'There is no label for portal list view defs');
        $this->assertEquals($paneldefs[0]['label'], 'LBL_PANEL_DEFAULT', "Expected portal list view panel label to be 'LBL_PANEL_DEFAULT' but got '{$paneldefs[0]['label']}'");
    }
}
