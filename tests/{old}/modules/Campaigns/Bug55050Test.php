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
 * Bug #55050
 * Campaign Viewed Messages bug with Adding to Target List
 *
 * @ticket 55050
 */
class Bug55050Test extends TestCase
{
    private $campaign;
    private $emailmarketing;
    private $prospectlist;
    private $prospectlist2;
    private $email;
    private $emailman;
    private $list_max_entries_per_subpanel;

    protected function create_campaign_log($campaign, $target, $marketing, $prospectlist, $activity_type, $target_tracker_key = '')
    {
        global $timedate;
        $campaign_log = new CampaignLog();
        $campaign_log->campaign_id = $campaign->id;
        $campaign_log->target_tracker_key = $target_tracker_key;
        $campaign_log->target_id = $target->id;
        $campaign_log->target_type = $target->module_dir;
        $campaign_log->marketing_id = $marketing->id;
        $campaign_log->more_information = $target->email1;
        $campaign_log->activity_type = $activity_type;
        $campaign_log->activity_date = $timedate->asDb($timedate->getNow());
        $campaign_log->list_id = $prospectlist->id;
        $campaign_log->related_type = 'Emails';
        $campaign_log->related_id = $this->email->id;
        $campaign_log->save();
    }

    protected function setUp(): void
    {
        global $timedate, $sugar_config;

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, 1]);

        $this->campaign = new Campaign();
        $this->campaign->name = 'Bug55050TestCampaign ' . random_int(0, mt_getrandmax());
        $this->campaign->campaign_type = 'Email';
        $this->campaign->status = 'Active';
        $timeDate = new TimeDate();
        $this->campaign->end_date = $timedate->asDbDate($timedate->getNow()->modify('+1 year'));
        $this->campaign->assigned_id = $GLOBALS['current_user']->id;
        $this->campaign->team_id = '1';
        $this->campaign->team_set_id = '1';
        $this->campaign->save();
        SugarTestCampaignUtilities::setCreatedCampaign($this->campaign->id);

        $this->emailmarketing = new EmailMarketing();
        $this->emailmarketing->name = $this->campaign->name . ' Email1';
        $this->emailmarketing->campaign_id = $this->campaign->id;
        $this->emailmarketing->from_name = 'SugarCRM';
        $this->emailmarketing->from_addr = 'from@exmaple.com';
        $this->emailmarketing->reply_to_name = 'SugarCRM';
        $this->emailmarketing->reply_to_addr = 'reply@exmaple.com';
        $this->emailmarketing->status = 'active';
        $this->emailmarketing->all_prospect_lists = 1;
        $this->emailmarketing->template_id = 'test';
        $this->emailmarketing->date_start = $timedate->asDb($timedate->getNow());

        $query = 'SELECT id FROM inbound_email WHERE deleted=0';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $this->emailmarketing->inbound_email_id = $row['id'];
            break;
        }

        $query = 'SELECT id FROM email_templates WHERE deleted=0';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $this->emailmarketing->template_id = $row['id'];
            break;
        }

        $this->emailmarketing->save();

        $this->campaign->load_relationship('prospectlists');
        $this->prospectlist = new ProspectList();
        $this->prospectlist->name = $this->campaign->name . ' Prospect List1';
        $this->prospectlist->assigned_user_id = $GLOBALS['current_user']->id;
        $this->prospectlist->list_type = 'test';
        $this->prospectlist->save();
        $this->campaign->prospectlists->add($this->prospectlist->id);

        $this->email = new Email();
        $this->email->name = 'Bug55050TestEmail';
        $this->email->type = 'out';
        $this->email->status = 'sent';
        $this->email->state = Email::STATE_ARCHIVED;
        $this->email->parent_type = 'Campaigns';
        $this->email->parent_id = $this->campaign->id;
        $this->email->save();
        SugarTestEmailUtilities::setCreatedEmail($this->email->id);

        $this->emailman = new EmailMan();
        $this->emailman->campaign_id = $this->campaign->id;
        $this->emailman->user_id = $GLOBALS['current_user']->id;
        $this->emailman->marketing_id = $this->emailmarketing->id;
        $this->emailman->list_id = $this->prospectlist->id;
        $this->emailman->save();

        for ($i = 0; $i < 2; $i++) {
            $contact = SugarTestContactUtilities::createContact();
            $contact->campaign_id = $this->campaign->id;
            $contact->email2 = 'contact' . random_int(0, mt_getrandmax()) . '@sugar.com'; //Simulate a secondary email
            $contact->save();
            $contact->load_relationship('prospect_lists');
            $contact->prospect_lists->add($this->prospectlist->id);
            SugarTestContactUtilities::setCreatedContact([$contact->id]);
            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist, 'targeted');
        }

        for ($i = 0; $i < 2; $i++) {
            $lead = SugarTestLeadUtilities::createLead();
            $lead->campaign_id = $this->campaign->id;
            $lead->email2 = 'lead2' . random_int(0, mt_getrandmax()) . '@sugar.com'; //Simulate a secondary email
            $lead->save();
            $lead->load_relationship('prospect_lists');
            $lead->prospect_lists->add($this->prospectlist->id);
            SugarTestLeadUtilities::setCreatedLead([$lead->id]);
            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing, $this->prospectlist, 'targeted');
        }

        for ($i = 0; $i < 2; $i++) {
            $prospect = SugarTestProspectUtilities::createProspect();
            $this->create_campaign_log($this->campaign, $prospect, $this->emailmarketing, $this->prospectlist, 'targeted');
        }

        $this->prospectlist2 = new ProspectList();
        $this->prospectlist2->name = $this->campaign->name . ' Prospect List2';
        $this->prospectlist2->assigned_user_id = $GLOBALS['current_user']->id;
        $this->prospectlist2->list_type = 'test';
        $this->prospectlist2->save();

        $this->list_max_entries_per_subpanel = $sugar_config['list_max_entries_per_subpanel'];
        $sugar_config['list_max_entries_per_subpanel'] = 1;
    }

    protected function tearDown(): void
    {
        global $sugar_config;

        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestProspectListsUtilities::removeProspectLists([$this->prospectlist->id, $this->prospectlist2->id]);
        SugarTestEmailUtilities::removeAllCreatedEmails();

        $sql = 'DELETE FROM email_marketing WHERE campaign_id = \'' . $this->campaign->id . '\'';
        $GLOBALS['db']->query($sql);

        $sql = 'DELETE FROM emailman WHERE campaign_id = \'' . $this->campaign->id . '\'';
        $GLOBALS['db']->query($sql);

        $sugar_config['list_max_entries_per_subpanel'] = $this->list_max_entries_per_subpanel;

        SugarTestHelper::tearDown();
    }

    /**
     * @group 55050
     */
    public function testAddToProspectList()
    {
        global $beanList, $beanFiles;
        require_once 'include/formbase.php';
        add_to_prospect_list('targeted', 'ProspectLists', 'ProspectList', $this->prospectlist2->id, 'target_id', 'target_type', 'polymorphic', $this->campaign);

        $this->prospectlist2->retrieve($this->prospectlist2->id);

        $this->prospectlist2->load_relationship('contacts');
        $contacts = $this->prospectlist2->contacts->get();
        $this->assertEquals(2, sizeof($contacts));

        $this->prospectlist2->load_relationship('leads');
        $leads = $this->prospectlist2->leads->get();
        $this->assertEquals(2, sizeof($leads));


        $this->prospectlist2->load_relationship('prospects');
        $prospects = $this->prospectlist2->prospects->get();
        $this->assertEquals(2, sizeof($prospects));
    }
}
