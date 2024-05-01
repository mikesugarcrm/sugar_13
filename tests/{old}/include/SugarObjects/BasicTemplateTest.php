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

class BasicTemplateTest extends TestCase
{
    private $bean;

    protected function setUp(): void
    {
        $this->bean = new Basic();
    }

    protected function tearDown(): void
    {
        unset($this->bean);
    }

    public function testNameIsReturnedAsSummaryText()
    {
        $this->bean->name = 'teststring';
        $this->assertEquals($this->bean->get_summary_text(), $this->bean->name);
    }

    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeTrueAsAString()
    {
        $this->bean->field_defs['date_entered']['importable'] = 'true';
        $this->assertTrue(
            array_key_exists('date_entered', $this->bean->get_importable_fields()),
            'Field date_entered should be importable'
        );
    }

    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeTrueAsABoolean()
    {
        $this->bean->field_defs['date_entered']['importable'] = true;
        $this->assertTrue(
            array_key_exists('date_entered', $this->bean->get_importable_fields()),
            'Field date_entered should be importable'
        );
    }

    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeFalseAsAString()
    {
        $this->bean->field_defs['date_entered']['importable'] = 'false';
        $this->assertFalse(
            array_key_exists('date_entered', $this->bean->get_importable_fields()),
            'Field date_entered should not be importable'
        );
    }

    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeFalseAsABoolean()
    {
        $this->bean->field_defs['date_entered']['importable'] = false;
        $this->assertFalse(
            array_key_exists('date_entered', $this->bean->get_importable_fields()),
            'Field date_entered should not be importable'
        );
    }

    public function testGetBeanFieldsAsAnArray()
    {
        $this->bean->field_defs['date_entered'] = [];
        $this->bean->date_entered = '2009-01-01 12:00:00';
        $array = $this->bean->toArray();
        $this->assertEquals($array['date_entered'], $this->bean->date_entered);
    }
}
