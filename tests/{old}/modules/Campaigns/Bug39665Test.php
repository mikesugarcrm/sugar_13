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

class Bug39665Test extends TestCase
{
    public $campaign = null;
    public $prospectlist = null;
    public $prospectlist2 = null;
    public $emailmarketing = null;
    public $emailmarketing2 = null;
    public $saved_current_user = null;
    public $clear_database = true;
    public $remove_beans = true;

    protected function setUp(): void
    {
        $this->saved_current_user = $GLOBALS['current_user'];
        $user = new User();
        $user->retrieve('1');
        $GLOBALS['current_user'] = $user;

        $this->campaign = new Campaign();
        $this->campaign->name = 'Bug39665Test ' . time();
        $this->campaign->campaign_type = 'Email';
        $this->campaign->status = 'Active';
        $timeDate = new TimeDate();
        $this->campaign->end_date = $timeDate->to_display_date(date('Y') + 1 . '-01-01');
        $this->campaign->assigned_id = $user->id;
        $this->campaign->team_id = '1';
        $this->campaign->team_set_id = '1';
        $this->campaign->save();

        $this->emailmarketing = new EmailMarketing();
        $this->emailmarketing->name = $this->campaign->name . ' Email1';
        $this->emailmarketing->campaign_id = $this->campaign->id;
        $this->emailmarketing->from_name = 'SugarCRM';
        $this->emailmarketing->from_addr = 'from@exmaple.com';
        $this->emailmarketing->reply_to_name = 'SugarCRM';
        $this->emailmarketing->reply_to_addr = 'reply@exmaple.com';
        $this->emailmarketing->status = 'active';
        $this->emailmarketing->all_prospect_lists = 1;
        $this->emailmarketing->date_start = $timeDate->to_display_date(date('Y') + 1 . '-01-01') . ' 00:00:00';

        $this->emailmarketing2 = new EmailMarketing();
        $this->emailmarketing2->name = $this->campaign->name . ' Email2';
        $this->emailmarketing2->campaign_id = $this->campaign->id;
        $this->emailmarketing2->from_name = 'SugarCRM';
        $this->emailmarketing2->from_addr = 'do_not_reply@exmaple.com';
        $this->emailmarketing2->reply_to_name = 'SugarCRM';
        $this->emailmarketing2->reply_to_addr = 'reply@exmaple.com';
        $this->emailmarketing2->status = 'active';
        $this->emailmarketing2->all_prospect_lists = 1;
        $this->emailmarketing2->date_start = $timeDate->to_display_date(date('Y') + 1 . '-01-01') . ' 00:00:00';

        $query = 'SELECT id FROM inbound_email WHERE deleted=0';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $this->emailmarketing->inbound_email_id = $row['id'];
            $this->emailmarketing2->inbound_email_id = $row['id'];
            break;
        }

        $query = 'SELECT id FROM email_templates WHERE deleted=0';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $this->emailmarketing->template_id = $row['id'];
            $this->emailmarketing2->template_id = $row['id'];
            break;
        }

        $this->emailmarketing->save();
        $this->emailmarketing2->save();

        $this->campaign->load_relationship('prospectlists');
        $this->prospectlist = new ProspectList();
        $this->prospectlist->name = $this->campaign->name . ' Prospect List1';
        $this->prospectlist->assigned_user_id = $GLOBALS['current_user']->id;
        $this->prospectlist->list_type = 'default';
        $this->prospectlist->save();
        $this->campaign->prospectlists->add($this->prospectlist->id);

        $this->campaign->load_relationship('prospectlists');
        $this->prospectlist2 = new ProspectList();
        $this->prospectlist2->name = $this->campaign->name . ' Prospect List2';
        $this->prospectlist2->assigned_user_id = $GLOBALS['current_user']->id;
        $this->prospectlist2->list_type = 'default';
        $this->prospectlist2->save();
        $this->campaign->prospectlists->add($this->prospectlist2->id);

        $campaign_log_states = [0 => 'viewed', 1 => 'link', 2 => 'invalid email', 3 => 'send error', 4 => 'removed', 5 => 'blocked', 6 => 'lead', 7 => 'contact'];

        for ($i = 0; $i < 10; $i++) {
            $contact = SugarTestContactUtilities::createContact();
            $contact->campaign_id = $this->campaign->id;
            $contact->save();
            $contact->load_relationship('prospect_lists');
            $contact->prospect_lists->add($this->prospectlist->id);
            $contact->prospect_lists->add($this->prospectlist2->id);

            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);

            //$this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist2, 'targeted');
            //$this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist2, $campaign_log_states[mt_rand(0, 7)]);

            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);

            //$this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist2, 'targeted');
            //$this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist2, $campaign_log_states[mt_rand(0, 7)]);
        }

        for ($i = 0; $i < 10; $i++) {
            $lead = SugarTestLeadUtilities::createLead();
            $lead->campaign_id = $this->campaign->id;
            $lead->save();
            $lead->load_relationship('prospect_lists');
            $lead->prospect_lists->add($this->prospectlist->id);
            $lead->prospect_lists->add($this->prospectlist2->id);

            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);

            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing2, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing2, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);
        }
    }

    protected function tearDown(): void
    {
        $GLOBALS['current_user'] = $this->saved_current_user;

        if ($this->remove_beans) {
            $this->campaign->mark_deleted($this->campaign->id);
            $this->prospectlist->mark_deleted($this->prospectlist->id);

            SugarTestContactUtilities::removeAllCreatedContacts();
            SugarTestLeadUtilities::removeAllCreatedLeads();
        }

        if ($this->clear_database) {
            $sql = 'DELETE FROM email_marketing WHERE campaign_id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM campaign_log WHERE campaign_id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_lists_prospects WHERE prospect_list_id=\'' . $this->prospectlist->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_lists_prospects WHERE prospect_list_id=\'' . $this->prospectlist2->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_lists WHERE id = \'' . $this->prospectlist->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_lists WHERE id = \'' . $this->prospectlist2->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_list_campaigns WHERE campaign_id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM campaigns WHERE id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);
        }
    }

    public function test_viewed_message()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST['module'] = 'Campaigns';
        $subpanel_definitions = new SubPanelDefinitions($this->campaign);

        $ids = ['viewed', 'link', 'blocked'];
        foreach ($ids as $id) {
            $subpanel_def = $subpanel_definitions->load_subpanel($id);
            $ListView = new ListView();
            $ListView->initNewXTemplate('include/SubPanelDynamic.html', $subpanel_def->mod_strings);
            $ListView->setHeaderTitle('');
            $ListView->setHeaderText('');
            $ListView->is_dynamic = true;
            $ListView->records_per_page = 3;
            $ListView->start_link_wrapper = "javascript:showSubPanel('{$id}','";
            $ListView->subpanel_id = $id;
            $ListView->end_link_wrapper = "',true);";
            $query = $ListView->process_dynamic_listview('Campaigns', $this->campaign, $subpanel_def);
            $this->assertEquals(preg_match('/GROUP\sBY/', $query), 0, "Assert that query for {$id} subpanel does not have the GROUP BY clause");
        }
    }

    protected function create_campaign_log($campaign, $target, $marketing, $prospectlist, $activity_type, $target_tracker_key = '')
    {
        $campaign_log = new CampaignLog();
        $campaign_log->campaign_id = $campaign->id;
        $campaign_log->target_tracker_key = $target_tracker_key;
        $campaign_log->target_id = $target->id;
        $campaign_log->target_type = $target->module_dir;
        $campaign_log->marketing_id = $marketing->id;
        $campaign_log->more_information = $target->email1;
        $campaign_log->activity_type = $activity_type;
        $campaign_log->activity_date = $GLOBALS['timedate']->to_display_date_time(gmdate($GLOBALS['timedate']->get_db_date_time_format()));
        $campaign_log->list_id = $prospectlist->id;
        $campaign_log->related_type = 'Emails';
        $campaign_log->save();
    }
}
