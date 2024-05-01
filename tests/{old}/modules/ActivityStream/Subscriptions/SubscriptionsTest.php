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
 * @group ActivityStream
 */
class SubscriptionsTest extends TestCase
{
    private $user;
    private $record;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->user = $GLOBALS['current_user'];
        $this->record = self::getUnsavedRecord();
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestActivityUtilities::removeAllCreatedActivities();
        BeanFactory::setBeanClass('Activities');
        BeanFactory::setBeanClass('Accounts');
        SugarTestHelper::tearDown();

        $GLOBALS['db']->query("DELETE FROM subscriptions WHERE parent_id = '{$this->record->id}'");
    }

    /**
     * @covers Subscription::getSubscribedUsers
     */
    public function testGetSubscribedUsers()
    {
        $kls = BeanFactory::getBeanClass('Subscriptions');
        $return = $kls::getSubscribedUsers($this->record);
        $this->assertIsArray($return);
        $this->assertCount(0, $return);

        $kls::subscribeUserToRecord($this->user, $this->record);
        $return = $kls::getSubscribedUsers($this->record);
        $this->assertIsArray($return);
        $this->assertCount(1, $return);
        $this->assertEquals($return[0]['created_by'], $this->user->id);
    }

    /**
     * @covers Subscription::getSubscribedRecords
     */
    public function testGetSubscribedRecords()
    {
        $kls = BeanFactory::getBeanClass('Subscriptions');
        $return = $kls::getSubscribedRecords($this->user);
        $this->assertIsArray($return);
        $this->assertCount(0, $return);

        $kls::subscribeUserToRecord($this->user, $this->record);
        $return = $kls::getSubscribedRecords($this->user);
        $this->assertIsArray($return);
        $this->assertCount(1, $return);
        $this->assertEquals($return[0]['parent_id'], $this->record->id);
    }

    /**
     * @covers Subscription::checkSubscription
     */
    public function testCheckSubscription()
    {
        $kls = BeanFactory::getBeanClass('Subscriptions');
        $return = $kls::checkSubscription($this->user, $this->record);
        $this->assertNull($return, "A subscription shouldn't exist for a new record.");

        $guid = $kls::subscribeUserToRecord($this->user, $this->record);
        $return = $kls::checkSubscription($this->user, $this->record);
        $this->assertEquals($guid, $return['id']);
    }

    /**
     * @covers Subscription::subscribeUserToRecord
     */
    public function testSubscribeUserToRecord()
    {
        $kls = BeanFactory::getBeanClass('Subscriptions');
        $return = $kls::subscribeUserToRecord($this->user, $this->record);
        // Expect a Subscription bean GUID if we're creating the subscription.
        $this->assertIsString($return);

        $return = $kls::subscribeUserToRecord($this->user, $this->record);
        // Expect false if we cannot add another subscription for the user.
        $this->assertFalse($return);
    }

    /**
     * @covers Subscription::addActivitySubscriptions
     */
    public function testAddActivitySubscriptions()
    {
        $GLOBALS['reload_vardefs'] = true;
        $bean = SugarTestAccountUtilities::createAccount();

        Activity::enable();
        $activity = SugarTestActivityUtilities::createActivity();
        $activity->activity_type = 'create';
        $activity->parent_id = $bean->id;
        $activity->parent_type = 'Accounts';
        $activity->save();
        Activity::restoreToPreviousState();

        $data = [
            'act_id' => $activity->id,
            'user_partials' => [
                [
                    'created_by' => $this->user->id,
                ],
            ],
        ];
        $subscriptionsBeanName = BeanFactory::getBeanClass('Subscriptions');
        $subscriptionsBeanName::addActivitySubscriptions($data);
        $activity->load_relationship('activities_users');
        $expected = [$this->user->id];
        $actual = $activity->activities_users->get();
        $this->assertEquals($expected, $actual, 'Should have added the user relationship to the activity.');
        unset($GLOBALS['reload_vardefs']);
    }

    private static function getUnsavedRecord()
    {
        // SugarTestAccountUtilities::createAccount saves the bean, which
        // triggers the OOB subscription logic. For that reason, we create our
        // own record and give it an ID.
        $record = new Account();
        $record->id = create_guid();
        return $record;
    }
}
