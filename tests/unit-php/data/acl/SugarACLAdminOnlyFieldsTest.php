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

namespace Sugarcrm\SugarcrmTestsUnit\data\acl;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarACLAdminOnlyFields
 */
class SugarACLAdminOnlyFieldsTest extends TestCase
{
    /**
     * If action is not a write action, grant access.
     *
     * @covers ::checkAccess
     */
    public function testReadonlyAccess()
    {
        $aclClass = $this->getMockBuilder('SugarACLAdminOnlyFields')
            ->setConstructorArgs([[]])
            ->setMethods(['isWriteOperation'])
            ->getMock();

        $aclClass->method('isWriteOperation')
            ->willReturn(false);

        $expected = true;
        $result = $aclClass->checkAccess(null, null, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * If view is field, and the field is set in acl options, and is not admin,
     * deny access.
     *
     * @covers ::checkAccess
     */
    public function testAccessDenied()
    {
        $currentUserMock = $this->getCurrentUserMock(['isAdminForModule']);

        $currentUserMock->method('isAdminForModule')
            ->willReturn(false);

        $aclClass = $this->getMockBuilder('SugarACLAdminOnlyFields')
            ->setConstructorArgs([['non_writable_fields' => ['admin_field']]])
            ->setMethods(['isWriteOperation', 'getCurrentUser'])
            ->getMock();

        $aclClass->method('isWriteOperation')
            ->willReturn(true);

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $expected = false;
        $result = $aclClass->checkAccess(null, 'field', ['field' => 'admin_field']);
        $this->assertEquals($expected, $result);
    }

    /**
     * If view is not field grant access.
     *
     * @covers ::checkAccess
     */
    public function testNonFieldView()
    {
        $currentUserMock = $this->getCurrentUserMock(['isAdminForModule']);

        $currentUserMock->method('isAdminForModule')
            ->willReturn(false);

        $aclClass = $this->getMockBuilder('SugarACLAdminOnlyFields')
            ->setConstructorArgs([['non_writable_fields' => ['admin_field']]])
            ->setMethods(['isWriteOperation', 'getCurrentUser'])
            ->getMock();

        $aclClass->method('isWriteOperation')
            ->willReturn(true);

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $expected = true;
        $result = $aclClass->checkAccess(null, 'not_field', ['field' => 'admin_field']);
        $this->assertEquals($expected, $result);
    }

    /**
     * If field is not set in acl options, grant access.
     *
     * @covers ::checkAccess
     */
    public function testFieldNotInAclOptions()
    {
        $currentUserMock = $this->getCurrentUserMock(['isAdminForModule']);

        $currentUserMock->method('isAdminForModule')
            ->willReturn(false);

        $aclClass = $this->getMockBuilder('SugarACLAdminOnlyFields')
            ->setConstructorArgs([['non_writable_fields' => ['admin_field']]])
            ->setMethods(['isWriteOperation', 'getCurrentUser'])
            ->getMock();

        $aclClass->method('isWriteOperation')
            ->willReturn(true);

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $expected = true;
        $result = $aclClass->checkAccess(null, 'field', ['field' => 'not_admin_field']);
        $this->assertEquals($expected, $result);
    }

    /**
     * If admin, grant access.
     *
     * @covers ::checkAccess
     */
    public function testIsAdmin()
    {
        $currentUserMock = $this->getCurrentUserMock(['isAdminForModule']);

        $currentUserMock->method('isAdminForModule')
            ->willReturn(true);

        $aclClass = $this->getMockBuilder('SugarACLAdminOnlyFields')
            ->setConstructorArgs([['non_writable_fields' => ['admin_field']]])
            ->setMethods(['isWriteOperation', 'getCurrentUser'])
            ->getMock();

        $aclClass->method('isWriteOperation')
            ->willReturn(true);

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $expected = true;
        $result = $aclClass->checkAccess(null, 'field', ['field' => 'admin_field']);
        $this->assertEquals($expected, $result);
    }

    /**
     * Returns a mock for the current user. The method isAdminForModule is
     * stubbed.
     *
     * @param array $stubbedMethods The methods that are intended to be stubbed.
     *
     * @return MockObject
     */
    private function getCurrentUserMock($stubbedMethods)
    {
        $currentUserMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods($stubbedMethods)
            ->getMock();

        return $currentUserMock;
    }
}
