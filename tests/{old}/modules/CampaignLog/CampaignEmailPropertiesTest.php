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

require_once 'modules/Campaigns/ProcessBouncedEmails.php';

/**
 * @coversDefaultClass SugarEmailAddress
 */
class CampaignEmailPropertiesTest extends TestCase
{
    private $contact;
    private $emailAddress;

    protected function setUp(): void
    {
        $this->emailAddress = 'test_' . Uuid::uuid1() . '@test.net';
        $this->contact = SugarTestContactUtilities::createContact(null, ['email' => $this->emailAddress]);
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        $GLOBALS['db']->query("DELETE from email_addresses where email_address = '{$this->emailAddress}'");
    }

    public function optoutDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @covers ::save
     * @dataProvider optoutDataProvider
     */
    public function testMarkEmailAddressInvalid_OptoutValueOfFalseIsUnaffected(bool $optOut)
    {
        $this->contact->emailAddress->save($this->contact->id, $this->contact->module_dir);

        $this->setoptOut($this->emailAddress, $optOut);

        $emailBefore = $this->fetchEmailAddress($this->emailAddress);
        markEmailAddressInvalid($this->emailAddress);
        $emailAfter = $this->fetchEmailAddress($this->emailAddress);

        $this->assertTrue((bool)$emailAfter['invalid_email'], 'invalid_email property should be true');
        $this->assertSame(
            $emailBefore['opt_out'],
            $emailAfter['opt_out'],
            'Marking an Email Address Invalid should not alter its opt_out property value'
        );
    }

    private function fetchEmailAddress($emailAddress = '')
    {
        $sea = BeanFactory::newBean('EmailAddresses');
        $q = new SugarQuery();
        $q->select(['*']);
        $q->from($sea);
        $q->where()->queryAnd()
            ->equals('email_address_caps', sugarStrToUpper($emailAddress))
            ->equals('deleted', 0);
        $q->limit(1);
        $rows = $q->execute();
        return $rows[0];
    }

    private function setoptOut($emailAddress = '', $optOut = true)
    {
        $optoutValue = intval($optOut);
        $ea = $this->fetchEmailAddress($emailAddress);
        if (!empty($ea['id'])) {
            $sea = BeanFactory::newBean('EmailAddresses');
            $sea->AddUpdateEmailAddress($emailAddress, null, $optoutValue, $ea['id']);
        }
    }
}
