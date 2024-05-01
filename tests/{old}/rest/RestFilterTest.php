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


class RestFilterTest extends RestTestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Need at least 20 records so we can test pagination
        for ($i = 0; $i < 20; $i++) {
            $account = BeanFactory::newBean('Accounts');
            $account->id = 'UNIT-TEST-' . create_guid_section(10);
            $account->new_with_id = true;
            $account->name = "TEST $i Account";
            $account->billing_address_postalcode = ($i % 10) . '0210';
            $account->save();
            $this->accounts[] = $account;
            for ($ii = 0; $ii < 2; $ii++) {
                $opp = BeanFactory::newBean('Opportunities');
                $opp->id = 'UNIT-TEST-' . create_guid_section(10);
                $opp->new_with_id = true;
                $opp->name = "TEST $ii Opportunity FOR $i Account";
                $opp->amount = $ii * 10000;
                $opp->expected_close_date = '12-1' . $ii . '-2012';
                $opp->save();
                $this->opps[] = $opp;
                $account->load_relationship('opportunities');
                $account->opportunities->add([$opp]);
            }
            if ($i < 5) {
                // Only need a few notes
                $note = BeanFactory::newBean('Notes');
                $note->id = 'UNIT-TEST-' . create_guid_section(10);
                $note->new_with_id = true;
                $note->name = "Test $i Note";
                $note->description = "This is a note for account $i";
                $note->save();
                $account->load_relationship('notes');
                $account->notes->add([$note]);
                $note->save();
                $this->notes[] = $note;
            }
        }

        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '" . $GLOBALS['current_user']->id . "'");

        $this->cleanUpRecords();
        SugarTestFilterUtilities::removeAllCreatedFilters();
    }

    /**
     * @group rest
     */
    public function testSimpleFilter()
    {
        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"name":"TEST 7 Account"}]') . '&fields=id,name');
        $this->assertEquals('TEST 7 Account', $reply['reply']['records'][0]['name'], 'Simple: The name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'Simple: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['reply']['records']), 'Simple: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testSimpleFilterWithOffset()
    {
        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"name":{"$starts":"TEST 1"}}]') . '&fields=id,name&max_num=5');
        $this->assertEquals(5, $reply['reply']['next_offset'], 'Offset-1: Next offset is not set correctly');
        $this->assertEquals(5, safeCount($reply['reply']['records']), 'Offset-1: Returned too many results');

        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"name":{"$starts":"TEST 1"}}]') . '&fields=id,name&max_num=5&offset=5');
        $this->assertEquals(10, $reply['reply']['next_offset'], 'Offset-2: Next offset is not set correctly');
        $this->assertEquals(5, safeCount($reply['reply']['records']), 'Offset-2: Returned too many results');

        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"name":{"$starts":"TEST 1"}}]') . '&fields=id,name&max_num=5&offset=10');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'Offset-3: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['reply']['records']), 'Offset-3: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testOrFilter()
    {
        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"$or":[{"name":"TEST 7 Account"},{"name":"TEST 17 Account"}]}]') . '&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 17 Account', $reply['reply']['records'][0]['name'], 'Or-1: The name is not set correctly');
        $this->assertEquals('TEST 7 Account', $reply['reply']['records'][1]['name'], 'Or-2: The name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'Or: Next offset is not set correctly');
        $this->assertEquals(2, safeCount($reply['reply']['records']), 'Or: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testAndFilter()
    {
        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"$and":[{"name":{"$starts":"TEST 1"}},{"billing_address_postalcode":"70210"}]}]') . '&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 17 Account', $reply['reply']['records'][0]['name'], 'And: The name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'And: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['reply']['records']), 'And: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testFavoriteFilter()
    {
        $this->assertEquals('TEST 4 Account', $this->accounts[4]->name, 'Favorites: Making sure the name is correct before favoriting.');

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Accounts', $this->accounts[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Accounts';
        $fav->record_id = $this->accounts[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"$favorite":""}]') . '&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 4 Account', $reply['reply']['records'][0]['name'], 'Favorites: The name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'Favorites: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['reply']['records']), 'Favorites: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testRelatedFavoriteFilter()
    {
        $this->assertEquals('TEST 0 Opportunity FOR 3 Account', $this->opps[6]->name, 'FavRelated: Making sure the name is correct before favoriting.');

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities', $this->opps[6]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = $this->opps[6]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"$favorite":"opportunities"}]') . '&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 3 Account', $reply['reply']['records'][0]['name'], 'FavRelated: The name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'FavRelated: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['reply']['records']), 'FavRelated: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testMultipleRelatedFavoriteFilter()
    {
        $this->assertEquals('TEST 0 Opportunity FOR 0 Account', $this->opps[0]->name, 'FavMulRelated: Making sure the opp name is correct before favoriting.');

        $this->assertEquals('Test 4 Note', $this->notes[4]->name, 'FavMulRelated: Making sure the note name is correct before favoriting.');

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities', $this->opps[0]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = $this->opps[0]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Notes', $this->notes[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Notes';
        $fav->record_id = $this->notes[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"$or":[{"$favorite":"opportunities"},{"$favorite":"notes"}]}]') . '&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 0 Account', $reply['reply']['records'][0]['name'], 'FavMulRelated: The first name is not set correctly');
        $this->assertEquals('TEST 4 Account', $reply['reply']['records'][1]['name'], 'FavMulRelated: The second name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'FavMulRelated: Next offset is not set correctly');
        $this->assertEquals(2, safeCount($reply['reply']['records']), 'FavMulRelated: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testOwnerFilter()
    {
        $this->assertEquals('TEST 7 Account', $this->accounts[7]->name, 'Owner: Making sure the name is correct before ownering.');

        $this->accounts[7]->assigned_user_id = $GLOBALS['current_user']->id;
        $this->accounts[7]->save();

        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"$owner":""}]') . '&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 7 Account', $reply['reply']['records'][0]['name'], 'Owner: The name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'Owner: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['reply']['records']), 'Owner: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testRelatedOwnerFilter()
    {
        $this->assertEquals('TEST 1 Opportunity FOR 3 Account', $this->opps[7]->name, 'OwnerRelated: Making sure the name is correct before ownering.');

        $this->opps[7]->assigned_user_id = $GLOBALS['current_user']->id;
        $this->opps[7]->save();

        $reply = $this->restCall('Accounts/filter?filter=' . urlencode('[{"$owner":"opportunities"}]') . '&fields=id,name&order_by=name:ASC');
        $this->assertEquals('TEST 3 Account', $reply['reply']['records'][0]['name'], 'OwnerRelated: The name is not set correctly');
        $this->assertEquals(-1, $reply['reply']['next_offset'], 'OwnerRelated: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['reply']['records']), 'OwnerRelated: Returned too many results');
    }

    /**
     * @group rest
     */
    public function testUserFilterByName()
    {
        global $current_user;

        $filter = SugarTestFilterUtilities::createUserFilter($current_user->id, 'test_user_filter', json_encode(['name' => 'TEST 1 Account']));
        $this->assertEquals($filter->name, 'test_user_filter');
        $this->assertEquals($filter->filter_definition, '{"name":"TEST 1 Account"}');

        $reply = $this->restCall('Filters/' . $filter->id);
        $this->assertEquals('test_user_filter', $reply['reply']['name']);
        $this->assertEquals(json_decode('{"name":"TEST 1 Account"}', true), $reply['reply']['filter_definition']);

        $reply = $this->restCall('Filters', json_encode(['name' => 'test_user_filter2', 'filter_definition' => 'TEST 2 Account']), 'POST');
        $this->assertEquals('test_user_filter2', $reply['reply']['name']);
        $this->assertEquals('TEST 2 Account', $reply['reply']['filter_definition']);

        $id = $reply['reply']['id'];

        $reply = $this->restCall('Filters/' . $id, json_encode(['name' => 'test_user_filter3', 'filter_definition' => 'TEST 3 Account']), 'PUT');
        $this->assertEquals('test_user_filter3', $reply['reply']['name']);
        $this->assertEquals('TEST 3 Account', $reply['reply']['filter_definition']);

        $reply = $this->restCall('Filters/' . $id, [], 'DELETE');
        $this->assertEquals($id, $reply['reply']['id']);
    }

    /**
     * @group rest
     */
    public function testsPreviouslyUsedFilters()
    {
        global $current_user;

        $filter = SugarTestFilterUtilities::createUserFilter($current_user->id, 'test_user_filter', json_encode(['name' => 'TEST 1 Account']));
        $this->assertEquals($filter->name, 'test_user_filter');
        $this->assertEquals($filter->filter_definition, '{"name":"TEST 1 Account"}');

        $reply = $this->restCall('Filters/Accounts/used/', json_encode(['filters' => [$filter->id,]]), 'PUT');
        $this->assertEquals($filter->id, $reply['reply'][0]['id'], 'Test Put');

        $reply = $this->restCall('Filters/Accounts/used/');
        $this->assertEquals($filter->id, $reply['reply'][0]['id'], 'Test Get');

        $reply = $this->restCall('Filters/Accounts/used/' . $filter->id, [], 'DELETE');

        if (!empty($reply['reply'])) {
            foreach ($reply['reply'] as $record) {
                $this->assertNotEquals($filter->id, $record['id'], 'Test Delete');
            }
        } else {
            $this->assertEmpty($reply['reply'], 'Test Delete');
        }
    }

    /**
     * @group rest
     */
    public function testFilteringOnARelationship()
    {
        $account_id = $this->accounts[0]->id;
        $oppty_id = $this->opps[0]->id;
        $url = 'Accounts/' . $account_id . '/link/opportunities/filter?filter=' . urlencode('[{"name":{"$starts":"TEST 0 Opportunity"}}]');
        $reply = $this->restCall($url);
        $this->assertEquals(1, safeCount($reply['reply']['records']));
        $this->assertEquals($oppty_id, $reply['reply']['records'][0]['id']);
    }

    /**
     * @group rest
     */
    public function testNoFilter()
    {
        $reply = $this->restCall('Accounts/filter?max_num=10');
        $this->assertNotEmpty($reply['reply'], 'Empty filter returned no results.');
        $this->assertEquals(10, $reply['reply']['next_offset'], 'Empty filter did not return at least 10 results.');
    }
}
