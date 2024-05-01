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

/**
 * @coversDefaultClass EmailsApi
 */
class EmailsApiIntegrationTest extends EmailsApiIntegrationTestCase
{
    private static $systemConfiguration;
    private static $overrideConfig;
    private static $userConfig;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        static::$overrideConfig = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
        static::$userConfig = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectUtilities::removeAllCreatedProspects();
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // By default, system configuration is not used, but can be safely overwritten by any test if needed
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
    }

    /**
     * When creating an archived email, any sender and recipients are allowed.
     *
     * @covers EmailsApi::createRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateArchivedEmail()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $args = [
            'state' => Email::STATE_ARCHIVED,
            'from' => [
                'create' => [
                    $this->createEmailParticipant($user),
                ],
            ],
            'to' => [
                'create' => [
                    $this->createEmailParticipant($contact),
                ],
            ],
            'cc' => [
                'create' => [
                    $this->createEmailParticipant($account),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame(Email::DIRECTION_OUTBOUND, $record['direction'], 'From user to contact and account');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $user->getModuleName(),
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'parent_name' => $user->name,
                'email_addresses' => [
                    'id' => $user->emailAddress->getGuid($user->email1),
                    'email_address' => $user->email1,
                ],
                'email_address_id' => $user->emailAddress->getGuid($user->email1),
                'email_address' => $user->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $contact->getModuleName(),
                    'id' => $contact->id,
                    'name' => $contact->name,
                ],
                'parent_name' => $contact->name,
                'email_addresses' => [
                    'id' => $contact->emailAddress->getGuid($contact->email1),
                    'email_address' => $contact->email1,
                ],
                'email_address_id' => $contact->emailAddress->getGuid($contact->email1),
                'email_address' => $contact->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc',
                'parent' => [
                    'type' => $account->getModuleName(),
                    'id' => $account->id,
                    'name' => $account->name,
                ],
                'parent_name' => $account->name,
                'email_addresses' => [
                    'id' => $account->emailAddress->getGuid($account->email1),
                    'email_address' => $account->email1,
                ],
                'email_address_id' => $account->emailAddress->getGuid($account->email1),
                'email_address' => $account->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc_collection');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals("{$user->name} <{$user->email1}>", $bean->from_addr_name);
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $bean->to_addrs_names);
        $this->assertEquals("{$account->name} <{$account->email1}>", $bean->cc_addrs_names);
    }

    /**
     * When creating a draft, the current user is always the sender, any recipients are allowed, and the specified
     * configuration is persisted.
     *
     * @covers EmailsApi::createRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateDraftEmail()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $lead = SugarTestLeadUtilities::createLead();
        $leadEmailAddress = BeanFactory::retrieveBean('EmailAddresses', $lead->emailAddress->getGuid($lead->email1));

        $args = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => static::$overrideConfig->id,
            'to' => [
                'create' => [
                    $this->createEmailParticipant($lead, $leadEmailAddress),
                ],
            ],
            // The same email participant can appear in multiple roles.
            'bcc' => [
                'create' => [
                    $this->createEmailParticipant($GLOBALS['current_user']),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be a draft');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            'Should use the specified configuration'
        );
        $this->assertSame(Email::DIRECTION_UNKNOWN, $record['direction'], 'Drafts use default direction');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $address->getGuid(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $address->getGuid(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $lead->getModuleName(),
                    'id' => $lead->id,
                    'name' => $lead->name,
                ],
                'parent_name' => $lead->name,
                'email_addresses' => [
                    'id' => $lead->emailAddress->getGuid($lead->email1),
                    'email_address' => $lead->email1,
                ],
                'email_address_id' => $leadEmailAddress->id,
                'email_address' => $lead->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'bcc',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'bcc_collection');
        $this->assertRecords($expected, $collection, 'The BCC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$overrideConfig->email_address . '>',
            $bean->from_addr_name
        );
        $this->assertEquals("{$lead->name} <{$lead->email1}>", $bean->to_addrs_names);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $bean->bcc_addrs_names
        );
    }

    /**
     * When updating a draft, the sender always remains the current user and the recipients and configuration may
     * change.
     *
     * @covers ::updateRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testUpdateDraftEmail()
    {
        $prospect = SugarTestProspectUtilities::createProspect();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $args = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => static::$overrideConfig->id,
            'cc' => [
                'create' => [
                    $this->createEmailParticipant($prospect),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be draft after create');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            'The configuration did not match expectations after create'
        );
        $this->assertSame(Email::DIRECTION_UNKNOWN, $record['direction'], 'Drafts use default direction');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc',
                'parent' => [
                    'type' => $prospect->getModuleName(),
                    'id' => $prospect->id,
                    'name' => $prospect->name,
                ],
                'parent_name' => $prospect->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc_collection');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations after create');

        $args = [
            'outbound_email_id' => static::$userConfig->id,
            'to' => [
                'create' => [
                    $this->createEmailParticipant(null, $address),
                ],
            ],
            // This should patch the email address onto the existing row for the prospect.
            'cc' => [
                'create' => [
                    $this->createEmailParticipant(
                        $prospect,
                        BeanFactory::retrieveBean('EmailAddresses', $prospect->emailAddress->getGuid($prospect->email1))
                    ),
                ],
            ],
        ];
        $record = $this->updateRecord($record['id'], $args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be draft after update');
        $this->assertSame(
            static::$userConfig->id,
            $record['outbound_email_id'],
            'The configuration should have changed'
        );
        $this->assertSame(Email::DIRECTION_UNKNOWN, $record['direction'], 'Direction should not change');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$userConfig->email_address),
                    'email_address' => static::$userConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$userConfig->email_address),
                'email_address' => static::$userConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords(
            $expected,
            $collection,
            'The sender should still be the current user, but with the email address assigned to the user configuration'
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [],
                'parent_name' => '',
                'email_addresses' => [
                    'id' => $address->id,
                    'email_address' => $address->email_address,
                ],
                'email_address_id' => $address->id,
                'email_address' => $address->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after update');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc',
                'parent' => [
                    'type' => $prospect->getModuleName(),
                    'id' => $prospect->id,
                    'name' => $prospect->name,
                ],
                'parent_name' => $prospect->name,
                'email_addresses' => [
                    'id' => $prospect->emailAddress->getGuid($prospect->email1),
                    'email_address' => $prospect->email1,
                ],
                'email_address_id' => $prospect->emailAddress->getGuid($prospect->email1),
                'email_address' => $prospect->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc_collection');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations after update');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$userConfig->email_address . '>',
            $bean->from_addr_name
        );
        $this->assertEquals($address->email_address, $bean->to_addrs_names);
        $this->assertEquals("{$prospect->name} <{$prospect->email1}>", $bean->cc_addrs_names);
    }

    /**
     * When sending a previously saved draft, the sender always remains the current user, the recipients and
     * configuration may change, and the email is ultimately archived.
     *
     * @covers ::updateRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers ModuleApi::unlinkRelatedRecords
     * @covers ::sendEmail
     * @covers Email::sendEmail
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testSendDraftEmail()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();
        $lead = SugarTestLeadUtilities::createLead();

        $args = [
            'state' => Email::STATE_DRAFT,
            'to' => [
                'create' => [
                    $this->createEmailParticipant($account1),
                    $this->createEmailParticipant($lead),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be draft after create');
        $this->assertTrue(empty($record['outbound_email_id']), 'No configuration was specified during create');
        $this->assertSame(Email::DIRECTION_UNKNOWN, $record['direction'], 'Drafts use default direction');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $account1->getModuleName(),
                    'id' => $account1->id,
                    'name' => $account1->name,
                ],
                'parent_name' => $account1->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $lead->getModuleName(),
                    'id' => $lead->id,
                    'name' => $lead->name,
                ],
                'parent_name' => $lead->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after create');

        // Need to extract the data that represents the EmailParticipants record for $account1, so we can use its ID.
        $epAccount1 = array_filter($collection['records'], function ($ep) {
            return $ep['parent']['type'] === 'Accounts';
        });

        $args = [
            'state' => Email::STATE_READY,
            'to' => [
                'create' => [
                    $this->createEmailParticipant($account2),
                ],
                'delete' => [
                    $epAccount1[0]['id'],
                ],
            ],
        ];
        $record = $this->updateRecord($record['id'], $args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived after sending');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            "Should use the user's system override configuration"
        );
        $this->assertSame(Email::DIRECTION_OUTBOUND, $record['direction'], 'From user to account and lead');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender should not have changed');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $account2->getModuleName(),
                    'id' => $account2->id,
                    'name' => $account2->name,
                ],
                'parent_name' => $account2->name,
                'email_addresses' => [
                    'id' => $account2->emailAddress->getGuid($account2->email1),
                    'email_address' => $account2->email1,
                ],
                'email_address_id' => $account2->emailAddress->getGuid($account2->email1),
                'email_address' => $account2->email1,
            ],
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $lead->getModuleName(),
                    'id' => $lead->id,
                    'name' => $lead->name,
                ],
                'parent_name' => $lead->name,
                'email_addresses' => [
                    'id' => $lead->emailAddress->getGuid($lead->email1),
                    'email_address' => $lead->email1,
                ],
                'email_address_id' => $lead->emailAddress->getGuid($lead->email1),
                'email_address' => $lead->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after sending');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$overrideConfig->email_address . '>',
            $bean->from_addr_name
        );
        $expected = ["{$lead->name} <{$lead->email1}>", "{$account2->name} <{$account2->email1}>"];
        $this->assertEquals(
            $this->sortEmailAddresses($expected),
            $this->emailAddrsToArray($bean->to_addrs_names)
        );
    }

    /**
     * When creating an email and immediately sending it, the current user is always the sender, any recipients are
     * allowed, the configuration that is used is persisted, and the email is ultimately archived.
     *
     * @covers EmailsApi::createRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers Email::sendEmail
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateAndSendEmail()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $args = [
            'state' => Email::STATE_READY,
            'to' => [
                'create' => [
                    $this->createEmailParticipant($contact1),
                    $this->createEmailParticipant($contact2),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame(Email::DIRECTION_OUTBOUND, $record['direction'], 'From user to contacts');

        $this->assertSame(
            static::$systemConfiguration->id,
            $record['outbound_email_id'],
            'Should use the system configuration'
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $contact1->getModuleName(),
                    'id' => $contact1->id,
                    'name' => $contact1->name,
                ],
                'parent_name' => $contact1->name,
                'email_addresses' => [
                    'id' => $contact1->emailAddress->getGuid($contact1->email1),
                    'email_address' => $contact1->email1,
                ],
                'email_address_id' => $contact1->emailAddress->getGuid($contact1->email1),
                'email_address' => $contact1->email1,
            ],
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $contact2->getModuleName(),
                    'id' => $contact2->id,
                    'name' => $contact2->name,
                ],
                'parent_name' => $contact2->name,
                'email_addresses' => [
                    'id' => $contact2->emailAddress->getGuid($contact2->email1),
                    'email_address' => $contact2->email1,
                ],
                'email_address_id' => $contact2->emailAddress->getGuid($contact2->email1),
                'email_address' => $contact2->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$overrideConfig->email_address . '>',
            $bean->from_addr_name
        );
        $sorted_to = ["{$contact1->name} <{$contact1->email1}>", "{$contact2->name} <{$contact2->email1}>"];
        sort($sorted_to);
        $to = implode(', ', $sorted_to);
        $this->assertEquals($to, $bean->to_addrs_names);
    }

    /**
     * When replying to an email, the reply_to_id is set on the new Email record being created. The reply_to_id must
     * refer to an existing Email Record in the 'Archived' state. If successfully sent, that Replied-To Email record's
     * reply_to status is set to true.
     *
     * @covers EmailsApi::createRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers EmailsApi::sendEmail
     * @covers Email::sendEmail
     */
    public function testCreateAndSendReplyEmail()
    {
        //we have put back the outbound configuration
        //here it's already altered by the previous tests
        static::$overrideConfig = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
        static::$userConfig = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );

        $emailValues = [
            'state' => Email::STATE_ARCHIVED,
            'reply_to_status' => false,
        ];
        $repliedToEmail = SugarTestEmailUtilities::createEmail('', $emailValues);

        $contact = SugarTestContactUtilities::createContact();
        $args = [
            'state' => Email::STATE_READY,
            'reply_to_id' => $repliedToEmail->id,
            'to' => [
                'create' => [
                    $this->createEmailParticipant($contact),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame($repliedToEmail->id, $record['reply_to_id'], 'Should contain id of Email being replied to');
        $this->assertSame(Email::DIRECTION_OUTBOUND, $record['direction'], 'From user to contact');

        $repliedToEmail = $repliedToEmail->retrieve($repliedToEmail->id);
        $this->assertEquals('1', $repliedToEmail->reply_to_status, 'reply_to_status value should be True');
    }

    /**
     * An email is inbound when it is sent by a non-employee.
     *
     * @covers EmailsApi::createRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateArchivedEmailSentByNonEmployee()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $args = [
            'state' => Email::STATE_ARCHIVED,
            'from' => [
                'create' => [
                    $this->createEmailParticipant($contact1),
                ],
            ],
            'to' => [
                'create' => [
                    $this->createEmailParticipant($user),
                ],
            ],
            'cc' => [
                'create' => [
                    $this->createEmailParticipant($contact2),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame(Email::DIRECTION_INBOUND, $record['direction'], 'From contact to user and contact');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $contact1->getModuleName(),
                    'id' => $contact1->id,
                    'name' => $contact1->name,
                ],
                'parent_name' => $contact1->name,
                'email_addresses' => [
                    'id' => $contact1->emailAddress->getGuid($contact1->email1),
                    'email_address' => $contact1->email1,
                ],
                'email_address_id' => $contact1->emailAddress->getGuid($contact1->email1),
                'email_address' => $contact1->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $user->getModuleName(),
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'parent_name' => $user->name,
                'email_addresses' => [
                    'id' => $user->emailAddress->getGuid($user->email1),
                    'email_address' => $user->email1,
                ],
                'email_address_id' => $user->emailAddress->getGuid($user->email1),
                'email_address' => $user->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc',
                'parent' => [
                    'type' => $contact2->getModuleName(),
                    'id' => $contact2->id,
                    'name' => $contact2->name,
                ],
                'parent_name' => $contact2->name,
                'email_addresses' => [
                    'id' => $contact2->emailAddress->getGuid($contact2->email1),
                    'email_address' => $contact2->email1,
                ],
                'email_address_id' => $contact2->emailAddress->getGuid($contact2->email1),
                'email_address' => $contact2->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc_collection');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals("{$contact1->name} <{$contact1->email1}>", $bean->from_addr_name);
        $this->assertEquals("{$user->name} <{$user->email1}>", $bean->to_addrs_names);
        $this->assertEquals("{$contact2->name} <{$contact2->email1}>", $bean->cc_addrs_names);
    }

    /**
     * An email is internal when it is sent by an employee to only employees.
     *
     * @covers EmailsApi::createRecord
     * @covers ModuleApi::getRelatedRecordArguments
     * @covers ModuleApi::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateArchivedEmailSentInternally()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user3 = SugarTestUserUtilities::createAnonymousUser();

        $args = [
            'state' => Email::STATE_ARCHIVED,
            'from' => [
                'create' => [
                    $this->createEmailParticipant($user1),
                ],
            ],
            'to' => [
                'create' => [
                    $this->createEmailParticipant($user2),
                ],
            ],
            'cc' => [
                'create' => [
                    $this->createEmailParticipant($user3),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame(Email::DIRECTION_INTERNAL, $record['direction'], 'From user to users');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from',
                'parent' => [
                    'type' => $user1->getModuleName(),
                    'id' => $user1->id,
                    'name' => $user1->name,
                ],
                'parent_name' => $user1->name,
                'email_addresses' => [
                    'id' => $user1->emailAddress->getGuid($user1->email1),
                    'email_address' => $user1->email1,
                ],
                'email_address_id' => $user1->emailAddress->getGuid($user1->email1),
                'email_address' => $user1->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from_collection');
        $this->assertRecords($expected, $collection, 'The sender did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to',
                'parent' => [
                    'type' => $user2->getModuleName(),
                    'id' => $user2->id,
                    'name' => $user2->name,
                ],
                'parent_name' => $user2->name,
                'email_addresses' => [
                    'id' => $user2->emailAddress->getGuid($user2->email1),
                    'email_address' => $user2->email1,
                ],
                'email_address_id' => $user2->emailAddress->getGuid($user2->email1),
                'email_address' => $user2->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to_collection');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc',
                'parent' => [
                    'type' => $user3->getModuleName(),
                    'id' => $user3->id,
                    'name' => $user3->name,
                ],
                'parent_name' => $user3->name,
                'email_addresses' => [
                    'id' => $user3->emailAddress->getGuid($user3->email1),
                    'email_address' => $user3->email1,
                ],
                'email_address_id' => $user3->emailAddress->getGuid($user3->email1),
                'email_address' => $user3->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc_collection');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals("{$user1->name} <{$user1->email1}>", $bean->from_addr_name);
        $this->assertEquals("{$user2->name} <{$user2->email1}>", $bean->to_addrs_names);
        $this->assertEquals("{$user3->name} <{$user3->email1}>", $bean->cc_addrs_names);
    }

    private function emailAddrsToArray($emailAddrs)
    {
        $emailAddresses = [];
        $temp = explode(', ', $emailAddrs);
        foreach ($temp as $emailAddr) {
            $emailAddresses[] = $emailAddr;
        }
        $emailAddresses = $this->sortEmailAddresses($emailAddresses);
        return $emailAddresses;
    }

    private function sortEmailAddresses($emailAddresses)
    {
        sort($emailAddresses);
        return $emailAddresses;
    }
}
