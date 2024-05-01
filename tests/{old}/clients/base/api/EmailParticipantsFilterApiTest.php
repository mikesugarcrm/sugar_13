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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass FilterApi
 * @group api
 * @group email
 */
class EmailParticipantsFilterApiTest extends TestCase
{
    protected $api;
    protected $service;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new FilterApi();
    }

    protected function tearDown(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers ::filterList
     * @covers ::filterListSetup
     * @covers ::addFilters
     * @covers ::addFilter
     * @covers ::addFieldFilter
     * @covers ::addEmailParticipantFilter
     * @covers ::runQuery
     */
    public function testAddParticipantFilter()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'name' => 'Archived email sent by the current user to $contact',
        ];
        $email1 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email1->load_relationship('from');
        $email1->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email1->load_relationship('to');
        $email1->to->add($this->createEmailParticipant($contact));
        $email1->save();

        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'name' => 'Archived email sent by the current user to $user and $contact',
        ];
        $email2 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email2->load_relationship('from');
        $email2->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email2->load_relationship('to');
        $email2->to->add($this->createEmailParticipant($user));
        $email2->load_relationship('cc');
        $email2->cc->add($this->createEmailParticipant($contact));
        $email2->save();

        $data = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'name' => 'Draft email owned by the current user',
        ];
        $email3 = SugarTestEmailUtilities::createEmail('', $data);
        $email3->load_relationship('to');
        $email3->to->add($this->createEmailParticipant($user));

        $data = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'name' => 'Draft email owned by the current user to be sent to $contact',
        ];
        $email4 = SugarTestEmailUtilities::createEmail('', $data);
        $email4->load_relationship('from');
        $email4->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email4->load_relationship('cc');
        $email4->cc->add($this->createEmailParticipant($contact));

        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
            'name' => 'Archived email sent by $user to the current user',
        ];
        $email5 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email5->load_relationship('from');
        $email5->from->add($this->createEmailParticipant($user));
        $email5->load_relationship('to');
        $email5->to->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email5->save();

        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'name' => 'Archived email sent by $contact to $user and the current user',
        ];
        $email6 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email6->load_relationship('from');
        $email6->from->add($this->createEmailParticipant($contact));
        $email6->load_relationship('to');
        $email6->to->add($this->createEmailParticipant($user));
        $email6->load_relationship('bcc');
        $email6->bcc->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email6->save();

        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'name' => 'Archived email sent by the current user to an email address',
        ];
        $email7 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email7->load_relationship('from');
        $email7->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email7->load_relationship('to');
        $email7->to->add($this->createEmailParticipant(null, $address));
        $email7->save();

        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
            'name' => 'Archived email sent by the contact to the user with the specified email address',
        ];
        $email8 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email8->load_relationship('from');
        $email8->from->add($this->createEmailParticipant($contact));
        $email8->load_relationship('to');
        $email8->to->add($this->createEmailParticipant($user, $address));
        $email8->save();

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'parent_name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'parent_name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(5, $response['records'], "{$desc}: All emails where the current user is the sender");

            $expected = [$email1->id, $email2->id, $email3->id, $email4->id, $email7->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails where the current user is the sender: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                    ],
                    [
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'parent_name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                    ],
                    [
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(3, $response['records'], "{$desc}: All archived emails sent by the current user");

            $expected = [$email1->id, $email2->id, $email7->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All archived emails sent by the current user: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                            [
                                'parent_type' => 'Users',
                                'parent_id' => $user->id,
                            ],
                        ],
                    ],
                    [
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                ],
                            ],
                        ],
                    ],
                    [
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                4,
                $response['records'],
                "{$desc}: All archived emails sent by the current user or other user"
            );

            $expected = [$email1->id, $email2->id, $email5->id, $email7->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All archived emails sent by the current user or other user: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact->id,
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(2, $response['records'], "{$desc}: All emails sent by the contact");

            $expected = [$email6->id, $email8->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent by the contact: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(2, $response['records'], "{$desc}: All archived emails received by the current user");

            $expected = [$email5->id, $email6->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All archived emails received by the current user: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => $user->id,
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => $user->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => $user->id,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => $user->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => $user->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => $user->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(5, $response['records'], "{$desc}: All emails received by the current user or \$user");

            $expected = [$email2->id, $email3->id, $email5->id, $email6->id, $email8->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails received by the current user or \$user: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                '$from' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                ],
                            ],
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                'from_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                5,
                $response['records'],
                "{$desc}: All emails sent by the current user or to the contact"
            );

            $expected = [$email1->id, $email2->id, $email3->id, $email4->id, $email7->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent by the current user or to the contact: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                '$to' => [
                                    [
                                        'email_address_id' => $address->id,
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'email_address_id' => $address->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'email_address_id' => $address->id,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'email_address_id' => $address->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'email_address_id' => $address->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'email_address_id' => $address->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(2, $response['records'], "{$desc}: All emails sent to the email address");

            $expected = [$email7->id, $email8->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent to the email address: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => $user->id,
                                        'email_address_id' => $address->id,
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => $user->id,
                                        'email_address_id' => $address->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => $user->id,
                                        'email_address_id' => $address->id,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$or' => [
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => $user->id,
                                            'email_address_id' => $address->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => $user->id,
                                            'email_address_id' => $address->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => $user->id,
                                            'email_address_id' => $address->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                1,
                $response['records'],
                "{$desc}: All emails sent to the user with the specified email address"
            );

            $expected = [$email8->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent to the user with the specified email address: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                    ],
                    [
                        '$to' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => $user->id,
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact->id,
                            ],
                        ],
                    ],
                    [
                        '$cc' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => $user->id,
                            ],
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact->id,
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                    ],
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                    ],
                    [
                        'cc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                ],
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                1,
                $response['records'],
                "{$desc}: All emails sent by the current user to \$user and the contact"
            );

            $expected = [$email2->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent by the current user to \$user and the contact: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                    ],
                    [
                        '$to' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => $user->id,
                            ],
                        ],
                    ],
                    [
                        '$cc' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact->id,
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                    ],
                    [
                        'to_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                ],
                            ],
                        ],
                    ],
                    [
                        'cc_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                1,
                $response['records'],
                "{$desc}: All emails sent by the current user to \$user, copying the contact"
            );

            $expected = [$email2->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent by the current user to \$user, copying the contact: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$and' => [
                            [
                                '$from' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => $user->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Users',
                                        'parent_id' => '$current_user_id',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$and' => [
                            [
                                'from_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => $user->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Users',
                                            'parent_id' => '$current_user_id',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                1,
                $response['records'],
                "{$desc}: All emails sent from the contact to \$user, blind copying the current user"
            );

            $expected = [$email6->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent from the contact to \$user, blind copying the current user: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                        '$or' => [
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                        '$or' => [
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                3,
                $response['records'],
                "{$desc}: All emails sent by the current user and to the contact"
            );

            $expected = [$email1->id, $email2->id, $email4->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All emails sent by the current user and to the contact: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                        '$or' => [
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                        ],
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                        '$or' => [
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'state' => [
                            '$in' => ['Archived'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                2,
                $response['records'],
                "{$desc}: All archived emails sent by the current user and to the contact"
            );

            $expected = [$email1->id, $email2->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All archived emails sent by the current user and to the contact: {$record['name']}"
                );
            }
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                        '$to' => [
                            [
                                'parent_type' => 'Contacts',
                                'parent_id' => $contact->id,
                            ],
                        ],
                        'state' => [
                            '$in' => ['Draft'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                        'to_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                        'state' => [
                            '$in' => ['Draft'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                0,
                $response['records'],
                "{$desc}: All drafts to be sent by the current user and directly to the contact"
            );
        }

        $shapes = [
            'uses_macro' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Users',
                                'parent_id' => '$current_user_id',
                            ],
                        ],
                        '$or' => [
                            [
                                '$to' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$cc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                            [
                                '$bcc' => [
                                    [
                                        'parent_type' => 'Contacts',
                                        'parent_id' => $contact->id,
                                    ],
                                ],
                            ],
                        ],
                        'state' => [
                            '$in' => ['Draft'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
            'uses_field_name' => [
                'module' => 'Emails',
                'filter' => [
                    [
                        'from_collection' => [
                            '$in' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                        '$or' => [
                            [
                                'to_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'cc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bcc_collection' => [
                                    '$in' => [
                                        [
                                            'parent_type' => 'Contacts',
                                            'parent_id' => $contact->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'state' => [
                            '$in' => ['Draft'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        ];

        foreach ($shapes as $desc => $args) {
            $response = $this->api->filterList($this->service, $args);

            $this->assertCount(
                1,
                $response['records'],
                "{$desc}: All drafts to be sent by the current user and to the contact"
            );

            $expected = [$email4->id];

            foreach ($response['records'] as $record) {
                $this->assertContains(
                    $record['id'],
                    $expected,
                    "{$desc}: All archived emails sent by the current user and to the contact: {$record['name']}"
                );
            }
        }
    }

    public function throwsSugarApiExceptionInvalidParameterProvider()
    {
        return [
            'no_parent_id_or_email_address_id' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Contacts',
                            ],
                        ],
                    ],
                ],
            ],
            'no_parent_id' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Contacts',
                                'email_address_id' => Uuid::uuid1(),
                            ],
                        ],
                    ],
                ],
            ],
            'no_parent_type_or_email_address_id' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_id' => Uuid::uuid1(),
                            ],
                        ],
                    ],
                ],
            ],
            'no_parent_type' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_id' => Uuid::uuid1(),
                                'email_address_id' => Uuid::uuid1(),
                            ],
                        ],
                    ],
                ],
            ],
            'filter_is_empty' => [
                [
                    [
                        '$from' => [
                            [],
                        ],
                    ],
                ],
            ],
            'operators_are_missing' => [
                [
                    [
                        'from_collection' => [],
                    ],
                ],
            ],
            '$in_operator_is_missing' => [
                [
                    [
                        'from_collection' => [
                            '$equals' => Uuid::uuid1(),
                        ],
                    ],
                ],
            ],
            'unsupported_operators' => [
                [
                    [
                        'from_collection' => [
                            '$in' => [
                                'parent_type' => 'Contacts',
                                'parent_id' => Uuid::uuid1(),
                            ],
                            '$not_equals' => Uuid::uuid1(),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::addEmailParticipantFilter
     * @dataProvider throwsSugarApiExceptionInvalidParameterProvider
     */
    public function testAddParticipantFilter_ThrowsSugarApiExceptionInvalidParameter($def)
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->api->filterList($this->service, [
            'module' => 'Emails',
            'filter' => $def,
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ]);
    }

    /**
     * Sets up an EmailParticipants bean from the data on the bean and the email address so that it is ready to add to a
     * relationship.
     *
     * @param null|SugarBean $bean
     * @param null|SugarBean $address
     * @return SugarBean
     */
    private function createEmailParticipant($bean, $address = null)
    {
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        BeanFactory::registerBean($ep);

        if ($bean) {
            $ep->parent_type = $bean->getModuleName();
            $ep->parent_id = $bean->id;
        }

        if ($address) {
            $ep->email_address_id = $address->id;
        }

        return $ep;
    }
}
