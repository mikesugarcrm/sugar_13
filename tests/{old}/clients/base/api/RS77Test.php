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
 * RS-77: Prepare Subscriptions Api
 */
class RS77Test extends TestCase
{
    /**
     * @var SubscriptionsApi
     */
    protected $subscriptionsApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    /**
     * Subscription
     *
     * @var string
     */
    protected $subscription;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->subscriptionsApi = new SubscriptionsApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        if ($this->subscription) {
            $GLOBALS['db']->query("DELETE FROM subscriptions WHERE id = '{$this->subscription}'");
        }

        Activity::restoreToPreviousState();

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of subscribeToRecord
     */
    public function testSubscribeToRecord()
    {
        $account = SugarTestAccountUtilities::createAccount();

        Activity::enable();

        $result1 = $this->subscriptionsApi->subscribeToRecord($this->serviceMock, [
            'module' => 'Accounts',
            'record' => $account->id,
        ]);

        $this->assertNotEmpty($result1);

        $subscription = BeanFactory::newBean('Subscriptions');
        $subscription->retrieve($result1);

        $this->assertEquals($result1, $subscription->id);
        $this->assertEquals('Accounts', $subscription->parent_type);
        $this->assertEquals($account->id, $subscription->parent_id);

        // check subscribe for already subscribed record
        $result2 = $this->subscriptionsApi->subscribeToRecord($this->serviceMock, [
            'module' => 'Accounts',
            'record' => $account->id,
        ]);

        $this->assertFalse($result2);

        $GLOBALS['db']->query("DELETE FROM subscriptions WHERE id = '{$result1}'");
        $GLOBALS['db']->query("DELETE FROM subscriptions WHERE id = '{$result2}'");
    }

    /**
     * Test asserts behavior of unsubscribeFromRecord
     */
    public function testUnsubscribeFromRecord()
    {
        $account = SugarTestAccountUtilities::createAccount();

        Activity::enable();

        $result = $this->subscriptionsApi->unsubscribeFromRecord($this->serviceMock, [
            'module' => 'Accounts',
            'record' => $account->id,
        ]);
        $this->assertFalse($result);

        $this->subscription = Subscription::subscribeUserToRecord($this->serviceMock->user, $account);

        $result = $this->subscriptionsApi->unsubscribeFromRecord($this->serviceMock, [
            'module' => 'Accounts',
            'record' => $account->id,
        ]);

        $this->assertTrue($result);
    }
}
