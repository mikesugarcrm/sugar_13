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
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;

/**
 * @coversDefaultClass MailApi
 * @group api
 * @group email
 */
class MailApiTest extends TestCase
{
    private $api;
    private $mailApi;
    private $userCacheDir;
    private $dp;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('dictionary');
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->mailApi = $this->createPartialMock(
            'MailApi',
            [
                'initMailRecord',
                'getEmailRecipientsService',
                'getEmailBean',
                'getBeanFromServiceRecord',
            ]
        );

        $emailUI = new EmailUI();
        $this->userCacheDir = $emailUI->userCacheDir;
        $this->dp = [];
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        if (file_exists($this->userCacheDir)) {
            rmdir_recursive($this->userCacheDir);
        }

        if (!empty($this->dp)) {
            $GLOBALS['db']->query('DELETE FROM data_privacy WHERE id IN (\'' . implode("', '", $this->dp) . '\')');
        }

        $this->dp = [];
    }

    public function testArchiveMail_StatusIsArchive_CallsMailRecordArchive()
    {
        $args = [
            MailApi::STATUS => 'archive',
            MailApi::DATE_SENT => '2014-12-25T18:30:00',
            MailApi::FROM_ADDRESS => 'John Doe <x@y.z>',
            MailApi::TO_ADDRESSES => [['email' => 'a@b.c']],
            MailApi::SUBJECT => 'foo',
        ];

        $mailRecordMock = $this->createPartialMock('MailRecord', ['archive']);
        $mailRecordMock->expects($this->once())
            ->method('archive');

        $this->mailApi->expects($this->any())
            ->method('initMailRecord')
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->archiveMail($this->api, $args);
    }

    public function testCreateMail_StatusIsSaveAsDraft_CallsMailRecordSaveAsDraft()
    {
        $args = [
            MailApi::STATUS => 'draft',
        ];

        $mockResult = [
            'id' => '1234567890',
        ];

        $mailRecordMock = $this->createPartialMock('MailRecord', ['saveAsDraft']);
        $mailRecordMock->expects($this->once())
            ->method('saveAsDraft')
            ->will($this->returnValue($mockResult));

        $this->mailApi->expects($this->any())
            ->method('initMailRecord')
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->createMail($this->api, $args);
    }

    public function testCreateMail_StatusIsReady_CallsMailRecordSend()
    {
        $args = [
            MailApi::STATUS => 'ready',
            MailApi::EMAIL_CONFIG => 'foo',
            MailApi::TO_ADDRESSES => [['email' => 'a@b.c']],
        ];

        $mockResult = [
            'id' => '1234567890',
        ];

        $mailRecordMock = $this->createPartialMock('MailRecord', ['send']);
        $mailRecordMock->expects($this->once())
            ->method('send')
            ->will($this->returnValue($mockResult));

        $this->mailApi->expects($this->any())
            ->method('initMailRecord')
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->createMail($this->api, $args);
    }

    public function testRecipientLookup_AttemptToResolveTenRecipients_CallsLookupTenTimes()
    {
        $expected = 10;
        $args = [];

        for ($i = 0; $i < $expected; $i++) {
            $args[] = ['email' => "recipient{$i}"];
        }

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['lookup']);
        $emailRecipientsServiceMock->expects($this->exactly($expected))
            ->method('lookup')
            ->will($this->returnArgument(0));

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $actual = $this->mailApi->recipientLookup($this->api, $args);
        $this->assertEquals($args, $actual, 'Should have returned an array matching $args.');
    }

    public function testValidateEmailAddresses_OneIsValidAndOneIsInvalid()
    {
        $args = [
            'foo@bar.com',
            'foo',
        ];
        $actual = $this->mailApi->validateEmailAddresses($this->api, $args);
        $this->assertTrue($actual[$args[0]], "Should have set the value for key '{$args[0]}' to true.");
        $this->assertFalse($actual[$args[1]], "Should have set the value for key '{$args[1]}' to false.");
    }

    /**
     * @covers ::findRecipients
     */
    public function testFindRecipients_NextOffsetIsLessThanTotalRecords_ReturnsRealNextOffset()
    {
        $args = [
            'offset' => 0,
            'max_num' => 2,
        ];

        $mockContact1 = BeanFactory::newBean('Contacts');
        $mockContact1->id = Uuid::uuid1();
        $mockContact2 = BeanFactory::newBean('Contacts');
        $mockContact2->id = Uuid::uuid1();
        $mockContact3 = BeanFactory::newBean('Contacts');
        $mockContact3->id = Uuid::uuid1();

        BeanFactory::registerBean($mockContact1);
        BeanFactory::registerBean($mockContact2);
        BeanFactory::registerBean($mockContact3);

        $recipients = [
            [
                'id' => $mockContact1->id,
                '_module' => 'Contacts',
                'name' => 'Foo Bar 1',
                'email' => 'foo2@bar.com',
            ],
            [
                'id' => $mockContact2->id,
                '_module' => 'Contacts',
                'name' => 'Foo Bar 2',
                'email' => 'foo2@bar.com',
            ],
            [
                'id' => $mockContact3->id,
                '_module' => 'Contacts',
                'name' => 'Foo Bar 3',
                'email' => 'foo3@bar.com',
            ],
        ];

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue($recipients));

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = 2;
        $actual = $response['next_offset'];
        $this->assertEquals($expected, $actual, 'The next offset should be {$expected}.');

        BeanFactory::unregisterBean($mockContact1);
        BeanFactory::unregisterBean($mockContact2);
        BeanFactory::unregisterBean($mockContact3);
    }

    /**
     * @covers ::findRecipients
     */
    public function testFindRecipients_NextOffsetIsGreaterThanTotalRecords_ReturnsNextOffsetAsNegativeOne()
    {
        $args = [
            'offset' => 5,
            'max_num' => 5,
        ];

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->any())
            ->method('findCount')
            ->will($this->returnValue(4));
        $emailRecipientsServiceMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue([]));

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = -1;
        $actual = $response['next_offset'];
        $this->assertEquals($expected, $actual, 'The next offset should be -1.');
    }

    /**
     * @covers ::findRecipients
     */
    public function testFindRecipients_OffsetIsEnd_ReturnsNextOffsetAsNegativeOne()
    {
        $args = [
            'offset' => 'end',
        ];

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->never())->method('findCount');
        $emailRecipientsServiceMock->expects($this->never())->method('find');

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = -1;
        $actual = $response['next_offset'];
        $this->assertEquals($expected, $actual, 'The next offset should be -1.');
    }

    /**
     * @covers ::findRecipients
     */
    public function testFindRecipients_NoArguments_CallsFindCountAndFindWithDefaults()
    {
        $args = [];

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->once())
            ->method('find')
            ->with(
                $this->isEmpty(),
                $this->equalTo('LBL_DROPDOWN_LIST_ALL'),
                $this->isEmpty(),
                $this->equalTo(21),
                $this->equalTo(0)
            )
            ->will($this->returnValue([]));

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
    }

    /**
     * @covers ::findRecipients
     */
    public function testFindRecipients_HasAllArguments_CallsFindCountAndFindWithArguments()
    {
        $args = [
            'q' => 'foo',
            'module_list' => 'contacts',
            'order_by' => 'name,email:desc',
            'max_num' => 5,
            'offset' => 3,
        ];

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo($args['q']),
                $this->equalTo($args['module_list']),
                $this->equalTo(['name' => 'ASC', 'email' => 'DESC']),
                $this->equalTo(6),
                $this->equalTo(3)
            )
            ->will($this->returnValue([]));

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
    }

    /**
     * @covers ::findRecipients
     */
    public function testFindRecipients_AclAttributePopulatedForBeans()
    {
        $mockContact = BeanFactory::newBean('Contacts');
        $mockContact->id = Uuid::uuid1();
        BeanFactory::registerBean($mockContact);

        $recipients = [
            [
                'id' => $mockContact->id,
                '_module' => 'Contacts',
                'name' => 'Foo Bar',
                'email' => 'foo@bar.com',
            ],
        ];

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue($recipients));

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, []);

        $record = array_shift($response['records']);
        $this->assertNotEmpty($record['_acl'], '_acl should be populated on the record');

        BeanFactory::unregisterBean($mockContact);
    }

    /**
     * @covers ::findRecipients
     */
    public function testFindRecipients_DataPrivacyErasedValuesReturned()
    {

        $contact = BeanFactory::newBean('Contacts');
        $contact->id = Uuid::uuid1();
        $contact->erased_fields = ['first_name', 'last_name'];

        $recipients = [
            [
                'id' => $contact->id,
                '_module' => 'Contacts',
                'name' => '',
                'email' => 'foo@bar.com',
            ],
        ];

        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue($recipients));

        $this->mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $this->mailApi->expects($this->any())
            ->method('getBeanFromServiceRecord')
            ->will($this->returnValue($contact));

        $response = $this->mailApi->findRecipients($this->api, ['erased_fields' => true]);

        $record = array_shift($response['records']);
        $this->assertArrayHasKey('_erased_fields', $record, 'Erased Fields expected, not returned');
        $this->assertCount(2, $record['_erased_fields'], 'Expected 2 erased fields');
        $this->assertSame(
            ['first_name', 'last_name'],
            $record['_erased_fields'],
            'Unexpected Erased Fields were returned'
        );
    }

    /**
     * @group mailattachment
     */
    public function testSaveAttachment_CallsAppropriateEmailFunction()
    {
        $mockResult = ['name' => 'foo'];

        $emailMock = $this->createPartialMock('Email', ['email2init', 'email2saveAttachment']);
        $emailMock->expects($this->once())
            ->method('email2init');
        $emailMock->expects($this->once())
            ->method('email2saveAttachment')
            ->will($this->returnValue($mockResult));

        $api = $this->getMockBuilder('MailApi')
            ->disableOriginalConstructor()
            ->setMethods(['checkPostRequestBody', 'getEmailBean'])
            ->getMock();
        $api->expects($this->once())->method('checkPostRequestBody')->willReturn(true);
        $api->expects($this->once())->method('getEmailBean')->willReturn($emailMock);

        $result = $api->saveAttachment($this->api, []);

        $this->assertEquals($mockResult, $result, 'Should return the response from email2saveAttachment');
    }

    /**
     * @group mailattachment
     */
    public function testSaveAttachment_AttachmentIsTooLarge_ThrowsException()
    {
        $_FILES = [];

        $api = $this->getMockBuilder('MailApi')
            ->disableOriginalConstructor()
            ->setMethods(['getContentLength', 'getPostMaxSize'])
            ->getMock();
        $api->expects($this->once())->method('getContentLength')->willReturn(500);
        $api->expects($this->once())->method('getPostMaxSize')->willReturn(100);

        $this->expectException(SugarApiExceptionRequestTooLarge::class);
        $api->saveAttachment($this->api, []);
    }

    /**
     * @group mailattachment
     */
    public function testRemoveAttachment_FileExists_RemovedSuccessfully()
    {
        //create the test attachment to be removed
        $fileGuid = create_guid();
        sugar_file_put_contents($this->userCacheDir . '/' . $fileGuid, create_guid());

        $this->mailApi->expects($this->once())
            ->method('getEmailBean')
            ->will($this->returnValue(new Email()));

        $this->mailApi->removeAttachment($this->api, ['file_guid' => $fileGuid]);

        //verify it was removed
        $this->assertCacheDirEmpty();
    }

    /**
     * @dataProvider validationProvider
     */
    public function testMailApi_run_validation($args, $exceptionExpected, $exceptionArgs = null)
    {
        $mailApiMock = $this->createPartialMock('MailApi', ['invalidParameter']);
        if (!empty($exceptionExpected)) {
            $mailApiMock->expects($this->once())
                ->method('invalidParameter')
                ->with($exceptionExpected, $exceptionArgs)
                ->will($this->throwException(new SugarApiExceptionInvalidParameter($exceptionExpected)));
            $this->expectException(SugarApiExceptionInvalidParameter::class);
        } else {
            $mailApiMock->expects($this->never())
                ->method('invalidParameter');
        }

        $data = [
            MailApi::STATUS => 'ready',
            MailApi::EMAIL_CONFIG => '1234567890',
            MailApi::TO_ADDRESSES => [['email' => 'a@b.c']],
        ];
        $arguments = array_merge($data, $args);

        $mailApiMock->validateArguments($arguments);
    }

    public function validationProvider()
    {
        return [
            0 => [
                [],
                false,
            ],
            1 => [
                [MailApi::STATUS => 'draft'],
                false,
            ],
            2 => [
                [MailApi::STATUS => 'qwerty'],
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                [MailApi::STATUS],
            ],
            3 => [
                [MailApi::TO_ADDRESSES => []],
                'LBL_MAILAPI_NO_RECIPIENTS',
                null,
            ],
            4 => [
                [
                    MailApi::TO_ADDRESSES => [],
                    MailApi::CC_ADDRESSES => [['email' => 'a@b.c']],
                ],
                false,
            ],
            5 => [
                [
                    MailApi::TO_ADDRESSES => [],
                    MailApi::BCC_ADDRESSES => [['email' => 'a@b.c']],
                ],
                false,
            ],
            6 => [
                [
                    MailApi::STATUS => 'draft',
                    MailApi::TO_ADDRESSES => [],
                ],
                false,
            ],
            7 => [
                [MailApi::TO_ADDRESSES => [['email' => null]]],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TO_ADDRESSES, 'email'],
            ],
            8 => [
                [MailApi::CC_ADDRESSES => [['email' => []]]],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::CC_ADDRESSES, 'email'],
            ],
            9 => [
                [MailApi::BCC_ADDRESSES => [['email' => true]]],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::BCC_ADDRESSES, 'email'],
            ],
            10 => [
                [MailApi::CC_ADDRESSES => [['email' => new stdClass()]]],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::CC_ADDRESSES, 'email'],
            ],
            11 => [
                [MailApi::ATTACHMENTS => '1234567890'],
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                [MailApi::ATTACHMENTS],
            ],
            12 => [
                [
                    MailApi::ATTACHMENTS => [
                        [],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::ATTACHMENTS, 'type'],
            ],
            13 => [
                [
                    MailApi::ATTACHMENTS => [
                        ['type' => 'document'],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::ATTACHMENTS, 'id'],
            ],
            14 => [
                [
                    MailApi::ATTACHMENTS => [
                        [
                            'type' => 'upload',
                            'id' => '1234567890',
                        ],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::ATTACHMENTS, 'name'],
            ],
            15 => [
                [
                    MailApi::TEAMS => '1',
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                [MailApi::TEAMS],
            ],
            16 => [
                [
                    MailApi::TEAMS => [],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TEAMS, 'primary'],
            ],
            17 => [
                [
                    MailApi::TEAMS => [
                        'others' => [
                            [],
                        ],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TEAMS, 'primary'],
            ],
            18 => [
                [
                    MailApi::TEAMS => [
                        'primary' => '',
                        'others' => [
                            '1234-1234-1234',
                        ],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TEAMS, 'primary'],
            ],
            19 => [
                [
                    MailApi::TEAMS => [
                        'primary' => 123,
                        'others' => [
                            '1234-1234-1234',
                        ],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TEAMS, 'primary'],
            ],
            20 => [
                [
                    MailApi::TEAMS => [
                        'primary' => '1234567890',
                        'others' => [
                            [],
                        ],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TEAMS, 'others'],
            ],
            21 => [
                [
                    MailApi::TEAMS => [
                        'primary' => '1234567890',
                        'others' => [
                            '',
                        ],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TEAMS, 'others'],
            ],
            22 => [
                [
                    MailApi::TEAMS => [
                        'primary' => '1234567890',
                        'others' => [
                            new stdClass(),
                        ],
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TEAMS, 'others'],
            ],
            23 => [
                [
                    MailApi::TEAMS => [
                        'primary' => '1234567890',
                        'others' => [
                            '1234-1234-1234',
                        ],
                    ],
                ],
                false,
            ],
            24 => [
                [MailApi::RELATED => '1234567890'],
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                [MailApi::RELATED],
            ],
            25 => [
                [
                    MailApi::RELATED => [
                        'type' => 'Contacts',
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::RELATED, 'id'],
            ],
            26 => [
                [
                    MailApi::RELATED => [
                        'type' => 'Contacts',
                        'id' => '1234567890',
                    ],
                ],
                false,
            ],
            27 => [
                [
                    MailApi::RELATED => [
                        'type' => 'Widgets',
                        'id' => '1234567890',
                    ],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::RELATED, 'type'],
            ],
            28 => [
                [MailApi::SUBJECT => 'Email Subject'],
                false,
            ],
            29 => [
                [MailApi::SUBJECT => []],
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                [MailApi::SUBJECT],
            ],
            30 => [
                [MailApi::HTML_BODY => 'HTML Body'],
                false,
            ],
            31 => [
                [MailApi::HTML_BODY => new stdClass()],
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                [MailApi::HTML_BODY],
            ],
            32 => [
                [MailApi::TEXT_BODY => 'TEXT Body'],
                false,
            ],
            33 => [
                [MailApi::TEXT_BODY => false],
                'LBL_MAILAPI_INVALID_ARGUMENT_FORMAT',
                [MailApi::TEXT_BODY],
            ],
            /* 'Archive' has some specific requirements */
            34 => [
                [
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => [['email' => 'a@b.c']],
                    MailApi::SUBJECT => 'foo',
                ],
                false,
            ],
            35 => [
                [
                    MailApi::STATUS => 'archive',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => [['email' => 'a@b.c']],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                [MailApi::FROM_ADDRESS],
            ],
            36 => [
                [
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::TO_ADDRESSES => [['email' => 'a@b.c']],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                [MailApi::DATE_SENT],
            ],
            37 => [
                [
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => [['name' => 'John']],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_FIELD',
                [MailApi::TO_ADDRESSES, 'email'],
            ],
            38 => [
                [
                    MailApi::STATUS => 'archive',
                    MailApi::FROM_ADDRESS => 'John Doe <john@doe.com>',
                    MailApi::DATE_SENT => '2014-12-25T18:30:00',
                    MailApi::TO_ADDRESSES => [['email' => 'a@b.c']],
                ],
                'LBL_MAILAPI_INVALID_ARGUMENT_VALUE',
                [MailApi::SUBJECT],
            ],
        ];
    }

    /**
     * Check to make sure path is created
     */
    private function assertCacheDirCreated()
    {
        $this->assertTrue(file_exists($this->userCacheDir), 'Cache directory should exist');
    }

    /**
     * Check to make sure path is empty
     */
    private function assertCacheDirEmpty()
    {
        $files = findAllFiles($this->userCacheDir, []);
        $this->assertEquals(0, safeCount($files), 'Cache directory should be empty');
    }

    /**
     * Create DataPrivacy Record, Link to Contact, and Re-Save to Complete the Erasure and set up the Erase record.
     */
    private function createDpErasureRecord($contact, $fields)
    {
        $dp = BeanFactory::newBean('DataPrivacy');
        $dp->name = 'Data Privacy Test';
        $dp->type = 'Request to Erase Information';
        $dp->status = 'Open';
        $dp->priority = 'Low';
        $dp->assigned_user_id = $GLOBALS['current_user']->id;
        $dp->date_opened = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->date_due = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->save();

        $module = 'Contacts';
        $linkName = strtolower($module);
        $dp->load_relationship($linkName);
        $dp->$linkName->add([$contact]);

        $options = ['use_cache' => false, 'encode' => false];
        $dp = BeanFactory::retrieveBean('DataPrivacy', $dp->id, $options);
        $dp->status = 'Closed';

        $fieldInfo = implode('","', $fields);
        $dp->fields_to_erase = '{"' . strtolower($module) . '":{"' . $contact->id . '":["' . $fieldInfo . '"]}}';

        $context = Container::getInstance()->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');

        $dp->save();
        $this->dp[] = $dp->id;
        return $dp;
    }
}
