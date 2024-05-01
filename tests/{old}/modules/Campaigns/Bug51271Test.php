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

class Bug51271Test extends TestCase
{
    public $campaign = null;
    public $prospectlist = null;
    public $emailmarketing = null;
    public $emailmarketing2 = null;
    public $email = null;
    public $emailman = null;
    public $saved_current_user = null;
    public $clear_database = true;

    protected function setUp(): void
    {
        global $current_user, $beanFiles, $beanList, $timedate;
        include 'include/modules.php';

        $current_user = new User();
        $current_user->retrieve('1');

        $this->campaign = new Campaign();
        $this->campaign->name = 'Bug39665Test ' . time();
        $this->campaign->campaign_type = 'Email';
        $this->campaign->status = 'Active';
        $timeDate = new TimeDate();
        $this->campaign->end_date = $timedate->asDbDate($timedate->getNow()->modify('+1 year'));
        $this->campaign->assigned_id = $current_user->id;
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
        $this->emailmarketing->template_id = 'test';
        $this->emailmarketing->date_start = $timedate->asDb($timedate->getNow()->modify('+1 week'));

        $this->emailmarketing2 = new EmailMarketing();
        $this->emailmarketing2->name = $this->campaign->name . ' Email2';
        $this->emailmarketing2->campaign_id = $this->campaign->id;
        $this->emailmarketing2->from_name = 'SugarCRM';
        $this->emailmarketing2->from_addr = 'do_not_reply@exmaple.com';
        $this->emailmarketing2->reply_to_name = 'SugarCRM';
        $this->emailmarketing2->reply_to_addr = 'reply@exmaple.com';
        $this->emailmarketing2->status = 'active';
        $this->emailmarketing2->all_prospect_lists = 1;
        $this->emailmarketing2->template_id = 'test';
        $this->emailmarketing2->date_start = $timedate->asDb($timedate->getNow()->modify('+1 week'));

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
        $this->prospectlist->assigned_user_id = $current_user->id;
        $this->prospectlist->list_type = 'test';
        $this->prospectlist->save();
        $this->campaign->prospectlists->add($this->prospectlist->id);

        $this->email = new Email();
        $this->email->name = 'Bug51271Test';
        $this->email->type = 'out';
        $this->email->status = 'sent';
        $this->email->state = Email::STATE_ARCHIVED;
        $this->email->parent_type = 'Campaigns';
        $this->email->parent_id = $this->campaign->id;
        $this->email->save();

        $this->emailman = new EmailMan();
        $this->emailman->campaign_id = $this->campaign->id;
        $this->emailman->user_id = $current_user->id;
        $this->emailman->marketing_id = $this->emailmarketing->id;
        $this->emailman->list_id = $this->prospectlist->id;
        $this->emailman->save();

        $campaign_log_states = [0 => 'viewed', 1 => 'link', 2 => 'invalid email', 3 => 'send error', 4 => 'removed', 5 => 'blocked', 6 => 'lead', 7 => 'contact'];

        for ($i = 0; $i < 1; $i++) {
            $contact = SugarTestContactUtilities::createContact();
            $contact->campaign_id = $this->campaign->id;
            $contact->email2 = 'contact' . random_int(0, mt_getrandmax()) . '@sugar.com'; //Simulate a secondary email
            $contact->save();
            $contact->load_relationship('prospect_lists');
            $contact->prospect_lists->add($this->prospectlist->id);

            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);
            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);
        }

        for ($i = 0; $i < 1; $i++) {
            $lead = SugarTestLeadUtilities::createLead();
            $lead->campaign_id = $this->campaign->id;
            $lead->email2 = 'lead2' . random_int(0, mt_getrandmax()) . '@sugar.com'; //Simulate a secondary email
            $lead->save();
            $lead->load_relationship('prospect_lists');
            $lead->prospect_lists->add($this->prospectlist->id);

            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);
            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing2, $this->prospectlist, 'targeted');
            $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing2, $this->prospectlist, $campaign_log_states[random_int(0, 7)]);
        }
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();

        if ($this->clear_database) {
            $sql = 'DELETE FROM emails WHERE id = \'' . $this->email->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM email_marketing WHERE campaign_id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM emailman WHERE campaign_id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM campaign_log WHERE campaign_id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_lists_prospects WHERE prospect_list_id=\'' . $this->prospectlist->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_lists WHERE id = \'' . $this->prospectlist->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM prospect_list_campaigns WHERE campaign_id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);

            $sql = 'DELETE FROM campaigns WHERE id = \'' . $this->campaign->id . '\'';
            $GLOBALS['db']->query($sql);
        }
    }

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


    public function testDeleteTestCampaigns()
    {
        $result = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM emails WHERE deleted=0 AND parent_id = '{$this->campaign->id}'");
        $this->assertEquals(1, $result);

        $result = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM emailman WHERE deleted=0 AND campaign_id = '{$this->campaign->id}'");
        $this->assertEquals(1, $result);

        $result = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM campaign_log WHERE deleted=0 AND campaign_id = '{$this->campaign->id}'");
        $this->assertEquals(8, $result);

        $deleteTest = new DeleteTestCampaigns();
        $deleteTest->deleteTestRecords($this->campaign);

        $result = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM emails WHERE deleted=0 AND parent_id = '{$this->campaign->id}'");
        $this->assertEquals(0, $result);

        $result = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM emailman WHERE campaign_id = '{$this->campaign->id}'");
        $this->assertEquals(0, $result);

        $result = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM campaign_log WHERE deleted=0 AND campaign_id = '{$this->campaign->id}'");
        $this->assertEquals(0, $result);
    }
}
