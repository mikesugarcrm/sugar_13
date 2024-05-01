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

require_once 'data/Relationships/SugarRelationship.php';

class SugarRelationshipTest extends TestCase
{
    protected $hooks;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        LogicHook::refreshHooks();
        $this->hooks = [
            ['Opportunities', 'after_relationship_update', [1, 'Opportunities::after_relationship_update', __FILE__, 'SugarRelationshipTestHook', 'testFunction']],
            ['Contacts', 'after_relationship_update', [1, 'Contacts::after_relationship_update', __FILE__, 'SugarRelationshipTestHook', 'testFunction']],
        ];
        foreach ($this->hooks as $hook) {
            call_user_func_array('check_logic_hook_file', $hook);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->hooks as $hook) {
            call_user_func_array('remove_logic_hook', $hook);
        }
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testCallAfterUpdate()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $contact = SugarTestContactUtilities::createContact();
        $opportunity->load_relationship('contacts');
        $opportunity->contacts->add($contact->id);
        // clear log
        SugarRelationshipTestHook::$log = [];
        // adding existing relationship should call 'after_relationship_update' hook
        $opportunity->contacts->add($contact->id);
        $this->assertEquals($contact->id, SugarRelationshipTestHook::$log[$opportunity->id]['after_relationship_update'], 'Logic hook not triggered for Opportunities:after_relationship_update:Contacts');
        $this->assertEquals($opportunity->id, SugarRelationshipTestHook::$log[$contact->id]['after_relationship_update'], 'Logic hook not triggered for Contacts:after_relationship_update:Opportunities');
    }

    /**
     * Tests getting the optional where clause
     *
     * @param array $options The options array
     * @param string $where Existing where table
     * @param SugarBean $related Related bean
     * @param string $expect Expected result
     * @dataProvider whereProvider
     */
    public function testGetOptionalWhereClause($options, $where, $related, $expect)
    {
        $relObj = $this->getMockRelationship();
        $actual = $relObj->getWhereClause($options, $where, $related);
        $this->assertEquals($actual, $expect);
    }

    protected function getMockBean()
    {
        // Mocks the related bean used in the relationship
        $mock = $this->getMockBuilder('Bug')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->any())
            ->method('get_custom_table_name')
            ->will($this->returnValue('bug_foo_c'));

        // Sets certain test field defs to ensure proper functionality
        $mock->field_defs['foo']['source'] = 'custom_fields';
        $mock->field_defs['baz']['source'] = 'non-db';
        $mock->field_defs['zim'] = [];

        return $mock;
    }

    /**
     * Gets the mock relationship object, disabling the constructor since we
     * don't really need it.
     * @return SugarRelationship
     */
    protected function getMockRelationship()
    {
        $mock = $this->getMockBuilder('SugarRelationshipMock')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        return $mock;
    }

    public function whereProvider()
    {
        return [
            [
                'options' => [
                    'lhs_field' => 'foo',
                    'operator' => '=',
                    'rhs_value' => 'bar',
                ],
                'where' => 'mytable',
                'related' => $this->getMockBean(),
                'expect' => "bug_foo_c.foo='bar'",
            ],
            [
                'options' => [
                    'lhs_field' => 'baz',
                    'operator' => '=',
                    'rhs_value' => 'zim',
                ],
                'where' => 'thattable',
                'related' => $this->getMockBean(),
                'expect' => "thattable.baz='zim'",
            ],
            [
                'options' => [
                    'lhs_field' => 'zim',
                    'operator' => '=',
                    'rhs_value' => 'car',
                ],
                'where' => '',
                'related' => $this->getMockBean(),
                'expect' => "zim='car'",
            ],
        ];
    }
}

class SugarRelationshipTestHook
{
    public static $log = [];

    public function testFunction($bean, $event, $arguments)
    {
        self::$log[$bean->id][$event] = $arguments['related_id'];
    }
}

/**
 * Test class used for exposing protected methods
 */
class SugarRelationshipMock extends M2MRelationship
{
    public function getWhereClause($options, $where, $related)
    {
        return $this->getOptionalWhereClause($options, $where, $related);
    }
}
