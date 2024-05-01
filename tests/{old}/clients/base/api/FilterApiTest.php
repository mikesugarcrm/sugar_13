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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group ApiTests
 */
class FilterApiTest extends TestCase
{
    /**
     * @var \RestService|mixed
     */
    public $serviceMock;
    public static $notes;
    public static $opps;
    public static $accounts;
    public static $meetings;
    public static $oldLimit;
    public static $test5AccountFilter;

    /** @var FilterApi */
    private $filterApi;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        // Need at least 20 records so we can test pagination
        for ($i = 0; $i < 20; $i++) {
            $account = BeanFactory::newBean('Accounts');
            $account->id = 'UNIT-TEST-' . create_guid_section(10);
            $account->new_with_id = true;
            $account->name = "TEST $i Account";
            $account->billing_address_postalcode = ($i % 10) . '0210';
            $account->save();
            self::$accounts[] = $account;
            for ($ii = 0; $ii < 2; $ii++) {
                $opp = BeanFactory::newBean('Opportunities');
                $opp->id = 'UNIT-TEST-' . create_guid_section(10);
                $opp->new_with_id = true;
                $opp->name = "TEST $ii Opportunity FOR $i Account";
                $opp->amount = $ii * 10000;
                $opp->expected_close_date = '12-1' . $ii . '-2012';
                $opp->save();
                self::$opps[] = $opp;
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
                self::$notes[] = $note;
            }

            // create some meetings
            $meeting = SugarTestMeetingUtilities::createMeeting('UNIT-TEST-' . create_guid_section(10));
            $meeting->name = 'Test Meeting';
            $day = 10 + $i;
            $meeting->date_start = gmdate('Y-m-d H:i:s', gmmktime(1, 0, 0, 8, $day, 2013));
            $meeting->save();
            self::$meetings[] = $meeting;
        }

        // create a simple predefined filter
        self::$test5AccountFilter = SugarTestFilterUtilities::createUserFilter(
            'admin',
            'test5AccountFilter',
            json_encode([['name' => 'TEST 5 Account']]),
            'test5AccountFilter'
        );

        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();
        if (!empty($GLOBALS['sugar_config']['max_list_limit'])) {
            self::$oldLimit = $GLOBALS['sugar_config']['max_list_limit'];
        }
    }

    protected function setUp(): void
    {
        $this->filterApi = new FilterApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '" . $GLOBALS['current_user']->id . "'");
        $GLOBALS['db']->query("DELETE FROM subscriptions WHERE created_by = '{$GLOBALS['current_user']->id}'");
        $GLOBALS['sugar_config']['max_list_limit'] = self::$oldLimit;
        SugarConfig::getInstance()->clearCache();
    }

    public static function tearDownAfterClass(): void
    {
        if (safeCount(self::$accounts)) {
            $accountIds = [];
            foreach (self::$accounts as $account) {
                $accountIds[] = $account->id;
            }
            $accountIds = "('" . implode("','", $accountIds) . "')";
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
        }

        // Opportunities clean up
        if (safeCount(self::$opps)) {
            $oppIds = [];
            foreach (self::$opps as $opp) {
                $oppIds[] = $opp->id;
            }
            $oppIds = "('" . implode("','", $oppIds) . "')";
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
            $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
        }

        // Notes cleanup
        if (safeCount(self::$notes)) {
            $noteIds = [];
            foreach (self::$notes as $note) {
                $noteIds[] = $note->id;
            }
            $noteIds = "('" . implode("','", $noteIds) . "')";

            $GLOBALS['db']->query("DELETE FROM notes WHERE id IN {$noteIds}");
        }

        if (safeCount(self::$meetings)) {
            $meetingIds = [];
            foreach (self::$meetings as $meeting) {
                $meetingIds[] = $meeting->id;
            }
            $meetingIds = "('" . implode("','", $meetingIds) . "')";

            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN {$meetingIds}");
        }

        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestHelper::tearDown();
    }

    public function testFilterListSetupStringFilterUnsupported()
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);

        // Making $args['filter'] a JSON-encoded string is only supported
        // on endpoints where 'filter' is a jsonParam. They must be converted
        // before FilterApi::filterListSetup is called.
        $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Contacts',
                'filter' => '[{"id": 1}]',
            ]
        );
    }

    public function testSimpleFilter()
    {
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['name' => 'TEST 7 Account']],
                'fields' => 'id,name',
            ]
        );
        $this->assertEquals('TEST 7 Account', $reply['records'][0]['name'], 'Simple: The name is not set correctly');
        $this->assertEquals(-1, $reply['next_offset'], 'Simple: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'Simple: Returned too many results');
    }

    public function testEmptyFilterDefinition()
    {
        // FIXME TY-1821: Empty filter definitions are currently supported to
        // maintain backward compatibility on v10. This behaviour will change on
        // one of the upcoming API versions.
        $result = $this->filterApi->filterListSetup(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => '',
            ]
        );
        $this->assertEquals([], $result[0]['filter'], 'Empty definition: filter becomes empty array');
    }

    public function testBadLinkName()
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['accounts.name' => 'TEST 3 Account']],
            ]
        );
    }

    public function testFilterId()
    {
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter_id' => self::$test5AccountFilter->id,
                'fields' => 'id,name',
            ]
        );
        $this->assertEquals('TEST 5 Account', $reply['records'][0]['name'], 'FilterID: The name is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'FilterID: Returned the wrong number of results');
    }

    public function testFilterNotFound()
    {
        $filterId = 'iDefinitelyDoNotExist';
        $this->expectException(SugarApiExceptionNotFound::class);
        $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter_id' => $filterId,
            ]
        );
    }

    public function testFilterIdQMutuallyExclusive()
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter_id' => self::$test5AccountFilter->id,
                'q' => "some query we don't care about",
            ]
        );
    }

    /**
     * Provider for testMergeDefinitionAndId.
     *
     * @return array Data for testMergeDefinitionAndId.
     */
    public function providerMergeDefinitionAndId()
    {
        $test5AccountFilterDef = [
            'name' => 'TEST 5 Account',
        ];
        $nameInFilterDef = [
            'name' => [
                '$in' => [
                    'Test 3 Account',
                    'Test 4 Account',
                ],
            ],
        ];
        $idStartsFilterDef = [
            'id' => [
                '$starts' => 'UNIT-TEST-',
            ],
        ];
        return [
            // check with an empty filter definition
            [
                [],
                [$test5AccountFilterDef],
            ],

            // check with an identical filter
            [
                [$test5AccountFilterDef],
                [$test5AccountFilterDef, $test5AccountFilterDef],
            ],

            // check with a more complicated filter
            // note that since filter definitions have numeric keys,
            // the filters do not overlap even though they both filter on 'name'
            [
                [$nameInFilterDef, $idStartsFilterDef],
                [$test5AccountFilterDef, $nameInFilterDef, $idStartsFilterDef],
            ],
        ];
    }

    /**
     * @dataProvider providerMergeDefinitionAndId
     * @param array $filterDefinition Filter definition.
     * @param array $merged The expected merged array.
     * @throws SugarApiExceptionError If retrieving a predefined filter failed.
     * @throws SugarApiExceptionInvalidParameter If any arguments are invalid.
     * @throws SugarApiExceptionNotAuthorized If we lack ACL access.
     */
    public function testMergeDefinitionAndId($filterDefinition, $merged)
    {
        [$args, $q, $options, $seed] = $this->filterApi->filterListSetup(
            $this->serviceMock,
            [
                'filter_id' => self::$test5AccountFilter->id,
                'filter' => $filterDefinition,
                'fields' => 'name,id',
                'module' => 'Accounts',
            ]
        );
        $this->assertEquals($merged, $args['filter'], 'Did not merge filters correctly');
    }

    /**
     * Ensure getDefaultLimit works.
     */
    public function testGetDefaultLimit()
    {
        $this->assertEquals(
            $this->filterApi->getDefaultLimit(),
            SugarTestReflection::getProtectedValue($this->filterApi, 'defaultLimit')
        );
    }

    public function testSimpleJoinFilter()
    {
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['notes.name' => 'Test 3 Note']],
                'fields' => 'id,name',
            ]
        );
        $this->assertEquals(
            'TEST 3 Account',
            $reply['records'][0]['name'],
            'SimpleJoin: The account name is not set correctly'
        );
        $this->assertEquals(-1, $reply['next_offset'], 'SimpleJoin: Next offset is not set correctly');

        $reply = $this->filterApi->getFilterListCount(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['notes.name' => 'Test 3 Note']],
                'fields' => 'id,name',
            ]
        );
        $this->assertSame(1, $reply['record_count'], 'SimpleJoin: Returned too many results');
    }

    /**
     * @dataProvider providerMaxListAndNumLimit
     */
    public function testMaxListAndNumLimit($max_list_limit, $max_num, $expected)
    {
        $GLOBALS['sugar_config']['max_list_limit'] = $max_list_limit;
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['name' => ['$starts' => 'TEST 1']]],
                'fields' => 'id,name',
                'max_num' => $max_num,
            ]
        );
        $this->assertEquals($expected, $reply['next_offset'], 'Next offset is not set correctly');
        $this->assertEquals($expected, safeCount($reply['records']), 'Returned wrong count of results');
    }

    public static function providerMaxListAndNumLimit()
    {
        return [
            [
                'max_list_limit' => 5,
                'max_num' => '',
                'expected' => 5,
            ],
            [
                'max_list_limit' => 4,
                'max_num' => 2,
                'expected' => 2,
            ],
            [
                'max_list_limit' => 3,
                'max_num' => 10,
                'expected' => 3,
            ],
            [
                'max_list_limit' => 2,
                'max_num' => -1,
                'expected' => 2,
            ],
            [
                'max_list_limit' => 1,
                'max_num' => -10,
                'expected' => 1,
            ],
        ];
    }

    public function testSimpleFilterWithOffset()
    {
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['name' => ['$starts' => 'TEST 1']]],
                'fields' => 'id,name',
                'max_num' => '5',
            ]
        );
        $this->assertEquals(5, $reply['next_offset'], 'Offset-1: Next offset is not set correctly');
        $this->assertEquals(5, safeCount($reply['records']), 'Offset-1: Returned too many results');

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['name' => ['$starts' => 'TEST 1']]],
                'fields' => 'id,name',
                'max_num' => '5',
                'offset' => '5',
            ]
        );
        $this->assertEquals(10, $reply['next_offset'], 'Offset-2: Next offset is not set correctly');
        $this->assertEquals(5, safeCount($reply['records']), 'Offset-2: Returned too many results');

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['name' => ['$starts' => 'TEST 1']]],
                'fields' => 'id,name',
                'max_num' => '5',
                'offset' => '10',
            ]
        );
        $this->assertEquals(-1, $reply['next_offset'], 'Offset-3: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'Offset-3: Returned too many results');

        $reply = $this->filterApi->getFilterListCount(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['name' => ['$starts' => 'TEST 1']]],
                'fields' => 'id,name',
            ]
        );
        $this->assertSame(11, $reply['record_count'], 'SimpleJoin: Returned too many results');
    }

    public function testOrFilter()
    {
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [
                    [
                        '$or' => [
                            ['name' => 'TEST 7 Account'],
                            ['name' => 'TEST 17 Account'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );

        $this->assertEquals('TEST 17 Account', $reply['records'][0]['name'], 'Or-1: The name is not set correctly');
        $this->assertEquals('TEST 7 Account', $reply['records'][1]['name'], 'Or-2: The name is not set correctly');
        $this->assertEquals(-1, $reply['next_offset'], 'Or: Next offset is not set correctly');
        $this->assertEquals(2, safeCount($reply['records']), 'Or: Returned too many results');
        $reply = $this->filterApi->getFilterListCount(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [
                    [
                        '$or' => [
                            ['name' => 'TEST 7 Account'],
                            ['name' => 'TEST 17 Account'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );
        $this->assertSame(2, $reply['record_count'], 'SimpleJoin: Returned too many results');
    }

    public function testAndFilter()
    {
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [
                    [
                        '$and' => [
                            ['name' => ['$starts' => 'TEST 1']],
                            ['billing_address_postalcode' => '70210'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );
        $this->assertEquals('TEST 17 Account', $reply['records'][0]['name'], 'And: The name is not set correctly');
        $this->assertEquals(-1, $reply['next_offset'], 'And: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'And: Returned too many results');
    }

    public function testNoFilter()
    {
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            ['module' => 'Accounts', 'filter' => [], 'max_num' => '10']
        );

        $this->assertNotEmpty($reply, 'Empty filter returned no results.');
        $this->assertEquals(10, $reply['next_offset'], 'Empty filter did not return at least 10 results.');
    }

    public function testNoFilterNoDeleted()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, true);
        $uh = BeanFactory::newBean('UpgradeHistory');
        $uh->save();

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            ['module' => 'UpgradeHistory', 'filter' => [], 'max_num' => '-1']
        );

        $this->assertNotEmpty($reply['records'], 'Empty filter returned no results.');
        $records = array_column($reply['records'], 'id');
        $this->assertContains($uh->id, $records, 'No filter with no deleted field bean did not return results.');
    }

    public function testEmptyFilters()
    {
        $field = $this->equalTo('account_type');
        $bean = new Account();
        $q = new SugarQuery();
        $q->from($bean);

        /** @var SugarQuery_Builder_Where|MockObject $where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->setMethods(['isEmpty'])
            ->getMockForAbstractClass();
        $where->expects($this->once())->method('isEmpty')->with($field)->will($this->returnSelf());

        FilterApiMock::addFilters([
            [
                'account_type' => [
                    '$empty' => '',
                ],
            ],
        ], $where, $q);
    }

    public function testNotEmptyFilters()
    {
        $field = $this->equalTo('account_type');
        $bean = new Account();
        $q = new SugarQuery();
        $q->from($bean);

        /** @var SugarQuery_Builder_Where|MockObject $where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->setMethods(['isNotEmpty'])
            ->getMockForAbstractClass();
        $where->expects($this->once())->method('isNotEmpty')->with($field)->will($this->returnSelf());

        FilterApiMock::addFilters([
            [
                'account_type' => [
                    '$not_empty' => '',
                ],
            ],
        ], $where, $q);
    }

    /**
     * @param array $filter Filter definition
     * @dataProvider followerFilterProvider
     */
    public function testFollowerFilter(array $filter)
    {
        $GLOBALS['db']->query("DELETE FROM subscriptions WHERE created_by = '{$GLOBALS['current_user']->id}'");

        Subscription::subscribeUserToRecord($GLOBALS['current_user'], self::$accounts[1]);

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => $filter,
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );
        $this->assertNotEmpty($reply['records']);
        $this->assertEquals(1, safeCount($reply['records']));
    }

    public static function followerFilterProvider()
    {
        return [
            'simple' => [
                [
                    [
                        '$following' => '',
                    ],
                ],
            ],
            'BR-1432' => [
                [
                    [
                        '$or' => [
                            [
                                'id' => 'non-existing-id',
                            ],
                            [
                                '$following' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testFavoriteFilter()
    {
        $this->assertEquals(
            'TEST 4 Account',
            self::$accounts[4]->name,
            'Favorites: Making sure the name is correct before favoriting.'
        );

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Accounts', self::$accounts[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Accounts';
        $fav->record_id = self::$accounts[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['$favorite' => '']],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );
        $this->assertEquals('TEST 4 Account', $reply['records'][0]['name'], 'Favorites: The name is not set correctly');
        $this->assertEquals(-1, $reply['next_offset'], 'Favorites: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'Favorites: Returned too many results');
    }

    public function testRelatedFavoriteFilter()
    {
        $this->assertEquals(
            'TEST 0 Opportunity FOR 3 Account',
            self::$opps[6]->name,
            'FavRelated: Making sure the name is correct before favoriting.'
        );

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities', self::$opps[6]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = self::$opps[6]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();
        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['$favorite' => 'opportunities']],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );

        $this->assertEquals(
            'TEST 3 Account',
            $reply['records'][0]['name'],
            'FavRelated: The name is not set correctly'
        );
        $this->assertEquals(-1, $reply['next_offset'], 'FavRelated: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'FavRelated: Returned too many results');
    }

    public function testMultipleRelatedFavoriteFilter()
    {
        $this->assertEquals(
            'TEST 0 Opportunity FOR 0 Account',
            self::$opps[0]->name,
            'FavMulRelated: Making sure the opp name is correct before favoriting.'
        );

        $this->assertEquals(
            'Test 4 Note',
            self::$notes[4]->name,
            'FavMulRelated: Making sure the note name is correct before favoriting.'
        );

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Opportunities', self::$opps[0]->id);
        $fav->new_with_id = true;
        $fav->module = 'Opportunities';
        $fav->record_id = self::$opps[0]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Notes', self::$notes[4]->id);
        $fav->new_with_id = true;
        $fav->module = 'Notes';
        $fav->record_id = self::$notes[4]->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [
                    [
                        '$or' => [
                            ['$favorite' => 'opportunities'],
                            ['$favorite' => 'notes'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );

        $this->assertEquals(
            'TEST 0 Account',
            $reply['records'][0]['name'],
            'FavMulRelated: The first name is not set correctly'
        );
        $this->assertEquals(
            'TEST 4 Account',
            $reply['records'][1]['name'],
            'FavMulRelated: The second name is not set correctly'
        );
        $this->assertEquals(-1, $reply['next_offset'], 'FavMulRelated: Next offset is not set correctly');

        $reply = $this->filterApi->getFilterListCount(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [
                    [
                        '$or' => [
                            ['$favorite' => 'opportunities'],
                            ['$favorite' => 'notes'],
                        ],
                    ],
                ],
                'fields' => 'id,name',
            ]
        );

        $this->assertSame(3, $reply['record_count'], 'FavMulRelated: Returned too many results');
    }

    public function testGuestFilter()
    {
        $meetingId = self::$meetings[7]->id;
        self::$meetings[7]->load_relationship('users');
        self::$meetings[7]->users->add($GLOBALS['current_user']->id);
        self::$meetings[7]->save();

        $meetingReply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Meetings',
                'filter' => [['$guest' => '']],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ],
        );

        $this->assertEquals(1, safeCount($meetingReply['records']), 'Guest filter: unexpected meeting count');
        $this->assertEquals($meetingId, $meetingReply['records'][0]['id'], 'Guest filter: unexpected meeting id');
    }

    public function testOwnerFilter()
    {
        $this->assertEquals(
            'TEST 7 Account',
            self::$accounts[7]->name,
            'Owner: Making sure the name is correct before ownering.'
        );

        self::$accounts[7]->assigned_user_id = $GLOBALS['current_user']->id;
        self::$accounts[7]->save();

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['$owner' => '']],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );

        $this->assertEquals('TEST 7 Account', $reply['records'][0]['name'], 'Owner: The name is not set correctly');
        $this->assertEquals(-1, $reply['next_offset'], 'Owner: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'Owner: Returned too many results');
    }

    public function testRelatedOwnerFilter()
    {
        $this->assertEquals(
            'TEST 1 Opportunity FOR 3 Account',
            self::$opps[7]->name,
            'OwnerRelated: Making sure the name is correct before ownering.'
        );

        self::$opps[7]->assigned_user_id = $GLOBALS['current_user']->id;
        self::$opps[7]->save();

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => [['$owner' => 'opportunities']],
                'fields' => 'id,name',
                'order_by' => 'name:ASC',
            ]
        );

        $this->assertEquals(
            'TEST 3 Account',
            $reply['records'][0]['name'],
            'OwnerRelated: The name is not set correctly'
        );
        $this->assertEquals(-1, $reply['next_offset'], 'OwnerRelated: Next offset is not set correctly');
        $this->assertEquals(1, safeCount($reply['records']), 'OwnerRelated: Returned too many results');
    }

    public function testBetweenDates()
    {
        // set the timezone to something out of range
        $GLOBALS['current_user']->setPreference('timezone', 'Pacific/Niue');
        $GLOBALS['current_user']->savePreferencesToDB();
        $GLOBALS['current_user']->reloadPreferences();

        $user = $GLOBALS['current_user'];
        unset($GLOBALS['current_user']);
        unset($_SESSION);
        $GLOBALS['current_user'] = $user;

        $leftDate = '2013-08-10';
        $rightDate = '2013-08-29';

        $reply = $this->filterApi->filterList(
            $this->serviceMock,
            [
                'module' => 'Meetings',
                'filter' => [['date_start' => ['$between' => [$leftDate, $rightDate]]]],
                'fields' => 'id,name',
            ]
        );

        $this->assertNotEmpty($reply['records']);

        // the first one is out of range, so we should only get 19 records back
        $this->assertEquals(19, safeCount($reply['records']));
    }

    /**
     * Test query execution with distinct/offset compensation
     *
     * @covers       FilterApi::runQuery
     * @covers       FilterApi::parseQueryResults
     * @dataProvider providerTestRunQueryOffsetCompensation
     * @group unit
     */
    public function testRunQueryOffsetCompensation($numBeans, $compensation, $limit, $offset, $expected)
    {
        // prepare fetched bean array
        $beans = array_fill(0, $numBeans, new Account());
        $beans['_rows'] = array_fill(0, $numBeans, []);
        $beans['_distinctCompensation'] = $compensation;

        $query = new SugarQuery();

        // setup seed bean
        $seed = $this->getMockBuilder('SugarBean')
            ->setMethods(['call_custom_logic', 'fetchFromQuery'])
            ->getMock();

        // expected query options when calling SugarBean::fetchFromQuery
        $queryOptions = [
            'returnRawRows' => true,
            'compensateDistinct' => true,
        ];

        $seed->expects($this->once())
            ->method('fetchFromQuery')
            ->with(
                $this->equalTo($query),
                $this->equalTo([]),
                $this->equalTo($queryOptions)
            )
            ->will($this->returnValue($beans));

        // sut
        $filterApi = $this->getMockBuilder('FilterApi')
            ->setMethods(['populateRelatedFields', 'formatBeans'])
            ->getMock();

        $options = ['limit' => $limit, 'offset' => $offset];
        $methodArgs = [$this->serviceMock, [], $query, $options, $seed];
        $result = SugarTestReflection::callProtectedMethod($filterApi, 'runQuery', $methodArgs);

        $this->assertEquals(
            $expected,
            $result['next_offset'],
            'Wrong next_offset returned'
        );
    }

    public function providerTestRunQueryOffsetCompensation()
    {
        return [

            // bean records less than limit
            [
                2,
                0, // compensation
                5, // limit
                0, // offset
                -1, // expected next_offset
            ],

            // bean records equal to limit
            [
                5,
                0, // compensation
                5, // limit
                0, // offset
                -1, // expected next_offset
            ],

            // bean records equal to limit + 1
            [
                6,
                0, // compensation
                5, // limit
                0, // offset
                5, // expected next_offset
            ],

            // bean records equal to limit, but with compensation
            [
                5,
                1, // compensation
                5, // limit
                0, // offset
                5, // expected next_offset
            ],

            // bean records less than limit, but with compensation
            [
                3,
                3, // compensation
                5, // limit
                0, // offset
                5, // expected next_offset
            ],

            // bean records less than limit and combination not reaching threshold
            [
                3,
                2, // compensation
                5, // limit
                0, // offset
                -1, // expected next_offset
            ],

            // compensation is bigger than limit
            [
                3,
                2, // compensation
                1, // limit
                0, // offset
                1, // expected next_offset
            ],
        ];
    }

    /**
     * Test select fields options set by parseArguments
     * @covers FilterApi::parseArguments
     * @group unit
     */
    public function testParseArgumentsSelectFields()
    {
        $filter = $this->getMockBuilder('FilterApiMock')
            ->disableOriginalConstructor()
            ->setMethods(['getFieldsFromArgs'])
            ->getMock();

        $filter->expects($this->once())
            ->method('getFieldsFromArgs')
            ->will($this->returnValue([]));

        $service = $this->getMockBuilder('ServiceBase')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $filter->parseArguments($service, ['module' => 'any-module']);
    }

    /**
     * Integration test returned fields based on fields list and/or view parameter
     * @dataProvider providerTestFilterReturnedField
     */
    public function testFilterReturnedFields($args, $expected, $suppressed)
    {
        $reply = $this->filterApi->filterList($this->serviceMock, $args);

        $this->assertArrayHasKey('records', $reply, 'Invalid reply');
        $this->assertArrayHasKey(0, $reply['records'], 'No records found');

        foreach ($expected as $field) {
            $this->assertArrayHasKey(
                $field,
                $reply['records'][0],
                "Expected field $field not present"
            );
        }

        foreach ($suppressed as $field) {
            $this->assertArrayNotHasKey(
                $field,
                $reply['records'][0],
                "Field $field should not be present"
            );
        }
    }

    public function providerTestFilterReturnedField()
    {
        return [

            // only fields specified
            [
                [
                    'module' => 'Accounts',
                    'fields' => 'name,billing_address_street',
                ],
                [
                    'name',
                    'billing_address_street',
                ],
                [
                    'billing_address_country',
                    'website',
                ],
            ],

            // only view specified
            [
                [
                    'module' => 'Accounts',
                    'view' => 'list',
                ],
                [
                    'name',
                    'billing_address_country',
                ],
                [
                    'billing_address_street',
                    'website',
                ],
            ],

            // both fields and view
            [
                [
                    'module' => 'Accounts',
                    'fields' => 'name,billing_address_street',
                    'view' => 'list',
                ],
                [
                    'name',
                    'billing_address_street',
                    'billing_address_country',
                ],
                [
                    'website',
                ],
            ],

            // nothing specified, returns every field
            [
                [
                    'module' => 'Accounts',
                ],
                [
                    'name',
                    'billing_address_street',
                    'billing_address_country',
                    'website',
                ],
                [],
            ],
        ];
    }

    public function testAddFiltersWithFieldCompare()
    {
        /** @var SugarQuery $query */
        $query = SugarTestReflection::callProtectedMethod('FilterApi', 'getQueryObject', [
            BeanFactory::newBean('Accounts'),
            [
                'select' => ['name', 'contacts'],
                'order_by' => [],
                'limit' => null,
                'offset' => 0,
            ],
        ]);

        /** @var SugarQuery_Builder_Where|MockObject $where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->setMethods(['gt'])
            ->getMockForAbstractClass();
        $where->expects($this->once())->method('gt')->with(
            $this->equalTo('date_entered'),
            $this->equalTo(['$field' => 'date_modified']),
            $this->anything()
        );

        SugarTestReflection::callProtectedMethod(
            'FilterApi',
            'addFilters',
            [
                [
                    [
                        'date_entered' => [
                            '$gt' => [
                                '$field' => 'date_modified',
                            ],
                        ],
                    ],
                ],
                $where,
                $query,
            ]
        );
    }

    /**
     * When we need $dateRange filter then $dateRange method should receive bean as parameter
     */
    public function testAddFiltersDateRange()
    {
        $bean = new Account();
        $q = new SugarQuery();
        $q->from($bean);
        /** @var SugarQuery_Builder_Where|MockObject $where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->setMethods(['dateRange'])
            ->getMockForAbstractClass();
        $where->expects($this->once())->method('dateRange')->with(
            $this->equalTo('date_entered'),
            $this->equalTo(''),
            $this->equalTo($bean)
        );
        FilterApiMock::addFilters([
            [
                'date_entered' => [
                    '$dateRange' => '',
                ],
            ],
        ], $where, $q);
    }

    public function testGetQueryObjectDoesNotSelectLinkFields()
    {
        $seed = BeanFactory::newBean('Accounts');
        /** @var SugarQuery $query */
        $query = SugarTestReflection::callProtectedMethod('FilterApi', 'getQueryObject', [
            $seed,
            [
                'select' => ['name', 'contacts'],
                'order_by' => [],
                'limit' => null,
                'offset' => 0,
            ],
        ]);
        $this->assertTrue($query->select->checkField('name', $seed->getTableName()));
        $this->assertFalse($query->select->checkField('contacts', $seed->getTableName()));
    }

    public function mapDistanceDataProvider()
    {
        $pi = pi();

        return [
            [
                1.222,
                4.23233,
                5,
                "~latitude \* $pi / 180\) - \(1.222 \* $pi / 180\).*longitude \* $pi / 180\) - \(4.23233 \* $pi / 180\).*6371\) < 5~is",
            ],
            [
                '(select 1)',
                '(select *)',
                'sleep(3)',
                "~latitude \* $pi / 180\) - \(0 \* $pi / 180\).*longitude \* $pi / 180\) - \(0 \* $pi / 180\).*6371\) < 0~is",
            ],
        ];
    }

    /**
     * @dataProvider mapDistanceDataProvider
     * @param $latitude
     * @param $longitude
     * @param $radius
     * @param $expectedRegexp
     * @return void
     * @throws SugarQueryException
     */
    public function testApplyMapDistance($latitude, $longitude, $radius, $expectedRegexp)
    {
        $administration = $this->getMockBuilder(\Administration::class)
            ->onlyMethods(['retrieveSettings'])
            ->getMock();
        $administration->method('retrieveSettings')
            ->willReturn(['maps_modulesData' => ['Account' => ['mappingType' => '']]]);
        $administration->id = 'adminID';
        $administration->module_name = 'Administration';

        \BeanFactory::registerBean($administration);

        $q = $this->getMockBuilder(SugarQuery::class)
            ->onlyMethods(['whereRaw'])
            ->getMock();
        $q->from(new \Account());
        $q->expects($this->once())
            ->method('whereRaw')
            ->with($this->matchesRegularExpression($expectedRegexp))
            ->will($this->returnSelf());
        FilterApi::applyMapDistance($q, ['latitude' => $latitude, 'longitude' => $longitude], $radius, 'km');
        \BeanFactory::unregisterBean($administration);
    }
}

class FilterApiMock extends FilterApi
{
    public function parseArguments(ServiceBase $api, array $args, SugarBean $seed = null)
    {
        return parent::parseArguments($api, $args, $seed);
    }

    public static function addFilters(array $filterDefs, SugarQuery_Builder_Where $where, SugarQuery $q)
    {
        parent::addFilters($filterDefs, $where, $q);
    }
}
