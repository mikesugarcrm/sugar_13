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

use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Command\StateAwareRebuild;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

/**
 * @covers \Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denormalized
 * @covers NormalizedTeamSecurity
 * @covers TeamSecurity
 */
class TeamSecurityTest extends TestCase
{
    /**
     * @var User
     */
    private static $admin;

    /**
     * @var array<string,User>
     */
    private static $users = [];

    /**
     * @var array<string,Account>
     */
    private static $accounts = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        self::$admin = SugarTestHelper::setUp('current_user', [true, true]);

        self::configure(true, false, false);

        $command = Container::getInstance()->get(StateAwareRebuild::class);
        $command();

        self::createFixtures();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    /**
     * @test
     * @dataProvider sugarQuerySelectWithRelatedFieldProvider
     */
    public function sugarQuerySelectWithRelatedField($useDenorm, $useWhere, $prefetchForRetrieve, $userName, array $expected)
    {
        global $current_user;

        $current_user = self::$users[$userName];

        self::configure($useDenorm, $useWhere, $prefetchForRetrieve);

        $query = new SugarQuery();
        $query->from(BeanFactory::newBean('Contacts'));
        $query->select('last_name', 'account_name');
        $query->where()->equals('created_by', self::$admin->id);
        $query->orderBy('last_name', 'ASC');
        $data = $query->execute();

        $this->assertEquals($expected, $this->filterColumns($data, ['last_name', 'account_name']));
    }

    public static function sugarQuerySelectWithRelatedFieldProvider()
    {
        $expected = [
            'User #1' => [
                [
                    'last_name' => 'Contact #T1A1',
                    'account_name' => 'Account #T1',
                ], [
                    'last_name' => 'Contact #T2A2',
                    'account_name' => 'Account #T2',
                ], [
                    'last_name' => 'Contact #T2A3',
                    'account_name' => 'Account #T3',
                ],
            ],
            'User #2' => [
                [
                    'last_name' => 'Contact #T2A2',
                    'account_name' => 'Account #T2',
                ], [
                    'last_name' => 'Contact #T2A3',
                    'account_name' => 'Account #T3',
                ],
            ],
            'User #3' => [
                [
                    'last_name' => 'Contact #T3A2',
                    'account_name' => 'Account #T2',
                ], [
                    'last_name' => 'Contact #T3A3',
                    'account_name' => 'Account #T3',
                ],
            ],
        ];

        foreach ($expected as $userName => $data) {
            foreach (self::configurationProvider() as $configName => [$useDenorm, $useWhere, $prefetchForRetrieve]) {
                yield sprintf('%s, %s', $userName, $configName) => [
                    $useDenorm,
                    $useWhere,
                    $prefetchForRetrieve,
                    $userName,
                    $data,
                ];
            }
        }
    }

    /**
     * @test
     * @dataProvider sugarQueryFilterByRelatedFieldProvider
     * @ticket BR-5762
     */
    public function sugarQueryFilterByRelatedField($useDenorm, $useWhere, $prefetchForRetrieve, $userName, $accountName, array $expected)
    {
        global $current_user;

        $current_user = self::$users[$userName];
        $account = self::$accounts[$accountName];

        self::configure($useDenorm, $useWhere, $prefetchForRetrieve);

        $query = new SugarQuery();
        $query->from(BeanFactory::newBean('Contacts'));
        $query->select('last_name');
        $join = $query->join('accounts', ['joinType' => 'LEFT']);
        $query->where()
            ->equals('created_by', self::$admin->id)
            ->equals($join->joinName() . '.id', $account->id);
        $query->orderBy('last_name', 'ASC');
        $data = $query->execute();

        $this->assertEquals($expected, $data);
    }

    public function sugarQueryFilterByRelatedFieldProvider()
    {
        $expected = [
            'User #1 by Account #T2' => [
                'User #2',
                'Account #T2',
                [
                    [
                        'last_name' => 'Contact #T2A2',
                    ],
                ],
            ],
        ];

        foreach ($expected as $userName => $data) {
            foreach (self::configurationProvider() as $configName => [$useDenorm, $useWhere, $prefetchForRetrieve]) {
                yield sprintf('%s, %s', $userName, $configName) => array_merge(
                    [$useDenorm, $useWhere, $prefetchForRetrieve],
                    $data
                );
            }
        }
    }

    /**
     * @test
     * @dataProvider reportByTwoModulesProvider
     */
    public function reportByTwoModules($useDenorm, $useWhere, $prefetchForRetrieve, $userName, array $expected)
    {
        $definition = [
            'display_columns' => [
                [
                    'name' => 'name',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'user_name',
                    'table_key' => 'Opportunities:assigned_user_link',
                ],
            ],
            'module' => 'Opportunities',
            'group_defs' => [],
            'summary_columns' => [],
            'order_by' => [
                [
                    'name' => 'name',
                    'table_key' => 'self',
                ],
            ],
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Opportunities',
                    'module' => 'Opportunities',
                ],
                'Opportunities:assigned_user_link' => [
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'assigned_user_link',
                        'relationship_name' => 'opportunities_assigned_user',
                        'link_type' => 'one',
                        'table_key' => 'Opportunities:assigned_user_link',
                    ],
                    'module' => 'Users',
                ],
                'Opportunities:created_by_link' => [
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'created_by_link',
                        'relationship_name' => 'opportunities_created_by',
                        'module' => 'Users',
                        'table_key' => 'Opportunities:created_by_link',
                    ],
                    'module' => 'Users',
                ],
            ],
            'filters_def' => [
                'Filter_1' => [
                    'operator' => 'AND',
                    [
                        'name' => 'user_name',
                        'table_key' => 'Opportunities:created_by_link',
                        'qualifier_name' => 'is',
                        'input_name0' => self::$admin->id,
                    ],
                ],
            ],
        ];

        $data = $this->runReport($definition, $useDenorm, $prefetchForRetrieve, $useWhere, $userName);

        $this->assertEquals($expected, $this->filterColumns($data, ['opportunities_name']));
    }

    public static function reportByTwoModulesProvider()
    {
        $expected = [
            'User #1' => [
                [
                    'opportunities_name' => 'Opportunity #1',
                ], [
                    'opportunities_name' => 'Opportunity #2',
                ],
            ],
            'User #2' => [
                [
                    'opportunities_name' => 'Opportunity #2',
                ],
            ],
            'User #3' => [
                [
                    'opportunities_name' => 'Opportunity #3',
                ],
            ],
        ];

        foreach ($expected as $userName => $data) {
            foreach (self::configurationProvider() as $configName => [$useDenorm, $useWhere, $prefetchForRetrieve]) {
                yield sprintf('%s, %s', $userName, $configName) => [
                    $useDenorm,
                    $useWhere,
                    $prefetchForRetrieve,
                    $userName,
                    $data,
                ];
            }
        }
    }

    /**
     * @test
     * @dataProvider reportByTwoModulesWithAnOptionalJoinProvider
     */
    public function reportByTwoModulesWithAnOptionalJoin($useDenorm, $useWhere, $prefetchForRetrieve, $userName, array $expected)
    {
        $definition = [
            'display_columns' => [
                [
                    'name' => 'name',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'name',
                    'table_key' => 'Contacts:accounts',
                ],
            ],
            'module' => 'Contacts',
            'group_defs' => [],
            'summary_columns' => [],
            'order_by' => [
                [
                    'name' => 'name',
                    'table_key' => 'self',
                ],
            ],
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Contacts',
                    'module' => 'Contacts',
                ],
                'Contacts:accounts' => [
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'accounts',
                        'relationship_name' => 'accounts_contacts',
                        'link_type' => 'one',
                        'table_key' => 'Contacts:accounts',
                    ],
                    'module' => 'Accounts',
                    'optional' => true,
                ],
                'Contacts:created_by_link' => [
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'created_by_link',
                        'relationship_name' => 'contacts_created_by',
                        'module' => 'Users',
                        'table_key' => 'Contacts:created_by_link',
                    ],
                    'module' => 'Users',
                ],
            ],
            'filters_def' => [
                'Filter_1' => [
                    'operator' => 'AND',
                    [
                        'name' => 'id',
                        'table_key' => 'Contacts:created_by_link',
                        'qualifier_name' => 'is',
                        'input_name0' => self::$admin->id,
                    ],
                ],
            ],
        ];

        $data = $this->runReport($definition, $useDenorm, $prefetchForRetrieve, $useWhere, $userName);

        $this->assertEquals($expected, $this->filterColumns($data, ['contacts_last_name', 'l1_name']));
    }

    public static function reportByTwoModulesWithAnOptionalJoinProvider()
    {
        $expected = [
            'User #1' => [
                [
                    'contacts_last_name' => 'Contact #T1A1',
                    'l1_name' => 'Account #T1',
                ], [
                    'contacts_last_name' => 'Contact #T2A2',
                    'l1_name' => 'Account #T2',
                ], [
                    'contacts_last_name' => 'Contact #T2A3',
                    'l1_name' => '',
                ],
            ],
            'User #2' => [
                [
                    'contacts_last_name' => 'Contact #T2A2',
                    'l1_name' => 'Account #T2',
                ], [
                    'contacts_last_name' => 'Contact #T2A3',
                    'l1_name' => '',
                ],
            ],
            'User #3' => [
                [
                    'contacts_last_name' => 'Contact #T3A2',
                    'l1_name' => '',
                ], [
                    'contacts_last_name' => 'Contact #T3A3',
                    'l1_name' => 'Account #T3',
                ],
            ],
        ];

        foreach ($expected as $userName => $data) {
            foreach (self::configurationProvider() as $configName => [$useDenorm, $useWhere, $prefetchForRetrieve]) {
                yield sprintf('%s, %s', $userName, $configName) => [
                    $useDenorm,
                    $useWhere,
                    $prefetchForRetrieve,
                    $userName,
                    $data,
                ];
            }
        }
    }

    /**
     * Runs a report with the given definition run under the given configuration on behalf of the given user
     */
    private function runReport($definition, $useDenorm, $prefetchForRetrieve, $useWhere, $userName)
    {
        global $current_user;

        $current_user = self::$users[$userName];

        self::configure($useDenorm, $useWhere, $prefetchForRetrieve);

        $reporter = new Report(json_encode($definition));
        $reporter->run_query();

        $data = [];

        while (($row = $reporter->db->fetchByAssoc($reporter->result))) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * @test
     * @dataProvider retrieveAccountProvider
     */
    public function retrieveAccount($useDenorm, $useWhere, $prefetchForRetrieve, $userName, $accountName, ?string $expected)
    {
        global $current_user;

        $current_user = self::$users[$userName];
        self::configure($useDenorm, $useWhere, $prefetchForRetrieve);

        $account = BeanFactory::newBean('Accounts');
        $account = $account->retrieve(self::$accounts[$accountName]->id);
        if ($expected) {
            $this->assertInstanceOf(get_class(self::$accounts[$expected]), $account);
            $this->assertSame(self::$accounts[$expected]->id, $account->id);
        } else {
            $this->assertNull($account);
        }
    }

    public static function retrieveAccountProvider()
    {
        $expected = [
            'User #1' => [
                'Account #T1' => 'Account #T1',
                'Account #T2' => 'Account #T2',
                'Account #T3' => null,
            ],
            'User #2' => [
                'Account #T1' => null,
                'Account #T2' => 'Account #T2',
                'Account #T3' => null,
            ],
            'User #3' => [
                'Account #T1' => null,
                'Account #T2' => null,
                'Account #T3' => 'Account #T3',
            ],
        ];

        foreach ($expected as $userName => $accounts) {
            foreach (self::configurationProvider() as $configName => [$useDenorm, $useWhere, $prefetchForRetrieve]) {
                foreach ($accounts as $accountName => $expected) {
                    yield sprintf('%s, %s, %s', $userName, $accountName, $configName) => [
                        $useDenorm,
                        $useWhere,
                        $prefetchForRetrieve,
                        $userName,
                        $accountName,
                        $expected,
                    ];
                }
            }
        }
    }

    /**
     * Returns named visibility configurations to be tested
     *
     * @return array<string,mixed[]>
     */
    private static function configurationProvider()
    {
        return [
            'denormalized' => [true, false, false],
            'join' => [false, false, false],
            'where' => [false, true, false],
            'prefetch_for_retrieve' => [false, false, true],
        ];
    }

    /**
     * to create test beans
     */
    private static function createFixtures()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user3 = SugarTestUserUtilities::createAnonymousUser();

        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();
        $team3 = SugarTestTeamUtilities::createAnonymousTeam();

        // User #1 belongs to Teams #1 and #2
        $team1->add_user_to_team($user1->id);
        $team2->add_user_to_team($user1->id);

        // User #2 belongs to Team #2
        $team2->add_user_to_team($user2->id);

        // User #3 belongs to Teams #3
        $team3->add_user_to_team($user3->id);

        $account1 = self::createAccount('Account #T1', $team1);
        self::createContact('Contact #T1A1', $account1, $team1);
        self::createOpportunity('Opportunity #1', $account1, $user1, $team1);

        $account2 = self::createAccount('Account #T2', $team2);
        self::createContact('Contact #T2A2', $account2, $team2);
        self::createOpportunity('Opportunity #2', $account2, $user2, $team2);

        $account3 = self::createAccount('Account #T3', $team3);
        self::createContact('Contact #T3A3', $account3, $team3);
        self::createOpportunity('Opportunity #3', $account3, $user3, $team3);

        self::createContact('Contact #T3A2', $account2, $team3);
        self::createContact('Contact #T2A3', $account3, $team2);

        self::$users = [
            'User #1' => $user1,
            'User #2' => $user2,
            'User #3' => $user3,
        ];

        self::$accounts = [
            'Account #T1' => $account1,
            'Account #T2' => $account2,
            'Account #T3' => $account3,
        ];
    }

    private static function createAccount($name, Team $team)
    {
        return SugarTestAccountUtilities::createAccount('', [
            'name' => $name,
            'team_id' => $team->id,
        ]);
    }

    private static function createContact($name, Account $account, Team $team)
    {
        $contact = SugarTestContactUtilities::createContact('', [
            'last_name' => $name,
            'team_id' => $team->id,
        ]);

        $contact->load_relationship('accounts');
        $contact->accounts->add($account);
    }

    private static function createOpportunity($name, Account $account, User $user, Team $team)
    {
        $bean = SugarTestOpportunityUtilities::createOpportunity('', $account);
        $bean->team_id = $team->id;
        $bean->team_set_id = $account->team_set_id;
        $bean->name = $name;
        $bean->amount = 2000.00;
        $bean->assigned_user_id = $user->id;

        $bean->save();
    }

    /**
     * helper method, to set config flags for different options
     *
     * @param bool $useDenorm
     * @param bool $useWhere
     */
    private static function configure($useDenorm, $useWhere, $prefetchForRetrieve)
    {
        global $sugar_config;

        $sugar_config['perfProfile'] = [
            'TeamSecurity' => [
                'default' => [
                    'use_denorm' => $useDenorm,
                    'where_condition' => $useWhere,
                    'prefetch_for_retrieve' => $prefetchForRetrieve,
                ],
            ],
        ];

        SugarConfig::getInstance()->clearCache();
    }

    /**
     * Filters the given tabular data set by leaving only the specified columns.
     *
     * @param array<int,array<string,mixed>> $data Tabular data
     * @param array<int,string> $columns Column names to leave
     *
     * @return array<int,array<string,mixed>>
     */
    private function filterColumns(array $data, array $columns): array
    {
        return array_map(function (array $row) use ($columns): array {
            return array_filter($row, function (string $key) use ($columns) {
                return in_array($key, $columns, true);
            }, ARRAY_FILTER_USE_KEY);
        }, $data);
    }
}
