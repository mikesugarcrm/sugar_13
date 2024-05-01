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

/**
 * Bug 57507 - Empty int's & floats shouldn't be 0
 */
class RestBug57507Test extends RestTestBase
{
    /**
     * @var mixed[]|mixed|\SugarBean[]|null[]
     */
    public $calls;

    protected function setUp(): void
    {
        parent::setUp();

        if (!isset($this->accounts)) {
            $this->accounts = [];
        }
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Bug 57507 Test Account';
        $account->team_id = '1';
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts[] = $account;

        $this->opps = [];
        $this->calls = [];
    }

    protected function tearDown(): void
    {
        // Transition this to _cleanUpRecords() when it is available
        foreach ($this->opps as $opp) {
            $opp->mark_deleted($opp->id);
        }
        foreach ($this->calls as $call) {
            $call->mark_deleted($call->id);
        }
        foreach ($this->accounts as $account) {
            $account->mark_deleted($account->id);
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testEmptySaveInt()
    {
        $reply = $this->restCall(
            'Calls/',
            json_encode([
                'name' => 'Test call, empty int',
                'duration_hours' => 1,
                'duration_minutes' => 15,
                'date_start' => TimeDate::getInstance()->asIso(TimeDate::getInstance()->getNow()),
                'status' => 'Not Held',
                'direction' => 'Incoming',
                'repeat_count' => null,
            ]),
            'POST'
        );
        $this->assertTrue(!empty($reply['reply']['id']), 'Could not create a call..response was: ' . print_r($reply, true));
        $call = BeanFactory::getBean('Calls', $reply['reply']['id']);
        $this->calls[] = $call;

        // because of a change to SugarFieldInt this should return null
        $this->assertTrue($call->repeat_count == 0, 'The repeat count has a value.');
    }

    /**
     * @group rest
     */
    public function testEmptyRetrieveInt()
    {
        $call = BeanFactory::newBean('Calls');
        $call->name = 'Test call, empty int';
        $call->duration_hours = '1';
        $call->duration_minutes = 15;
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $call->status = 'Not Held';
        $call->direction = 'Incoming';
        $call->repeat_count = null;
        $call->save();
        $this->calls[] = $call;

        $reply = $this->restCall('Calls/' . $call->id);

        $this->assertNull($reply['reply']['repeat_count'], 'Repeat count is different from null');
    }
}
