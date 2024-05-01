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

/*
 * Tests mass update Rest api.
 */

class RestAddToProspectListTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectListsUtilities::removeAllCreatedProspectLists();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
    }

    /*
     * This function simulates job queue to call SugarJobMassUpdate::run().
     * @return Boolean false when error occurs, otherwise true
     */
    protected function runJob($id)
    {
        $schedulerJob = new SchedulersJob();
        $schedulerJob->retrieve($id);

        $job = new SugarJobMassUpdate();
        $job->setJob($schedulerJob);
        $ret = $job->run($schedulerJob->data);
        if (is_array($ret) && !empty($ret)) {
            foreach ($ret as $jid) {
                $schedulerJob = new SchedulersJob();
                $schedulerJob->retrieve($jid);
                $job = new SugarJobMassUpdate();
                $job->setJob($schedulerJob);
                $job->run($schedulerJob->data);
            }
        }

        return true;
    }

    /*
     * This function Adds 2 contacts to a specified ProspectList.
     */
    public function testAddToProspectListSelectedIds()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'massupdate_params' => [
                'uid' => [$contact1->id, $contact2->id],
                'prospect_lists' => [
                    $prospectList->id,
                ],
            ],
            'module' => 'Contacts',
        ];

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        [$result, $uids] = $this->fetchProspectListInfo($args['module'], $prospectList->id);
        $this->assertEquals(2, safeCount($result), 'ProspectList Does Not Contain Expected Prospect Relationships');
        $this->assertTrue(in_array($contact1->id, $uids), 'First Contact Expected In Prospect List');
        $this->assertTrue(in_array($contact2->id, $uids), 'Second Contact Expected In Prospect List');
    }

    /*
     * This function Adds Same Lead to multiple ProspectLists
     */
    public function testAddProspectToMultipleProspectLists()
    {
        $lead = SugarTestLeadUtilities::createLead();
        $prospectList1 = SugarTestProspectListsUtilities::createProspectLists();
        $prospectList2 = SugarTestProspectListsUtilities::createProspectLists();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'massupdate_params' => [
                'uid' => [$lead->id],
                'prospect_lists' => [
                    $prospectList1->id,
                    $prospectList2->id,
                ],
            ],
            'module' => 'Leads',
        ];

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        [$result_1, $uids_1] = $this->fetchProspectListInfo($args['module'], $prospectList1->id, $lead->id);
        [$result_2, $uids_2] = $this->fetchProspectListInfo($args['module'], $prospectList2->id, $lead->id);
        $this->assertEquals($uids_1, $uids_2, 'ProspectLists Do Not Both Contain Prospect as Expected');
    }


    /*
     * This function Adds Same Lead to multiple ProspectLists
     */
    public function testAddToProspectListWithFilter()
    {
        $contactValues1 = [
            'first_name' => 'Victor',
            'last_name' => 'Zaxby',
        ];
        $contactValues2 = [
            'first_name' => 'Hank',
            'last_name' => 'Ziebart',
        ];
        $contactValues3 = [
            'first_name' => 'David',
            'last_name' => 'Copperfield',
        ];
        $contact1 = SugarTestContactUtilities::createContact('', $contactValues1);
        $contact2 = SugarTestContactUtilities::createContact('', $contactValues2);
        $contact3 = SugarTestContactUtilities::createContact('', $contactValues3);
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'massupdate_params' => [
                'entire' => true, // entire selected list
                'prospect_lists' => [
                    $prospectList->id,
                ],

                'filter' => [
                    [
                        '$or' => [
                            ['first_name' => ['$starts' => 'V']],
                            ['last_name' => ['$starts' => 'C']],
                        ],
                    ],
                ],
            ],
            'module' => 'Contacts',
        ];

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        [$result, $uids] = $this->fetchProspectListInfo($args['module'], $prospectList->id);
        $this->assertTrue(in_array($contact1->id, $uids), 'First Contact Expected In Prospect List');
        $this->assertFalse(in_array($contact2->id, $uids), 'Second Contact Not Expected In Prospect List');
        $this->assertTrue(in_array($contact3->id, $uids), 'Third Contact Expected In Prospect List');
    }


    /*
     * This function tests mass delete with select ids.
     * This function creates 3 contacts, adds 2 to a Prospect List and then removes one of them
     * The expected contact (and only the expected contact) should be removed (deleted=1).
     */
    public function testRemoveFromProspectListSelectedIds()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $contact3 = SugarTestContactUtilities::createContact();
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'massupdate_params' => [
                'uid' => [$contact1->id, $contact3->id],
                'prospect_lists' => [
                    $prospectList->id,
                ],
            ],
            'module' => 'Contacts',
        ];

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        [$result, $uids] = $this->fetchProspectListInfo($args['module'], $prospectList->id);
        $this->assertEquals(2, safeCount($result), 'ProspectList Does Not Contain Expected Number of Prospect Relationships');
        $this->assertTrue(in_array($contact1->id, $uids), 'First Contact Expected In Prospect List');
        $this->assertFalse(in_array($contact2->id, $uids), 'Second Contact Not Expected In Prospect List');
        $this->assertTrue(in_array($contact3->id, $uids), 'Third Contact Expected In Prospect List');

        $args = [
            'massupdate_params' => [
                'uid' => [$contact1->id],
                'prospect_lists' => [
                    $prospectList->id,
                ],
            ],
            'module' => 'Contacts',
        ];

        $apiClass = new MassUpdateApi();
        $apiClass->massDelete($api, $args);
        [$result, $uids] = $this->fetchProspectListInfo($args['module'], $prospectList->id);

        $this->assertEquals(1, safeCount($result), 'ProspectList Does Not Contain Expected Number of Prospect Relationships');
        $this->assertTrue(in_array($contact3->id, $uids), 'Third Contact Expected In Prospect List');
    }

    /*
     * This function Adds Set of Leads to multiple Prospect Lists
     * All Leads are then Removed from one of the Lists
     * Other List should remain in tact - and should contain full set of Leads
     */
    public function testRemoveMultipleProspectsFromMultipleProspectLists()
    {
        $leadsList = [];
        for ($i = 0; $i < 5; $i++) {
            $lead = SugarTestLeadUtilities::createLead();
            $leadsList[] = $lead->id;
        }
        $prospectList1 = SugarTestProspectListsUtilities::createProspectLists();
        $prospectList2 = SugarTestProspectListsUtilities::createProspectLists();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = [
            'massupdate_params' => [
                'uid' => $leadsList,
                'prospect_lists' => [
                    $prospectList1->id,
                    $prospectList2->id,
                ],
            ],
            'module' => 'Leads',
        ];

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        [$result_1, $uids_1] = $this->fetchProspectListInfo($args['module'], $prospectList1->id);
        sort($leadsList);
        sort($uids_1);
        $this->assertEquals($leadsList, $uids_1, '2 ID Lists Expected to be Same');

        //--- Remove all Leads from List 1 only
        $args['massupdate_params']['prospect_lists'] = [$prospectList1->id];
        $apiClass = new MassUpdateApi();
        $apiClass->massDelete($api, $args);

        [$result_1, $uids_1] = $this->fetchProspectListInfo($args['module'], $prospectList1->id);
        [$result_2, $uids_2] = $this->fetchProspectListInfo($args['module'], $prospectList2->id);

        $this->assertEquals(0, safeCount($uids_1), 'ProspectList 1 Expected To Be Empty');
        sort($leadsList);
        sort($uids_2);
        $this->assertEquals($uids_2, $leadsList, 'ProspectList 2 Expected To Still Contain All Created Leads');
    }


    //-------- Private Helper Functions ---------------

    /**
     * @param string $related_type -  one of  Accounts,Contacts,Leads,Prospects
     * @param string $prospect_list_id
     * @param string $related_id
     * @return array
     */
    private function fetchProspectListInfo($related_type, $prospect_list_id = null, $related_id = null)
    {
        if (strtolower($related_type) != 'accounts') {
            $sql = 'SELECT prospect_list_id, related_id, related_type, c.first_name, c.last_name from prospect_lists_prospects as p left join ' . strtolower($related_type) . " as c on p.related_id=c.id WHERE p.related_type='";
            $sql .= $related_type;
            $sql .= "' AND p.deleted=0 order by c.last_name, c.first_name";
        } else {
            $sql = 'SELECT prospect_list_id, related_id, related_type, c.name from prospect_lists_prospects as p left join ' . strtolower($related_type) . " as c on p.related_id=c.id WHERE p.related_type='";
            $sql .= $related_type;
            $sql .= "' AND p.deleted=0 order by c.name";
        }

        $result = $GLOBALS['db']->query($sql);
        $rows = [];
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            if ($prospect_list_id == null || $prospect_list_id == $row['prospect_list_id']) {
                if ($related_id == null || $related_id == $row['related_id']) {
                    $rows[] = $row;
                }
            }
        }
        // print_r($rows);
        $related_ids = array_map(
            function ($row) {
                return $row['related_id'];
            },
            $rows
        );
        return [$rows, $related_ids];
    }
}
