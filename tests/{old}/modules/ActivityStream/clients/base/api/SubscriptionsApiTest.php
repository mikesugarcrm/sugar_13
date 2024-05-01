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
 * @group api
 * @group subscriptions
 */
class SubscriptionsApiTest extends TestCase
{
    private $api;
    private $subscriptionApi;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user'];
        $this->subscriptionApi = new SubscriptionsApi();
    }

    protected function tearDown(): void
    {
        BeanFactory::setBeanClass('Leads');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        Activity::restoreToPreviousState();
        SugarTestHelper::tearDown();
    }

    public function testSubscribeToRecord_RecordNotFound_ThrowsException()
    {
        Activity::enable();

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->subscriptionApi->subscribeToRecord(
            $this->api,
            [
                'module' => 'Leads',
                'record' => create_guid(),
            ]
        );
    }

    public function testSubscribeToRecord_NoAccess_ThrowsException()
    {
        $mockLead = $this->getMockBuilder('Lead')->setMethods(['ACLAccess'])->getMock();
        $mockLead->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Leads', get_class($mockLead));

        $mockLead->id = create_guid();
        BeanFactory::registerBean($mockLead);

        Activity::enable();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->subscriptionApi->subscribeToRecord(
            $this->api,
            [
                'module' => 'Leads',
                'record' => $mockLead->id,
            ]
        );
    }

    public function testUnSubscribeFromRecord_RecordNotFound_ThrowsException()
    {
        $lead = SugarTestLeadUtilities::createLead();
        $lead->mark_deleted($lead->id);

        Activity::enable();

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->subscriptionApi->unsubscribeFromRecord(
            $this->api,
            [
                'module' => 'Leads',
                'record' => $lead->id,
            ]
        );
    }

    public function testUnSubscribeFromRecord_NoAccess_ThrowsException()
    {
        $mockLead = $this->getMockBuilder('Lead')->setMethods(['ACLAccess'])->getMock();
        $mockLead->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Leads', get_class($mockLead));

        $mockLead->id = create_guid();
        BeanFactory::registerBean($mockLead);

        Activity::enable();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->subscriptionApi->unsubscribeFromRecord(
            $this->api,
            [
                'module' => 'Leads',
                'record' => $mockLead->id,
            ]
        );
    }
}
