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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSEEmailHandler
 */
class PMSEEmailHandlerTest extends TestCase
{
    /**
     * @covers ::isUserActiveForEmail
     * @dataProvider providerIsUserActiveForEmail
     */
    public function providerIsUserActiveForEmail()
    {
        return [
            [
                [
                    'full_name' => 'John Doe',
                    'email1' => 'john.doe@example.com',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ],
                true,
            ],
            [
                [
                    'full_name' => '',
                    'email1' => 'jane.elliot@abc.com',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ],
                false,
            ],
            [
                [
                    'full_name' => 'Bob Smith',
                    'email1' => '',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ],
                false,
            ],
            [
                [
                    'full_name' => 'Mary Jones',
                    'email1' => 'mjones@xyz.com',
                    'status' => 'Inactive',
                    'employee_status' => 'Active',
                ],
                false,
            ],
            [
                [
                    'full_name' => 'Jim Brown',
                    'email1' => 'jim.brown@foo.com',
                    'status' => 'Active',
                    'employee_status' => '',
                ],
                false,
            ],
        ];
    }

    /**
     * @var array
     */
    protected $sugarConfigBackUp;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        global $sugar_config;
        $this->sugarConfigBackUp = [];
        if (!empty($sugar_config) && is_array($sugar_config)) {
            $this->sugarConfigBackUp = $sugar_config;
        }
        $sugar_config = ['pmse_settings_default' => ['logger_level' => 7]];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        global $sugar_config;

        $sugar_config = $this->sugarConfigBackUp;
        parent::tearDown();
    }

    /**
     * @covers ::isUserActiveForEmail
     * @dataProvider providerIsUserActiveForEmail
     */
    public function testIsUserActiveForEmail($userAttr, $expected)
    {
        $user = $this->createMock('\User');
        foreach ($userAttr as $prop => $val) {
            $user->$prop = $val;
        }

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $emailHandlerMock->isUserActiveForEmail($user);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getRecipients
     */
    public function testGetRecipients()
    {
        $eventDefBean = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $eventDefBean->evn_params = '{"to":[{"value":"a@a.com","type":"email","label":"a@a.com"},' .
            '{"value":"b@b.com","type":"email","label":"b@b.com"}],' .
            '"cc":[{"value":"c@c.com","type":"email","label":"c@c.com"}],' .
            '"bcc":[{"value":"bc@bc.com","type":"email","label":"bc@bc.com"}]}';
        $targetBean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $recipients = $emailHandlerMock->getRecipients($eventDefBean, $targetBean, []);
        $this->assertEquals('a@a.com', $recipients->to[0]->address);
        $this->assertEquals('b@b.com', $recipients->to[1]->address);
        $this->assertEquals('c@c.com', $recipients->cc[0]->address);
        $this->assertEquals('bc@bc.com', $recipients->bcc[0]->address);
    }

    /**
     * @covers ::getEmailBody
     */
    public function testGetEmailBody()
    {
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->body = '';
        $templateMock->body_html = '<p>Body</p>';
        $targetBean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $targetBean->id = 'id';
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['fromHtml'])
            ->getMock();

        $emailHandlerMock->method('fromHtml')->will($this->returnArgument(0));
        $emailBody = $emailHandlerMock->getEmailBody($templateMock, $targetBean);

        $this->assertEquals('Body', $emailBody['textBody']);
        $this->assertEquals('<p>Body</p>', $emailBody['htmlBody']);
    }

    /**
     * @covers ::getSubject
     */
    public function testGetSubject()
    {
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->subject = 'Hello';
        $targetBean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['fromHtml'])
            ->getMock();
        $emailHandlerMock->method('fromHtml')->will($this->returnArgument(0));

        $subject = $emailHandlerMock->getSubject($templateMock, $targetBean);
        $this->assertEquals('Hello', $subject);
    }

    /**
     * @covers ::getSender
     */
    public function testGetSender()
    {
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->from_name = 'Jim';
        $templateMock->from_address = 'jim@jim.com';

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $sender = $emailHandlerMock->getSender($templateMock);
        $this->assertEquals('Jim', $sender['name']);
        $this->assertEquals('jim@jim.com', $sender['address']);
    }

    /**
     * @covers ::saveEmailContent
     */
    public function testSaveEmailContent()
    {
        $eventDefinitionBeanMock = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $eventDefinitionBeanMock->evn_params = '{"to":[{"value":"a@a.com","type":"email","label":"a@a.com"},' .
            '{"value":"b@b.com","type":"email","label":"b@b.com"}],' .
            '"cc":[{"value":"c@c.com","type":"email","label":"c@c.com"}],' .
            '"bcc":[{"value":"bc@bc.com","type":"email","label":"bc@bc.com"}]}';

        $eventDefinitionBeanMock->id = 'eventId';

        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->id = 'templateId';
        $templateMock->subject = 'Subject';
        $templateMock->body_html = '<p>Body</p>';

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['fromHtml', 'getBeansForEmailContentSave'])
            ->getMock();

        $emailHandlerMock->method('fromHtml')->will($this->returnArgument(0));

        $targetBeanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $targetBeanMock->id = 'id';

        $emailMessageMock = $this->getMockBuilder('pmse_EmailMessage')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $emailHandlerMock->method('getBeansForEmailContentSave')
            ->willReturn([$eventDefinitionBeanMock, $templateMock, $targetBeanMock, $emailMessageMock]);

        $flowData = ['id' => 'flowid'];
        $emailHandlerMock->saveEmailContent($flowData);

        $this->assertEquals('Subject', $emailMessageMock->subject);
        $this->assertEquals('Body', $emailMessageMock->body);
        $this->assertEquals('<p>Body</p>', $emailMessageMock->body_html);
        $this->assertEquals(
            '[{"name":"a@a.com","address":"a@a.com"},{"name":"b@b.com","address":"b@b.com"}]',
            $emailMessageMock->to_addrs
        );
        $this->assertEquals('[{"name":"c@c.com","address":"c@c.com"}]', $emailMessageMock->cc_addrs);
        $this->assertEquals('[{"name":"bc@bc.com","address":"bc@bc.com"}]', $emailMessageMock->bcc_addrs);
    }

    /**
     * @covers ::saveActivityEmailContent()
     */
    public function testSaveActivityEmailContent()
    {
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->id = 'templateId';
        $templateMock->subject = 'Subject';
        $templateMock->body_html = '<p>Body</p>';

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods([
                'fromHtml',
                'getBeansForActivityEmailSave',
                'getUserEmails',
                'getcontactInformationFromId',
                'saveEmailBean',
            ])
            ->getMock();

        $targetBeanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $targetBeanMock->id = 'id';

        $emailMessageMock = $this->getMockBuilder('pmse_EmailMessage')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $assigneeMock = $this->getMockBuilder('Users')
            ->disableOriginalConstructor()
            ->getMock();

        $emailHandlerMock->method('getBeansForActivityEmailSave')
            ->willReturn([$templateMock, $targetBeanMock, $emailMessageMock, $assigneeMock]);

        $emailHandlerMock->method('getUserEmails')
            ->willReturn('abc123');

        $emailHandlerMock->method('getContactInformationFromId')
            ->willReturn('systemEmail');

        $emailHandlerMock->expects($this->once())
            ->method('saveEmailBean')
            ->with($targetBeanMock, $templateMock, ['from' => 'systemEmail', 'reply' => 'systemEmail'], $this->isInstanceOf('stdClass'), $emailMessageMock, null);

        $emailHandlerMock->saveActivityEmailContent([], null, null);
    }

    /**
     * @covers ::getContactInformationFromBean()
     */
    public function testGetContactInformationFromUserBean()
    {
        $userMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $userMock->full_name = 'First Last';
        $userMock->email1 = 'mockUser@email.com';
        $userMock->id = 'id1';
        $userMock->method('getModuleName')->willReturn('Users');

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromBean($userMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromBean()
     */
    public function testGetContactInformationFromNullBean()
    {
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedResult = [
            'name' => null,
            'address' => null,
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromBean(null, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromBean()
     */
    public function testGetContactInformationFromOutboundEmailBean()
    {
        $obMock = $this->getMockBuilder('OutboundEmail')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $obMock->name = 'First Last';
        $obMock->email_address = 'mockUser@email.com';
        $obMock->id = 'id1';
        $obMock->method('getModuleName')->willReturn('OutboundEmail');

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromBean($obMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromBean()
     */
    public function testGetContactInformationFromBeanReply()
    {
        $obMock = $this->getMockBuilder('OutboundEmail')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $obMock->reply_to_name = 'First Last';
        $obMock->reply_to_email_address = 'mockUser@email.com';
        $obMock->id = 'id1';
        $obMock->method('getModuleName')->willReturn('OutboundEmail');

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromBean($obMock, 'reply');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactBeanFromId()
     */
    public function testGetContactBeanFromId()
    {
        $userMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();
        $userMock->full_name = 'First Last';
        $userMock->email1 = 'mockUser@email.com';
        $userMock->id = 'mockUserId';

        $returnMap = [
            ['Users', 'mockUserId', [], $userMock],
        ];

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();
        $emailHandlerMock->method('retrieveBean')
            ->will($this->returnValueMap($returnMap));

        $this->assertEquals($userMock, $emailHandlerMock->getContactBeanFromId('mockUserId'));
        $this->assertNull($emailHandlerMock->getContactBeanFromId('NotARealIDAndShouldReturnNull'));
    }

    /**
     * @covers ::getContactInformationFromId()
     */
    public function testGetContactInformationFromIdVariableUserCreatedBy()
    {
        $userMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $userMock->full_name = 'First Last';
        $userMock->email1 = 'mockUser@email.com';
        $userMock->id = 'id1';
        $userMock->method('getModuleName')->willReturn('Users');

        $targetBeanMock = $this->getMockBuilder('Accounts')
            ->disableOriginalConstructor()
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getRecordCreator'])
            ->getMock();
        $emailHandlerMock->method('getRecordCreator')
            ->willReturn($userMock);

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromId('created_by', $targetBeanMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromId()
     */
    public function testGetContactInformationFromIdVariableUserCurrentUser()
    {
        $userMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $userMock->full_name = 'First Last';
        $userMock->email1 = 'mockUser@email.com';
        $userMock->id = 'id1';
        $userMock->method('getModuleName')->willReturn('Users');

        $targetBeanMock = $this->getMockBuilder('Accounts')
            ->disableOriginalConstructor()
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUser'])
            ->getMock();
        $emailHandlerMock->method('getCurrentUser')
            ->willReturn($userMock);

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromId('currentuser', $targetBeanMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromId()
     */
    public function testGetContactInformationFromIdVariableUserLastModifier()
    {
        $userMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $userMock->full_name = 'First Last';
        $userMock->email1 = 'mockUser@email.com';
        $userMock->id = 'id1';
        $userMock->method('getModuleName')->willReturn('Users');

        $targetBeanMock = $this->getMockBuilder('Accounts')
            ->disableOriginalConstructor()
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getLastModifier'])
            ->getMock();
        $emailHandlerMock->method('getLastModifier')
            ->willReturn($userMock);

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromId('modified_user_id', $targetBeanMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromId()
     */
    public function testGetContactInformationFromIdVariableUserOwner()
    {
        $userMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $userMock->full_name = 'First Last';
        $userMock->email1 = 'mockUser@email.com';
        $userMock->id = 'id1';
        $userMock->method('getModuleName')->willReturn('Users');

        $targetBeanMock = $this->getMockBuilder('Accounts')
            ->disableOriginalConstructor()
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentAssignee'])
            ->getMock();
        $emailHandlerMock->method('getCurrentAssignee')
            ->willReturn($userMock);

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromId('owner', $targetBeanMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromId()
     */
    public function testGetContactInformationFromIdVariableUserSupervisor()
    {
        $userAssigneeMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();

        $userSupervisorMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();
        $userSupervisorMock->full_name = 'First Last';
        $userSupervisorMock->email1 = 'mockSupervisor@email.com';
        $userSupervisorMock->id = 'id1';
        $userSupervisorMock->method('getModuleName')->willReturn('Users');

        $targetBeanMock = $this->getMockBuilder('Accounts')
            ->disableOriginalConstructor()
            ->getMock();

        $returnMap = [
            [$userAssigneeMock, $userSupervisorMock],
        ];

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentAssignee', 'getSupervisor'])
            ->getMock();
        $emailHandlerMock->method('getCurrentAssignee')
            ->willReturn($userAssigneeMock);
        $emailHandlerMock->method('getSupervisor')
            ->will($this->returnValueMap($returnMap));

        $expectedResult = [
            'name' => 'First Last',
            'address' => 'mockSupervisor@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromId('supervisor', $targetBeanMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getContactInformationFromId()
     */
    public function testGetContactInformationFromId()
    {
        $userMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $userMock->full_name = 'User First Last';
        $userMock->email1 = 'mockUser@email.com';
        $userMock->id = 'mockUserId';
        $userMock->method('getModuleName')->willReturn('Users');

        $targetBeanMock = $this->getMockBuilder('Accounts')
            ->disableOriginalConstructor()
            ->getMock();

        $returnMap = [
            ['mockUserId', $userMock],
        ];

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getContactBeanFromId'])
            ->getMock();
        $emailHandlerMock->method('getContactBeanFromId')
            ->will($this->returnValueMap($returnMap));

        $expectedResult = [
            'name' => 'User First Last',
            'address' => 'mockUser@email.com',
        ];
        $actualResult = $emailHandlerMock->getContactInformationFromId('mockUserId', $targetBeanMock, 'from');
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @covers ::getSenderFromEventDefinition()
     */
    public function testGetSenderFromEventDefinition()
    {
        $eventDefMock = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableOriginalConstructor()
            ->getMock();
        $eventDefMock->evn_params = '{"from":{"name":"Chris Olliver","id":"seed_chris_id"},' .
            '"replyTo":{"name":"Sally Bronsen","id":"seed_sally_id"},"to":[{"type":"user",' .
            '"module":"Accounts","moduleLabel":"Accounts","value":"record_creator","user":"who",' .
            '"label":"User who created the %MODULE%","filter":{}}],"cc":[],"bcc":[]}';

        $targetBeanMock = $this->getMockBuilder('Accounts')
            ->disableOriginalConstructor()
            ->getMock();

        $fromDataMock = [
            'name' => 'Chris Olliver',
            'address' => 'chris@example.com',
        ];
        $replyDataMock = [
            'name' => 'Sally Bronsen',
            'address' => 'sally@example.com',
        ];

        $returnMap = [
            ['seed_chris_id', $targetBeanMock, 'from', $fromDataMock],
            ['seed_sally_id', $targetBeanMock, 'reply', $replyDataMock],
        ];

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getContactInformationFromId'])
            ->getMock();
        $emailHandlerMock->method('getContactInformationFromId')
            ->will($this->returnValueMap($returnMap));

        $expected = [
            'from' => [
                'name' => 'Chris Olliver',
                'address' => 'chris@example.com',
            ],
            'reply' => [
                'name' => 'Sally Bronsen',
                'address' => 'sally@example.com',
            ],
        ];
        $result = $emailHandlerMock->getSenderFromEventDefinition($eventDefMock, $targetBeanMock);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::processDirectEmails
     * @dataProvider providerProcessDirectEmails
     */
    public function testProcessDirectEmails($entry, $name, $address, $beanReturns)
    {
        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $emailHandlerMock->method('retrieveBean')->willReturn(...$beanReturns);
        $entry = json_decode($entry);
        $result = $emailHandlerMock->processDirectEmails(null, $entry, null);
        $result = $result[0];
        $this->assertEquals($name, $result->name);
        $this->assertEquals($address, $result->address);
    }

    /**
     * Format for data provider
     * [
     *      json encoded entry,
     *      name,
     *      email address,
     *      results of each retrieveBean call
     * ]
     * @covers ::processDirectEmails
     * @dataProvider providerProcessDirectEmails
     */
    public function providerProcessDirectEmails()
    {
        $bean1 = $this->getMockBuilder('\SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['getRecordName'])
            ->getMock();
        $bean1->full_name = 'test';
        $bean1->email1 = 'a@a.com';
        $bean1->method('getRecordName')->willReturn($bean1->full_name);

        $bean2 = $this->getMockBuilder('\SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['getRecordName'])
            ->getMock();
        $bean2->name = 'noModule';
        $bean2->email1 = 'noM@noM.nom';
        $bean2->method('getRecordName')->willReturn($bean2->name);

        return [
            // ID and module are set
            [
                '{"id": "id1", "module": "Users"}',
                'test',
                'a@a.com',
                [$bean1],
            ],
            // User typed an address directly
            [
                '{"value": "value@value.com"}',
                'value@value.com',
                'value@value.com',
                [null],
            ],
            // ID is set but module is unknown
            [
                '{"id":"id2"}',
                'noModule',
                'noM@noM.nom',
                [null, null, $bean2],
            ],
        ];
    }
}
