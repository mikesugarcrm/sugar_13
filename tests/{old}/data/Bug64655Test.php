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
 * @ticket 64655
 */
class Bug64655Test extends TestCase
{
    /** @var SugarBean */
    private $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->bean = new Bug64655Test_SugarBean1();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testPopulateFromRow()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f l');

        $this->bean->populateFromRow(
            [
                'rel_contact_name_first_name' => 'John',
                'rel_contact_name_last_name' => 'Doe',
            ]
        );

        $this->assertEquals('John Doe', $this->bean->contact_name);
    }

    public function testFillInRelationshipFields()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'l, f');

        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->first_name = 'John';
        $contact1->last_name = 'Doe';
        $contact1->save();

        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->load_relationship('reports_to_link');
        $contact2->reports_to_link->add($contact1);

        $contact2 = BeanFactory::retrieveBean($contact2->module_name, $contact2->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals('Doe, John', $contact2->report_to_name);
    }

    /**
     * @param array $rel_field_defs
     * @param string $alias
     * @param string $expected
     *
     * @dataProvider provider
     */
    public function testGetRelateFieldQuery(array $rel_field_defs, $alias, $expectedSelect, $expectedFields)
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f b');

        $bean = new Bug64655Test_SugarBean2();
        $bean->field_defs = $rel_field_defs;
        $query = $bean->getRelateFieldQuery($this->bean->field_defs['contact_name'], $alias);
        $this->assertEquals($expectedSelect, $query['select']);
        $this->assertEquals($expectedFields, $query['fields']);
    }

    public function testCustomFieldsInFormat()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f b');

        $bean = new Bug64655Test_SugarBean3();
        $query = $bean->getRelateFieldQuery($this->bean->field_defs['contact_name'], 'jt');

        $this->assertStringContainsString('jt.foo rel_contact_name_foo', $query['select']);
        $this->assertStringContainsString('jt_cstm.bar rel_contact_name_bar', $query['select']);
        $this->assertStringContainsString(
            'LEFT JOIN bug64655test2_cstm jt_cstm ON jt_cstm.id_c = jt.id',
            $query['join']
        );
    }

    public static function provider()
    {
        return [
            'empty-vardefs' => [
                [], 'jt1', '', [],
            ],
            'non-name-field' => [
                [
                    'name' => [
                        'name' => 'name',
                        'type' => 'varchar',
                    ],
                ],
                'jt2',
                'jt2.name contact_name',
                [
                    'contact_name' => 'jt2.name',
                ],
            ],
            'name-field' => [
                [
                    'name' => [
                        'name' => 'name',
                        'type' => 'fullname',
                    ],
                ],
                'jt3',
                'jt3.foo rel_contact_name_foo, jt3.bar rel_contact_name_bar',
                [
                    'rel_contact_name_foo' => 'jt3.foo',
                    'rel_contact_name_bar' => 'jt3.bar',
                ],
            ],
        ];
    }
}

class Bug64655Test_SugarBean1 extends SugarBean
{
    public $object_name = 'Bug64655Test1';
    public $field_defs = [
        'contact_name' => [
            'name' => 'contact_name',
            'rname' => 'name',
            'type' => 'relate',
            'module' => 'Contacts',
            'id_name' => 'contact_id',
        ],
    ];
}

class Bug64655Test_SugarBean2 extends SugarBean
{
    public $object_name = 'Bug64655Test2';
    public $name_format_map = [
        'f' => 'foo',
        'b' => 'bar',
    ];
}

class Bug64655Test_SugarBean3 extends SugarBean
{
    public $object_name = 'Bug64655Test3';
    public $name_format_map = [
        'f' => 'foo',
        'b' => 'bar',
    ];
    public $table_name = 'bug64655test2';
    public $field_defs = [
        'name' => [
            'name' => 'name',
            'type' => 'fullname',
        ],
        'foo' => [
            'name' => 'foo',
        ],
        'bar' => [
            'name' => 'bar',
            'custom_module' => 'Bug64655Test2',
        ],
    ];
}
