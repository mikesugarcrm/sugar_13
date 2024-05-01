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

namespace Sugarcrm\SugarcrmTests\Portal;

use BeanFactory;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;
use SugarTestHelper;

/**
 * @covers \Sugarcrm\Sugarcrm\Portal\Session::getAccountIds
 */
class SessionTest extends TestCase
{
    public static $records;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]); // admin
    }

    public static function tearDownAfterClass(): void
    {
        unset($_SESSION['contact_id']);

        // delete all the created records
        foreach (self::$records as $table => $ids) {
            foreach ($ids as $id) {
                $qb = \DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
                $qb->delete($table)->where($qb->expr()->eq('id', $qb->createPositionalParameter($id)))->execute();
            }
        }

        SugarTestHelper::tearDown();
    }

    public function testAccountIds(): void
    {
        // test contact with accounts
        $portalSession = PortalFactory::getInstance('Session');
        $portalSession->unsetCache();

        $a = BeanFactory::newBean('Accounts');
        $a->name = 'testAccountIds';
        $a->save();
        $accountId = $a->id;
        self::$records['accounts'][] = $accountId;

        $c = BeanFactory::newBean('Contacts');
        $c->last_name = 'testAccontIds1';
        $c->account_id = $accountId;
        $c->save();
        $contactId = $c->id;
        self::$records['contacts'][] = $contactId;

        $_SESSION['contact_id'] = $contactId;

        $accountIds = $portalSession->getAccountIds();

        $this->assertNotEmpty($accountIds);
        $this->assertIsArray($accountIds);
        $this->assertCount(1, $accountIds);
        $this->assertContains($accountId, $accountIds);

        // test contact with deleted account
        $a->mark_deleted($accountId);
        $portalSession->unsetCache();

        $accountIds = $portalSession->getAccountIds();
        $this->assertEmpty($accountIds);

        // test contact without accounts
        $portalSession->unsetCache();

        $c = BeanFactory::newBean('Contacts');
        $c->last_name = 'testAccontIds2';
        $c->save();
        $contactId = $c->id;
        self::$records['contacts'][] = $contactId;

        $_SESSION['contact_id'] = $contactId;

        $accountIds = $portalSession->getAccountIds();

        $this->assertEmpty($accountIds);

        unset($_SESSION['contact_id']);
    }

    public function testContactId(): void
    {
        $portalSession = PortalFactory::getInstance('Session');
        $portalSession->unsetCache();

        // load empty contact id
        $retrievedContactId = $portalSession->getContactId();
        $this->assertEmpty($retrievedContactId);

        // now the contact won't stick as the previous one is cached
        $firstContactId = 'first_contact_id';
        $_SESSION['contact_id'] = $firstContactId;
        $retrievedContactId = $portalSession->getContactId();
        $this->assertEmpty($retrievedContactId);

        // now we clear the cache and see that it sticks
        $portalSession->unsetCache();
        $retrievedContactId = $portalSession->getContactId();
        $this->assertNotEmpty($retrievedContactId);
        $this->assertEquals($retrievedContactId, $firstContactId);

        // now we change contact id and see that it does not stick
        $secondContactId = 'second_contact_id';
        $_SESSION['contact_id'] = $secondContactId;
        $retrievedContactId = $portalSession->getContactId();
        $this->assertNotEmpty($retrievedContactId);
        $this->assertEquals($retrievedContactId, $firstContactId);

        // now we use the setContactId to overwrite the currently cached contact id
        $portalSession->setContactId($secondContactId);
        $retrievedContactId = $portalSession->getContactId();
        $this->assertNotEmpty($retrievedContactId);
        $this->assertEquals($retrievedContactId, $secondContactId);

        unset($_SESSION['contact_id']);
    }

    public function testContact(): void
    {
        $portalSession = PortalFactory::getInstance('Session');
        $portalSession->unsetCache();

        // load empty contact
        $retrievedContact = $portalSession->getContact();
        $this->assertEmpty($retrievedContact);

        // create new contact
        $c = BeanFactory::newBean('Contacts');
        $c->last_name = 'testContact1';
        $c->save();
        $firstContactId = $c->id;
        self::$records['contacts'][] = $firstContactId;

        // now the contact won't stick as the previous one is cached
        $_SESSION['contact_id'] = $firstContactId;
        $retrievedContact = $portalSession->getContact();
        $this->assertEmpty($retrievedContact);

        // now we clear the cache and see that it sticks
        $portalSession->unsetCache();
        $retrievedContact = $portalSession->getContact();
        $this->assertNotEmpty($retrievedContact);
        $this->assertEquals($retrievedContact->id, $firstContactId);

        // now we change contact and see that it does not stick
        $c = BeanFactory::newBean('Contacts');
        $c->last_name = 'testContact2';
        $c->save();
        $secondContactId = $c->id;
        self::$records['contacts'][] = $secondContactId;

        $_SESSION['contact_id'] = $secondContactId;
        $retrievedContact = $portalSession->getContact();
        $this->assertEquals($retrievedContact->id, $firstContactId);

        // now we use the setContactId to overwrite the currently cached contact id and contact
        $portalSession->setContactId($secondContactId);
        $retrievedContact = $portalSession->getContact();
        $this->assertNotEmpty($retrievedContact);
        $this->assertEquals($retrievedContact->id, $secondContactId);

        unset($_SESSION['contact_id']);
    }
}
