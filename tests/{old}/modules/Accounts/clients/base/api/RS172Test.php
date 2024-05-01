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
 * RS-172: Prepare Accounts Relate Api
 */
class RS172Test extends TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var AccountsRelateApi */
    protected $api = null;

    /** @var Account */
    protected $account = null;

    /** @var Call */
    protected $call = null;

    /** @var Meeting */
    protected $meeting = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new AccountsRelateApi();

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown(): void
    {
        if ($this->call instanceof Call) {
            $this->call->mark_deleted($this->call->id);
        }
        if ($this->meeting instanceof Meeting) {
            SugarTestMeetingUtilities::removeAllCreatedMeetings();
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts correct result from query
     */
    public function testFilterRelatedCall()
    {
        // FIXME TY-1309: investigate why this test fails
        $this->call = new Call();
        $this->call->name = 'Test Call';
        $this->call->assigned_user_id = $GLOBALS['current_user']->id;
        $this->call->save();
        $this->account->load_relationship('calls');
        $this->account->calls->add($this->call);

        $actual = $this->api->filterRelated($this->service, [
            'module' => 'Accounts',
            'record' => $this->account->id,
            'link_name' => 'calls',
            'include_child_items' => true,
        ]);
        $this->assertArrayHasKey('records', $actual);
        $actual = reset($actual['records']);
        $this->assertEquals($this->call->id, $actual['id']);
    }

    /**
     * Test asserts correct result from query
     */
    public function testFilterRelatedMeeting()
    {
        $this->meeting = SugarTestMeetingUtilities::createMeeting('', $GLOBALS['current_user']);
        $this->account->load_relationship('meetings');
        $this->account->meetings->add($this->meeting);

        $actual = $this->api->filterRelated($this->service, [
            'module' => 'Accounts',
            'record' => $this->account->id,
            'link_name' => 'meetings',
            'include_child_items' => true,
        ]);
        $this->assertArrayHasKey('records', $actual);
        $actual = reset($actual['records']);
        $this->assertEquals($this->meeting->id, $actual['id']);
    }
}
