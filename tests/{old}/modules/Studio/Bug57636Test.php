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
 * Bug 57636
 *
 * For meetings module, in mobile edit and detail, duration hours and duration_minutes
 * should not be on any layout.
 */
class Bug57636Test extends TestCase
{
    protected $testModule = 'Meetings';
    protected $testFields = ['duration_hours', 'duration_minutes'];

    protected function setUp(): void
    {
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('beanFiles');
        SugarTestHelper::setup('app_list_strings');
        SugarTestHelper::setup('mod_strings', [$this->testModule]);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug57636
     *
     * Tests that duration_minutes and duration_hours are not in both default fields
     * and available fields for mobile edit and detail layout editors
     */
    public function testDurationFieldsAreNotInMobileMeetingsGridLayout()
    {
        // Get the mobile edit parser
        $parser = ParserFactory::getParser(MB_WIRELESSEDITVIEW, $this->testModule, null, null, MB_WIRELESS);

        // Fields that are on the layout
        $fields = $parser->getLayout();
        foreach ($this->testFields as $field) {
            $test = $this->fieldNameFoundInFields($field, $fields['LBL_PANEL_DEFAULT']);
            $this->assertFalse($test, "$field should not be in default edit view fields");
        }

        // Fields that can be added to a layout
        $fields = $parser->getAvailableFields();
        foreach ($this->testFields as $field) {
            $test = $this->fieldNameFoundInFields($field, $fields);
            $this->assertFalse($test, "$field should not be in available edit view fields");
        }

        // Now get the mobile detail parser
        $parser = ParserFactory::getParser(MB_WIRELESSDETAILVIEW, $this->testModule, null, null, MB_WIRELESS);

        // Fields that are on the layout
        $fields = $parser->getLayout();
        foreach ($this->testFields as $field) {
            $test = $this->fieldNameFoundInFields($field, $fields['LBL_PANEL_DEFAULT']);
            $this->assertFalse($test, "$field should not be in default detail view fields");
        }

        // Fields that can be added to a layout
        $fields = $parser->getAvailableFields();
        foreach ($this->testFields as $field) {
            $test = $this->fieldNameFoundInFields($field, $fields);
            $this->assertFalse($test, "$field should not be in available in detail view fields");
        }
    }

    /**
     * Utility method to parse field defs for MOST grid type layouts
     *
     * @param string $name The field name to check for
     * @param array $fields The defs to search
     * @return bool
     */
    private function fieldNameFoundInFields($name, $fields)
    {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] == $name) {
                return true;
            }
        }

        return false;
    }
}
