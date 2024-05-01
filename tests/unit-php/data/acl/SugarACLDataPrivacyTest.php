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

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarACLDataPrivacy
 */
class SugarACLDataPrivacyTest extends TestCase
{
    /**
     * @covers ::checkAccess
     *
     * @dataProvider providerTestCheckAccess
     */
    public function testCheckAccess($isAdmin, $action, $fieldToChange, $expected)
    {
        $currentUserMock = $this->getCurrentUserMock(['isAdminForModule']);

        $currentUserMock->method('isAdminForModule')
            ->willReturn($isAdmin);

        $aclClass = $this->getMockBuilder('SugarACLDataPrivacy')
            ->setMethods(['getCurrentUser'])
            ->getMock();

        $aclClass->method('getCurrentUser')
            ->willReturn($currentUserMock);

        $dataPrivacyMock = $this->getMockBuilder('DataPrivacy')
            ->disableOriginalConstructor()
            ->getMock();

        $context = [
            'action' => $action,
            'field' => $fieldToChange,
            'bean' => $dataPrivacyMock,
        ];
        $result = $aclClass->checkAccess('DataPrivacy', 'field', $context);
        $this->assertEquals($expected, $result);
    }

    public function providerTestCheckAccess()
    {
        return [
            // action = save
            // is Admin of 'DataPrivacy' module
            [
                true,
                'save',
                'status',
                true,
            ],
            [
                true,
                'save',
                'fields_to_erase',
                true,
            ],
            [
                true,
                'save',
                'any_other_fields',
                true,
            ],
            // not admin of 'DataPrivacy' module, action = 'save'
            [
                false,
                'save',
                'status',
                true,
            ],
            // fields_to_erase change
            [
                false,
                'save',
                'fields_to_erase',
                false,
            ],
            [
                false,
                'save',
                'any_other_fields',
                true,
            ],
            // admin of 'DataPrivacy' module, action != 'save'
            [
                true,
                'view',
                'status',
                true,
            ],
            [
                true,
                'edit',
                'fields_to_erase',
                true,
            ],
            [
                true,
                'view',
                'any_other_fields',
                true,
            ],
            // not admin of 'DataPrivacy' module, action != 'save'
            [
                false,
                'view',
                'status',
                true,
            ],
            [
                false,
                'edit',
                'fields_to_erase',
                true,
            ],
            [
                false,
                'view',
                'any_other_fields',
                true,
            ],
        ];
    }

    /**
     * Returns a mock for the current user. The method isAdminForModule is
     * stubbed.
     * @param array $stubbedMethods The methods that are intended to be stubbed.
     *
     * @return MockBuilder
     */
    private function getCurrentUserMock($stubbedMethods)
    {
        $currentUserMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods($stubbedMethods)
            ->getMock();

        return $currentUserMock;
    }
}
