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

class CampaignLogApiHelperTest extends TestCase
{
    protected $bean = null;
    protected $helper = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');

        $this->bean = BeanFactory::newBean('CampaignLog');
        $this->bean->id = create_guid();

        $this->helper = new CampaignLogApiHelper(new CampaignLogServiceMockup());
    }

    protected function tearDown(): void
    {
        unset($this->helper);
        unset($this->bean);
        SugarTestHelper::tearDown();
    }

    public function testFormatForApi_WithRelatedCampaignTracker_ReturnsCampaignTrackerUrl()
    {
        $campaignTracker = SugarTestCampaignUtilities::createCampaignTracker('123');
        $this->bean->related_id = $campaignTracker->id;
        $this->bean->related_type = 'CampaignTrackers';

        $data = $this->helper->formatForApi($this->bean, ['related_name']);

        $this->assertEquals($data['related_name'], $campaignTracker->tracker_url, 'Tracker URL does not match');

        //cleanup
        unset($campaignTracker);
        SugarTestCampaignUtilities::removeAllCreatedCampaignTrackers();
    }

    public function testFormatForApi_WithRelatedContact_ReturnsContactFullName()
    {
        $contact = SugarTestContactUtilities::createContact();
        $this->bean->related_id = $contact->id;
        $this->bean->related_type = 'Contacts';

        $data = $this->helper->formatForApi($this->bean, ['related_name']);

        $this->assertEquals($data['related_name'], $contact->full_name, 'Contact name does not match');

        //cleanup
        unset($contact);
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testFormatForApi_WithRelatedAccount_ReturnsAccountName()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->bean->related_id = $account->id;
        $this->bean->related_type = 'Accounts';

        $data = $this->helper->formatForApi($this->bean, ['related_name']);

        $this->assertEquals($data['related_name'], $account->name, 'Account name does not match');

        //cleanup
        unset($account);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }
}

class CampaignLogServiceMockup extends ServiceBase
{
    public function __construct()
    {
        $this->user = $GLOBALS['current_user'];
    }

    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
