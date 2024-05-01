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

require_once 'modules/Campaigns/utils.php';

/**
 * Bug #54098
 * Manage Subscriptions Doesn't Properly Work With More Than Two Default Target Lists
 *
 * @ticket 54098
 */
class Bug54098Test extends TestCase
{
    private $aProspectlists_Prospects;
    private $aProspectlists_Campaigns;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestProspectListsUtilities::removeCreatedProspectLists();
        $this->deleteProspectlistToCampaignRelationRecords();
        $this->deleteProspectlistToContactRelationRecords();
        SugarTestHelper::tearDown();
    }

    /**
     * If we create two default Target Lists for newsletter campaign and attach a contact to one of this target lists,
     * than when "Select Manage Subscriptions" for that contact - campaign should be listed once (not twice! as before)
     */
    public function testGetSubscriptionLists()
    {
        $oCampaign = SugarTestCampaignUtilities::createCampaign();
        $oCampaign->campaign_type = 'NewsLetter';
        $oCampaign->save();
        $oProspectList = SugarTestProspectListsUtilities::createProspectList(null, [
            'list_type' => 'default',
        ]);
        $oProspectList2 = SugarTestProspectListsUtilities::createProspectList(null, [
            'list_type' => 'default',
        ]);
        $oProspectList3 = SugarTestProspectListsUtilities::createProspectList(null, [
            'list_type' => 'exempt',
        ]);
        $oContact = SugarTestContactUtilities::createContact();
        $oContact2 = SugarTestContactUtilities::createContact();
        $this->createProspectlistToCampaignRelationRecord($oCampaign, $oProspectList);
        $this->createProspectlistToCampaignRelationRecord($oCampaign, $oProspectList2);
        $this->createProspectlistToCampaignRelationRecord($oCampaign, $oProspectList3);

        $this->createContactToProspectlistRelationRecord($oContact, $oProspectList);
        $this->createContactToProspectlistRelationRecord($oContact2, $oProspectList);

        $aResult = get_subscription_lists($oContact2);

        $this->assertIsArray($aResult['unsubscribed']);
        $this->assertIsArray($aResult['subscribed']);
        $this->assertArrayHasKey($oCampaign->name, $aResult['subscribed']);
        $this->assertArrayNotHasKey($oCampaign->name, $aResult['unsubscribed']);
    }

    private function createProspectlistToCampaignRelationRecord(Campaign $oCampaign, ProspectList $oProspectList)
    {
        if (!empty($oCampaign->id) and !empty($oProspectList->id)) {
            $id = 'BUg54098' . random_int(0, mt_getrandmax());
            $this->aProspectlists_Campaigns[] = $id;
            $sDate = $GLOBALS['db']->convert(date('\'Y-m-d H:i:s\''), 'datetime');
            $GLOBALS['db']->query("INSERT INTO prospect_list_campaigns VALUES ('{$id}','{$oProspectList->id}', '{$oCampaign->id}', {$sDate}, 0)");
        }
    }

    private function deleteProspectlistToCampaignRelationRecords()
    {
        if (!empty($this->aProspectlists_Campaigns)) {
            $GLOBALS['db']->query("DELETE FROM prospect_list_campaigns WHERE id IN ('" . implode("','", $this->aProspectlists_Campaigns) . "')");
        }
    }

    private function createContactToProspectlistRelationRecord(Contact $oContact, ProspectList $oProspectList)
    {
        if (!empty($oContact->id) and !empty($oProspectList->id)) {
            $id = 'BUg54098' . random_int(0, mt_getrandmax());
            $this->aProspectlists_Prospects[] = $id;
            $sDate = $GLOBALS['db']->convert(date('\'Y-m-d H:i:s\''), 'datetime');
            $GLOBALS['db']->query("INSERT INTO prospect_lists_prospects VALUES ('{$id}','{$oProspectList->id}', '{$oContact->id}','Contacts',{$sDate}, 0)");
        }
    }

    private function deleteProspectlistToContactRelationRecords()
    {
        if (!empty($this->aProspectlists_Campaigns)) {
            $GLOBALS['db']->query("DELETE FROM prospect_lists_prospects WHERE id IN ('" . implode("','", $this->aProspectlists_Prospects) . "')");
        }
    }
}
