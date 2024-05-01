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
 * @coversDefaultClass EmailParticipant
 */
class EmailParticipantTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
    }

    public function saveEmailTextProvider()
    {
        return [
            'do_not_save_email_text' => [
                false,
                0,
            ],
            'save_email_text' => [
                true,
                1,
            ],
        ];
    }

    /**
     * @dataProvider saveEmailTextProvider
     * @covers ::save
     */
    public function testSave($isUpdate, $callCount)
    {
        $email = $this->createPartialMock('Email', ['isUpdate', 'saveEmailText']);
        $email->method('isUpdate')->willReturn($isUpdate);
        $email->expects($this->exactly($callCount))->method('saveEmailText');
        $email->id = Uuid::uuid1();
        BeanFactory::registerBean($email);
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->email_id = $email->id;
        $ep->save();

        BeanFactory::unregisterBean($email);
    }

    /**
     * @covers ::mark_deleted
     * @covers ::mark_relationships_deleted
     * @covers ::delete_linked
     * @covers Link2::delete
     * @covers EmailRecipientRelationship::removeAll
     * @covers EmailRecipientRelationship::remove
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testMarkDeleted()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $email->load_relationship('to');

        $contact = SugarTestContactUtilities::createContact();

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        $ep->parent_type = $contact->getModuleName();
        $ep->parent_id = $contact->id;
        BeanFactory::registerBean($ep);

        $email->to->add($ep);
        SugarRelationship::resaveRelatedBeans();

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);

        $ep->mark_deleted($ep->id);

        $email->retrieveEmailText();
        $this->assertEmpty($email->to_addrs_names);
    }

    public function checkIfEmployeeProvider()
    {
        return [
            'parent_type_is_contacts' => [
                'SugarTestContactUtilities',
                'createContact',
                'Contacts',
                false,
            ],
            'parent_type_is_users' => [
                'SugarTestUserUtilities',
                'createAnonymousUser',
                'Users',
                true,
            ],
            'parent_type_is_employees' => [
                'SugarTestUserUtilities',
                'createAnonymousUser',
                'Employees',
                true,
            ],
        ];
    }

    /**
     * @dataProvider checkIfEmployeeProvider
     * @covers ::isAnEmployee
     */
    public function testIsAnEmployee_WithParent($beanCreateClass, $beanCreateMethod, $module, $expected)
    {
        $bean = call_user_func([$beanCreateClass, $beanCreateMethod]);
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $email->load_relationship('to');

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        $ep->parent_type = $module;
        $ep->parent_id = $bean->id;
        $ep->email_address_id = $bean->emailAddress->addresses[0]['email_address_id'];

        BeanFactory::registerBean($ep);
        $email->to->add($ep);
        $email->state = Email::STATE_ARCHIVED;
        $email->save();

        $actual = $ep->isAnEmployee();

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::isAnEmployee
     * @covers SugarEmailAddress::getEmployeesWithEmailAddress
     */
    public function testIsAnEmployee_WithoutParent_EmailAddressBelongsToAnEmployee()
    {
        $bean = SugarTestUserUtilities::createAnonymousUser();
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $email->load_relationship('to');

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        $ep->email_address_id = $bean->emailAddress->addresses[0]['email_address_id'];

        BeanFactory::registerBean($ep);
        $email->to->add($ep);
        $email->state = Email::STATE_ARCHIVED;
        $email->save();

        $actual = $ep->isAnEmployee();

        $this->assertTrue($actual);
    }

    /**
     * @covers ::isAnEmployee
     * @covers SugarEmailAddress::getEmployeesWithEmailAddress
     */
    public function testIsAnEmployee_WithoutParent_EmailAddressBelongsToSnipUser()
    {
        $user = SugarSNIP::getInstance()->getSnipUser();

        // Link an email address to the user.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($user, $address);

        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $email->load_relationship('to');

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        $ep->email_address_id = $address->id;

        BeanFactory::registerBean($ep);
        $email->to->add($ep);
        $email->state = Email::STATE_ARCHIVED;
        $email->save();

        $actual = $ep->isAnEmployee();

        $this->assertTrue($actual);
    }


    /**
     * @covers ::isAnEmployee
     * @covers SugarEmailAddress::getEmployeesWithEmailAddress
     */
    public function testIsAnEmployee_WithoutParent_EmailAddressBelongsToPortalAdmin()
    {
        $portal = new ParserModifyPortalConfig();
        $user = $portal->getPortalUser();

        // Link an email address to the user.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($user, $address);

        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $email->load_relationship('to');

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        $ep->email_address_id = $address->id;

        BeanFactory::registerBean($ep);
        $email->to->add($ep);
        $email->state = Email::STATE_ARCHIVED;
        $email->save();

        $actual = $ep->isAnEmployee();

        // These Special Users are treated as Regular Users
        $this->assertTrue($actual);
    }

    /**
     * @covers ::isAnEmployee
     * @covers SugarEmailAddress::getEmployeesWithEmailAddress
     */
    public function testIsAnEmployee_WithoutParent_EmailAddressDoesNotBelongToAnEmployee()
    {
        $bean = SugarTestContactUtilities::createContact();
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $email->load_relationship('to');

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        $ep->email_address_id = $bean->emailAddress->addresses[0]['email_address_id'];

        BeanFactory::registerBean($ep);
        $email->to->add($ep);
        $email->state = Email::STATE_ARCHIVED;
        $email->save();

        $result = $ep->isAnEmployee();

        $this->assertFalse($result, 'isAnEmployee: NonEmployee tested true');
    }
}
