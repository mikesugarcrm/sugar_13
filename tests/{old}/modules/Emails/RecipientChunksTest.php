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

class RecipientChunksTest extends TestCase
{
    private $strategies;

    private $chunkSize;

    protected function setUp(): void
    {
        global $current_user;

        OutboundEmailConfigurationTestHelper::setUp();
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();

        $this->strategies = SugarTestReflection::getProtectedValue(MailerFactory::class, 'strategies');
        $strategies = [OutboundEmailConfigurationPeer::MODE_SMTP => MockStoringMailer::class];
        SugarTestReflection::setProtectedValue(MailerFactory::class, 'strategies', $strategies);

        $this->chunkSize = $GLOBALS['sugar_config']['email_recipient_chunk_size'] ?? null;
        $GLOBALS['sugar_config']['email_recipient_chunk_size'] = 2;
        SugarConfig::getInstance()->clearCache('email_recipient_chunk_size');
    }


    /**
     * @covers ::sendEmail
     */
    public function testRecipientChunks()
    {
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);

        $contact = SugarTestContactUtilities::createContact();

        $data = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => $config->getConfigId(),
            'name' => 'Welcome $contact_first_name',
            'description_html' => 'Hello <b>$contact_first_name</b>',
            'parent_type' => 'Contacts',
            'parent_id' => $contact->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('to');

        for ($i = 0; $i < 3; $i++) {
            $recipientContact = SugarTestContactUtilities::createContact();
            $ep = BeanFactory::newBean('EmailParticipants');
            $ep->new_with_id = true;
            $ep->id = Uuid::uuid1();
            BeanFactory::registerBean($ep);
            $ep->parent_type = 'Contacts';
            $ep->parent_id = $recipientContact->id;
            $email->to->add($ep);
        }

        $email->sendEmail($config);
        $result = MockStoringMailer::getInstance()->getStored();

        $this->assertCount(2, $result);
        $this->assertSame("Hello <b>{$contact->first_name}</b>", $result[0]['htmlBody'], 'Incorrect HTML part');
        $this->assertSame("Hello <b>{$contact->first_name}</b>", $result[1]['htmlBody'], 'Incorrect HTML part');
        $this->assertCount(2, $result[0]['recipients']['to'], 'Incorrect recipient count');
        $this->assertCount(1, $result[1]['recipients']['to'], 'Incorrect recipient count');
    }

    protected function tearDown(): void
    {
        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
        OutboundEmailConfigurationTestHelper::tearDown();

        VardefManager::$linkFields = [];
        VardefManager::loadVardef('Contacts', 'Contact', true);

        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailUtilities::removeAllCreatedEmails();

        unset($GLOBALS['current_user']);
        if (isset($this->chunkSize)) {
            $GLOBALS['sugar_config']['email_recipient_chunk_size'] = $this->chunkSize;
        } else {
            unset($GLOBALS['sugar_config']['email_recipient_chunk_size']);
        }
        SugarTestReflection::setProtectedValue(MailerFactory::class, 'strategies', $this->strategies);
    }
}


class MockStoringMailer extends SmtpMailer
{
    private static $instance = null;

    private $stored = [];

    public function __construct(OutboundEmailConfiguration $config)
    {
        $this->config = $config;
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From, $config->getFrom());
        $headers->setHeader(EmailHeaders::Sender, $config->getFrom());
        $this->headers = $headers;
        $this->recipients = new RecipientsCollection();
        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
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
        $this->stored[] = [
            'recipients' => $this->getRecipients(),
            'htmlBody' => $this->getHtmlBody(),
        ];
    }

    public function getStored()
    {
        return $this->stored;
    }

    public function clearStored()
    {
        $this->stored = [];
    }
}
