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
 * Bug54507Test.php
 *
 * Tests available fields for portal list, edit and detail to make sure that
 * duplicate labels are not included for the Bugs module.
 */
class Bug54507Test extends TestCase
{
    protected $editModule = 'Bugs';

    public static function setUpBeforeClass(): void
    {
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        require 'include/modules.php';
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testBugsPortalRecordAvailableFields()
    {
        $view = 'portalrecordview';
        $parser = ParserFactory::getParser($view, $this->editModule, null, null, MB_PORTAL);
        $fielddefs = $parser->getFieldDefs();

        $test = $parser->isValidField('fixed_in_release_name', $fielddefs['fixed_in_release_name']);
        $this->assertFalse($test, 'fixed_in_release_name should not be a valid available field in Bugs Portal Record View');

        $test = $parser->isValidField('fixed_in_release', $fielddefs['fixed_in_release']);
        $this->assertFalse($test, 'fixed_in_release should not be a valid available field in Bugs Portal Record View');

        $test = $parser->isValidField('fixed_in_release_link', $fielddefs['fixed_in_release_link']);
        $this->assertFalse($test, 'fixed_in_release_link should not be a valid available field in Bugs Portal Record View');

        $test = $parser->isValidField('found_in_release', $fielddefs['found_in_release']);
        $this->assertFalse($test, 'found_in_release should not be a valid available field in Bugs Portal Record View');

        $test = $parser->isValidField('release_name', $fielddefs['release_name']);
        $this->assertFalse($test, 'release_name should not be a valid available field in Bugs Portal Record View');

        $test = $parser->isValidField('release_link', $fielddefs['release_link']);
        $this->assertFalse($test, 'release_link should not be a valid available field in Bugs Portal Record View');
    }

    public function testBugsPortalListAvailableFields()
    {
        $parser = ParserFactory::getParser(MB_PORTALLISTVIEW, $this->editModule, null, null, MB_PORTAL);
        $fielddefs = $parser->getFieldDefs();

        $test = $parser->isValidField('fixed_in_release', $fielddefs['fixed_in_release']);
        $this->assertFalse($test, 'fixed_in_release should not be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('date_modified', $fielddefs['date_modified']);
        $this->assertTrue($test, 'date_modified should be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('fixed_in_release_link', $fielddefs['fixed_in_release_link']);
        $this->assertFalse($test, 'fixed_in_release_link should not be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('found_in_release', $fielddefs['found_in_release']);
        $this->assertFalse($test, 'found_in_release should not be a valid available field in Bugs Portal List View');

        $test = $parser->isValidField('release_link', $fielddefs['release_link']);
        $this->assertFalse($test, 'release_link should not be a valid available field in Bugs Portal List View');
    }
}
