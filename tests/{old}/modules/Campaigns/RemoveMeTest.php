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

class RemoveMeTest extends TestCase
{
    /** @var Campaign */
    private $campaign;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('mod_strings', ['Campaigns']);
        $this->campaign = SugarTestCampaignUtilities::createCampaign();
    }

    protected function tearDown(): void
    {
        SugarTestCampaignUtilities::removeAllCreatedCampaignLogs();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

    public function testRemoveContact()
    {
        global $mod_strings;

        $contact = SugarTestContactUtilities::createContact();
        $log = SugarTestCampaignUtilities::createCampaignLog(
            $this->campaign->id,
            'targeted',
            $contact
        );
        $trackerKey = Uuid::uuid1();
        $log->target_tracker_key = $trackerKey;
        $log->save();

        $this->expectOutputString('*' . $mod_strings['LBL_ELECTED_TO_OPTOUT']);

        $_REQUEST['identifier'] = $trackerKey;
        require 'modules/Campaigns/RemoveMe.php';

        $emails = $contact->emailAddress->getAddressesForBean($contact, true);
        $this->assertNotEmpty($emails);

        foreach ($emails as $email) {
            $this->assertEquals(1, $email['opt_out']);
        }
    }

    public function testOptOut_LeadAndContactHaveSameId_RemoveProperRecipient()
    {
        global $mod_strings;

        $contact = SugarTestContactUtilities::createContact();
        $log1 = SugarTestCampaignUtilities::createCampaignLog(
            $this->campaign->id,
            'targeted',
            $contact
        );

        $trackerKey1 = Uuid::uuid1();
        $log1->target_tracker_key = $trackerKey1;
        $log1->save();

        /* Lead and Contact have Same Bean Id */
        $lead = SugarTestLeadUtilities::createLead($contact->id);
        $log2 = SugarTestCampaignUtilities::createCampaignLog(
            $this->campaign->id,
            'targeted',
            $lead
        );

        $trackerKey2 = Uuid::uuid1();
        $log2->target_tracker_key = $trackerKey2;
        $log2->save();

        $this->expectOutputString('*' . $mod_strings['LBL_ELECTED_TO_OPTOUT']);

        $_REQUEST['identifier'] = $trackerKey1;
        require 'modules/Campaigns/RemoveMe.php';

        $contactEmails = $contact->emailAddress->getAddressesForBean($contact, true);
        $this->assertNotEmpty($contactEmails);
        foreach ($contactEmails as $contactEmail) {
            $this->assertEquals(1, $contactEmail['opt_out']);
        }

        $leadEmails = $lead->emailAddress->getAddressesForBean($lead, true);
        $this->assertNotEmpty($leadEmails);
        foreach ($leadEmails as $leadEmail) {
            $this->assertEquals(0, $leadEmail['opt_out']);
        }
    }
}
