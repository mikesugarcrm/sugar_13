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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Emails;

use Email;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarException;
use SugarNullLogger;

/**
 * @coversDefaultClass \Email
 */
class EmailTest extends TestCase
{
    private $mockDb;

    protected function setUp(): void
    {
        $GLOBALS['log'] = new SugarNullLogger();

        $GLOBALS['timedate'] = \TimeDate::getInstance();

        $this->mockDb = TestMockHelper::getMockForAbstractClass(
            $this,
            '\\DBManager',
            [
                'insert',
                'update',
                'getDataChanges',
            ]
        );
        $this->mockDb->method('insert')->willReturn(true);
        $this->mockDb->method('update')->willReturn(true);
        $this->mockDb->method('getDataChanges')->willReturn([]);

        $GLOBALS['current_user'] = $this->createPartialMock('\\User', ['getPreference']);
        $GLOBALS['current_user']->method('getPreference')->willReturnMap([
            ['datef', 'global', 'm/d/Y'],
            ['timef', 'global', 'h:i a'],
            ['timezone', 'global', 'UTC'],
        ]);
        $GLOBALS['current_user']->id = Uuid::uuid1();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        unset($GLOBALS['timedate']);
        unset($GLOBALS['log']);
    }

    public function saveAndAssignToCurrentUserProvider()
    {
        return [
            'create draft and assigned user is undefined' => [
                [
                    'id' => null,
                    'state' => \Email::STATE_DRAFT,
                    'assigned_user_id' => '',
                ],
            ],
            'create draft and assigned user is defined' => [
                [
                    'id' => null,
                    'state' => \Email::STATE_DRAFT,
                    'assigned_user_id' => Uuid::uuid1(),
                ],
            ],
            'update draft and assigned user is undefined' => [
                [
                    'id' => Uuid::uuid1(),
                    'state' => \Email::STATE_DRAFT,
                    'assigned_user_id' => '',
                ],
            ],
            'update draft and assigned user is defined' => [
                [
                    'id' => Uuid::uuid1(),
                    'state' => \Email::STATE_DRAFT,
                    'assigned_user_id' => Uuid::uuid1(),
                ],
            ],
        ];
    }

    /**
     * @dataProvider saveAndAssignToCurrentUserProvider
     * @covers ::save
     */
    public function testSave_EmailIsAssignedToTheCurrentUser(array $properties)
    {
        $email = $this->createMock(Email::class);

        foreach ($properties as $key => $value) {
            $email->$key = $value;
        }

        TestReflection::callProtectedMethod($email, 'updateAssignedUser');

        $this->assertSame($GLOBALS['current_user']->id, $email->assigned_user_id, 'assigned_user_id is incorrect');
    }

    public function saveAndAssignToSpecifiedUserProvider()
    {
        return [
            'create archived email without an assigned user' => [
                [
                    'id' => null,
                    'state' => \Email::STATE_ARCHIVED,
                    'assigned_user_id' => '',
                ],
            ],
            'create archived email and with an assigned user' => [
                [
                    'id' => null,
                    'state' => \Email::STATE_ARCHIVED,
                    'assigned_user_id' => Uuid::uuid1(),
                ],
            ],
            'update archived email and unassign it' => [
                [
                    'id' => Uuid::uuid1(),
                    'state' => \Email::STATE_ARCHIVED,
                    'assigned_user_id' => '',
                ],
            ],
            'update archived email and reassign it' => [
                [
                    'id' => Uuid::uuid1(),
                    'state' => \Email::STATE_ARCHIVED,
                    'assigned_user_id' => Uuid::uuid1(),
                ],
            ],
        ];
    }

    /**
     * @dataProvider saveAndAssignToSpecifiedUserProvider
     * @covers ::save
     */
    public function testSave_EmailIsAssignedToTheSpecifiedUser(array $properties)
    {
        $email = $this->createMock(Email::class);

        foreach ($properties as $key => $value) {
            $email->$key = $value;
        }

        TestReflection::callProtectedMethod($email, 'updateAssignedUser');

        $this->assertSame($properties['assigned_user_id'], $email->assigned_user_id, 'assigned_user_id is incorrect');
    }

    public function setsBodyProvider()
    {
        return [
            'save archived email with a text body and without an html body' => [
                \Email::STATE_ARCHIVED,
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                null,
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                'This is a text body&lt;br /&gt;With more&lt;br /&gt; than...&lt;br /&gt;&lt;br /&gt;... one line',
            ],
            'save archived email with a text body and with an empty html body' => [
                \Email::STATE_ARCHIVED,
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                '',
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                'This is a text body&lt;br /&gt;With more&lt;br /&gt; than...&lt;br /&gt;&lt;br /&gt;... one line',
            ],
            'save archived email with a text body and with an html body' => [
                \Email::STATE_ARCHIVED,
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                'This is a <b>completely different</b> message',
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                'This is a &lt;b&gt;completely different&lt;/b&gt; message',
            ],
            'save archived email without a text body and without an html body' => [
                \Email::STATE_ARCHIVED,
                null,
                null,
                null,
                null,
            ],
            'save archived email without a text body and with an empty html body' => [
                \Email::STATE_ARCHIVED,
                null,
                '',
                null,
                '',
            ],
            'save archived email with an empty text body and without an html body' => [
                \Email::STATE_ARCHIVED,
                '',
                null,
                '',
                null,
            ],
            'save archived email with an empty text body and with an empty html body' => [
                \Email::STATE_ARCHIVED,
                '',
                '',
                '',
                '',
            ],
            'save archived email without a text body and with an html body' => [
                \Email::STATE_ARCHIVED,
                null,
                'This is a text body<br />With more<br /> than...<br /><br />... <b>one line</b>',
                "This is a text body\nWith more\n than...\n\n... one line",
                'This is a text body&lt;br /&gt;With more&lt;br /&gt; than...&lt;br /&gt;&lt;br /&gt;... ' .
                '&lt;b&gt;one line&lt;/b&gt;',
            ],
            'save archived email with an empty a text body and with an html body' => [
                \Email::STATE_ARCHIVED,
                '',
                'This is a text body<br />With more<br /> than...<br /><br />... <b>one line</b>',
                "This is a text body\nWith more\n than...\n\n... one line",
                'This is a text body&lt;br /&gt;With more&lt;br /&gt; than...&lt;br /&gt;&lt;br /&gt;... ' .
                '&lt;b&gt;one line&lt;/b&gt;',
            ],
            'save draft with a text body and without an html body' => [
                \Email::STATE_DRAFT,
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                null,
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                null,
            ],
            'save draft with a text body and with an empty html body' => [
                \Email::STATE_DRAFT,
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                '',
                "This is a text body\nWith more
 than...\r\n\r\n... one line",
                '',
            ],
            'save draft without a text body and with an html body' => [
                \Email::STATE_DRAFT,
                null,
                '<div>Test email</div>',
                null,
                '&lt;div&gt;Test email&lt;/div&gt;',
            ],
            'save draft with an empty a text body and with an html body' => [
                \Email::STATE_DRAFT,
                '',
                '<div>Test email</div>',
                '',
                '&lt;div&gt;Test email&lt;/div&gt;',
            ],
            'html characters should not be converted for the text body for archived emails' => [
                \Email::STATE_ARCHIVED,
                'Allow <b>HTML</b> if sent for text',
                'Allow <b>HTML</b> if sent for text',
                'Allow <b>HTML</b> if sent for text',
                'Allow &lt;b&gt;HTML&lt;/b&gt; if sent for text',
            ],
            'html characters should not be converted for the text body for drafts' => [
                \Email::STATE_DRAFT,
                'Allow <b>HTML</b> if sent for text',
                'Allow <b>HTML</b> if sent for text',
                'Allow <b>HTML</b> if sent for text',
                'Allow &lt;b&gt;HTML&lt;/b&gt; if sent for text',
            ],
        ];
    }

    public function theCurrentUserIsTheSenderForDraftsProvider()
    {
        return [
            'no outbound_email_id' => [
                null,
            ],
            'empty outbound_email_id' => [
                '',
            ],
            'the outbound_email_id does not exist' => [
                Uuid::uuid1(),
            ],
        ];
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_OnlyDraftsCanBeSent()
    {
        $user = $this->createMock('\\User');
        $user->id = Uuid::uuid1();
        $config = new \OutboundEmailConfiguration($user);

        $email = $this->createPartialMock('\\Email', []);
        $email->state = \Email::STATE_ARCHIVED;

        $this->expectException(SugarException::class);
        $email->sendEmail($config);
    }

    /**
     * @covers ::getMobileSupportingModules
     */
    public function testGetMobileSupportingModules()
    {
        $actual = \Email::getMobileSupportingModules();

        $expected = [
            'EmailAddresses',
            'EmailParticipants',
            'OutboundEmail',
            'UserSignatures',
        ];
        $this->assertEquals($expected, $actual);
    }

    public function isStateTransitionAllowedProvider()
    {
        return [
            [
                false,
                \Email::STATE_ARCHIVED,
                \Email::STATE_ARCHIVED,
                true,
            ],
            [
                false,
                \Email::STATE_ARCHIVED,
                \Email::STATE_DRAFT,
                true,
            ],
            [
                false,
                \Email::STATE_ARCHIVED,
                \Email::STATE_READY,
                true,
            ],
            [
                false,
                \Email::STATE_ARCHIVED,
                'Foo',
                false,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                \Email::STATE_ARCHIVED,
                true,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                \Email::STATE_DRAFT,
                false,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                \Email::STATE_READY,
                false,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                'Foo',
                false,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                \Email::STATE_ARCHIVED,
                false,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                \Email::STATE_DRAFT,
                true,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                \Email::STATE_READY,
                true,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                'Foo',
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                \Email::STATE_ARCHIVED,
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                \Email::STATE_DRAFT,
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                \Email::STATE_READY,
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                'Foo',
                false,
            ],
        ];
    }

    /**
     * @covers ::isStateTransitionAllowed
     * @dataProvider isStateTransitionAllowedProvider
     * @param bool $isUpdate
     * @param string $currentState
     * @param string $newState
     * @param bool $expected
     */
    public function testIsStateTransitionAllowed($isUpdate, $currentState, $newState, $expected)
    {
        $email = $this->createPartialMock('\\Email', ['isUpdate']);
        $email->method('isUpdate')->willReturn($isUpdate);
        $email->state = $currentState;

        $actual = $email->isStateTransitionAllowed($newState);
        $this->assertEquals($expected, $actual);
    }

    public function isArchivedProvider()
    {
        return [
            [
                \Email::STATE_ARCHIVED,
                true,
                true,
            ],
            [
                \Email::STATE_ARCHIVED,
                false,
                false,
            ],
            [
                \Email::STATE_DRAFT,
                true,
                false,
            ],
            [
                \Email::STATE_DRAFT,
                false,
                false,
            ],
        ];
    }

    /**
     * @covers ::isArchived
     * @dataProvider isArchivedProvider
     * @param string $state
     * @param bool $isUpdate
     * @param bool $expected
     */
    public function testIsArchived($state, $isUpdate, $expected)
    {
        $email = $this->createPartialMock('\\Email', ['isUpdate']);
        $email->method('isUpdate')->willReturn($isUpdate);
        $email->state = $state;

        $actual = $email->isArchived();
        $this->assertSame($expected, $actual);
    }

    public function shouldPerformCaseAssignmentProvider()
    {
        return [
            [
                'fetched_row' => ['state' => \Email::STATE_DRAFT],
                'state' => \Email::STATE_ARCHIVED,
                'expected' => true,
            ],
            [
                'fetched_row' => [],
                'state' => \Email::STATE_ARCHIVED,
                'expected' => true,
            ],
            [
                'fetched_row' => ['state' => \Email::STATE_ARCHIVED],
                'state' => \Email::STATE_ARCHIVED,
                'expected' => false,
            ],
            [
                'fetched_row' => [],
                'state' => \Email::STATE_READY,
                'expected' => false,
            ],
        ];
    }

    /**
     * @covers ::shouldPerformCaseAssignment
     * @dataProvider shouldPerformCaseAssignmentProvider
     * @param array $fetched_row
     * @param string $state
     * @param bool $expected
     */
    public function testShouldPerformCaseAssignment($fetched_row, $state, $expected)
    {
        $email = $this->getMockBuilder(\Email::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $email->fetched_row = $fetched_row;
        $email->state = $state;
        $actual = \SugarTestReflection::callProtectedMethod($email, 'shouldPerformCaseAssignment');
        $this->assertEquals($actual, $expected);
    }

    /**
     * Returns all of the method names from Email::save() and SugarBean::save() that we want to avoid calling in unit
     * tests for Email::save().
     *
     * @return array
     */
    private function getBeanMethodsWithSideEffects()
    {
        $methods = array_merge($this->getEmailMethodsWithSideEffects(), $this->getSugarBeanMethodsWithSideEffects());

        return array_unique($methods);
    }

    /**
     * Returns all of the method names from SugarBean::save() that we want to avoid calling in unit tests for
     * Email::save().
     *
     * @return array
     */
    private function getSugarBeanMethodsWithSideEffects()
    {
        return [
            'cleanBean',
            'fixUpFormatting',
            '_checkOptimisticLocking',
            'save_relationship_changes',
            'updateCalculatedFields',
            'call_custom_logic',
            'populateFetchedEmail',
            '_sendNotifications',
            'updateRelatedCalcFields',
            'process_workflow_alerts',
            'track_view',
            'loadAutoIncrementValues', // This could be fixed by allowing mock beans to set $field_defs to an array
        ];
    }

    /**
     * Returns all of the method names from Email::save() that we want to avoid calling in unit tests for Email::save().
     *
     * @return array
     */
    private function getEmailMethodsWithSideEffects()
    {
        return [
            'cleanEmails',
            'saveEmailAddresses',
            'setSender',
            'linkParentUsingRelationship',
            'cleanContent',
            'saveEmailText',
            'updateAttachmentsVisibility',
        ];
    }
}
