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

use PHPUnit\Framework\TestCase;

class PMSEEmailHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testProcessEmailsFromJson()
    {
        $json = '{
            "to": ["geronimo@gmail.com"],
            "cc": ["ariana@gmail.com"],
            "bcc": ["joane.gill@gmail.com"]
        }';

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $bean = new stdClass();

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['processEmailsAndExpand'])
            ->getMock();

        $emailHandlerMock->expects($this->exactly(3))
            ->method('processEmailsAndExpand')
            ->willReturnOnConsecutiveCalls('geronimo@gmail.com', 'ariana@gmail.com', 'joane.gill@gmail.com');

        $result = $emailHandlerMock->processEmailsFromJson($bean, $json, $flowData);

        $this->assertEquals('geronimo@gmail.com', $result->to);
        $this->assertEquals('ariana@gmail.com', $result->cc);
        $this->assertEquals('joane.gill@gmail.com', $result->bcc);
    }

    public function testSendTemplateEmailAddressesNotDefined()
    {
        // The mock object to be tested
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getLogger'])
            ->getMock();

        // The logger mock, needed because no addresses means a log write
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['alert'])
            ->getMock();

        // The log should write an alert, one time
        $loggerMock->expects($this->once())
            ->method('alert')
            ->will($this->returnValue(true));

        // The getLogger method should return our mock logger
        $emailHandlerMock->method('getLogger')->will($this->returnValue($loggerMock));

        // Updated to Leads module, bean01 link, empty addresses array and template01 Template ID
        $emailHandlerMock->sendTemplateEmail('Leads', 'bean01', new stdClass(), 'template01');
    }

    public function testSendTemplateEmailAddressesDefined()
    {
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['addRecipients', 'retrieveBean', 'retrieveMailer'])
            ->getMock();


        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['warning'])
            ->getMock();

        $beanUtilsMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(['mergeBeanInTemplate'])
            ->getMock();

        $sugarMailerMock = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods([
                'addRecipientsTo',
                'addRecipientsCc',
                'addRecipientsBcc',
                'setHtmlBody',
                'setTextBody',
                'setSubject',
                'setHeader',
                'send',
            ])
            ->getMock();

        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->getMock();

        $beanMock = new stdClass();
        $beanMock->id = 'beanId';

        $emailHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->willReturnOnConsecutiveCalls($beanMock, $templateMock);

        $emailHandlerMock->expects($this->once())
            ->method('retrieveMailer')
            ->will($this->returnValue($sugarMailerMock));

        $templateMock->id = 'templateId';
        $templateMock->from_name = 'administrator';
        $templateMock->from_address = 'admin@gmail.com';
        $templateMock->body = 'Hello Mr Goodman';
        $templateMock->body_html = '<h1>Hello Mr Goodman</h1>';
        $templateMock->subject = 'Nice to hear from you!';

        $emailHandlerMock->setLogger($loggerMock);
        $emailHandlerMock->setBeanUtils($beanUtilsMock);

        $moduleName = 'Leads';

        $beanId = 'bean01';

        $addresses = (object)[
            'to' => [
                (object)['name' => 'user01', 'address' => 'user01@mail.com'],
                (object)['name' => 'user02', 'address' => 'user02@mail.com'],
            ],
            'cc' => [
                (object)['name' => 'user03', 'address' => 'user03@mail.com'],
                (object)['name' => 'user04', 'address' => 'user04@mail.com'],
            ],
            'bcc' => [
                (object)['name' => 'user05', 'address' => 'user05@mail.com'],
                (object)['name' => 'user06', 'address' => 'user06@mail.com'],
            ],
        ];

        $templateId = 'template01';
        $emailHandlerMock->sendTemplateEmail($moduleName, $beanId, $addresses, $templateId);
    }

    public function testSendTemplateEmailTemplateIdNotDefined()
    {
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveMailer', 'retrieveBean', 'addRecipients'])
            ->getMock();

        $sugarMailerMock = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods([
                'addRecipientsTo',
                'addRecipientsCc',
                'addRecipientsBcc',
                'setHtmlBody',
                'setTextBody',
                'setSubject',
                'setHeader',
                'send',
            ])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['warning'])
            ->getMock();

        $beanUtilsMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(['mergeBeanInTemplate'])
            ->getMock();

        $templateObjectMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $beanMock = new stdClass();
        $beanMock->id = 'beanId';

        $emailHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->willReturnOnConsecutiveCalls($beanMock, $templateObjectMock);

        $emailHandlerMock->expects($this->once())
            ->method('retrieveMailer')
            ->will($this->returnValue($sugarMailerMock));

        $templateObjectMock->id = 'templateId';
        $templateObjectMock->from_name = 'administrator';
        $templateObjectMock->from_address = 'admin@gmail.com';
        $templateObjectMock->body = '';
        $templateObjectMock->body_html = '';
        $templateObjectMock->subject = 'Nice to hear from you!';

        $emailHandlerMock->setLogger($loggerMock);
        $emailHandlerMock->setBeanUtils($beanUtilsMock);

        $moduleName = 'Leads';

        $beanId = 'bean01';

        $addresses = (object)[
            'to' => [
                (object)['name' => 'user01', 'address' => 'user01@mail.com'],
                (object)['name' => 'user02', 'address' => 'user02@mail.com'],
            ],
            'cc' => [
                (object)['name' => 'user03', 'address' => 'user03@mail.com'],
                (object)['name' => 'user04', 'address' => 'user04@mail.com'],
            ],
            'bcc' => [
                (object)['name' => 'user05', 'address' => 'user05@mail.com'],
                (object)['name' => 'user06', 'address' => 'user06@mail.com'],
            ],
        ];

        $templateId = '';
        $emailHandlerMock->sendTemplateEmail($moduleName, $beanId, $addresses, $templateId);
    }

    public function testDoesPrimaryEmailExistsFalse()
    {
        $field = new stdClass();
        $field->field = 'email_addresses_primary';
        $field->value = 'address@mail.com';

        $bean = new stdClass();
        $bean->id = 'beanId01';
        $bean->module_dir = 'Leads';
        $bean->emailAddress = $addressMock = $this->getMockBuilder('EmailAddress')
            ->disableOriginalConstructor()
            ->setMethods(['getPrimaryAddress'])
            ->getMock();

        $historyDataMock = $this->getMockBuilder('PMSEHistoryData')
            ->disableOriginalConstructor()
            ->setMethods(['savePredata'])
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getPrimaryEmailKeyFromREQUEST', 'updateEmails'])
            ->getMock();

        $emailHandlerMock->expects($this->once())
            ->method('getPrimaryEmailKeyFromREQUEST')
            ->will($this->returnValue('someKey'));

        $_REQUEST['someKey'] = '';
        $result = $emailHandlerMock->doesPrimaryEmailExists($field, $bean, $historyDataMock);

        $this->assertEquals('address@mail.com', $_REQUEST['someKey']);
        $this->assertEquals(true, $result);
    }

    public function testDoesPrimaryEmailExistsTrue()
    {
        $field = new stdClass();
        $field->field = 'email_addresses_primary';
        $field->value = 'address@mail.com';

        $bean = new stdClass();
        $bean->id = 'beanId01';
        $bean->module_dir = 'Leads';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
            ->disableOriginalConstructor()
            ->setMethods(['getPrimaryAddress'])
            ->getMock();

        $bean->emailAddress->expects($this->once())
            ->method('getPrimaryAddress')
            ->will($this->returnValue('address@mail.com'));

        $historyDataMock = $this->getMockBuilder('PMSEHistoryData')
            ->disableOriginalConstructor()
            ->setMethods(['savePredata'])
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getPrimaryEmailKeyFromREQUEST', 'updateEmails'])
            ->getMock();

        $_REQUEST['someKey'] = '';
        $result = $emailHandlerMock->doesPrimaryEmailExists($field, $bean, $historyDataMock);

        $this->assertEquals('', $_REQUEST['someKey']);
        $this->assertEquals(true, $result);
    }

    public function testDoesPrimaryEmailExistsInvalidField()
    {
        $field = new stdClass();
        $field->field = '';
        $field->value = '';

        $bean = new stdClass();

        $historyDataMock = new stdClass();

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['getPrimaryEmailKeyFromREQUEST', 'updateEmails'])
            ->getMock();

        $result = $emailHandlerMock->doesPrimaryEmailExists($field, $bean, $historyDataMock);

        $this->assertEquals(false, $result);
    }

    public function testGetPrimaryEmailKeyFromREQUESTInvalid()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['updateEmails'])
            ->getMock();

        $_REQUEST['emailAddress'] = 'admin@mail.com';
        $_REQUEST['Leads_email_widget_id'] = 1;

        $key = $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
        $this->assertEquals('Leads0emailAddress0', $key);
    }

    public function testGetPrimaryEmailKeyFromREQUESTValid()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['updateEmails'])
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1'] = '';
        $_REQUEST['Leads1emailAddressPrimaryFlag'] = 'primary@mail.com';

        $key = $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
        $this->assertEquals('Leads0emailAddress0', $key);
    }

    public function testGetPrimaryEmailKeyFromREQUESTValidPrimaryAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['updateEmails'])
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1'] = 'primary@mail.com';
        $_REQUEST['Leads1emailAddressPrimaryFlag'] = 'primary@mail.com';
        $_REQUEST['LeadsemailAddressPrimaryFlag'] = 'primary@mail.com';

        $key = $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
        $this->assertEquals('Leads1emailAddress1', $key);
    }

    public function testGetPrimaryEmailKeyFromREQUESTInvalidPrimaryAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['updateEmails'])
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1'] = 'primary@mail.com';
        $_REQUEST['LeadsemailAddressPrimaryFlag'] = 'primary@mail.com';

        $key = $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
        $this->assertEquals('Leads1emailAddress1', $key);
    }

    public function testGetPrimaryEmailKeyFromREQUESTInvalidAllAddresses()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['updateEmails'])
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1'] = 'primary@mail.com';

        $key = $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
        $this->assertEquals('Leads0emailAddress0', $key);
    }

    public function testUpdateEmails()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $bean->id = 'bean01';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
            ->disableOriginalConstructor()
            ->setMethods(['getAddressesByGUID'])
            ->getMock();

        $addresses = [
            'address' => [
                'primary_address' => 'address@mail.com',
                'email_address' => 'address@mail.com',
                'email_address_id' => 'address@mail.com',
            ],
        ];

        $bean->emailAddress->expects($this->once())
            ->method('getAddressesByGUID')
            ->will($this->returnValue($addresses));

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'debug', 'info', 'warning'])
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['emailAddressWidget'] = '';
        $_REQUEST['Leads1emailAddress1'] = 'primary@mail.com';

        $emailHandlerMock->setLogger($loggerMock);
        $newEmailAddress = 'new@mail.com';
        $emailHandlerMock->updateEmails($bean, $newEmailAddress);
    }

    public function testUpdateEmailsWithValidAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $bean->id = 'bean01';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
            ->disableOriginalConstructor()
            ->setMethods(['getAddressesByGUID'])
            ->getMock();

        $addresses = [
            'address' => [
                'primary_address' => 1,
                'email_address' => 'address@mail.com',
                'email_address_id' => 'address@mail.com',
            ],
        ];

        $bean->emailAddress->expects($this->once())
            ->method('getAddressesByGUID')
            ->will($this->returnValue($addresses));

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'debug', 'info', 'warning'])
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['emailAddressWidget'] = '';
        $_REQUEST['Leads1emailAddress1'] = 'primary@mail.com';

        $emailHandlerMock->setLogger($loggerMock);
        $newEmailAddress = 'new@mail.com';
        $emailHandlerMock->updateEmails($bean, $newEmailAddress);
    }

    public function testUpdateEmailsWithoutPrimaryAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $bean->id = 'bean01';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
            ->disableOriginalConstructor()
            ->setMethods(['getAddressesByGUID'])
            ->getMock();

        $addresses = [
            'address' => [
                'email_address' => 'address@mail.com',
                'email_address_id' => 'address@mail.com',
            ],
        ];

        $bean->emailAddress->expects($this->once())
            ->method('getAddressesByGUID')
            ->will($this->returnValue($addresses));

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'debug', 'info', 'warning'])
            ->getMock();

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['emailAddressWidget'] = '';
        $_REQUEST['Leads1emailAddress1'] = 'primary@mail.com';

        $emailHandlerMock->setLogger($loggerMock);
        $newEmailAddress = 'new@mail.com';
        $emailHandlerMock->updateEmails($bean, $newEmailAddress);
    }

    /**
     * Checks the Reply-To address is set from the contact info from user
     *
     * @covers ::getContactInformationFromBean
     * @param null|string $replyTo Reply-To address set to the user
     * @param bool $replyToIsSet Reply-To address is set or not
     *
     * @dataProvider contactInfoReplyToAddressProvider
     */
    public function testContactInfoReplyToAddress(?string $replyTo, bool $replyToIsSet)
    {
        $bean = SugarTestUserUtilities::createAnonymousUser();

        $primaryAddress = $bean->emailAddress->getPrimaryAddress($bean);

        $replyToAddress = $replyTo;
        SugarTestEmailAddressUtilities::createEmailAddress($replyToAddress);
        $bean->emailAddress->addAddress($replyToAddress, false, true);
        $bean->emailAddress->save($bean->id, $bean->module_dir);

        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $return = $emailHandlerMock->getContactInformationFromBean($bean, 'reply');

        if ($replyToIsSet) {
            $this->assertEquals($replyToAddress, $return['address'], "The Reply-To should be {$replyToAddress}");
        } else {
            $this->assertEquals($primaryAddress, $return['address'], "The Reply-To should be {$primaryAddress}");
        }
    }

    /**
     * Provider for ::testContactInfoReplyToAddress
     *
     * @return array
     */
    public function contactInfoReplyToAddressProvider()
    {
        return [
            ['replyTo' => 'replyTo@example.com', 'replyToIsSet' => true],
            ['replyTo' => null, 'replyToIsSet' => false],
        ];
    }
}
