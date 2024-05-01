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

namespace Sugarcrm\SugarcrmTests\EventRepository;

use BeanFactory;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Sugarcrm\Sugarcrm\Audit\EventRepository;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Util\Uuid;
use SugarTestEmailAddressUtilities;
use SugarTestHelper;
use SugarTestUserUtilities;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\EventRepository
 */
class EventRepositoryTest extends TestCase
{
    /**
     * @var EventRepository
     */
    private $eventRepo;

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var Context
     */
    private $context;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $container = Container::getInstance();
        $this->context = $container->get(Context::class);

        $this->eventRepo = $container->get(EventRepository::class);
        $this->conn = $container->get(Connection::class);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    private function createContact($fields)
    {
        $contact = BeanFactory::newBean('Contacts');
        $contact->id = Uuid::uuid1();
        $contact->new_with_id = true;
        $contact->last_name = 'Test' . $contact->id;

        $emails = [];
        foreach ($fields as $field => $value) {
            if ($field !== 'email') {
                $contact->$field = $value;
            } else {
                $emails = $value;
            }
        }

        foreach ($emails as $email) {
            $emailAddr = SugarTestEmailAddressUtilities::createEmailAddress($email);
            SugarTestEmailAddressUtilities::addAddressToPerson(
                $contact,
                $emailAddr
            );
            $contact->emailAddress->addAddress($email, true, false, false, false, $emailAddr->id);
        }
        $contact->emailAddress->dontLegacySave = true;
        $contact->save();

        return $contact->retrieve();
    }

    private function updateContact($contact)
    {
        $contact->save(false);
        return $contact->retrieve();
    }

    /**
     * @test
     * @covers ::getLatestBeanEvents()
     */
    public function getLatestBeanEventsCheckSource()
    {
        $user1 = $GLOBALS['current_user'];
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $subject = new User($user1, new RestApiClient());
        $this->context->activateSubject($subject);
        $this->context->setAttribute('platform', 'base');

        $contactData = ['phone_mobile' => '(111) 111-1111'];
        $contact = $this->createContact($contactData);
        $this->context->deactivateSubject($subject);

        $actual = $this->eventRepo->getLatestBeanEvents($contact, ['phone_mobile']);

        $this->assertEquals($user1->id, $actual[0]['source']['subject']['id']);

        $subject = new User($user2, new RestApiClient());
        $this->context->activateSubject($subject);
        $this->context->setAttribute('platform', 'base');

        $contact->phone_mobile = '(111) 222-2222';
        $updContact = $this->updateContact($contact);

        $this->context->deactivateSubject($subject);

        $actual = $this->eventRepo->getLatestBeanEvents($updContact, ['phone_mobile']);

        $this->assertEquals($user2->id, $actual[0]['source']['subject']['id']);
    }

    /**
     * @test
     * @covers ::getLatestBeanEvents()
     */
    public function getLatestBeanEventsOnlyEmailFieldButNoEmailValue()
    {
        $contactData = [];
        $contact = $this->createContact($contactData);

        $actual = $this->eventRepo->getLatestBeanEvents($contact, ['email']);

        $this->assertEmpty($actual, 'Expected empty results.');
    }

    /**
     * @test
     * @covers ::getLatestBeanEvents()
     *
     * @dataProvider providerGetLatestBeanEvents
     */
    public function getLatestBeanEventsOneContactTwoFields($fields)
    {
        $contactData = ['phone_mobile' => '(222) 111-1111',
            'phone_home' => '(222) 222-2222',
            'email' => ['abc222@xyz.com', 'abc2221@xyz.com'],
        ];
        $contact = $this->createContact($contactData);

        $actual = $this->eventRepo->getLatestBeanEvents($contact, $fields);

        if (array_diff($fields, ['email'])) {
            $actualByFieldName = array_combine(array_column($actual, 'field_name'), $actual);
            $this->assertEquals(
                $contact->phone_mobile,
                $actualByFieldName['phone_mobile']['after_value_string'],
                'Expected phone_mobile value not returned.'
            );
            $this->assertEquals(
                $contact->phone_home,
                $actualByFieldName['phone_home']['after_value_string'],
                'Expected phone_home value not returned.'
            );
        }

        if (in_array('email', $fields)) {
            $actualByAfterValue = array_combine(array_column($actual, 'after_value_string'), $actual);

            $email1 = $actualByAfterValue[$contact->emailAddress->addresses[0]['email_address_id']];
            $this->assertEquals('email', $email1['field_name']);

            $email2 = $actualByAfterValue[$contact->emailAddress->addresses[1]['email_address_id']];
            $this->assertEquals('email', $email2['field_name']);
        }
    }

    /**
     * @test
     * @covers ::getLatestBeanEvents()
     *
     * @dataProvider providerGetLatestBeanEvents
     */
    public function getLatestBeanEventsOneContactTwoSaves($fields)
    {
        $contactData = ['phone_mobile' => '(333) 111-1111',
            'email' => ['abc333@xyz.com'],
        ];
        $contact = $this->createContact($contactData);

        $contact->phone_mobile = '(333) 222-2222';
        $updContact = $this->updateContact($contact);

        $actual = $this->eventRepo->getLatestBeanEvents($updContact, $fields);
        $actualByFieldName = array_combine(array_column($actual, 'field_name'), $actual);

        if (array_diff($fields, ['email'])) {
            $this->assertEquals(
                $contact->phone_mobile,
                $actualByFieldName['phone_mobile']['after_value_string'],
                'Expected phone_mobile value not returned.'
            );
        }

        if (in_array('email', $fields)) {
            $this->assertEquals(
                $contact->emailAddress->addresses[0]['email_address_id'],
                $actualByFieldName['email']['after_value_string'],
                'Email entry not found for contact1.'
            );
        }
    }

    /**
     * @test
     * @covers ::getLatestBeanEvents()
     *
     * @dataProvider providerGetLatestBeanEvents
     */
    public function getLatestBeanEventsTwoContacts($fields)
    {
        $contactData1 = ['phone_mobile' => '(444) 111-1111',
            'email' => ['abc444@xyz.com'],
        ];
        $contact1 = $this->createContact($contactData1);

        $contactData2 = ['phone_mobile' => '(555) 111-1111',
            'email' => ['abc555@xyz.com'],
        ];
        $contact2 = $this->createContact($contactData2);

        $actual1 = $this->eventRepo->getLatestBeanEvents($contact1, $fields);
        $actualByFieldName1 = array_combine(array_column($actual1, 'field_name'), $actual1);

        $actual2 = $this->eventRepo->getLatestBeanEvents($contact2, $fields);
        $actualByFieldName2 = array_combine(array_column($actual2, 'field_name'), $actual2);

        if (array_diff($fields, ['email'])) {
            $this->assertEquals(
                $contact1->phone_mobile,
                $actualByFieldName1['phone_mobile']['after_value_string'],
                'Expected phone_mobile value not returned for contact1.'
            );
            $this->assertEquals(
                $contact2->phone_mobile,
                $actualByFieldName2['phone_mobile']['after_value_string'],
                'Expected phone_mobile value not returned for contact2.'
            );
        }

        if (in_array('email', $fields)) {
            $this->assertEquals(
                $contact1->emailAddress->addresses[0]['email_address_id'],
                $actualByFieldName1['email']['after_value_string'],
                'Email entry not found for contact1.'
            );
            $this->assertEquals(
                $contact2->emailAddress->addresses[0]['email_address_id'],
                $actualByFieldName2['email']['after_value_string'],
                'Email entry not found for contact2.'
            );
        }
    }

    public function providerGetLatestBeanEvents()
    {
        return [
            'Retrieve fields without email' => [
                ['phone_mobile', 'phone_home'],
            ],
            'Retrieve only email field' => [
                ['email'],
            ],
            'Retrieve fields with email' => [
                ['phone_mobile', 'phone_home', 'email'],
            ],
        ];
    }

    /**
     * @test
     * @covers ::registerUpdate()
     */
    public function registerUpdate()
    {
        $contact = $this->createContact([]);
        $auditEventId = $this->eventRepo->registerUpdate($contact);

        $this->assertTrue($this->eventExists($auditEventId), 'Audit event not created.');
    }

    /**
     * @test
     * @covers ::registerErasure()
     */
    public function registerErasure()
    {
        $contact = $this->createContact([]);
        $auditEventId = $this->eventRepo->registerErasure($contact);

        $this->assertTrue($this->eventExists($auditEventId), 'Audit event not created.');
    }

    private function eventExists(string $id): bool
    {
        return $this->conn
                ->executeQuery('SELECT id FROM audit_events WHERE id = ?', [$id])
                ->fetchOne() === $id;
    }
}
