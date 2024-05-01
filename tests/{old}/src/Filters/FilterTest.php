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

namespace Sugarcrm\SugarcrmTests\Filters;

use SugarTestContactUtilities;
use SugarTestHelper;
use SugarTestRestUtilities;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Filter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Filter
 */
class FilterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDownAfterClass();
    }

    /**
     * @covers ::apiSerialize
     * @covers ::doFilters
     * @covers ::doFilter
     * @covers ::doField
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\Field::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\Operand::apiSerialize
     */
    public function testApiSerializeWithAnEmailParticipantsField()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $filterDef = [
            [
                '$or' => [
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact1->id,
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact2->id,
                                ],
                            ],
                        ],
                        'cc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact1->id,
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact2->id,
                                ],
                            ],
                        ],
                        'bcc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact1->id,
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact2->id,
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $filter = new Filter('Emails', $filterDef);

        $actual = $filter->apiSerialize($api);

        $expected = [
            [
                '$or' => [
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'to',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact1->id,
                                    'parent_name' => $contact1->name,
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => $contact1->id,
                                        'name' => $contact1->name,
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'to',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact2->id,
                                    'parent_name' => $contact2->name,
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => $contact2->id,
                                        'name' => $contact2->name,
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                            ],
                        ],
                        'cc_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'cc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact1->id,
                                    'parent_name' => $contact1->name,
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => $contact1->id,
                                        'name' => $contact1->name,
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'cc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact2->id,
                                    'parent_name' => $contact2->name,
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => $contact2->id,
                                        'name' => $contact2->name,
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                            ],
                        ],
                        'bcc_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'bcc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact1->id,
                                    'parent_name' => $contact1->name,
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => $contact1->id,
                                        'name' => $contact1->name,
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'bcc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact2->id,
                                    'parent_name' => $contact2->name,
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => $contact2->id,
                                        'name' => $contact2->name,
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::apiUnserialize
     * @covers ::doFilters
     * @covers ::doFilter
     * @covers ::doField
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\Field::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\Operand::apiUnserialize
     */
    public function testApiUnserializeWithAnEmailParticipantsField()
    {
        $filterDef = [
            [
                '$or' => [
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'to',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'abc',
                                    'parent_name' => 'Joe Walsh',
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => 'abc',
                                        'name' => 'Joe Walsh',
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'to',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'def',
                                    'parent_name' => 'Tommy Hunter',
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => 'def',
                                        'name' => 'Tommy Hunter',
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                            ],
                        ],
                        'cc_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'cc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'abc',
                                    'parent_name' => 'Joe Walsh',
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => 'abc',
                                        'name' => 'Joe Walsh',
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'cc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'def',
                                    'parent_name' => 'Tommy Hunter',
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => 'def',
                                        'name' => 'Tommy Hunter',
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                            ],
                        ],
                        'bcc_collection' => [
                            '$in' => [
                                [
                                    '_link' => 'bcc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'abc',
                                    'parent_name' => 'Joe Walsh',
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => 'abc',
                                        'name' => 'Joe Walsh',
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
                                        '_erased_fields' => [],
                                    ],
                                ],
                                [
                                    '_link' => 'bcc',
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'def',
                                    'parent_name' => 'Tommy Hunter',
                                    'parent' => [
                                        'type' => 'Contacts',
                                        'id' => 'def',
                                        'name' => 'Tommy Hunter',
                                        '_acl' => [
                                            'fields' => (object)[],
                                        ],
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $filter = new Filter('Emails', $filterDef);

        $actual = $filter->apiUnserialize($api);

        $expected = [
            [
                '$or' => [
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'abc',
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'def',
                                ],
                            ],
                        ],
                        'cc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'abc',
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'def',
                                ],
                            ],
                        ],
                        'bcc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'abc',
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => 'def',
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::apiSerialize
     * @covers ::doFilters
     * @covers ::doFilter
     * @covers ::doField
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\Field::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiSerialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\Operand::apiSerialize
     */
    public function testApiSerializeWithAnEmailParticipantsOperand()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $filterDef = [
            [
                '$or' => [
                    [
                        '$to' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact1->id,
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact2->id,
                            ],
                        ],
                        '$cc' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact1->id,
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact2->id,
                            ],
                        ],
                        '$bcc' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact1->id,
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact2->id,
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $filter = new Filter('Emails', $filterDef);

        $actual = $filter->apiSerialize($api);

        $expected = [
            [
                '$or' => [
                    [
                        '$to' => [
                            [
                                '_link' => 'to',
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact1->id,
                                'parent_name' => $contact1->name,
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => $contact1->id,
                                    'name' => $contact1->name,
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                            [
                                '_link' => 'to',
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact2->id,
                                'parent_name' => $contact2->name,
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => $contact2->id,
                                    'name' => $contact2->name,
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                        ],
                        '$cc' => [
                            [
                                '_link' => 'cc',
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact1->id,
                                'parent_name' => $contact1->name,
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => $contact1->id,
                                    'name' => $contact1->name,
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                            [
                                '_link' => 'cc',
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact2->id,
                                'parent_name' => $contact2->name,
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => $contact2->id,
                                    'name' => $contact2->name,
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                        ],
                        '$bcc' => [
                            [
                                '_link' => 'bcc',
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact1->id,
                                'parent_name' => $contact1->name,
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => $contact1->id,
                                    'name' => $contact1->name,
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                            [
                                '_link' => 'bcc',
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact2->id,
                                'parent_name' => $contact2->name,
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => $contact2->id,
                                    'name' => $contact2->name,
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::apiUnserialize
     * @covers ::doFilters
     * @covers ::doFilter
     * @covers ::doField
     * @covers \Sugarcrm\Sugarcrm\Filters\Field\Field::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiUnserialize
     * @covers \Sugarcrm\Sugarcrm\Filters\Operand\Operand::apiUnserialize
     */
    public function testApiUnserializeWithAnEmailParticipantsOperand()
    {
        $filterDef = [
            [
                '$or' => [
                    [
                        '$to' => [
                            [
                                '_link' => 'to',
                                'parent_type' => 'Contacts',
                                'parent_id' => 'abc',
                                'parent_name' => 'Joe Walsh',
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => 'abc',
                                    'name' => 'Joe Walsh',
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                            [
                                '_link' => 'to',
                                'parent_type' => 'Contacts',
                                'parent_id' => 'def',
                                'parent_name' => 'Tommy Hunter',
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => 'def',
                                    'name' => 'Tommy Hunter',
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                        ],
                        '$cc' => [
                            [
                                '_link' => 'cc',
                                'parent_type' => 'Contacts',
                                'parent_id' => 'abc',
                                'parent_name' => 'Joe Walsh',
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => 'abc',
                                    'name' => 'Joe Walsh',
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                            [
                                '_link' => 'cc',
                                'parent_type' => 'Contacts',
                                'parent_id' => 'def',
                                'parent_name' => 'Tommy Hunter',
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => 'def',
                                    'name' => 'Tommy Hunter',
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                        ],
                        '$bcc' => [
                            [
                                '_link' => 'bcc',
                                'parent_type' => 'Contacts',
                                'parent_id' => 'abc',
                                'parent_name' => 'Joe Walsh',
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => 'abc',
                                    'name' => 'Joe Walsh',
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
                                ],
                            ],
                            [
                                '_link' => 'bcc',
                                'parent_type' => 'Contacts',
                                'parent_id' => 'def',
                                'parent_name' => 'Tommy Hunter',
                                'parent' => [
                                    'type' => 'Contacts',
                                    'id' => 'def',
                                    'name' => 'Tommy Hunter',
                                    '_acl' => [
                                        'fields' => (object)[],
                                    ],
                                    '_erased_fields' => [],
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $filter = new Filter('Emails', $filterDef);

        $actual = $filter->apiUnserialize($api);

        $expected = [
            [
                '$or' => [
                    [
                        '$to' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => 'abc',
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => 'def',
                            ],
                        ],
                        '$cc' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => 'abc',
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => 'def',
                            ],
                        ],
                        '$bcc' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => 'abc',
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => 'def',
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
                        'seed_will_id',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }
}
