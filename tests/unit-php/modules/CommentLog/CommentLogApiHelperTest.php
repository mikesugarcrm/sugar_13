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

namespace Sugarcrm\SugarcrmTestsUnit\modules\CommentLog;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass CommentLogApiHelper
 */
class CommentLogApiHelperTest extends TestCase
{
    /**
     * @covers ::formatEntryForApi
     */
    public function testFormatEntryForApi()
    {
        $helper = $this->createPartialMock('CommentLogApiHelper', ['getBean', 'getSugarFieldHandler']);
        $nameDef = [
            'name' => 'name',
            'type' => 'fullname',
            'fields' => ['first_name', 'last_name', 'salutation', 'title'],
        ];

        // Test regular case
        $usersMock = $this->createPartialMock(
            'User',
            ['getRecordName', 'getFieldDefinition', 'ACLAccess', 'ACLFieldAccess']
        );
        $usersMock->method('getRecordName')->willReturn('Changed Sally');
        $usersMock->method('getFieldDefinition')->willReturn($nameDef);
        $usersMock->method('ACLFieldAccess')->willReturn(true);
        $usersMock->method('ACLAccess')->willReturn(true);
        $usersMock->id = 'users_id1';

        // Test Value Erased
        $contactsMock = $this->createPartialMock(
            'Contact',
            ['getRecordName', 'getFieldDefinition', 'ACLAccess', 'ACLFieldAccess']
        );
        $contactsMock->method('getRecordName')->willReturn('');
        $contactsMock->method('getFieldDefinition')->willReturn($nameDef);
        $contactsMock->method('ACLFieldAccess')->willReturn(true);
        $contactsMock->method('ACLAccess')->willReturn(true);
        $contactsMock->id = 'contacts_id1';

        // Test no name field access
        $leadsMock = $this->createPartialMock(
            'Lead',
            ['getRecordName', 'getFieldDefinition', 'ACLAccess', 'ACLFieldAccess']
        );
        $leadsMock->method('getRecordName')->willReturn('');
        $leadsMock->method('getFieldDefinition')->willReturn($nameDef);
        $leadsMock->method('ACLFieldAccess')->willReturn(false);
        $contactsMock->method('ACLAccess')->willReturn(true);
        $leadsMock->id = 'leads_id1';

        // Test no name field access
        $tasksMock = $this->createPartialMock(
            'Task',
            ['getRecordName', 'getFieldDefinition', 'ACLAccess', 'ACLFieldAccess']
        );
        $leadsMock->method('getRecordName')->willReturn('');
        $leadsMock->method('getFieldDefinition')->willReturn($nameDef);
        $leadsMock->method('ACLFieldAccess')->willReturn(true);
        $contactsMock->method('ACLAccess')->willReturn(false);
        $leadsMock->id = 'tasks_id1';

        $getBeanMap = [
            ['Users', 'users_id1', ['erased_fields' => true], $usersMock],
            ['Contacts', 'contacts_id1', ['erased_fields' => true], $contactsMock],
            ['Leads', 'leads_id1', ['erased_fields' => true], $leadsMock],
            ['Tasks', 'tasks_id1', ['erased_fields' => true], $tasksMock],
        ];
        $helper->method('getBean')
            ->will($this->returnValueMap($getBeanMap));

        $isErasedMap = [
            [$usersMock, 'name', false],
            [$contactsMock, 'name', true],
            [$leadsMock, 'name', false],
            [$tasksMock, 'name', false],
        ];
        $sugarFieldFullnameMock = $this->createPartialMock('SugarFieldFullname', ['isErased']);
        $sugarFieldFullnameMock->method('isErased')->will($this->returnValueMap($isErasedMap));

        $helper->method('getSugarFieldHandler')
            ->willReturn($sugarFieldFullnameMock);

        $entry = 'Hey @[Users:users_id1], have you seen @[Contacts:contacts_id1], @[Leads:leads_id1] ' .
            'and @[Tasks:tasks_id1]?';
        $actual = $helper->formatEntryForApi($entry);
        $expected = 'Hey @[Users:users_id1:Changed Sally], have you seen @[Contacts:contacts_id1:LBL_VALUE_ERASED],' .
            ' @[Leads:leads_id1:LBL_NO_DATA_AVAILABLE_NO_PERIOD]' .
            ' and @[Tasks:tasks_id1:LBL_NO_DATA_AVAILABLE_NO_PERIOD]?';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::sanitizeSubmittedData
     */
    public function testSanitizeSubmittedData()
    {
        $helper = $this->createPartialMock('CommentLogApiHelper', []);
        $data = [
            'entry' => 'Hey @[Users:seed_sally_id:Sally Bronson], have you seen @[Contacts:asdf:Jim]?',
        ];
        $actual = $helper->sanitizeSubmittedData($data);
        $expected = [
            'entry' => 'Hey @[Users:seed_sally_id], have you seen @[Contacts:asdf]?',
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::removeNamesFromCommentEntry
     */
    public function testRemoveNamesFromCommentEntry()
    {
        $helper = $this->createPartialMock('CommentLogApiHelper', []);
        $entry = 'Hey @[Users:seed_sally_id:Sally Bronson], have you seen @[Contacts:asdf:Jim]?';
        $actual = $helper->removeNamesFromCommentEntry($entry);
        $expected = 'Hey @[Users:seed_sally_id], have you seen @[Contacts:asdf]?';
        $this->assertEquals($expected, $actual);
    }
}
