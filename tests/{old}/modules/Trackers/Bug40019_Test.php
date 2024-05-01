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
 * Bug40019_Test.php
 * This test verifies the fixes to properly store the items in the BreadCrumbStack class
 */
class Bug40019_Test extends TestCase
{
    /**
     * @var mixed
     */
    //@codingStandardsIgnoreStart
    public $_old_history_max_viewed;
    //@codingStandardsIgnoreEnd

    private $anonymous_user;
    private $saved_current_user;

    protected function setUp(): void
    {
        $this->anonymous_user = SugarTestUserUtilities::createAnonymousUser();
        if (!empty($GLOBALS['current_user'])) {
            $this->saved_current_user = $GLOBALS['current_user'];
        }
        $GLOBALS['current_user'] = $this->anonymous_user;

        $i = 0;
        while ($i++ < 10) {
            $account = SugarTestAccountUtilities::createAccount();
            $contact = SugarTestContactUtilities::createContact();

            $trackerManager = TrackerManager::getInstance();
            $trackerManager->unPause();
            if ($monitor = $trackerManager->getMonitor('tracker')) {
                $monitor->setEnabled(true);

                $monitor->setValue('date_modified', gmdate($GLOBALS['timedate']->get_db_date_time_format()));
                $monitor->setValue('user_id', $GLOBALS['current_user']->id);
                $monitor->setValue('module_name', $account->module_dir);
                $monitor->setValue('action', 'detailview');
                $monitor->setValue('item_id', $account->id);
                $monitor->setValue('item_summary', $account->name);
                $monitor->setValue('visible', 1);
                $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
                $trackerManager->saveMonitor($monitor, true, true);

                $monitor = $trackerManager->getMonitor('tracker');
                $monitor->setValue('date_modified', gmdate($GLOBALS['timedate']->get_db_date_time_format()));
                $monitor->setValue('user_id', $GLOBALS['current_user']->id);
                $monitor->setValue('module_name', $contact->module_dir);
                $monitor->setValue('action', 'detailview');
                $monitor->setValue('item_id', $contact->id);
                $monitor->setValue('item_summary', $contact->name);
                $monitor->setValue('visible', 1);
                $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
                $trackerManager->saveMonitor($monitor, true, true);
            }
        }

        if (isset($GLOBALS['sugar_config']['history_max_viewed'])) {
            $this->_old_history_max_viewed = $GLOBALS['sugar_config']['history_max_viewed'];
        }
        $GLOBALS['sugar_config']['history_max_viewed'] = 50;
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM tracker WHERE user_id = '{$this->anonymous_user->id}'");
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if (!empty($this->saved_current_user)) {
            $GLOBALS['current_user'] = $this->saved_current_user;
        }
        if (isset($this->_old_history_max_viewed)) {
            $GLOBALS['sugar_config']['history_max_viewed'] = $this->_old_history_max_viewed;
        }
    }

    public function testBreadCrumbStack()
    {
        $GLOBALS['sugar_config']['history_max_viewed'] = 50;
        $breadCrumbStack = new BreadCrumbStack($GLOBALS['current_user']->id);
        $list = $breadCrumbStack->getBreadCrumbList('Accounts');
        $this->assertCount(10, $list);

        $list = $breadCrumbStack->getBreadCrumbList('Contacts');
        $this->assertCount(10, $list);
    }
}
