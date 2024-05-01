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

use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use PHPUnit\Framework\TestCase;

/**
 * @group ApiTests
 */
class RecentApiTest extends TestCase
{
    /**
     * @var RecentApi
     */
    private $api;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');

        $this->api = new RecentApi();
    }

    protected function tearDown(): void
    {
        global $current_user;
        global $db;

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $db->query('DELETE FROM tracker WHERE user_id = ' . $db->quoted($current_user->id));

        SugarTestHelper::tearDown();
    }

    public function testFilterModules()
    {
        global $current_user;
        $account = SugarTestAccountUtilities::createAccount();
        $employee = BeanFactory::getBean('Employees', $current_user->id);

        $service = SugarTestRestUtilities::getRestServiceMock();
        $this->api->api = $service;

        $date = '2014-01-01 00:00:00';

        $this->trackAction($account, $date);
        $this->trackAction($employee, $date);

        // Employees module is currently handled in a special way, so test it explicitly
        $modules = ['Accounts', 'Employees', 'NonExistingModule'];
        $api = new RecentApi();
        $filtered = SugarTestReflection::callProtectedMethod($api, 'filterModules', [$modules]);

        $this->assertContains('Accounts', $filtered);
        $this->assertContains('Employees', $filtered);
        $this->assertNotContains('NonExistingModule', $filtered);
    }

    public function testGetRecentlyViewed()
    {
        global $timedate;

        $account = SugarTestAccountUtilities::createAccount(null, [
            'date_modified' => '2019-11-18 10:49:36',
            'update_date_modified' => false,
        ]);

        $service = SugarTestRestUtilities::getRestServiceMock();
        $this->api->api = $service;

        $date = '2014-01-01 00:00:00';

        $this->trackAction($account, $date);
        $response = $this->api->getRecentlyViewed($service, [
            'module_list' => $account->module_name,
        ]);

        $this->assertCount(1, $response['records'], 'API response should contain exactly one record');
        $record = array_shift($response['records']);
        $this->assertEquals($account->module_name, $record['_module']);
        $this->assertEquals($account->id, $record['id']);
        $this->assertEquals('2019-11-18T10:49:36+00:00', $record['date_modified']);

        $lastViewed = $record['_last_viewed_date'];
        $dateTime = $timedate->fromIso($lastViewed);
        $lastViewed = $dateTime->asDb();
        $this->assertEquals($date, $lastViewed);
    }

    /**
     * @test
     */
    public function erasedFields()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->erase(FieldList::fromArray(['field_list']), false);

        $service = SugarTestRestUtilities::getRestServiceMock();
        $this->api->api = $service;

        $this->trackAction($contact, '2014-01-01 00:00:00');

        $response = $this->api->getRecentlyViewed($service, [
            'module_list' => $contact->module_name,
            'erased_fields' => true,
        ]);

        $this->assertSame(['field_list'], $response['records'][0]['_erased_fields']);
    }

    private function trackAction(SugarBean $bean, $date)
    {
        global $timedate;

        $dateTime = $timedate->fromDb($date);
        $timedate->setNow($dateTime);

        $this->api->trackAction($bean);
    }
}
