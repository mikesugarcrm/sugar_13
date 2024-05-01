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
 * @coversDefaultClass EmailRecipientRelationship
 */
class EmailRecipientRelationshipTest extends TestCase
{
    private $relationship;

    public static function setUpBeforeClass(): void
    {
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
    }

    protected function tearDown(): void
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);
    }

    /**
     * An email address can be linked without a person record.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsLinkedWithoutParent()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $this->relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals($address->email_address, $email->to_addrs_names);
    }

    /**
     * The value of email_address_id is always used when it is set.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsLinkedWithParentAndEmailAddress()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column can be left empty if the email is a draft and the email address was not chosen when
     * the recipient was added. This comes up when a record -- Accounts, Contacts, Leads, etc. -- is added with the
     * intention of using that record's primary email address at send-time.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsLinkedWithoutAnEmailAddress()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column is set to the recipient's primary email address if the email is not a draft and the
     * email address was not chosen when the recipient was added. This comes up when a record -- Accounts, Contacts,
     * Leads, etc. -- is added with the intention of using that record's primary email address and the email is
     * immediately being archived.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsLinkedWithPrimaryEmailAddress()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);
        $contact = SugarTestContactUtilities::createContact();
        $primaryId = $contact->emailAddress->getGuid($contact->email1);

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column can be emptied if the email is a draft. This comes up when a record -- Accounts,
     * Contacts, Leads, etc. -- is added but the email address on record is incorrect and the intention is to repair the
     * data while leaving the reference to the person record intact.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIsRemoved()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $ep = $this->createEmailParticipant($contact, $address);
        $this->relationship->add($email, $ep);

        // The original object must be used to try to remove the email address from the relationship.
        $ep->email_address_id = '';
        $result = $this->relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * A record -- Accounts, Contacts, Leads, etc. -- can be added as a recipient with an email_address_id where that
     * email address is not linked to the record. When this happens, the email address should be linked to the record.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIsLinkedToTheContact()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);

        $addresses = $contact->emailAddress->getAddressesByGUID($contact->id, 'Contacts');
        $addresses = array_filter($addresses, function ($addr) use ($address) {
            return $addr['email_address'] === $address->email_address;
        });
        $this->assertCount(1, $addresses, "The contact should be linked to {$address->email_address}");
    }

    /**
     * The `parent_type` and `parent_id` fields are patched when a bean is linked with an email address that is already
     * linked as a plain email address.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsUpdatedWithParent()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();
        $this->relationship->add($email, $this->createEmailParticipant(null, $address));

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The `parent_type` and `parent_id` fields are patched when a bean is linked with an email address that is already
     * linked as a plain email address.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParentIsAddedToExistingParticipant()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();

        $ep = $this->createEmailParticipant(null, $address);
        $this->relationship->add($email, $ep);

        $ep->parent_type = $contact->getModuleName();
        $ep->parent_id = $contact->id;
        $result = $this->relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The `parent_type` and `parent_id` fields are preserved if an attempt is made to link an email address when that
     * email address is already linked along with a bean.
     *
     * @covers ::add
     * @covers Email::retrieveEmailText
     */
    public function testAdd_NoChangeWhenEmailAddressIsAlreadyLinkedToParticipantWithParent()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $this->relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $this->relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertFalse($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * Each role can have multiple recipients. The same email address can be used by more than one bean within the same
     * role.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_RolesCanHaveMultipleParticipants()
    {
        $expectedResults = [];
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $contact1 = SugarTestContactUtilities::createContact();
        $result = $this->relationship->add($email, $this->createEmailParticipant($contact1));
        $this->assertTrue($result, 'The contact should have been added without an email address');
        $participantKey = 'Contacts:' . $contact1->id;
        $expectedResults[$participantKey] = '';

        $contact2 = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant($contact2, $address1));
        $this->assertTrue($result, 'A different contact should have been added with an email address');
        $participantKey = 'Contacts:' . $contact2->id;
        $expectedResults[$participantKey] = $address1->id;

        $contact3 = SugarTestContactUtilities::createContact();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant($contact3, $address2));
        $this->assertTrue($result, 'A different contact should have been added with a different email address');
        $participantKey = 'Contacts:' . $contact3->id;
        $expectedResults[$participantKey] = $address2->id;

        $lead1 = SugarTestLeadUtilities::createLead();
        $result = $this->relationship->add($email, $this->createEmailParticipant($lead1));
        $this->assertTrue($result, 'The lead should have been added without an email address');
        $participantKey = 'Leads:' . $lead1->id;
        $expectedResults[$participantKey] = '';

        $lead2 = SugarTestLeadUtilities::createLead();
        $result = $this->relationship->add($email, $this->createEmailParticipant($lead2, $address1));
        $this->assertTrue($result, 'The lead should have been added even with a matching email address');
        $participantKey = 'Leads:' . $lead2->id;
        $expectedResults[$participantKey] = $address1->id;

        $address3 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant(null, $address3));
        $this->assertTrue($result, 'The plain email address should have been added');
        $participantKey = 'Email-Address:' . $address3->id;
        $expectedResults[$participantKey] = '';

        $address4 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant(null, $address4));
        $this->assertTrue($result, 'Another plain email address should have been added');
        $participantKey = 'Email-Address:' . $address4->id;
        $expectedResults[$participantKey] = '';

        $beans = $email->to->getBeans();
        $this->assertCount(7, $beans);

        foreach ($beans as $bean) {
            $emailAddressId = empty($bean->email_address_id) ? '' : $bean->email_address_id;
            if (!empty($bean->parent_type)) {
                $participant = $bean->parent_type . ':' . $bean->parent_id;
                $this->assertArrayHasKey($participant, $expectedResults);
                $this->assertSame($expectedResults[$participant], $emailAddressId);
            } else {
                $this->assertEmpty($bean->parent_type);
                $this->assertEmpty($bean->parent_id);
                $key = 'Email-Address:' . $emailAddressId;
                $this->assertArrayHasKey($key, $expectedResults);
            }
        }

        $email->retrieveEmailText();
        $expected = "{$contact1->name} <{$contact1->email1}>, {$contact2->name} <{$address1->email_address}>, " .
            "{$contact3->name} <{$address2->email_address}>, {$lead1->name} <{$lead1->email1}>, " .
            "{$lead2->name} <{$address1->email_address}>, {$address3->email_address}, {$address4->email_address}";
        $this->assertEquals($this->explodeRecipients($expected), $this->explodeRecipients($email->to_addrs_names));
    }

    /**
     * Adding an exact duplicate will result in no change.
     *
     * @covers ::add
     */
    public function testAdd_DuplicatesAreIgnored()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $this->relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertFalse($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * A person record's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ChangeEmailAddressByAddingSameContactWithDifferentEmailAddress()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $this->relationship->add($email, $this->createEmailParticipant($contact, $address1));

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact, $address2));
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->to_addrs_names);
    }

    /**
     * A person record's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ChangeEmailAddressByUpdatingExistingParticipantWithDifferentEmailAddress()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();

        $ep = $this->createEmailParticipant($contact, $address1);
        $this->relationship->add($email, $ep);

        $ep->email_address_id = $address2->id;
        $result = $this->relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->to_addrs_names);
    }

    /**
     * The `email_address_id` field is preserved if an attempt is made to link the same person record without an email
     * address when that person record is already linked along with an email address.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIsNotRemoved()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $this->relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertFalse($result);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * An Employees record should be seen as a Users record if the employee is a user.
     *
     * @covers ::add
     * @covers ::fixParentModule
     */
    public function testAdd_EmployeeChangesToUser()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $ep = $this->createEmailParticipant($GLOBALS['current_user']);
        $ep->parent_type = 'Employees';
        $this->relationship->add($email, $ep);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
    }

    /**
     * An Employees record should be seen as an Employees record if the employee is not a user.
     *
     * @covers ::add
     * @covers ::fixParentModule
     */
    public function testAdd_EmployeeDoesNotChangeToUser()
    {
        $employee = SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'user_name' => '',
            'user_hash' => '',
        ]);

        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $ep = $this->createEmailParticipant($employee);
        $ep->parent_type = 'Employees';
        $this->relationship->add($email, $ep);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Employees', $bean->parent_type);
        $this->assertSame($employee->id, $bean->parent_id);
    }

    /**
     * Only modules that use the email_address template can be used as parents of an EmailParticipants bean.
     *
     * @covers ::add
     * @covers ::assertParentModule
     */
    public function testAdd_CannotLinkParticipantWhoseParentModuleDoesNotUseTheEmailAddressTemplate()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $task = SugarTestTaskUtilities::createTask();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->add($email, $this->createEmailParticipant($task));
    }

    /**
     * The participants of an archived email cannot change.
     *
     * @covers ::add
     */
    public function testAdd_CannotLinkParticipantAfterEmailIsArchived()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->add($email, $this->createEmailParticipant($contact));
    }

    /**
     * The participants of an archived email cannot change.
     *
     * @covers ::add
     */
    public function testAdd_EmailAddressForParticipantCannotChangeAfterEmailIsArchived()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
    }

    /**
     * Unlinking a person record deletes the EmailParticipants record and updates emails_text for the email.
     *
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testRemove_ParticipantWithParentIsRemoved()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $ep = $this->createEmailParticipant($contact);
        $this->relationship->add($email, $ep);

        $result = $this->relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to->getBeans();
        $this->assertCount(0, $beans);

        $email->retrieveEmailText();
        $this->assertEmpty($email->to_addrs_names);
    }

    /**
     * Unlinking an email address record deletes the EmailParticipants record and updates emails_text for the email.
     *
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testRemove_ParticipantWithoutParentIsRemoved()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $ep = $this->createEmailParticipant(null, $address);
        $this->relationship->add($email, $ep);

        $result = $this->relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to->getBeans();
        $this->assertCount(0, $beans);

        $email->retrieveEmailText();
        $this->assertEmpty($email->to_addrs_names);
    }

    /**
     * There is no need to update emails_text for the email since the email has not been saved yet.
     *
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     */
    public function testRemove_ParticipantWithParentIsRemovedFromEmailThatHasNotYetBeenSaved()
    {
        $email = $this->getMockBuilder('Email')->setMethods(['saveEmailText'])->getMock();
        $email->new_with_id = true;
        $email->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $email->state = Email::STATE_DRAFT;
        $email->expects($this->never())->method('saveEmailText');
        // Even though the email is never saved, we want to be certain that all of its relationships are deleted after
        // the test.
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $email->load_relationship('to');
        $contact = SugarTestContactUtilities::createContact();
        $ep = $this->createEmailParticipant($contact);
        $this->relationship->add($email, $ep);

        $result = $this->relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to->getBeans();
        $this->assertCount(0, $beans);
    }

    /**
     * There is no need to update emails_text for the email since the email has not been saved yet.
     *
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     */
    public function testRemove_ParticipantWithoutParentIsRemovedFromEmailThatHasNotYetBeenSaved()
    {
        $email = $this->getMockBuilder('Email')->setMethods(['saveEmailText'])->getMock();
        $email->new_with_id = true;
        $email->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $email->state = Email::STATE_DRAFT;
        $email->expects($this->never())->method('saveEmailText');
        // Even though the email is never saved, we want to be certain that all of its relationships are deleted after
        // the test.
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $email->load_relationship('to');
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $ep = $this->createEmailParticipant(null, $address);
        $this->relationship->add($email, $ep);

        $result = $this->relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to->getBeans();
        $this->assertCount(0, $beans);
    }

    /**
     * The participants of an archived email cannot be unlinked.
     *
     * @covers ::remove
     */
    public function testRemove_CannotUnlinkParticipantAfterEmailIsArchived()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);
        $contact = SugarTestContactUtilities::createContact();
        $ep = $this->createEmailParticipant($contact);

        $result = $this->relationship->add($email, $ep);
        $this->assertTrue($result);

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->remove($email, $ep);
    }

    /**
     * The participants of an archived email can be unlinked if the email is being deleted.
     *
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testRemove_CanUnlinkParticipantOfArchivedEmailWhenEmailIsBeingDeleted()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);
        $contact = SugarTestContactUtilities::createContact();
        $ep = $this->createEmailParticipant($contact);
        $this->relationship->add($email, $ep);

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        // Act as if the email is being deleted.
        $email->deleted = 1;

        $result = $this->relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to->getBeans();
        $this->assertCount(0, $beans);

        $email->retrieveEmailText();
        $this->assertEmpty($email->to_addrs_names);
    }

    /**
     * After a parent bean is deleted, the EmailParticipants' `parent_type` and `parent_id` fields won't be able to
     * retrieve the bean and resolve the `parent_name`. The email address is still valid, so the EmailParticipants row
     * remains as if it only contains a plain email address. Should the bean be restored, then the `parent_name` will be
     * able to be used again.
     *
     * @covers SugarBean::mark_deleted
     */
    public function testEmailAddressIsStillPresentIfParentIsDeleted()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
        $contact->mark_deleted($contact->id);

        $beans = $email->to->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        // The parent fields remain, but the parent can't be loaded anymore.
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        // Since the parent_name field can't be resolved, only the email address should appear. However,
        // Email::saveEmailText isn't called after deleting the contact. If the email is a draft, then it will get
        // called eventually when updating or sending the email. If the email is archived, then it will never get
        // called unless a user updates a field that can be changed for archived emails. We don't use the fields from
        // emails_text in any OOTB UIs, so this is not critical. The emails_text data is not a source of truth for the
        // sender or recipients of an email. The worst case scenario is that it becomes out of date, but it is
        // correctable by simply saving the email.
        //FIXME: MAR-4668 Resave an email's emails_text data after any of its sender/recipient's parents are deleted.
        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$address->email_address}>",
            $email->to_addrs_names,
            "The contact's name should still be used in emails_text"
        );
    }

    /**
     * Activity streams entries should not be logged when linking or unlinking a recipient.
     *
     * @covers ::add
     * @covers ::remove
     */
    public function testAddAndRemove_ActivityStreamsShouldBeDisabled()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);
        $ep = $this->createEmailParticipant($contact, $address);

        Activity::enable();
        $this->relationship->add($email, $ep);
        $this->relationship->remove($email, $ep);
        Activity::restoreToPreviousState();

        $seed = BeanFactory::newBean('Activities');
        $q = new SugarQuery();
        $q->from($seed);
        $q->select('id');
        $q->where()
            ->equals('parent_type', 'Emails')
            ->equals('parent_id', $email->id);
        $rows = $q->execute();

        $this->assertCount(0, $rows);

        $q = new SugarQuery();
        $q->from($seed);
        $q->select('id');
        $q->where()
            ->equals('parent_type', 'EmailParticipants')
            ->equals('parent_id', $ep->id);
        $rows = $q->execute();

        $this->assertCount(0, $rows);
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
        $ep->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
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

    /**
     * Splits the comma-delimited recipients string and returns an array of recipients.
     *
     * @param string $recipients
     * @return array
     */
    private function explodeRecipients($recipients)
    {
        $recipientArray = [];
        $exploded = explode(',', $recipients);

        foreach ($exploded as $recipient) {
            $recipientArray[] = trim($recipient);
        }

        sort($recipientArray);

        return $recipientArray;
    }
}
