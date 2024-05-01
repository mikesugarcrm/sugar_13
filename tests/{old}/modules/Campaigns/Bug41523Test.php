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

/**
 * Bug #41523
 * Subject Blank Rows Are Displayed In Campaign Status "Leads Created" Subpanel If Leads Are Deleted
 *
 * @ticket 41523
 */
class Bug41523Test extends TestCase
{
    private $campaign;

    protected function setUp(): void
    {
        global $focus;

        SugarTestHelper::setUp('app_strings');

        // Init session user settings
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->setPreference('max_tabs', 2);

        $this->campaign = SugarTestCampaignUtilities::createCampaign();
        $focus = $this->campaign;

        // Setting for SubPanel
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_REQUEST['module'] = 'Campaigns';
        $_REQUEST['action'] = 'TrackDetailView';
        $_REQUEST['record'] = $this->campaign->id;
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        $_REQUEST = [];

        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestCampaignUtilities::removeAllCreatedCampaignLogs();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    /**
     * @group 41523
     */
    public function testDeletedLeadsOnCampaignStatusPage()
    {
        // create a few leads
        $leads = [
            $this->createLeadFromWebForm('User1:' . create_guid()),
            $this->createLeadFromWebForm('User2:' . create_guid()),
            $this->createLeadFromWebForm('User3:' . create_guid()),
        ];

        // delete one lead
        $leads[0]->mark_deleted($leads[0]->id);

        $logDeletedLeadsCount = $this->campaign->getDeletedCampaignLogLeadsCount();
        $this->assertEquals(1, $logDeletedLeadsCount);

        // test subpanel output
        require_once 'include/utils/layout_utils.php';
        $subpanel = new SubPanelTiles($this->campaign, 'Campaigns');
        $html = $subpanel->display();

        preg_match('|<div id="list_subpanel_lead">.*?<table.*?</table>.*?</tr>(.*?)</table>|s', $html, $match);
        preg_match_all('|module=Leads&action=DetailView|', $match[1], $match);

        $expectedLeadsInSubpanel = count($leads) - $logDeletedLeadsCount;
        $actualLeadsInSubpanel = safeCount($match[0]);
        $this->assertEquals(
            $expectedLeadsInSubpanel,
            $actualLeadsInSubpanel,
            'The number of leads listed in the Leads subpanel is not correct'
        );
    }

    /**
     * @param $lastName Last name for new lead
     *
     * @return Lead
     */
    private function createLeadFromWebForm($lastName)
    {
        $lead = SugarTestLeadUtilities::createLead('', ['last_name' => $lastName]);

        if (!empty($lead)) {
            $campaignLog = SugarTestCampaignUtilities::createCampaignLog($this->campaign->id, 'lead', $lead);
            $lead->load_relationship('campaigns');
            $lead->campaigns->add($campaignLog->id);
            $lead->save(false);
        }

        return $lead;
    }
}
