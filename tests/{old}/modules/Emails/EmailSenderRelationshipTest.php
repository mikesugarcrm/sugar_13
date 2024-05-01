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
 * @coversDefaultClass EmailSenderRelationship
 */
class EmailSenderRelationshipTest extends TestCase
{
    private $relationship;

    public static function setUpBeforeClass(): void
    {
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
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
        $this->relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
    }

    protected function tearDown(): void
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);
    }

    /**
     * Only the current user can be added as the sender of a draft.
     *
     * @covers ::add
     */
    public function testAdd_OnlyCurrentUserCanBeLinkedAsParent()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->add($email, $this->createEmailParticipant($contact));
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
     * The sender's email address can be changed for the email. The email address is derived from the outbound email
     * configuration.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsUpdatedWithEmailAddress()
    {
        // Create an email without an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );

        // Specify an outbound email configuration for the draft.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $oe = BeanFactory::newBean('OutboundEmail');
        $oe->email_address_id = $address->id;
        BeanFactory::registerBean($oe);
        $email->outbound_email_id = $oe->id;

        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertTrue($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ChangeEmailAddressForParticipant()
    {
        // Create an email with an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $oe1 = BeanFactory::newBean('OutboundEmail');
        $oe1->email_address_id = $address1->id;
        BeanFactory::registerBean($oe1);
        $email->outbound_email_id = $oe1->id;

        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertTrue($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address1->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address1->email_address}>", $email->from_addr_name);

        // Change the outbound email configuration to one with a different email address.
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $oe2 = BeanFactory::newBean('OutboundEmail');
        $oe2->email_address_id = $address2->id;
        BeanFactory::registerBean($oe2);
        $email->outbound_email_id = $oe2->id;

        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertTrue($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address2->email_address}>", $email->from_addr_name);
    }

    /**
     * The `email_address_id` field is emptied if the same person record is linked without an email address. This allows
     * the email address to be removed if a draft is saved without an outbound email configuration after previously
     * having one.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIsRemoved()
    {
        // Create an email with an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $oe = BeanFactory::newBean('OutboundEmail');
        $oe->email_address_id = $address->id;
        BeanFactory::registerBean($oe);
        $email->outbound_email_id = $oe->id;

        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertTrue($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);

        // Remove the outbound email configuration from the email.
        $email->outbound_email_id = null;

        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertTrue($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );
    }

    /**
     * It's a noop when adding the same sender without any change to the email address.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     */
    public function testAdd_DuplicatesWithoutAnEmailAddressAreIgnored()
    {
        // Create an email without an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );

        // Add the current user as the sender again.
        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertFalse($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );
    }

    /**
     * It's a noop when adding the same sender without any change to the email address.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     */
    public function testAdd_DuplicatesWithAnEmailAddressAreIgnored()
    {
        // Create an email with an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $oe = BeanFactory::newBean('OutboundEmail');
        $oe->email_address_id = $address->id;
        BeanFactory::registerBean($oe);
        $email->outbound_email_id = $oe->id;

        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertTrue($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);

        // Add the current user as the sender again.
        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address));
        $this->assertFalse($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address must come from the draft's outbound email configuration.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     */
    public function testAdd_TheEmailAddressMustComeFromTheConfiguration()
    {
        // Create an email without an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );

        // Add the current user as the sender again, but with an email address.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address));
    }

    /**
     * The sender's email address must come from the draft's outbound email configuration. It cannot be overridden.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     */
    public function testAdd_CannotOverrideTheEmailAddressOfTheConfiguration()
    {
        // Create an email with an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $oe = BeanFactory::newBean('OutboundEmail');
        $oe->email_address_id = $address1->id;
        BeanFactory::registerBean($oe);
        $email->outbound_email_id = $oe->id;

        $result = $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertTrue($result);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address1->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address1->email_address}>", $email->from_addr_name);

        // Add the current user as the sender again, but with a different email address.
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address2));
    }

    /**
     * The sender's email address must come from the draft's outbound email configuration. When the configuration cannot
     * be loaded, the specified email address cannot be verified to match the configuration's email address.
     *
     * @covers ::add
     * @covers ::setEmailAddress
     */
    public function testAdd_FailedToLoadTheConfiguration()
    {
        // Create an email without an outbound email configuration.
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );

        // Add the current user as the sender again, but with an email address. The specified configuration doesn't
        // exist.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $email->outbound_email_id = Uuid::uuid1();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address));
    }

    /**
     * An email address can be linked to an archived email without a person record.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsSavedWithoutParent()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $this->relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertTrue($result);

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals($address->email_address, $email->from_addr_name);
    }

    /**
     * An email address can be linked to an archived email with a person record.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsSavedWithParent()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The email_address_id column is set to the sender's primary email address if the email is not a draft and the
     * email address was not chosen when the sender was added. This comes up when a record -- Accounts, Contacts, Leads,
     * etc. -- is added with the intention of using that record's primary email address and the email is immediately
     * being archived.
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

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->from_addr_name);
    }

    /**
     * The `parent_type` and `parent_id` fields are patched when a bean is linked with an email address that is already
     * linked as a plain email address. This only works because all of the calls to
     * {@link EmailSenderRelationship::add()} are made before the archived email is saved.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ParticipantIsUpdatedWithParent()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);
        $this->relationship->add($email, $this->createEmailParticipant(null, $address));

        $result = $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * There can only be one sender.
     *
     * @covers ::add
     * @covers ::remove
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_OnlyOneSenderCanExistPerEmail()
    {
        $email = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), ['state' => Email::STATE_ARCHIVED], false);

        $contact1 = SugarTestContactUtilities::createContact();
        $result = $this->relationship->add($email, $this->createEmailParticipant($contact1));
        $this->assertTrue($result, 'The contact should have been added without an email address');

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $contact2 = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant($contact2, $address1));
        $this->assertTrue($result, 'A different contact should have been added with an email address');

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $contact3 = SugarTestContactUtilities::createContact();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant($contact3, $address2));
        $this->assertTrue($result, 'A different contact should have been added with a different email address');

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $lead1 = SugarTestLeadUtilities::createLead();
        $result = $this->relationship->add($email, $this->createEmailParticipant($lead1));
        $this->assertTrue($result, 'The lead should have been added without an email address');

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $lead2 = SugarTestLeadUtilities::createLead();
        $result = $this->relationship->add($email, $this->createEmailParticipant($lead2, $address1));
        $this->assertTrue($result, 'The lead should have been added with an email address');

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $address3 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant(null, $address3));
        $this->assertTrue($result, 'The plain email address should have been added');

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $address4 = SugarTestEmailAddressUtilities::createEmailAddress();
        $result = $this->relationship->add($email, $this->createEmailParticipant(null, $address4));
        $this->assertTrue($result, 'Another plain email address should have been added');

        // Save the archived email now that the relationships have been saved. This is how the REST API operates.
        $email->save();

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address4->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals($address4->email_address, $email->from_addr_name);
    }

    /**
     * The sender of an archived email cannot change.
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
     * The sender of an archived email cannot change.
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

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->add($email, $this->createEmailParticipant($contact, $address));
    }

    /**
     * The sender of an archived email cannot be unlinked.
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

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->remove($email, $ep);
    }

    /**
     * The sender of a draft cannot be unlinked.
     *
     * @covers ::remove
     */
    public function testRemove_CannotUnlinkSenderOfDraft()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->remove($email, $bean);
    }

    /**
     * The sender of a draft can be unlinked if the email is being deleted.
     *
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testRemove_CanUnlinkSenderOfDraftWhenEmailIsBeingDeleted()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        $beans = $email->from->getBeans();
        $this->assertCount(1, $beans);

        // Act as if the email is being deleted.
        $email->deleted = 1;

        $bean = array_shift($beans);
        $result = $this->relationship->remove($email, $bean);
        $this->assertTrue($result);
        $this->assertEquals(1, $bean->deleted);

        $beans = $email->from->getBeans();
        $this->assertCount(0, $beans);

        $email->retrieveEmailText();
        $this->assertEmpty($email->from_addr_name);
    }

    /**
     * The sender of an archived email can be unlinked if the email is being deleted.
     *
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testRemove_CanUnlinkSenderOfArchivedEmailWhenEmailIsBeingDeleted()
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

        $beans = $email->from->getBeans();
        $this->assertCount(0, $beans);

        $email->retrieveEmailText();
        $this->assertEmpty($email->from_addr_name);
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
}
