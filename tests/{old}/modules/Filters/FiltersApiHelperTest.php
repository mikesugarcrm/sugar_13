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
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass FiltersApiHelper
 */
class FiltersApiHelperTest extends TestCase
{
    private $helper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $api = SugarTestRestUtilities::getRestServiceMock();
        $this->helper = new FiltersApiHelper($api);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        parent::tearDownAfterClass();
    }

    /**
     * @covers ::formatForApi
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::doFilters
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::doFilter
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::doField
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\Field::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiSerialize
     */
    public function testFormatForApi()
    {
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();

        $filter = [
            [
                '$or' => [
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                ],
                                [
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                ],
                            ],
                        ],
                        'cc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                ],
                                [
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                ],
                            ],
                        ],
                        'bcc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                ],
                                [
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'state' => [
                    '$in' => [
                        'Archived',
                    ],
                ],
            ],
            [
                'assigned_user_id' => [
                    '$in' => [
                        $GLOBALS['current_user']->id,
                    ],
                ],
            ],
        ];

        $bean = BeanFactory::newBean('Filters');
        $bean->new_with_id = false;
        $bean->id = Uuid::uuid1();
        $bean->name = 'test filter';
        $bean->filter_definition = json_encode($filter);
        $bean->filter_template = json_encode($filter);
        $bean->module_name = 'Emails';

        $fieldList = [
            'id',
            'name',
            'filter_definition',
            'filter_template',
            'module_name',
        ];
        $data = $this->helper->formatForApi($bean, $fieldList);

        $this->assertSame($bean->name, $data['name'], 'The names do not match');
        $this->assertSame(
            $bean->module_name,
            $data['module_name'],
            'The module names do not match'
        );

        foreach (['filter_definition', 'filter_template'] as $field) {
            $this->assertSame(
                'to',
                $data[$field][0]['$or'][0]['to_collection']['$in'][0]['_link'],
                "{$field}: The links don't match for the contact in to_collection"
            );
            $this->assertSame(
                $contact->name,
                $data[$field][0]['$or'][0]['to_collection']['$in'][0]['parent_name'],
                "{$field}: The parent names don't match for the contact in to_collection"
            );
            $this->assertArrayHasKey(
                'parent',
                $data[$field][0]['$or'][0]['to_collection']['$in'][0],
                "{$field}: No parent key for the contact in to_collection"
            );
            $this->assertSame(
                'to',
                $data[$field][0]['$or'][0]['to_collection']['$in'][1]['_link'],
                "{$field}: The links don't match for the lead in to_collection"
            );
            $this->assertSame(
                $lead->name,
                $data[$field][0]['$or'][0]['to_collection']['$in'][1]['parent_name'],
                "{$field}: The parent names don't match for the lead in to_collection"
            );
            $this->assertArrayHasKey(
                'parent',
                $data[$field][0]['$or'][0]['to_collection']['$in'][1],
                "{$field}: No parent key for the lead in to_collection"
            );
            $this->assertSame(
                'cc',
                $data[$field][0]['$or'][0]['cc_collection']['$in'][0]['_link'],
                "{$field}: The links don't match for the contact in cc_collection"
            );
            $this->assertSame(
                $contact->name,
                $data[$field][0]['$or'][0]['cc_collection']['$in'][0]['parent_name'],
                "{$field}: The parent names don't match for the contact in cc_collection"
            );
            $this->assertArrayHasKey(
                'parent',
                $data[$field][0]['$or'][0]['cc_collection']['$in'][0],
                "{$field}: No parent key for the contact in cc_collection"
            );
            $this->assertSame(
                'cc',
                $data[$field][0]['$or'][0]['cc_collection']['$in'][1]['_link'],
                "{$field}: The links don't match for the lead in cc_collection"
            );
            $this->assertSame(
                $lead->name,
                $data[$field][0]['$or'][0]['cc_collection']['$in'][1]['parent_name'],
                "{$field}: The parent names don't match for the lead in cc_collection"
            );
            $this->assertArrayHasKey(
                'parent',
                $data[$field][0]['$or'][0]['cc_collection']['$in'][1],
                "{$field}: No parent key for the lead in cc_collection"
            );
            $this->assertSame(
                'bcc',
                $data[$field][0]['$or'][0]['bcc_collection']['$in'][0]['_link'],
                "{$field}: The links don't match for the contact in bcc_collection"
            );
            $this->assertSame(
                $contact->name,
                $data[$field][0]['$or'][0]['bcc_collection']['$in'][0]['parent_name'],
                "{$field}: The parent names don't match for the contact in bcc_collection"
            );
            $this->assertArrayHasKey(
                'parent',
                $data[$field][0]['$or'][0]['bcc_collection']['$in'][0],
                "{$field}: No parent key for the contact in bcc_collection"
            );
            $this->assertSame(
                'bcc',
                $data[$field][0]['$or'][0]['bcc_collection']['$in'][1]['_link'],
                "{$field}: The links don't match for the lead in bcc_collection"
            );
            $this->assertSame(
                $lead->name,
                $data[$field][0]['$or'][0]['bcc_collection']['$in'][1]['parent_name'],
                "{$field}: The parent names don't match for the lead in bcc_collection"
            );
            $this->assertArrayHasKey(
                'parent',
                $data[$field][0]['$or'][0]['bcc_collection']['$in'][1],
                "{$field}: No parent key for the lead in bcc_collection"
            );
        }
    }

    /**
     * @covers ::populateFromApi
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::doFilters
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::doFilter
     * @covers \Sugarcrm\Sugarcrm\Filters\Filter::doField
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\Field::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiUnserialize
     */
    public function testPopulateFromApi()
    {
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();

        $filter = [
            [
                '$or' => [
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'to',
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                    'parent_name' => $contact->name,
                                    'parent' => [
                                        'type' => $contact->getModuleName(),
                                        'id' => $contact->id,
                                        'name' => $contact->name,
                                        '_acl' => [],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'to',
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                    'parent_name' => $lead->name,
                                    'parent' => [
                                        'type' => $lead->getModuleName(),
                                        'id' => $lead->id,
                                        'name' => $lead->name,
                                        '_acl' => [],
                                        '_erased_fields' => [],
                                    ],
                                ],
                            ],
                        ],
                        'cc_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'cc',
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                    'parent_name' => $contact->name,
                                    'parent' => [
                                        'type' => $contact->getModuleName(),
                                        'id' => $contact->id,
                                        'name' => $contact->name,
                                        '_acl' => [],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'cc',
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                    'parent_name' => $lead->name,
                                    'parent' => [
                                        'type' => $lead->getModuleName(),
                                        'id' => $lead->id,
                                        'name' => $lead->name,
                                        '_acl' => [],
                                        '_erased_fields' => [],
                                    ],
                                ],
                            ],
                        ],
                        'bcc_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'bcc',
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                    'parent_name' => $contact->name,
                                    'parent' => [
                                        'type' => $contact->getModuleName(),
                                        'id' => $contact->id,
                                        'name' => $contact->name,
                                        '_acl' => [],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'bcc',
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                    'parent_name' => $lead->name,
                                    'parent' => [
                                        'type' => $lead->getModuleName(),
                                        'id' => $lead->id,
                                        'name' => $lead->name,
                                        '_acl' => [],
                                        '_erased_fields' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'state' => [
                    '$in' => [
                        'Archived',
                    ],
                ],
            ],
            [
                'assigned_user_id' => [
                    '$in' => [
                        $GLOBALS['current_user']->id,
                    ],
                ],
            ],
        ];

        $bean = BeanFactory::newBean('Filters');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'name' => 'test filter',
            'filter_definition' => $filter,
            'filter_template' => $filter,
            'module_name' => 'Emails',
        ];
        $result = $this->helper->populateFromApi($bean, $submittedData);

        $this->assertTrue($result, 'Populating was unsuccessful');
        $this->assertSame(
            $bean->name,
            $submittedData['name'],
            'The names do not match'
        );
        $this->assertSame(
            $bean->module_name,
            $submittedData['module_name'],
            'The module names do not match'
        );

        $expected = [
            [
                '$or' => [
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                ],
                                [
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                ],
                            ],
                        ],
                        'cc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                ],
                                [
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                ],
                            ],
                        ],
                        'bcc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => $contact->getModuleName(),
                                    'parent_id' => $contact->id,
                                ],
                                [
                                    'parent_type' => $lead->getModuleName(),
                                    'parent_id' => $lead->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'state' => [
                    '$in' => [
                        'Archived',
                    ],
                ],
            ],
            [
                'assigned_user_id' => [
                    '$in' => [
                        $GLOBALS['current_user']->id,
                    ],
                ],
            ],
        ];
        $this->assertEquals(
            json_encode($expected),
            $bean->filter_definition,
            'The filter definitions do not match'
        );
        $this->assertEquals(
            json_encode($expected),
            $bean->filter_template,
            'The filter templates do not match'
        );
    }
}
