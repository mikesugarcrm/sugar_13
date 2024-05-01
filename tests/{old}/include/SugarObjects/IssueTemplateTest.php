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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarObjects\Templates\Issue;

use PHPUnit\Framework\TestCase;

/**
 * Class IssueTemplateTest
 * @package Sugarcrm\SugarcrmTestsUnit\inc\SugarObjects\Templates\Issue
 */
class IssueTemplateTest extends TestCase
{
    private $bean;
    private $manager;
    private $bc;
    private $delete;

    protected function setUp(): void
    {
        $this->bean = \SugarTestCaseUtilities::createCase();
        $this->manager = \MetaDataManager::getManager();

        $this->bc = new \BusinessCenter();
        $this->bc->name = 'A Business Center';
        $this->bc->timezone = 'America/Los_Angeles';
        $this->bc->is_open_thursday = 1;
        $this->bc->thursday_open_hour = '08';
        $this->bc->thursday_open_minutes = '00';
        $this->bc->thursday_close_hour = '17';
        $this->bc->thursday_close_minutes = '00';
        $this->bc->save();

        $this->bean->business_center_id = $this->bc->id;
        $this->delete[$this->bc->getTableName()][$this->bc->id] = $this->bc->id;
    }

    protected function tearDown(): void
    {
        \SugarTestCaseUtilities::removeAllCreatedCases();
        unset($this->bean);
        \MetaDataManager::resetManagers();

        $db = \DBManagerFactory::getInstance();
        foreach ($this->delete as $table => $ids) {
            $in = "'" . implode("','", $ids) . "'";
            $sql = "DELETE FROM $table WHERE id IN ($in)";
            $db->query($sql);
        }
        unset($this->bc);
    }

    public function checkModuleHasFieldsProvider(): array
    {
        return [
            ['Bugs', 'follow_up_datetime', true],
            ['Bugs', 'resolved_datetime', true],
            ['Cases', 'follow_up_datetime', true],
            ['Cases', 'resolved_datetime', true],
            ['DataPrivacy', 'follow_up_datetime', true],
            ['DataPrivacy', 'resolved_datetime', true],
            ['Accounts', 'follow_up_datetime', false],
            ['Accounts', 'resolved_datetime', false],
            ['Contacts', 'follow_up_datetime', false],
            ['Contacts', 'resolved_datetime', false],
        ];
    }

    /**
     * Checks that modules should have certain issue type fields.
     *
     * @param string $module The module we would like to check fields
     * @param string $field The field to be checked
     * @param bool $hasField Whether the module should have the field being checked
     * @dataProvider checkModuleHasFieldsProvider
     */
    public function testCheckModuleHasFields(string $module, string $field, bool $hasField)
    {
        $field_defs = \BeanFactory::newBean($module)->field_defs;
        $this->assertSame($hasField, array_key_exists($field, $field_defs));
    }

    /**
     * Checks that modules that should have follow_up_datetime on the given
     * view do so.
     *
     * @param string $module The module for which we would like to check that
     *   follow_up_datetime is on the given view.
     * @param string $view The view to check.
     * @dataProvider hasFollowUpDateFieldOnViewProvider
     */
    public function testCheckModuleHasFollowUpDateFieldOnView(string $module, string $view)
    {
        $this->assertContains('follow_up_datetime', $this->manager->getModuleViewFields($module, $view));
    }

    public function hasFollowUpDateFieldOnViewProvider(): array
    {
        return [
            ['Bugs', 'record'],
            ['Cases', 'list'],
            ['Cases', 'record'],
            ['DataPrivacy', 'record'],
        ];
    }

    /**
     * Checks that modules that should not have follow_up_datetime on the
     * record view do not.
     *
     * @param string $module The module we would like to verify does not
     *   have follow_up_datetime on its record view.
     * @dataProvider hasNoFollowUpDateFieldOnRecordViewProvider
     */
    public function testCheckModulesHasNoFollowUpDateFieldOnView(string $module)
    {
        $this->assertNotContains('follow_up_datetime', $this->manager->getModuleViewFields($module, 'record'));
    }

    public function hasNoFollowUpDateFieldOnRecordViewProvider(): array
    {
        return [
            ['Accounts'], // not an issue type module
            ['Contacts'], // not an issue type module
        ];
    }

    /**
     * Ensure that calling calculateResolutionHours on a resolved Issue calculates its resolution hours.
     *
     * @dataProvider providerCalculateResolutionHours
     */
    public function testsCalculateResolutionHours(string $entered, string $resolved, array $expected)
    {
        $this->bean->date_entered = $entered;
        $this->bean->resolved_datetime = $resolved;
        unset($this->bean->hours_to_resolution);
        unset($this->bean->business_hours_to_resolution);

        $this->bean->calculateResolutionHours();

        $this->assertEquals($expected[0], $this->bean->hours_to_resolution);
        $this->assertEquals($expected[1], $this->bean->business_hours_to_resolution);
    }

    public function providerCalculateResolutionHours(): array
    {
        // datetime used are in UTC time, they will be converted to the timezone
        // defined in the Business Center and then calculated for business hours
        return [
            ['2019-09-05 12:30:00', '2019-09-05 16:30:00', [4.0, 1.5]],
        ];
    }
}
