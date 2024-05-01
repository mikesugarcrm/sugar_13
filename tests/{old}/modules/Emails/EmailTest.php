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

/**
 * Test cases for Bug 30591
 *
 * @coversDefaultClass Email
 */
class EmailTest extends TestCase
{
    private $email;

    protected function setUp(): void
    {
        global $current_user;

        OutboundEmailConfigurationTestHelper::setUp();
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();
        $this->email = new Email();
        $this->email->email2init();
    }

    protected function tearDown(): void
    {
        VardefManager::$linkFields = [];
        VardefManager::loadVardef('Contacts', 'Contact', true);

        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestSugarFavoriteUtilities::removeAllCreatedFavorites();
        unset($this->email);
        // SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    public function testSafeAttachmentName()
    {
        $extArray = [];
        $extArray[] = '0.py';
        $extArray[] = '1.php';
        $extArray[] = '2.php3';
        $extArray[] = '3.php4';
        $extArray[] = '4.php5';
        $extArray[] = '5.js';
        $extArray[] = '6.htm';
        $extArray[] = '7.html';
        $extArray[] = '8.txt';
        $extArray[] = '9.doc';
        $extArray[] = '10.xls';
        $extArray[] = '11.pdf';
        $extArray[] = '12';

        for ($i = 0; $i < count($extArray); $i++) {
            $result = $this->email->safeAttachmentName($extArray[$i]);
            if ($i < 8) {
                $this->assertEquals($result, true);
            } else {
                $this->assertEquals($result, false);
            }
        }
    }

    public function testEmail2ParseAddresses()
    {
        $emailDisplayName = [];
        $emailAddress = [];
        $emailString = [];
        $emailDisplayName[] = '';
        $emailDisplayName[] = 'Shine Ye';
        $emailDisplayName[] = 'Roger,Smith';
        $emailAddress[] = 'masonhu@sugarcrm.com';
        $emailAddress[] = 'xye@sugarcrm.com';
        $emailAddress[] = 'roger@sugarcrm.com';
        for ($j = 0; $j < count($emailDisplayName); $j++) {
            if ($j < 1) {
                $emailString[] = $emailDisplayName[$j] . $emailAddress[$j];
            } else {
                $emailString[] = $emailDisplayName[$j] . '<' . $emailAddress[$j] . '>';
            }
        }
        $emailAddressString = implode(', ', $emailString);
        $result = $this->email->email2ParseAddresses($emailAddressString);
        $onlyEmailResult = $this->email->email2ParseAddressesForAddressesOnly($emailAddressString);
        for ($v = 0; $v < safeCount($result); $v++) {
            $this->assertEquals($result[$v]['display'], $emailDisplayName[$v]);
            $this->assertEquals($result[$v]['email'], $emailAddress[$v]);
            $this->asserteQuals($onlyEmailResult[$v], $emailAddress[$v]);
        }
    }

    public function testEmail2ParseAddresses_ParameterIsEmpty_EmptyArrayIsReturned()
    {
        $actual = $this->email->email2ParseAddresses('');
        $this->assertCount(0, $actual, 'An empty array should have been returned.');
    }

    /**
     * @dataProvider dataProviderEmailAddressParsing
     * @param string $fullEmailAddress
     * @param string $expDisplayName
     * @param string $expEmailAddress
     */
    public function testEmail2ParseEmailAddresses($fullEmailAddress, $expDisplayName, $expEmailAddress)
    {
        $result = $this->email->email2ParseAddresses($fullEmailAddress);
        $this->assertEquals($expDisplayName, $result[0]['display'], 'Unexpected Email Display Name');
        $this->assertEquals($expEmailAddress, $result[0]['email'], 'Unexpected Email Address');
    }

    /**
     * @dataProvider dataProviderEmailAddressParsing
     * @param string $fullEmailAddress
     * @param string $expDisplayName Not used in this test.
     * @param string $expEmailAddress
     */
    public function testEmail2ParseEmailAddressesAddressOnly($fullEmailAddress, $expDisplayName, $expEmailAddress)
    {
        $result = $this->email->email2ParseAddressesForAddressesOnly($fullEmailAddress);
        $this->assertEquals($expEmailAddress, $result[0], 'Unexpected Email Address');
    }

    public function dataProviderEmailAddressParsing()
    {
        return [
            [htmlspecialchars('John Doe<john@doe.com>', ENT_COMPAT), 'John Doe', 'john@doe.com'],
            [htmlspecialchars('Jo<hn Doe<john@doe.com>', ENT_COMPAT), 'Jo<hn Doe', 'john@doe.com'],
            [htmlspecialchars('Jo>hn Doe<john@doe.com>', ENT_COMPAT), 'Jo>hn Doe', 'john@doe.com'],
            [htmlspecialchars('Jo>h<n Doe<john@doe.com>', ENT_COMPAT), 'Jo>h<n Doe', 'john@doe.com'],
            [htmlspecialchars('Jo>h<n Doe  <john@doe.com>', ENT_COMPAT), 'Jo>h<n Doe', 'john@doe.com'],
            [htmlspecialchars("Jo'h<n D\"oe  <john@doe.com>", ENT_COMPAT), "Jo'h<n D\"oe", 'john@doe.com'],
        ];
    }

    /**
     * @dataProvider providerEmail2ParseAddressesForAddressesOnly
     */
    public function testEmail2ParseAddressesForAddressesOnly($input, array $expected)
    {
        $actual = $this->email->email2ParseAddressesForAddressesOnly($input);
        $this->assertSame($expected, $actual);
    }

    public function providerEmail2ParseAddressesForAddressesOnly(): iterable
    {
        yield [[htmlspecialchars('te>s<t Test  <test@test.com>', ENT_COMPAT)], ['test@test.com']];
        yield [['a@b.c', 'c@d.e'], ['a@b.c', 'c@d.e']];
        yield [['a@b.c', 'c@d.e', 'ABC<a@b.c>', ''], ['a@b.c', 'c@d.e']];
    }

    public function testDecodeDuringSend()
    {
        $testString = 'Replace sugarLessThan and sugarGreaterThan with &lt; and &gt;';
        $expectedResult = 'Replace &lt; and &gt; with &lt; and &gt;';
        $resultString = $this->email->decodeDuringSend($testString);
        $this->asserteQuals($resultString, $expectedResult);
    }

    public function configParamProvider()
    {
        $address_array = [
            'id1' => 'test1@example.com',
            'id2' => 'test2@example.com',
            'id3' => 'test3@example.com',
        ];

        return [
            [',', $address_array, 'test1@example.com,test2@example.com,test3@example.com'], // default and correct delimiter for email addresses
            [';', $address_array, 'test1@example.com;test2@example.com;test3@example.com'], // outlook's delimiter for email addresses
        ];
    }

    /**
     * @group email
     * @group mailer
     */
    public function testEmailSend_Success()
    {
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
        $mockMailer = new MockMailer($config);
        MockMailerFactory::setMailer($mockMailer);

        $em = new Email();
        $em->email2init();
        $em->_setMailerFactoryClassName('MockMailerFactory');

        $em->name = 'This is the Subject';
        $em->description_html = 'This is the HTML Description';
        $em->description = 'This is the Text Description';

        $from = new EmailIdentity('twolf@sugarcrm.com', 'Tim Wolf');
        $replyto = $from;
        $to = new EmailIdentity('twolf@sugarcrm.com', 'Tim Wolf');
        $cc = new EmailIdentity('twolf@sugarcrm.com', 'Tim Wolf');

        $em->from_addr = $from->getEmail();
        $em->from_name = $from->getName();

        $em->reply_to_addr = $replyto->getEmail();
        $em->reply_to_name = $replyto->getName();

        $em->to_addrs_arr = [
            [
                'email' => $to->getEmail(),
                'display' => $to->getName(),
            ],
            [ // no warnings should appear in case of invalid input
                0,
            ],
        ];
        $em->cc_addrs_arr = [
            [
                'email' => $cc->getEmail(),
                'display' => $cc->getName(),
            ],
            [ // no warnings should appear in case of invalid input
                0,
            ],
        ];

        $em->send();

        $data = $mockMailer->toArray();
        $this->assertEquals($em->description_html, $data['htmlBody']);
        $this->assertEquals($em->description, $data['textBody']);

        $headers = $mockMailer->getHeaders();
        $this->assertEquals($em->name, $headers['Subject']);
        $this->assertEquals($from->getEmail(), $headers['From'][0]);
        $this->assertEquals($from->getName(), $headers['From'][1]);
        $this->assertEquals($replyto->getEmail(), $headers['Reply-To'][0]);
        $this->assertEquals($replyto->getName(), $headers['Reply-To'][1]);

        $this->assertEquals($from->getEmail(), $headers['Sender']);

        $recipients = $mockMailer->getRecipients();

        $actual_to = array_values($recipients['to']);
        $this->assertEquals($to->getEmail(), $actual_to[0]->getEmail(), 'TO Email Address Incorrect');
        $this->assertEquals($to->getName(), $actual_to[0]->getName(), 'TO Name Incorrect');

        $actual_cc = array_values($recipients['cc']);
        $this->assertEquals($to->getEmail(), $actual_cc[0]->getEmail(), 'CC Email Address Incorrect');
        $this->assertEquals($to->getName(), $actual_cc[0]->getName(), 'CC Name Incorrect');

        $this->assertEquals(true, $mockMailer->wasSent());
    }

    /**
     * @group bug51804
     * @dataProvider configParamProvider
     * @param string $config_param
     * @param array $address_array
     * @param string $expected
     */
    public function testArrayToDelimitedString($config_param, $address_array, $expected)
    {
        $GLOBALS['sugar_config']['email_address_separator'] = $config_param;

        $this->assertEquals($expected, $this->email->_arrayToDelimitedString($address_array), 'Array should be delimited with correct delimiter');
    }

    /**
     * @covers ::getOutboundEmailDropdown
     */
    public function testGetOutboundEmailDropdown()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        SugarTestHelper::setUp('current_user');
        $systemConfig = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        $overrideConfig = $systemConfig->getUsersMailerForSystemOverride($GLOBALS['current_user']->id);
        $overrideConfig->mail_smtpuser = $GLOBALS['current_user']->last_name;
        $overrideConfig->mail_smtppass = 'h1kdkdKKahsd73';
        $overrideConfig->save();

        // Create the test OutboundEmail accounts
        $userConfigs = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(3);

        // One account is not configured
        $notConfigured = $userConfigs[0]['outbound'];
        $notConfigured->mail_smtpuser = '';
        $notConfigured->save();

        // One account is favorited
        $favorited = $userConfigs[1]['outbound'];
        SugarTestSugarFavoriteUtilities::favoriteBean($favorited);

        // One account is not favorited, but is marked as preferred
        $preferred = $userConfigs[2]['outbound'];
        $preferred->preferred_sending_account = true;
        $preferred->save();

        $email = new Email();
        $options = $email->getOutboundEmailDropdown();
        $optionKeys = array_keys($options);

        $this->assertCount(3, $options, 'There should be three options');
        $this->assertArrayNotHasKey($notConfigured->id, $options, 'Should not include configs that are not configured');
        $this->assertSame($favorited->id, $optionKeys[0], 'The favorited email config should be the first option');
        $this->assertSame($preferred->id, $optionKeys[1], 'The preferred email config should be the second option');
        $this->assertSame($overrideConfig->id, $optionKeys[2], 'The normal email config should be the third option');
    }

    /**
     * @covers ::getOutboundEmailDropdown
     */
    public function testGetOutboundEmailDropdown_SystemOverrideIsNotConfigured()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        SugarTestHelper::setUp('current_user');
        $systemConfig = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        $overrideConfig = $systemConfig->getUsersMailerForSystemOverride($GLOBALS['current_user']->id);
        $overrideConfig->mail_smtpuser = '';
        $overrideConfig->save();

        $email = new Email();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $email->getOutboundEmailDropdown();
    }

    /**
     * @covers ::getOutboundEmailDropdown
     */
    public function testGetOutboundEmailDropdown_SystemIsNotConfigured()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);

        SugarTestHelper::setUp('current_user');
        $systemConfig = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        $systemConfig->mail_smtpuser = '';
        $systemConfig->save();

        $email = new Email();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $email->getOutboundEmailDropdown();
    }

    /**
     * @covers ::save
     * @covers ::updateAttachmentsVisibility
     * @covers ::updateAttachmentVisibility
     * @covers Note::save
     */
    public function testUpdateAttachmentsVisibility()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $data = [
            'email_type' => 'Emails',
            'email_id' => $email->id,
        ];
        $note1 = SugarTestNoteUtilities::createNote('', $data);
        $note2 = SugarTestNoteUtilities::createNote('', $data);

        // Change the teams on the email.
        $teams = BeanFactory::getBean('TeamSets');
        $email->state = Email::STATE_ARCHIVED;
        $email->assigned_user_id = $GLOBALS['current_user']->id;
        $email->team_id = 'East';
        $email->team_set_id = $teams->addTeams(['East', 'West']);
        $email->team_set_selected_id = 'East';
        $email->save();

        $this->assertEquals(
            $email->assigned_user_id,
            $note1->assigned_user_id,
            'note1.assigned_user_id does not match'
        );
        $this->assertEquals(
            $email->assigned_user_id,
            $note2->assigned_user_id,
            'note2.assigned_user_id does not match'
        );
        $this->assertEquals($email->team_set_id, $note1->team_set_id, 'note1.team_set_id does not match');
        $this->assertEquals($email->team_set_id, $note2->team_set_id, 'note2.team_set_id does not match');
        $this->assertEquals($email->team_id, $note1->team_id, 'note1.team_id does not match');
        $this->assertEquals($email->team_id, $note2->team_id, 'note2.team_id does not match');
        $this->assertEquals(
            $email->team_set_selected_id,
            $note1->team_set_selected_id,
            'note1.team_set_selected_id does not match'
        );
        $this->assertEquals(
            $email->team_set_selected_id,
            $note2->team_set_selected_id,
            'note2.team_set_selected_id does not match'
        );
    }

    /**
     * @covers ::updateAttachmentVisibility
     */
    public function testUpdateAttachmentVisibility_EmailIsADraft_TeamIsPrivateTeamOfAssignedUser()
    {
        $note = $this->getMockBuilder('Note')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $note->expects($this->once())->method('save');

        $email = BeanFactory::newBean('Emails');
        $email->state = Email::STATE_DRAFT;
        $email->assigned_user_id = $GLOBALS['current_user']->id;
        $email->team_id = 'East';
        $email->team_set_id = Uuid::uuid1();
        $email->team_set_selected_id = 'East';
        $email->updateAttachmentVisibility($note);

        $this->assertEquals($email->assigned_user_id, $note->assigned_user_id, 'assigned_user_id does not match');
        $expected = $GLOBALS['current_user']->getPrivateTeam();
        $this->assertEquals($expected, $note->team_set_id, 'team_set_id does not match');
        $this->assertEquals($expected, $note->team_id, 'team_id does not match');
        $this->assertEquals($expected, $note->team_set_selected_id, 'team_set_selected_id does not match');
    }

    /**
     * @covers ::updateAttachmentVisibility
     */
    public function testUpdateAttachmentVisibility_EmailIsADraft_NoAssignedUser()
    {
        $assignedUserId = Uuid::uuid1();
        $teamSetId = Uuid::uuid1();
        $note = $this->getMockBuilder('Note')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $note->expects($this->never())->method('save');
        $note->assigned_user_id = $assignedUserId;
        $note->team_id = 'West';
        $note->team_set_id = $teamSetId;
        $note->team_set_selected_id = 'West';

        $email = BeanFactory::newBean('Emails');
        $email->state = Email::STATE_DRAFT;
        $email->assigned_user_id = null;
        $email->team_id = 'East';
        $email->team_set_id = Uuid::uuid1();
        $email->team_set_selected_id = 'East';
        $email->updateAttachmentVisibility($note);

        $this->assertEquals($assignedUserId, $note->assigned_user_id, 'assigned_user_id should not have changed');
        $this->assertEquals($teamSetId, $note->team_set_id, 'team_set_id should not have changed');
        $this->assertEquals('West', $note->team_id, 'team_id should not have changed');
        $this->assertEquals('West', $note->team_set_selected_id, 'team_set_selected_id should not have changed');
    }

    /**
     * @covers ::save
     * @covers ::updateAttachmentsVisibility
     * @covers ::updateAttachmentVisibility
     * @covers Note::save
     */
    public function testUpdateAttachmentsVisibility_ArchivingADraftSynchronizesTeams()
    {
        $teams = BeanFactory::getBean('TeamSets');

        $data = [
            'state' => Email::STATE_DRAFT,
            'team_id' => 'East',
            'team_set_id' => $teams->addTeams(['East', 'West']),
            'team_set_selected_id' => 'East',
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);

        $data = [
            'email_type' => 'Emails',
            'email_id' => $email->id,
        ];
        $note = SugarTestNoteUtilities::createNote('', $data);

        $expected = $GLOBALS['current_user']->getPrivateTeam();
        $this->assertEquals($expected, $note->team_set_id, 'team_set_id should be the private team');
        $this->assertEquals($expected, $note->team_id, 'team_id should be the private team');
        $this->assertEquals($expected, $note->team_set_selected_id, 'team_set_selected_id should be the private team');

        // Archive the email.
        $email->state = Email::STATE_ARCHIVED;
        $email->save();

        $this->assertEquals($email->assigned_user_id, $note->assigned_user_id, 'assigned_user_id does not match');
        $this->assertEquals($email->team_set_id, $note->team_set_id, 'team_set_id does not match');
        $this->assertEquals($email->team_id, $note->team_id, 'team_id does not match');
        $this->assertEquals(
            $email->team_set_selected_id,
            $note->team_set_selected_id,
            'team_set_selected_id does not match'
        );
    }

    /**
     * @covers ::getParticipants
     */
    public function testGetParticipants()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $email->load_relationship('to');

        $link = function ($parent) use ($email) {
            $ep = BeanFactory::newBean('EmailParticipants');
            $ep->new_with_id = true;
            $ep->id = Uuid::uuid1();
            BeanFactory::registerBean($ep);
            $ep->parent_type = $parent->getModuleName();
            $ep->parent_id = $parent->id;
            $email->to->add($ep);
        };

        $accounts = 6;
        $contacts = 3;
        $leads = 2;
        $prospects = 3;
        $addresses = 1;

        for ($i = 0; $i < $accounts; $i++) {
            $account = SugarTestAccountUtilities::createAccount();
            $link($account);
        }

        for ($i = 0; $i < $contacts; $i++) {
            $contact = SugarTestContactUtilities::createContact();
            $link($contact);
        }

        for ($i = 0; $i < $leads; $i++) {
            $lead = SugarTestLeadUtilities::createLead();
            $link($lead);
        }

        for ($i = 0; $i < $prospects; $i++) {
            $prospect = SugarTestProspectUtilities::createProspect();
            $link($prospect);
        }

        for ($i = 0; $i < $addresses; $i++) {
            $address = SugarTestEmailAddressUtilities::createEmailAddress();
            $ep = BeanFactory::newBean('EmailParticipants');
            $ep->new_with_id = true;
            $ep->id = Uuid::uuid1();
            BeanFactory::registerBean($ep);
            $ep->email_address_id = $address->id;
            $email->to->add($ep);
        }

        $participants = SugarTestReflection::callProtectedMethod($email, 'getParticipants', ['to']);
        $accountsParticipants = array_filter($participants, function ($participant) {
            return $participant->parent_type === 'Accounts';
        });
        $contactsParticipants = array_filter($participants, function ($participant) {
            return $participant->parent_type === 'Contacts';
        });
        $leadsParticipants = array_filter($participants, function ($participant) {
            return $participant->parent_type === 'Leads';
        });
        $prospectsParticipants = array_filter($participants, function ($participant) {
            return $participant->parent_type === 'Prospects';
        });
        $addressesParticipants = array_filter($participants, function ($participant) {
            return empty($participant->parent_type);
        });


        $this->assertCount($accounts, $accountsParticipants, 'Incorrect number of accounts');
        $this->assertCount($contacts, $contactsParticipants, 'Incorrect number of contacts');
        $this->assertCount($leads, $leadsParticipants, 'Incorrect number of leads');
        $this->assertCount($prospects, $prospectsParticipants, 'Incorrect number of prospects');
        $this->assertCount($addresses, $addressesParticipants, 'Incorrect number of email addresses');
    }

    /**
     * This test proves that the `total_attachments` field increases and decreases when attachments are linked and
     * unlinked, respectively.
     */
    public function testTotalAttachments()
    {
        $this->email->state = Email::STATE_DRAFT;
        $this->email->save();
        SugarTestEmailUtilities::setCreatedEmail($this->email->id);

        $this->assertSame(0, $this->email->total_attachments, 'Should not have any attachments yet');

        $note = SugarTestNoteUtilities::createNote();
        $this->email->load_relationship('attachments');
        $this->email->attachments->add($note);

        $this->assertSame(1, $this->email->total_attachments, 'Should have incremented the count');

        // While unlinking an attachment, `One2MBeanRelationship::remove` triggers the `after_relationship_delete`
        // event. `SugarBean::call_custom_logic` is ultimately called, which calls `SugarBean::updateRelatedCalcFields`
        // because the event is `after_relationship_delete`. The `attachments` link is associated with a calculated
        // field, so the email is added to `SugarRelationship::$resaveQueue`. So the email is not saved and therefore
        // the `total_attachments` count is not updated when an attachment is unlinked until
        // `SugarRelationship::resaveRelatedBeans` is called, which `RelateRecordApi::deleteRelatedLink` does already.
        $this->email->attachments->delete($this->email->id, $note);
        SugarRelationship::resaveRelatedBeans();

        $this->assertSame(0, $this->email->total_attachments, 'Should have decremented the count');
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail()
    {
        // Cache a consistent now() for some time prior to when the email will be sent.
        $td = TimeDate::getInstance();
        $tz = new DateTimeZone('UTC');
        $now = SugarDateTime::createFromFormat(TimeDate::DB_DATETIME_FORMAT, '2016-08-13 07:33:00', $tz);
        $td->setNow($now);

        // Need a configuration to use to send the email, even when faking the send.
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);

        $contact = SugarTestContactUtilities::createContact();

        // Create a draft that will be sent.
        $data = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => $config->getConfigId(),
            'name' => 'Welcome $contact_first_name',
            'description_html' => 'Hello <b>$contact_first_name</b>',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);

        // Send to the contact.
        $email->load_relationship('to');
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        BeanFactory::registerBean($ep);
        $ep->parent_type = 'Contacts';
        $ep->parent_id = $contact->id;
        $email->to->add($ep);

        // Change the value of now() to simulate the send occurring some time after the draft was first created.
        $now = SugarDateTime::createFromFormat(TimeDate::DB_DATETIME_FORMAT, '2016-08-14 09:19:00', $tz);
        $td->setNow($now);
        $now = $td->getNow()->format('Y-m-d h:i:s');

        // Send the email.
        $email->sendEmail($config);

        $this->assertSame(Email::STATE_ARCHIVED, $email->state, 'Should be archived');
        $this->assertSame($now, $email->date_sent, 'Should reflect the date/time that the email was sent');
        $this->assertSame('out', $email->type, 'Should be out');
        $this->assertSame('sent', $email->status, 'Should be sent');
        $this->assertNotEmpty($email->message_id, 'Should have a Message-ID');
        $this->assertSame("Welcome {$contact->first_name}", $email->name, 'Incorrect subject');

        $email->retrieveEmailText();
        $this->assertSame("Hello <b>{$contact->first_name}</b>", $email->description_html, 'Incorrect HTML part');
        $this->assertSame("Hello {$contact->first_name}", $email->description, 'Incorrect text part');
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);

        // Restore the environment.
        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testArchiveEmailContent()
    {
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
        $contact = SugarTestContactUtilities::createContact();

        // Create a draft email
        $data = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => $config->getConfigId(),
            'name' => 'Welcome $contact_first_name',
            'description_html' => 'Hello <b>TEST</b>',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);

        $q = "SELECT description_html FROM emails_text WHERE email_id='{$email->id}'";
        $savedValue = DBManagerFactory::getInstance()->getOne($q);

        $this->assertEquals('80jNyclXsEmyC3ENDrHRT7IDAA==', $savedValue);

        $email = BeanFactory::getBean('Emails', $email->id);
        $this->assertEquals('Hello &lt;b&gt;TEST&lt;/b&gt;', $email->description_html);
    }

    public function theCurrentUserIsTheSenderForDraftsProvider()
    {
        return [
            // No outbound_email_id.
            [null],
            // The outbound_email_id doesn't exist.
            [\Sugarcrm\Sugarcrm\Util\Uuid::uuid1()],
        ];
    }

    /**
     * @covers ::save
     * @dataProvider theCurrentUserIsTheSenderForDraftsProvider
     * @param $outboundEmailId
     */
    public function testSave_TheCurrentUserIsTheSenderForDrafts($outboundEmailId)
    {
        $email = BeanFactory::newBean('Emails');
        $email->name = 'some subject';
        $email->state = Email::STATE_DRAFT;
        $email->outbound_email_id = $outboundEmailId;
        $email->save();
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $q = "SELECT * FROM emails_email_addr_rel WHERE email_id='{$email->id}' AND address_type='from' AND deleted=0";
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne($q);

        $this->assertSame('Users', $row['parent_type'], 'Should be Users');
        $this->assertSame(trim($GLOBALS['current_user']->id), trim($row['parent_id']), 'Should be the current user');
        $this->assertEmpty($row['email_address_id'], 'Should not have recorded an email address for the sender');
    }

    /**
     * @covers ::save
     * @covers ::saveEmailAddresses
     * @covers ::linkEmailToAddress
     * @covers ::saveEmailText
     * @covers ::retrieveEmailText
     */
    public function testSave_WillLinkEmailAddressesAndRecalculateEmailsText()
    {
        $data = [
            'from_addr' => 'sam@example.com',
            'from_addr_name' => '"Sam Rooker" <sam@example.com>',
            'to_addrs' => 'tom@example.com,wendy@example.com',
            'to_addrs_names' => 'Tom Hammond <tom@example.com>, "Wendy Towns" <wendy@example.com>',
            'cc_addrs' => 'randy@example.com',
            'cc_addrs_names' => '"Randy Ulman" <randy@example.com>',
            'bcc_addrs' => 'bonnie@example.com;tara@example.com,bill@example.com',
            'bcc_addrs_names' => '"Bonnie Vickers" <bonnie@example.com>;tara@example.com,bill@example.com',
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);

        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('sam@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('tom@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('wendy@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('randy@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('bonnie@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('tara@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('bill@example.com');

        $email->retrieveEmailText();
        $this->assertEmailAddresses(['sam@example.com'], $email->from_addr_name);
        $this->assertEmailAddresses(['tom@example.com', 'wendy@example.com'], $email->to_addrs_names);
        $this->assertEmailAddresses(['randy@example.com'], $email->cc_addrs_names);
        $this->assertEmailAddresses(
            ['bill@example.com', 'bonnie@example.com', 'tara@example.com'],
            $email->bcc_addrs_names
        );
    }

    /**
     * @covers ::save
     * @covers ::linkParentBeanUsingRelationship
     * @covers ::findEmailsLink
     */
    public function testSave_WillLinkTheParentContact()
    {
        $contact = SugarTestContactUtilities::createContact();
        $email = SugarTestEmailUtilities::createEmail();

        $email->parent_type = 'Contacts';
        $email->parent_id = $contact->id;
        $email->save();

        $contacts = $email->get_linked_beans('contacts', 'Contact');

        $this->assertCount(1, $contacts);
        $this->assertSame($contact->id, $contacts[0]->id);
    }

    /**
     * @covers ::linkEmailToAddress
     */
    public function testLinkEmailToAddress()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();

        // Link a new email address.
        $rowId = $email->linkEmailToAddress($address1->id, 'to');
        $this->assertNotEmpty(
            $rowId,
            "Should have returned the ID of the new row for address_type=to and email_address_id={$address1->id}"
        );

        // Link the same email address with the same address_type.
        $this->assertSame(
            $rowId,
            $email->linkEmailToAddress($address1->id, 'to'),
            "Should have returned the ID of the existing row for address_type=to and email_address_id={$address1->id}"
        );

        // Link the same email address with a different address_type.
        $this->assertNotSame(
            $rowId,
            $email->linkEmailToAddress($address1->id, 'cc'),
            "Should have returned the ID of the new row for address_type=cc and email_address_id={$address1->id}"
        );

        // Link a different email address.
        $this->assertNotEquals(
            $rowId,
            $email->linkEmailToAddress($address2->id, 'to'),
            "Should have returned the ID of the new row for address_type=to and email_address_id={$address2->id}"
        );

        // The email address is invalid.
        $this->assertEmpty(
            $email->linkEmailToAddress(Uuid::uuid1(), 'to'),
            'Should not have been able to load the email address'
        );

        // Link an email address that is already linked via multiple records.
        $contact = SugarTestContactUtilities::createContact();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address2);
        $email->load_relationship('bcc');
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        BeanFactory::registerBean($ep);
        $ep->parent_type = $contact->getModuleName();
        $ep->parent_id = $contact->id;
        $ep->email_address_id = $address2->id;
        $email->bcc->add($ep);

        $lead = SugarTestLeadUtilities::createLead();
        SugarTestEmailAddressUtilities::addAddressToPerson($lead, $address2);
        $email->load_relationship('bcc');
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        BeanFactory::registerBean($ep);
        $ep->parent_type = $lead->getModuleName();
        $ep->parent_id = $lead->id;
        $ep->email_address_id = $address2->id;
        $email->bcc->add($ep);

        $this->assertNotEmpty(
            $email->linkEmailToAddress($address2->id, 'bcc'),
            "Should have returned the ID of one of the rows for address_type=bcc and email_address_id={$address1->id}"
        );

        // Fails to link the email address and there are no existing rows for that email address and address_type.
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        BeanFactory::registerBean($ep);
        $ep->email_address_id = $address1->id;
        $link = $this->createPartialMock('Link2', ['add']);
        $link->method('add')->willReturn([$ep->id]);
        $email->from = $link;
        $this->assertEmpty($email->linkEmailToAddress($address1->id, 'from'), 'Should have returned nothing');

        // Need to remove the mock to avoid failures when SugarRelationship::resaveRelatedBeans() is called.
        unset($email->from);
    }

    /**
     * @covers ::retrieve
     * @covers ::loadAdditionalEmailData
     * @covers ::retrieveEmailText
     * @covers ::retrieveEmailAddresses
     * @covers ::synchronizeEmailParticipants
     */
    public function testRetrieveAndSynchronizeEmailParticipants()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);

        // The emails_text table should have data but not emails_email_addr_rel.
        $sql = "UPDATE emails_text SET from_addr='Norman Jones <norman@example.com>', " .
            "to_addrs='Rick Johns <rick@example.com>, Stacy Towns <stacy@example.com>', " .
            "cc_addrs='Betsy Williams <betsy@example.com>', " .
            "bcc_addrs='Jake Marr <jake@example.com>, frannie@example.com, paul@example.com' " .
            "WHERE email_id='{$email->id}'";
        $email->db->query($sql);

        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('norman@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('rick@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('stacy@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('betsy@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('jake@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('frannie@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('paul@example.com');

        // Verify the data in the emails_text table.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(['Norman Jones <norman@example.com>'], $email->from_addr_name);
        $this->assertEmailAddresses(
            ['Rick Johns <rick@example.com>', 'Stacy Towns <stacy@example.com>'],
            $email->to_addrs_names
        );
        $this->assertEmailAddresses(['Betsy Williams <betsy@example.com>'], $email->cc_addrs_names);
        $this->assertEmailAddresses(
            ['Jake Marr <jake@example.com>', 'frannie@example.com', 'paul@example.com'],
            $email->bcc_addrs_names
        );

        // Verify there is no data in the emails_email_addr_rel table.
        $email->retrieveEmailAddresses();
        $this->assertEmpty($email->from_addr);
        $this->assertEmpty($email->to_addrs);
        $this->assertEmpty($email->cc_addrs);
        $this->assertEmpty($email->bcc_addrs);

        // Trigger the synchronization to emails_email_addr_rel.
        $email->retrieve();

        // Verify the data in the emails_text table again. After synchronization, only the email addresses remain.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(['norman@example.com'], $email->from_addr_name);
        $this->assertEmailAddresses(['rick@example.com', 'stacy@example.com'], $email->to_addrs_names);
        $this->assertEmailAddresses(['betsy@example.com'], $email->cc_addrs_names);
        $this->assertEmailAddresses(
            ['jake@example.com', 'frannie@example.com', 'paul@example.com'],
            $email->bcc_addrs_names
        );

        // Verify the data in the emails_email_addr_rel table.
        $email->retrieveEmailAddresses();
        $this->assertEmailAddresses(['norman@example.com'], $email->from_addr);
        $this->assertEmailAddresses(['rick@example.com', 'stacy@example.com'], $email->to_addrs);
        $this->assertEmailAddresses(['betsy@example.com'], $email->cc_addrs);
        $this->assertEmailAddresses(
            ['jake@example.com', 'frannie@example.com', 'paul@example.com'],
            $email->bcc_addrs
        );

        // Change something and resave the email to show that no data is lost on save.
        $email->assigned_user_id = Uuid::uuid1();
        $email->save();

        // Verify the data in the emails_text table once more.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(['norman@example.com'], $email->from_addr_name);
        $this->assertEmailAddresses(['rick@example.com', 'stacy@example.com'], $email->to_addrs_names);
        $this->assertEmailAddresses(['betsy@example.com'], $email->cc_addrs_names);
        $this->assertEmailAddresses(
            ['jake@example.com', 'frannie@example.com', 'paul@example.com'],
            $email->bcc_addrs_names
        );

        // Verify the data in the emails_email_addr_rel table again.
        $email->retrieveEmailAddresses();
        $this->assertEmailAddresses(['norman@example.com'], $email->from_addr);
        $this->assertEmailAddresses(['rick@example.com', 'stacy@example.com'], $email->to_addrs);
        $this->assertEmailAddresses(['betsy@example.com'], $email->cc_addrs);
        $this->assertEmailAddresses(
            ['jake@example.com', 'frannie@example.com', 'paul@example.com'],
            $email->bcc_addrs
        );
    }

    /**
     * @covers ::retrieve
     * @covers ::loadAdditionalEmailData
     * @covers ::retrieveEmailText
     * @covers ::retrieveEmailAddresses
     * @covers ::synchronizeEmailParticipants
     */
    public function testRetrieveAndSynchronizeEmailParticipants_SenderOrRecipientFieldsAlreadyWithDataAreLeftUntouched()
    {
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'to_addrs' => 'lacy@example.com',
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);

        // emails_text.from_addr should have data that emails_email_addr_rel does not have. emails_text.to_addrs should
        // have data that does not match what emails_email_addr_rel has for address_type=to.
        $sql = "UPDATE emails_text SET from_addr='Dillon Trace <dillon@example.com>', " .
            "to_addrs='Vance Martin <vance@example.com>, Alice Chapman <alice@example.com>' " .
            "WHERE email_id='{$email->id}'";
        $email->db->query($sql);

        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('dillon@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('lacy@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('vance@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress('alice@example.com');

        // Verify the data in the emails_text table.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(['Dillon Trace <dillon@example.com>'], $email->from_addr_name);
        $this->assertEmailAddresses(
            ['Vance Martin <vance@example.com>', 'Alice Chapman <alice@example.com>'],
            $email->to_addrs_names
        );

        // Verify there is no sender in the emails_email_addr_rel table.
        $email->retrieveEmailAddresses();
        $this->assertEmpty($email->from_addr);
        $this->assertEmailAddresses(['lacy@example.com'], $email->to_addrs);

        // Trigger the synchronization to emails_email_addr_rel.
        $email->retrieve();

        // Verify the data in the emails_text table again.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(['dillon@example.com'], $email->from_addr_name);
        $this->assertEmailAddresses(['lacy@example.com'], $email->to_addrs_names);

        // Verify the data in the emails_email_addr_rel table.
        $email->retrieveEmailAddresses();
        $this->assertEmailAddresses(['dillon@example.com'], $email->from_addr);
        $this->assertEmailAddresses(['lacy@example.com'], $email->to_addrs);

        // Change something and resave the email to show that no data is lost on save.
        $email->assigned_user_id = Uuid::uuid1();
        $email->save();

        // emails_text.to_addrs should reflect the data in emails_email_addr_rel after saving.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(['dillon@example.com'], $email->from_addr_name);
        $this->assertEmailAddresses(['lacy@example.com'], $email->to_addrs_names);

        // Verify the data in the emails_email_addr_rel table again.
        $email->retrieveEmailAddresses();
        $this->assertEmailAddresses(['dillon@example.com'], $email->from_addr);
        $this->assertEmailAddresses(['lacy@example.com'], $email->to_addrs);
    }

    /**
     * @covers ::retrieve
     * @covers ::loadAdditionalEmailData
     * @covers ::retrieveEmailText
     * @covers ::retrieveEmailAddresses
     * @covers ::synchronizeEmailParticipants
     */
    public function testRetrieveAndSynchronizeEmailParticipants_ParticipantsWithoutEmailAddressAreLeftUntouched()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);

        // This recipient will be stored without an email address selected since it is a draft and no email address is
        // defined. emails_text.to_addrs will include an email address along with the contact's name.
        $contact = SugarTestContactUtilities::createContact();
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        BeanFactory::registerBean($ep);
        $ep->parent_type = $contact->getModuleName();
        $ep->parent_id = $contact->id;
        $email->load_relationship('to');
        $email->to->add($ep);

        // Verify the data in the emails_text table.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(["{$contact->name} <{$contact->email1}>"], $email->to_addrs_names);

        // Verify there is no recorded email address in the emails_email_addr_rel table for the recipient.
        $email->retrieveEmailAddresses();
        $this->assertEmpty($email->to_addrs);

        // Trigger the synchronization to emails_email_addr_rel.
        $email->retrieve();

        // Verify the data in the emails_text table again.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(["{$contact->name} <{$contact->email1}>"], $email->to_addrs_names);

        // Verify the data in the emails_email_addr_rel table again.
        $email->retrieveEmailAddresses();
        $this->assertEmpty($email->to_addrs);

        // Change something and resave the email to show that no data is lost on save.
        $email->description_html = '<p>foo bar</p>';
        $email->save();

        // emails_text.to_addrs should reflect the data in emails_email_addr_rel after saving.
        $email->retrieveEmailText();
        $this->assertEmailAddresses(["{$contact->name} <{$contact->email1}>"], $email->to_addrs_names);

        // Verify the data in the emails_email_addr_rel table once more.
        $email->retrieveEmailAddresses();
        $this->assertEmpty($email->to_addrs);
    }

    public function findEmailsLinkProvider()
    {
        return [
            'module has emails link' => [
                [
                    'emails' => [
                        'name' => 'emails',
                        'type' => 'link',
                        'relationship' => 'emails_contacts_rel',
                        'source' => 'non-db',
                        'vname' => 'LBL_EMAILS',
                    ],
                ],
                'emails',
            ],
            'activities relationship added via studio' => [
                [
                    'contacts_activities_1_emails' => [
                        'name' => 'contacts_activities_1_emails',
                        'type' => 'link',
                        'relationship' => 'contacts_activities_1_emails',
                        'source' => 'non-db',
                        'module' => 'Emails',
                        'bean_name' => 'Email',
                        'vname' => 'LBL_CONTACTS_ACTIVITIES_1_EMAILS_FROM_EMAILS_TITLE',
                    ],
                ],
                'contacts_activities_1_emails',
            ],
            'activities relationship added via module builder' => [
                [
                    'contacts_activities_emails' => [
                        'name' => 'contacts_activities_emails',
                        'type' => 'link',
                        'relationship' => 'contacts_activities_emails',
                        'source' => 'non-db',
                        'module' => 'Emails',
                        'bean_name' => 'Email',
                        'vname' => 'LBL_CONTACTS_ACTIVITIES_EMAILS_FROM_EMAILS_TITLE',
                    ],
                ],
                'contacts_activities_emails',
            ],
            'no link to emails' => [
                [],
                false,
            ],
        ];
    }

    /**
     * @dataProvider findEmailsLinkProvider
     * @covers ::findEmailsLink
     * @param array $fieldDefs
     * @param bool|string $expected
     */
    public function testFindEmailsLink(array $fieldDefs, $expected)
    {
        $email = BeanFactory::newBean('Emails');
        $contact = BeanFactory::newBean('Contacts');

        $GLOBALS['dictionary']['Contact']['fields'] = $fieldDefs;
        VardefManager::$linkFields = [];

        $actual = $email->findEmailsLink($contact);

        $this->assertSame($expected, $actual);
    }

    public function handleCaseAssignmentProvider()
    {
        return [
            [
                'shouldAssign' => false,
                'caseId' => 'abc123',
                'expected' => false,
            ],
            [
                'shouldAssign' => true,
                'caseId' => null,
                'expected' => false,
            ],
            [
                'shouldAssign' => true,
                'caseId' => 'abc123',
                'expected' => true,
            ],
        ];
    }

    /**
     * @covers ::handleCaseAssignment
     * @dataProvider handleCaseAssignmentProvider
     */
    public function testHandleCaseAssignment($shouldAssign, $caseId, $expected)
    {
        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['shouldPerformCaseAssignment', 'getCaseIdFromCaseNumber'])
            ->getMock();
        $email->method('shouldPerformCaseAssignment')->willReturn($shouldAssign);
        $email->method('getCaseIdFromCaseNumber')->willReturn($caseId);
        $email->name = 'Test Email Name';
        $case = null;
        if ($caseId) {
            SugarTestCaseUtilities::createCase($id = $caseId);
        }
        $actual = SugarTestReflection::callProtectedMethod($email, 'handleCaseAssignment');
        $this->assertEquals($expected, $actual);
        if ($expected) {
            $this->assertEquals('Cases', $email->parent_type);
            $this->assertEquals($caseId, $email->parent_id);
        }
    }

    /**
     * DataProvider for testCleanEmails
     */
    public function providerCleanEmails()
    {
        yield ['test@test.net', 'test@test.net'];
        yield ['test@test.net,test2@test.net', 'test@test.net, test2@test.net'];
        yield ['test@test.net;test2@test.net', 'test@test.net, test2@test.net'];
        yield ['test@test.net;test2@test.net,test3@test.net', 'test@test.net, test2@test.net, test3@test.net'];
        yield [
            [
                'test@test.net',
                'test2@test.net',
            ], 'test@test.net, test2@test.net'];
        yield [
            [
                'test@test.net;test2@test.net',
                'test3@test.net',
            ], 'test@test.net, test2@test.net, test3@test.net'];
        yield [
            ['test@test.net;test2@test.net,test3@test.net'], 'test@test.net, test2@test.net, test3@test.net'];
    }

    /**
     * @dataProvider providerCleanEmails
     * @covers ::cleanEmails
     */
    public function testCleanEmails($emails, $expected)
    {
        $email = new Email();
        $result = $email->cleanEmails($emails);
        $this->assertEquals($expected, $result);
    }

    /**
     * Splits a comma-separated list of email addresses and returns them as an array.
     *
     * @param string $emailAddrs
     * @return array
     */
    private function emailAddrsToArray($emailAddrs)
    {
        $emailAddresses = [];
        $temp = explode(', ', $emailAddrs);
        foreach ($temp as $emailAddr) {
            $emailAddresses[] = $emailAddr;
        }

        return $emailAddresses;
    }

    /**
     * Asserts that all of the expected email addresses are found in the value, regardless of their order.
     *
     * @param array $expected Expected email addresses.
     * @param string $value Comma-separated list of email addresses.
     * @param string $message Error message to report.
     */
    private function assertEmailAddresses(array $expected, $value, $message = '')
    {
        $arrayOfAddresses = $this->emailAddrsToArray($value);

        sort($expected);
        sort($arrayOfAddresses);

        $this->assertEquals($expected, $arrayOfAddresses, $message);
    }
}


class MockMailer extends SmtpMailer
{
    private $sent;

    public function __construct(OutboundEmailConfiguration $config)
    {
        $this->sent = false;
        $this->config = $config;
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From, $config->getFrom());
        $headers->setHeader(EmailHeaders::Sender, $config->getFrom());
        $this->headers = $headers;
        $this->recipients = new RecipientsCollection();
    }

    public function getHeaders()
    {
        return $this->headers->packageHeaders();
    }

    public function getRecipients()
    {
        return $this->recipients->getAll();
    }

    public function send()
    {
        $this->sent = true;
    }

    public function wasSent()
    {
        return $this->sent;
    }

    public function toArray()
    {
        return $this->asArray($this);
    }

    private function asArray($d)
    {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            return array_map(__METHOD__, $d);
        }
        return $d;
    }
}

class MockMailerFactory extends MailerFactory
{
    private static $mailer;

    public static function setMailer(BaseMailer $mailer)
    {
        static::$mailer = $mailer;
    }

    public static function getMailer(OutboundEmailConfiguration $config)
    {
        return static::$mailer;
    }
}
