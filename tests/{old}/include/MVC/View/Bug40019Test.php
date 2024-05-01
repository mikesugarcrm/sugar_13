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

class Bug40019Test extends TestCase
{
    protected function setUp(): void
    {
        $account = [];
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        global $sugar_config;
        $max = $sugar_config['history_max_viewed'];

        $contacts = [];
        for ($i = 0; $i < $max + 1; $i++) {
            $contacts[$i] = SugarTestContactUtilities::createContact();
            SugarTestTrackerUtility::insertTrackerEntry($contacts[$i], 'detailview');
        }

        for ($i = 0; $i < $max + 1; $i++) {
            $account[$i] = SugarTestAccountUtilities::createAccount();
            SugarTestTrackerUtility::insertTrackerEntry($account[$i], 'detailview');
        }

        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTrackerUtility::removeAllTrackerEntries();

        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    // Currently, getBreadCrumbList in BreadCrumbStack.php limits you to 10
    // Also, the Constructor in BreadCrumbStack.php limits it to 10 too.
    /*
     * @group bug40019
     */
    public function testModuleMenuLastViewedForModule()
    {
        global $sugar_config;
        $max = $sugar_config['history_max_viewed'];

        $tracker = new Tracker();
        $history = $tracker->get_recently_viewed($GLOBALS['current_user']->id, 'Contacts');

        $expected = $max > 10 ? 10 : $max;
        $history_count = safeCount($history);

        $this->assertTrue(
            $history_count <= $expected,
            "Recently viewed list is not as expected: History count = $history_count, and expected = $expected"
        );
    }

    // Currently, getBreadCrumbList in BreadCrumbStack.php limits you to 10
    /*
     * @group bug40019
     */
    public function testModuleMenuLastViewedForAll()
    {
        global $sugar_config;
        $max = $sugar_config['history_max_viewed'];

        $tracker = new Tracker();
        $history = $tracker->get_recently_viewed($GLOBALS['current_user']->id, '');

        $expected = $max > 10 ? 10 : $max;
        $history_count = safeCount($history);

        $this->assertTrue(
            $history_count <= $expected,
            "Recently viewed list is not as expected: History count = $history_count, and expected = $expected"
        );
    }
}
