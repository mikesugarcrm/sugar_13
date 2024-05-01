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

namespace Sugarcrm\SugarcrmTests\Filters\Field;

use SugarApiExceptionNotFound;
use SugarTestContactUtilities;
use SugarTestEmailAddressUtilities;
use SugarTestHelper;
use SugarTestRestUtilities;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants
 */
class EmailParticipantsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDownAfterClass();
    }

    public function fieldNameProvider()
    {
        return [
            'field: from_collection' => [
                'from_collection',
                'from',
            ],
            'field: to_collection' => [
                'to_collection',
                'to',
            ],
            'field: cc_collection' => [
                'cc_collection',
                'cc',
            ],
            'field: bcc_collection' => [
                'bcc_collection',
                'bcc',
            ],
        ];
    }

    /**
     * @covers ::apiSerialize
     * @covers       \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiSerialize
     * @dataProvider fieldNameProvider
     */
    public function testApiSerialize(string $fieldName, string $link)
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $emailAddress1 = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson(
            $contact1,
            $emailAddress1
        );

        $contact2 = SugarTestContactUtilities::createContact();

        $emailAddress2 = SugarTestEmailAddressUtilities::createEmailAddress();

        $filter = [
            '$in' => [
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => $contact1->id,
                    'email_address_id' => $emailAddress1->id,
                ],
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => $contact2->id,
                ],
                [
                    'email_address_id' => $emailAddress2->id,
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $field = new EmailParticipants($fieldName, $filter);

        $actual = $field->apiSerialize($api);

        $expected = [
            '$in' => [
                [
                    '_link' => $link,
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
                    'email_address_id' => $emailAddress1->id,
                    'email_address' => $emailAddress1->email_address,
                    'email_addresses' => [
                        'id' => $emailAddress1->id,
                        'email_address' => $emailAddress1->email_address,
                        '_acl' => [
                            'fields' => (object)[],
                        ],
                        '_erased_fields' => [],
                    ],
                ],
                [
                    '_link' => $link,
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
                [
                    '_link' => $link,
                    'email_address_id' => $emailAddress2->id,
                    'email_address' => $emailAddress2->email_address,
                    'email_addresses' => [
                        'id' => $emailAddress2->id,
                        'email_address' => $emailAddress2->email_address,
                        '_acl' => [
                            'fields' => (object)[],
                        ],
                        '_erased_fields' => [],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::apiSerialize
     * @covers       \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiSerialize
     * @dataProvider fieldNameProvider
     */
    public function testApiSerializeWithCurrentUserIdMacro(
        string $fieldName,
        string $link
    ) {

        $filter = [
            '$in' => [
                [
                    'parent_type' => 'Users',
                    'parent_id' => '$current_user_id',
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $field = new EmailParticipants($fieldName, $filter);

        $actual = $field->apiSerialize($api);

        $expected = [
            '$in' => [
                [
                    '_link' => $link,
                    'parent_type' => 'Users',
                    'parent_id' => '$current_user_id',
                    'parent_name' => $GLOBALS['current_user']->name,
                    'parent' => [
                        'type' => 'Users',
                        'id' => '$current_user_id',
                        'name' => $GLOBALS['current_user']->name,
                        '_acl' => [
                            'fields' => (object)[
                                'pwd_last_changed' => [
                                    'read' => 'yes',
                                ],
                                'last_login' => [
                                    'read' => 'yes',
                                ],
                            ],
                            'edit' => 'yes',
                            'create' => 'yes',
                        ],
                        '_erased_fields' => [],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::apiSerialize
     * @covers       \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiSerialize
     * @dataProvider fieldNameProvider
     */
    public function testApiSerializeParentNotFound(string $fieldName, string $link)
    {
        $filter = [
            '$in' => [
                [
                    'parent_type' => 'Contacts',
                    'parent_id' => Uuid::uuid1(),
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $field = new EmailParticipants($fieldName, $filter);

        $this->expectException(SugarApiExceptionNotFound::class);
        $field->apiSerialize($api);
    }

    /**
     * @covers ::apiSerialize
     * @covers       \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants::apiSerialize
     * @dataProvider fieldNameProvider
     */
    public function testApiSerializeEmailAddressNotFound(
        string $fieldName,
        string $link
    ) {

        $filter = [
            '$in' => [
                [
                    'email_address_id' => Uuid::uuid1(),
                ],
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $field = new EmailParticipants($fieldName, $filter);

        $this->expectException(SugarApiExceptionNotFound::class);
        $field->apiSerialize($api);
    }
}
