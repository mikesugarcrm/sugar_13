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

class ModuleBuilderRelatedTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * This is a test to check that the commit stage labels are not shown on the drop down editor
     *
     * @group forecasts
     * @group bug59133
     */
    public function testRestrictedDropdownOptions()
    {
        $this->assertTrue(in_array('commit_stage_dom', DropDownBrowser::$restrictedDropdowns));
        $this->assertTrue(in_array('commit_stage_binary_dom', DropDownBrowser::$restrictedDropdowns));
        $this->assertTrue(in_array('commit_stage_custom_dom', DropDownBrowser::$restrictedDropdowns));
    }
}
