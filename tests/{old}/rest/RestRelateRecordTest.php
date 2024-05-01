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


class RestRelateRecordTest extends RestTestBase
{
    /**
     * @var mixed[]|mixed|\SugarBean[]|null[]
     */
    public $calls;
    /**
     * @var mixed[]|mixed|\SugarBean[]|null[]
     */
    public $leads;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $this->accounts = [];
        $this->contacts = [];
        $this->opps = [];
        $this->calls = [];
        $this->notes = [];
        $this->leads = [];
    }

    protected function tearDown(): void
    {
        $accountIds = [];
        foreach ($this->accounts as $account) {
            $accountIds[] = $account->id;
        }
        $accountIds = "('" . implode("','", $accountIds) . "')";
        $oppIds = [];
        foreach ($this->opps as $opp) {
            $oppIds[] = $opp->id;
        }
        $oppIds = "('" . implode("','", $oppIds) . "')";
        $contactIds = [];
        foreach ($this->contacts as $contact) {
            $contactIds[] = $contact->id;
        }
        $contactIds = "('" . implode("','", $contactIds) . "')";

        $callsIds = [];
        foreach ($this->calls as $call) {
            if (is_string($call)) {
                $callsIds[] = $call;
            } else {
                $callsIds[] = $call->id;
            }
        }
        $callsIds = "('" . implode("','", $callsIds) . "')";

        $leadIds = [];
        foreach ($this->leads as $lead) {
            $leadIds[] = $lead->id;
        }
        $leadIds = "('" . implode("','", $leadIds) . "')";

        $noteIds = [];
        foreach ($this->notes as $note) {
            if (is_string($note)) {
                $noteIds[] = $note;
            } else {
                $noteIds[] = $note->id;
            }
        }
        $noteIds = "('" . implode("','", $noteIds) . "')";


        $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
        if ($GLOBALS['db']->tableExists('accounts_cstm')) {
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
        }
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
        if ($GLOBALS['db']->tableExists('opportunities_cstm')) {
            $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c IN {$oppIds}");
        }
        $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
        if ($GLOBALS['db']->tableExists('contacts_cstm')) {
            $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN {$contactIds}");
        }
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");

        $GLOBALS['db']->query("DELETE FROM calls WHERE id IN {$callsIds}");
        if ($GLOBALS['db']->tableExists('calls_cstm')) {
            $GLOBALS['db']->query("DELETE FROM calls_cstm WHERE id_c IN {$callsIds}");
        }

        $GLOBALS['db']->query("DELETE FROM leads WHERE id IN {$leadIds}");
        if ($GLOBALS['db']->tableExists('leads_cstm')) {
            $GLOBALS['db']->query("DELETE FROM leads_cstm WHERE id_c IN {$leadIds}");
        }

        $GLOBALS['db']->query("DELETE FROM notes WHERE id IN {$noteIds}");
        if ($GLOBALS['db']->tableExists('notes_cstm')) {
            $GLOBALS['db']->query("DELETE FROM notes_cstm WHERE id_c IN {$noteIds}");
        }

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testFetchRelatedRecord()
    {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least one page of each of the related modules
        for ($i = 0; $i < 2; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();
            $this->contacts[] = $contact;
        }
        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;


            $contactNums = [0, 1];

            foreach ($contactNums as $contactNum) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum % $ctsCount)];
                $this->contacts[$contactNum]->opportunity_role = $contact_type;
                $opp->contacts->add([$this->contacts[$contactNum]], ['contact_role' => $contact_type]);
            }
        }

        $GLOBALS['db']->commit();

        // Test normal fetch
        $restReply = $this->restCall('Opportunities/' . $this->opps[0]->id . '/link/contacts/' . $this->contacts[0]->id);

        $this->assertEquals($this->contacts[0]->id, $restReply['reply']['id'], 'Did not fetch the related contact');
        $this->assertNotEmpty($restReply['reply']['opportunity_role'], 'The role field on the Opportunity -> Contact relationship was not populated.');
        $this->assertEquals($this->contacts[0]->opportunity_role, $restReply['reply']['opportunity_role'], 'The role field on the Opportunity -> Contact relationship does not match the bean.');

        // Test fetch where the opp id is not there
        $restReply = $this->restCall('Opportunities/UNIT_TEST_THIS_IS_NOT_A_REAL_ID/link/contacts/' . $this->contacts[0]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Test fetch where the opp id is there, but the contact ID isn't
        $restReply = $this->restCall('Opportunities/' . $this->opps[0]->id . '/link/contacts/UNIT_TEST_THIS_IS_NOT_A_REAL_ID');
        $this->assertEquals('not_found', $restReply['reply']['error']);
    }

    /**
     * @group rest
     */
    public function testSameNumberOfRecords()
    {
        global $db;
        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least one page of each of the related modules
        for ($i = 0; $i < 2; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();
            $this->contacts[] = $contact;
        }
        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;


            $contactNums = [0, 1];

            foreach ($contactNums as $contactNum) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum % $ctsCount)];
                $this->contacts[$contactNum]->opportunity_role = $contact_type;
                $opp->contacts->add([$this->contacts[$contactNum]], ['contact_role' => $contact_type]);
            }
        }

        // Test normal fetch
        $restReply = $this->restCall('Opportunities/' . $this->opps[0]->id . '/link/contacts/' . $this->contacts[0]->id);
        $fetch_fields = safeCount($restReply['reply']);
        // create a record

        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;
        }

        $GLOBALS['db']->commit();

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link/contacts',
            json_encode([
                'last_name' => 'TEST',
                'first_name' => 'UNIT',
                'description' => 'UNIT TEST CONTACT',
            ]),
            'POST'
        );

        $create_fields = safeCount($restReply['reply']['related_record']);

        // update a record


        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ($i = 0; $i < 2; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i % $ctsCount)];
            $this->contacts[$i]->opportunity_role = $contact_type;
        }
        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $contactNums = [0, 1];
            $opp->load_relationship('contacts');
            foreach ($contactNums as $contactNum) {
                $opp->contacts->add([$this->contacts[$contactNum]], ['contact_role' => $this->contacts[$contactNum]->opportunity_role]);
            }
        }

        $GLOBALS['db']->commit();

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link/contacts/' . $this->contacts[1]->id,
            json_encode([
                'last_name' => "Test O'Chango",
            ]),
            'PUT'
        );


        $update_fields = safeCount($restReply['reply']['related_record']);
        // test fetch vs create
        $this->assertEquals($fetch_fields, $create_fields, "Number of fields doesn't match");

        // test fetch vs update
        $this->assertEquals($fetch_fields, $update_fields, "Number of fields doesn't match");
    }

    /**
     * @group rest
     */
    public function testCreateRelatedRecord()
    {
        global $db;

        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;
        }

        $GLOBALS['db']->commit();

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link/contacts',
            json_encode([
                'last_name' => 'TEST',
                'first_name' => 'UNIT',
                'contact_role' => 'Primary Decision Maker',
                'description' => 'UNIT TEST CONTACT',
            ]),
            'POST'
        );

        $contact = new Contact();
        $contact->retrieve($restReply['reply']['related_record']['id']);
        // Save it here so it gets deleted later
        $this->contacts[] = $contact;

        $this->assertTrue(!empty($restReply['reply']['related_record']['date_entered']), 'Date Entered was not set on the creat retrun of the related record');


        $ret = $db->query("SELECT * FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND contact_id = '" . $this->contacts[0]->id . "'");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('Primary Decision Maker', $row['contact_role'], "Did not set the related contact's role");
    }

    /**
     * @group rest
     */
    public function testUpdateRelatedLink()
    {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ($i = 0; $i < 2; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i % $ctsCount)];
            $this->contacts[$i]->opportunity_role = $contact_type;
        }
        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $contactNums = [0, 1];
            $opp->load_relationship('contacts');
            foreach ($contactNums as $contactNum) {
                $opp->contacts->add([$this->contacts[$contactNum]], ['contact_role' => $this->contacts[$contactNum]->opportunity_role]);
            }
        }

        $GLOBALS['db']->commit();

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link/contacts/' . $this->contacts[1]->id,
            json_encode([
                'opportunity_role' => 'Primary Decision Maker',
                'last_name' => 'Test O Chango',
            ]),
            'PUT'
        );

        $this->assertTrue(!empty($restReply['reply']['related_record']['date_entered']), 'Date Entered was not set in the Update related record reply');
        $this->assertEquals($this->contacts[1]->id, $restReply['reply']['related_record']['id'], "Changed the related ID when it shouldn't have");
        $this->assertEquals('Test O Chango', $restReply['reply']['related_record']['last_name'], 'Did not change the related contact');

        $ret = $db->query("SELECT * FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND contact_id = '" . $this->contacts[1]->id . "'");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('Primary Decision Maker', $row['contact_role'], "Did not set the related contact's role");
        $this->assertEquals('Primary Decision Maker', $restReply['reply']['related_record']['opportunity_role'], "Did not set the related contact's role");
    }

    /**
     * @group rest
     */
    public function testCreateRelatedLink()
    {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ($i = 0; $i < 2; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i % $ctsCount)];
            $this->contacts[$i]->opportunity_role = $contact_type;
        }
        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;
        }

        $GLOBALS['db']->commit();
        // get how many opps and contacts there currently are
        $oppCountQuery = $db->query('SELECT count(*) AS count FROM opportunities');
        $contactCountQuery = $db->query('SELECT count(*) AS count FROM contacts');

        $oppCountRow = $db->fetchByAssoc($oppCountQuery);
        $oppCount = $oppCountRow['count'];

        $contactCountRow = $db->fetchByAssoc($contactCountQuery);
        $contactCount = $contactCountRow['count'];

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link/contacts/' . $this->contacts[1]->id,
            json_encode([
                'contact_role' => $this->contacts[1]->opportunity_role,
            ]),
            'POST'
        );

        $this->assertEquals($this->contacts[1]->id, $restReply['reply']['related_record']['id'], 'Did not link the related contact');
        $this->assertEquals($this->contacts[1]->opportunity_role, $restReply['reply']['related_record']['opportunity_role'], "Did not fetch the related contact's role");


        $ret = $db->query("SELECT * FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND contact_id = '" . $this->contacts[1]->id . "'");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals($this->contacts[1]->opportunity_role, $row['contact_role'], "Did not set the related contact's role");

        //verify no duplicate contact or opportunity was created with this link
        $oppCountQuery = $db->query('SELECT count(*) AS count FROM opportunities');
        $contactCountQuery = $db->query('SELECT count(*) AS count FROM contacts');

        $oppCountRow = $db->fetchByAssoc($oppCountQuery);
        $oppCountNew = $oppCountRow['count'];

        $contactCountRow = $db->fetchByAssoc($contactCountQuery);
        $contactCountNew = $contactCountRow['count'];

        $this->assertEquals($oppCount, $oppCountNew, 'More Opps were created in this process');
        $this->assertEquals($contactCount, $contactCountNew, 'More contacts where created in this process');
    }

    /**
     * @group rest
     */
    public function testCreateRelatedLinks()
    {
        $linkName = 'contacts';
        $this->contacts[] = SugarTestContactUtilities::createContact();
        $this->contacts[] = SugarTestContactUtilities::createContact();
        $this->opps[] = SugarTestOpportunityUtilities::createOpportunity();

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link',
            json_encode(
                [
                    'link_name' => $linkName,
                    'ids' => [
                        $this->contacts[0]->id,
                        $this->contacts[1]->id,
                    ],
                ]
            ),
            'POST'
        );

        $this->opps[0]->load_relationship($linkName);
        $actualResult = $this->opps[0]->$linkName->getBeans();

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();

        $this->assertArrayHasKey($this->contacts[0]->id, $actualResult);
        $this->assertArrayHasKey($this->contacts[1]->id, $actualResult);
    }

    /**
     * @group rest
     */
    public function testDeleteRelatedLink()
    {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ($i = 0; $i < 2; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i % $ctsCount)];
            $this->contacts[$i]->opportunity_role = $contact_type;
        }
        for ($i = 0; $i < 1; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $contactNums = [0, 1];
            $opp->load_relationship('contacts');
            foreach ($contactNums as $contactNum) {
                $opp->contacts->add([$this->contacts[$contactNum]], ['contact_role' => $this->contacts[$contactNum]->opportunity_role]);
            }
        }

        $GLOBALS['db']->commit();

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND deleted = 0");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('2', $row['link_count'], 'The links were not properly generated');

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link/contacts/' . $this->contacts[1]->id,
            '',
            'DELETE'
        );

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND deleted = 0");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('1', $row['link_count'], 'The first link was not properly deleted');

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND contact_id = '" . $this->contacts[0]->id . "' AND deleted = 0");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('1', $row['link_count'], 'The wrong link was deleted');

        $restReply = $this->restCall(
            'Opportunities/' . $this->opps[0]->id . '/link/contacts/' . $this->contacts[0]->id,
            '',
            'DELETE'
        );

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND deleted = 0");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('0', $row['link_count'], 'The second link was not properly deleted');

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='" . $this->opps[0]->id . "' AND contact_id = '" . $this->contacts[0]->id . "' AND deleted = 0");

        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('0', $row['link_count'], 'The second link was never deleted');
    }

    public function testCreateWithModuleWithOutParentInfo()
    {
        $call = BeanFactory::newBean('Calls');
        $call->name = 'UNIT TEST' . create_guid();
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();

        $call->save();

        $this->calls[] = $call;

        $post = [
            'embed_flag' => 0,
            'deleted' => 0,
            'name' => 'Test Note',
            'description' => 'This is a test note',
            'assigned_user_id' => 1,
        ];

        $restReply = $this->restCall("Calls/{$call->id}/link/notes", $post, 'POST');

        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->notes[] = BeanFactory::getBean('Notes', $restReply['reply']['related_record']['id']);
        $this->assertEquals($restReply['reply']['related_record']['parent_id'], $call->id, 'Call ID was not the parent id of the note.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], 'Calls', 'Call Module was not the parent type of the note.');

        // try with leads and notes
        $lead = BeanFactory::newBean('Leads');
        $lead->name = 'Unit Test' . create_guid();
        $lead->save();
        $this->leads[] = $lead;

        $post = [
            'name' => 'CALL FOR LEAD ' . create_guid(),
        ];

        $restReply = $this->restCall("Leads/{$lead->id}/link/calls", $post, 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->calls[] = BeanFactory::getBean('Calls', $restReply['reply']['related_record']['id']);

        //shouldn't be set because they did not set them
        $this->assertEquals($restReply['reply']['related_record']['parent_id'], '', 'Lead ID was not the parent id of the call.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], '', 'Leads Module was not the parent type of the call.');
    }

    public function testCreateWithModuleWithParentType()
    {
        $call = BeanFactory::newBean('Calls');
        $call->name = 'UNIT TEST' . create_guid();
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();

        $call->save();

        $this->calls[] = $call;

        $post = [
            'embed_flag' => 0,
            'deleted' => 0,
            'name' => 'Test Note',
            'description' => 'This is a test note',
            'assigned_user_id' => 1,
        ];

        $restReply = $this->restCall("Calls/{$call->id}/link/notes", $post, 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->notes[] = BeanFactory::getBean('Notes', $restReply['reply']['related_record']['id']);
        $this->assertEquals($restReply['reply']['related_record']['parent_id'], $call->id, 'Call ID was not the parent id of the note.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], 'Calls', 'Call Module was not the parent type of the note.');

        // try with leads and notes
        $lead = BeanFactory::newBean('Leads');
        $lead->name = 'Unit Test' . create_guid();
        $lead->save();
        $this->leads[] = $lead;

        $post = [
            'name' => 'CALL FOR LEAD ' . create_guid(),
            'parent_type' => 'Leads',
            'parent_id' => $lead->id,
        ];

        $restReply = $this->restCall("Leads/{$lead->id}/link/calls", $post, 'POST');

        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->calls[] = BeanFactory::getBean('Calls', $restReply['reply']['related_record']['id']);

        $this->assertEquals($restReply['reply']['related_record']['parent_id'], $lead->id, 'Lead ID was not the parent id of the call.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], 'Leads', 'Leads Module was not the parent type of the call.');
    }

    public function testCreateWithModuleWithParentId()
    {
        $call = BeanFactory::newBean('Calls');
        $call->name = 'UNIT TEST' . create_guid();
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();

        $call->save();

        $this->calls[] = $call;

        $post = [
            'embed_flag' => 0,
            'deleted' => 0,
            'name' => 'Test Note',
            'description' => 'This is a test note',
            'assigned_user_id' => 1,
        ];

        $restReply = $this->restCall("Calls/{$call->id}/link/notes", $post, 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->notes[] = BeanFactory::getBean('Notes', $restReply['reply']['related_record']['id']);
        $this->assertEquals($restReply['reply']['related_record']['parent_id'], $call->id, 'Call ID was not the parent id of the note.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], 'Calls', 'Call Module was not the parent type of the note.');

        // try with leads and notes
        $lead = BeanFactory::newBean('Leads');
        $lead->name = 'Unit Test' . create_guid();
        $lead->save();
        $this->leads[] = $lead;

        $post = [
            'name' => 'CALL FOR LEAD ' . create_guid(),
            'parent_id' => $lead->id,
            'parent_type' => 'Leads',
        ];

        $restReply = $this->restCall("Leads/{$lead->id}/link/calls", $post, 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->calls[] = BeanFactory::getBean('Calls', $restReply['reply']['related_record']['id']);
        $this->assertEquals($restReply['reply']['related_record']['parent_id'], $lead->id, 'Lead ID was not the parent id of the call.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], 'Leads', 'Leads Module was not the parent type of the call.');
    }

    public function testCreateWithModuleWithParentInfo()
    {
        $call = BeanFactory::newBean('Calls');
        $call->name = 'UNIT TEST' . create_guid();
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();

        $call->save();

        $this->calls[] = $call;

        $post = [
            'embed_flag' => 0,
            'deleted' => 0,
            'name' => 'Test Note',
            'description' => 'This is a test note',
            'assigned_user_id' => 1,
        ];

        $restReply = $this->restCall("Calls/{$call->id}/link/notes", $post, 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->notes[] = BeanFactory::getBean('Notes', $restReply['reply']['related_record']['id']);
        $this->assertEquals($restReply['reply']['related_record']['parent_id'], $call->id, 'Call ID was not the parent id of the note.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], 'Calls', 'Call Module was not the parent type of the note.');

        // try with leads and notes
        $lead = BeanFactory::newBean('Leads');
        $lead->name = 'Unit Test' . create_guid();
        $lead->save();
        $this->leads[] = $lead;

        $post = [
            'name' => 'CALL FOR LEAD ' . create_guid(),
            'parent_id' => $lead->id,
            'parent_type' => 'Leads',
        ];

        $restReply = $this->restCall("Leads/{$lead->id}/link/calls", $post, 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id'], 'ID was not set for the related record');
        $this->calls[] = BeanFactory::getBean('Calls', $restReply['reply']['related_record']['id']);
        $this->assertEquals($restReply['reply']['related_record']['parent_id'], $lead->id, 'Lead ID was not the parent id of the call.');
        $this->assertEquals($restReply['reply']['related_record']['parent_type'], 'Leads', 'Leads Module was not the parent type of the call.');
    }
}
