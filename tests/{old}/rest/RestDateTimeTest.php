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


class RestDateTimeTest extends RestTestBase
{
    /**
     * @var mixed|\Account
     */
    public $account;
    /**
     * @var mixed|\Opportunity
     */
    public $opp;
    /**
     * @var mixed|\Meeting
     */
    public $meeting;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (isset($this->account->id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->account->id}'");
            }
        }
        if (isset($this->opp->id)) {
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id = '{$this->opp->id}'");
            if ($GLOBALS['db']->tableExists('opportunities_cstm')) {
                $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c = '{$this->opp->id}'");
            }
        }
        if (isset($this->meeting->id)) {
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_contacts WHERE meeting_id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_leads WHERE meeting_id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id = '{$this->meeting->id}'");
        }

        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '" . $GLOBALS['current_user']->id . "'");

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testRestDate()
    {
        $this->account = new Account();
        $this->account->name = 'UNIT TEST - Account';
        $this->account->assigned_user_id = $GLOBALS['current_user']->id;
        $this->account->team_id = '1';
        $this->account->team_set_id = '1';
        $this->account->save();

        $this->opp = new Opportunity();
        $this->opp->name = 'UNIT TEST - Opportunity';
        $this->opp->amount = 25000.1;
        $this->opp->date_closed = '2012-11-10';
        $this->opp->account_id = $this->account->id;
        $this->opp->assigned_user_id = $GLOBALS['current_user']->id;
        $this->opp->team_id = '1';
        $this->opp->team_set_id = '1';
        $this->opp->save();

        $GLOBALS['db']->commit();

        $restReply = $this->restCall("Opportunities/{$this->opp->id}");
        $this->assertEquals('2012-11-10', $restReply['reply']['date_closed'], 'POST REST request comparison failed to match');

        $restReply = $this->restCall(
            "Opportunities/{$this->opp->id}",
            json_encode(['date_closed' => '2012-10-11']),
            'PUT'
        );
        $this->assertEquals('2012-10-11', $restReply['reply']['date_closed'], 'PUT REST request comparison failed to match');

        $ret = $GLOBALS['db']->query("SELECT date_closed FROM opportunities WHERE id = '{$this->opp->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        // Substring the date because some DBs return 00:00:00 with date ONLY types
        $this->assertEquals('2012-10-11', substr($row['date_closed'], 0, 10), 'Database select comparison failed to match');
    }

    /**
     * @group rest
     */
    public function testRestDateTime()
    {
        $this->meeting = new Meeting();
        $this->meeting->name = 'UNIT TEST - Meeting';
        $this->meeting->date_start = '2012-12-13 17:00:00';
        $this->meeting->date_end = '2012-12-13 17:15:00';
        $this->meeting->duration_hours = 0;
        $this->meeting->duration_minutes = 15;
        $this->meeting->assigned_user_id = $GLOBALS['current_user']->id;
        $this->meeting->team_id = '1';
        $this->meeting->team_set_id = '1';
        $this->meeting->save();

        $GLOBALS['db']->commit();

        $GLOBALS['current_user']->setPreference('timezone', 'America/Boise');
        $GLOBALS['current_user']->savePreferencesToDB();

        $restReply = $this->restCall("Meetings/{$this->meeting->id}");
        $this->assertEquals($restReply['reply']['date_start'], '2012-12-13T10:00:00-07:00');
        $this->assertEquals($restReply['reply']['date_end'], '2012-12-13T10:15:00-07:00');

        // Check saving without offset
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T10:15:00']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));


        // Check saving with the user's offset
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T10:15:00-07:00']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));

        // Check saving in GMT
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T17:15:00Z']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));

        // Check saving with the user's offset (in JS format)
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T10:15:00.1234-07:00']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));

        // Check saving in GMT (in JS format)
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T17:15:00.1234Z']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));


        $GLOBALS['current_user']->setPreference('timezone', 'Europe/Helsinki');
        $GLOBALS['current_user']->savePreferencesToDB();

        // Need to logout so the perference changes will come in to effect.
        unset($this->authToken);

        $restReply = $this->restCall("Meetings/{$this->meeting->id}");
        $this->assertEquals($restReply['reply']['date_start'], '2012-12-13T19:00:00+02:00');
        $this->assertEquals($restReply['reply']['date_end'], '2012-12-13T19:15:00+02:00');

        // Check saving without offset
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T19:15:00']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));

        // Check saving without offset (in JS format)
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T19:15:00.1234']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));


        // Check saving with the user's offset
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T19:15:00+02:00']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));

        // Check saving in GMT
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => '2012-12-13T17:15:00Z']),
            'PUT'
        );
        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('2012-12-13 17:15:00', date('Y-m-d H:i:s', strtotime($row['date_end'])));
    }

    /**
     * @group rest
     */
    public function testRestInvalidDateTime()
    {
        $this->meeting = new Meeting();
        $this->meeting->name = 'UNIT TEST - Meeting';
        $this->meeting->date_start = '2012-12-13 17:00:00';
        $this->meeting->date_end = '2012-12-13 17:15:00';
        $this->meeting->duration_hours = 0;
        $this->meeting->duration_minutes = 15;
        $this->meeting->assigned_user_id = $GLOBALS['current_user']->id;
        $this->meeting->team_id = '1';
        $this->meeting->team_set_id = '1';
        $this->meeting->save();

        $GLOBALS['db']->commit();

        $GLOBALS['current_user']->setPreference('timezone', 'America/Boise');
        $GLOBALS['current_user']->savePreferencesToDB();

        // Check saving without offset
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_end' => 'this meeting will never end']),
            'PUT'
        );
        $this->assertEquals('invalid_parameter', $restReply['reply']['error']);
    }


    /**
     * @group rest
     */
    public function testRestBlankDateTime()
    {
        $this->meeting = new Meeting();
        $this->meeting->name = 'UNIT TEST - Meeting';
        $this->meeting->date_start = '2012-12-13 17:00:00';
        $this->meeting->date_end = '2012-12-13 17:15:00';
        $this->meeting->duration_hours = 0;
        $this->meeting->duration_minutes = 15;
        $this->meeting->assigned_user_id = $GLOBALS['current_user']->id;
        $this->meeting->team_id = '1';
        $this->meeting->team_set_id = '1';
        $this->meeting->save();

        $GLOBALS['db']->commit();

        $GLOBALS['current_user']->setPreference('timezone', 'America/Boise');
        $GLOBALS['current_user']->savePreferencesToDB();

        // Check saving without offset
        $restReply = $this->restCall(
            "Meetings/{$this->meeting->id}",
            json_encode(['date_start' => '', 'date_end' => '']),
            'PUT'
        );

        $ret = $GLOBALS['db']->query("SELECT date_end FROM meetings WHERE id = '{$this->meeting->id}'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals('', $row['date_end']);
    }
}
