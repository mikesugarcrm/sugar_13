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

namespace Sugarcrm\SugarcrmTestsUnit\src\Portal;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Portal\Session;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Psr\SimpleCache\CacheInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Portal\Session
 */
class SessionTest extends TestCase
{
    protected static $ps;

    public static function setUpBeforeClass(): void
    {
        self::$ps = new Session();
    }

    public static function tearDownAfterClass(): void
    {
        unset($_SESSION['contact_id']);
        unset($_SESSION['type']);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Portal\Session::getContactCacheKey
     */
    public function testGetContactCacheKey(): void
    {
        $this->assertEquals('portal_accounts_my-contact-id', TestReflection::callProtectedMethod(self::$ps, 'getContactCacheKey', ['my-contact-id']));
        $this->assertEquals('portal_accounts_my-contact-id', TestReflection::callProtectedMethod(self::$ps, 'getContactCacheKey', [' my-con tact-id']));
        $guid = create_guid();
        $this->assertEquals('portal_accounts_' . $guid, TestReflection::callProtectedMethod(self::$ps, 'getContactCacheKey', [$guid]));
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Portal\Session::isActive
     */
    public function testIsActive(): void
    {
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Portal\Session::class, self::$ps);

        $_SESSION['type'] = 'support_portal';
        $portalSession = self::$ps->isActive();
        $this->assertTrue($portalSession);

        $_SESSION['type'] = 'non_valid_value';
        $portalSession = self::$ps->isActive();
        $this->assertFalse($portalSession);

        unset($_SESSION['type']);
        $portalSession = self::$ps->isActive();
        $this->assertFalse($portalSession);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Portal\Session::getContactId
     * @covers Sugarcrm\Sugarcrm\Portal\Session::setContactId
     */
    public function testContactId(): void
    {
        $mockCache = $this->createMock(CacheInterface::class);
        $mockSession = $this->createPartialMock(Session::class, ['getCacheObject']);
        $mockSession->method('getCacheObject')
            ->willReturn($mockCache);

        $testId1 = 'test_contact_id_1';
        $_SESSION['contact_id'] = $testId1;
        $contactId = $mockSession->getContactId();
        $this->assertEquals($testId1, $contactId);

        $testId2 = 'test_contact_id_2';
        $mockSession->setContactId($testId2);
        $contactId = $mockSession->getContactId();
        $this->assertEquals($testId2, $contactId);

        unset($_SESSION['contact_id']);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Portal\Session::getContact
     */
    public function testContact(): void
    {
        // mocking of method executeRetrieveContact with a fixed contact id
        $testId1 = 'test_contact_id';
        $_SESSION['contact_id'] = $testId1;

        $mockContact = $this->createMock(\Contact::class);
        $mockContact->id = $testId1;

        $mockSession = $this->createPartialMock(Session::class, ['executeRetrieveContact', 'getCacheObject']);
        $mockSession->expects($this->once())
            ->method('executeRetrieveContact')
            ->with($this->equalTo($testId1))
            ->willReturn($mockContact);
        $mockCache = $this->createMock(CacheInterface::class);
        $mockSession->method('getCacheObject')
            ->willReturn($mockCache);

        $contact = $mockSession->getContact();
        $this->assertEquals($testId1, $contact->id);

        unset($_SESSION['contact_id']);
    }
}
