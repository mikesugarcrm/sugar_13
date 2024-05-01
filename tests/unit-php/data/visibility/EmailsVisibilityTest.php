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

namespace Sugarcrm\SugarcrmTestsUnit\data\visibility;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \EmailsVisibility
 */
class EmailsVisibilityTest extends TestCase
{
    public function doesNotAugmentTheQueryForAdminsProvider()
    {
        return [
            'admin_but_not_dev' => [
                true,
                false,
            ],
            'dev_but_not_admin' => [
                false,
                true,
            ],
            'admin_and_dev' => [
                true,
                true,
            ],
        ];
    }

    /**
     * @covers ::addVisibilityWhere
     * @covers ::isUserAnAdmin
     * @dataProvider doesNotAugmentTheQueryForAdminsProvider
     */
    public function testAddVisibilityWhere_DoesNotAugmentTheQueryForAdmins($isAdmin, $isDev)
    {
        $GLOBALS['current_user'] = $this->createPartialMock('\\User', ['isAdminForModule', 'isDeveloperForModule']);
        $GLOBALS['current_user']->method('isAdminForModule')->willReturn($isAdmin);
        $GLOBALS['current_user']->method('isDeveloperForModule')->willReturn($isDev);

        $email = $this->createMock('\\Email');
        $query = '';

        $strategy = new \EmailsVisibility($email);
        $result = $strategy->addVisibilityWhere($query);

        $this->assertEmpty($query, '$query should not have changed');
        $this->assertEmpty($result, 'The return value should be empty');

        unset($GLOBALS['current_user']);
    }

    /**
     * @covers ::addVisibilityWhereQuery
     * @covers ::isUserAnAdmin
     * @dataProvider doesNotAugmentTheQueryForAdminsProvider
     */
    public function testAddVisibilityWhereQuery_DoesNotAugmentTheQueryForAdmins($isAdmin, $isDev)
    {
        $GLOBALS['current_user'] = $this->createPartialMock('\\User', ['isAdminForModule', 'isDeveloperForModule']);
        $GLOBALS['current_user']->method('isAdminForModule')->willReturn($isAdmin);
        $GLOBALS['current_user']->method('isDeveloperForModule')->willReturn($isDev);

        $email = $this->createMock('\\Email');
        $query = $this->createPartialMock('\\SugarQuery', ['where']);
        $query->expects($this->never())->method('where');

        $strategy = new \EmailsVisibility($email);
        $result = $strategy->addVisibilityWhereQuery($query);

        unset($GLOBALS['current_user']);
    }

    /**
     * @covers ::elasticBuildMapping
     */
    public function testElasticBuildMapping()
    {
        $email = $this->createMock('\\Email');
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();
        $mapping = new \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping('Emails');

        $strategy = new \EmailsVisibility($email);
        $strategy->elasticBuildMapping($mapping, $provider);

        $properties = $mapping->compile();

        $expected = [
            'Emails__state' => [
                'type' => 'keyword',
                'index' => false,
                'fields' => [
                    'emails_state' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'state' => [
                'type' => 'keyword',
                'index' => false,
                'copy_to' => [
                    'Emails__state',
                ],
            ],
        ];
        $this->assertEquals($expected, $properties);
    }

    /**
     * @covers ::elasticGetBeanIndexFields
     */
    public function testElasticGetBeanIndexFields()
    {
        $email = $this->createMock('\\Email');
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();

        $strategy = new \EmailsVisibility($email);
        $fields = $strategy->elasticGetBeanIndexFields('Emails', $provider);

        $this->assertEquals(['state' => 'enum'], $fields);
    }

    /**
     * @covers ::elasticAddFilters
     */
    public function testElasticAddFilters()
    {
        $user = $this->createPartialMock('\\User', ['isAdminForModule', 'isDeveloperForModule']);
        $user->method('isAdminForModule')->willReturn(false);
        $user->method('isDeveloperForModule')->willReturn(false);
        $user->id = Uuid::uuid1();

        $email = $this->createMock('\\Email');
        $filter = new \Elastica\Query\BoolQuery();
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();

        $strategy = new \EmailsVisibility($email);
        $strategy->elasticAddFilters($user, $filter, $provider);

        $query = $filter->toArray();

        $expected = [
            'bool' => [
                'must' => [
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'term' => [
                                                    'Emails__state.emails_state' => [
                                                        'value' => 'Draft',
                                                        'boost' => 1,
                                                    ],
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'Common__owner_id.owner' => [
                                                        'value' => $user->id,
                                                        'boost' => 1.0,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            [
                                                'term' => [
                                                    'Emails__state.emails_state' => [
                                                        'value' => 'Draft',
                                                        'boost' => 1,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $query);
    }

    /**
     * @covers ::elasticAddFilters
     * @covers ::isUserAnAdmin
     * @dataProvider doesNotAugmentTheQueryForAdminsProvider
     */
    public function testElasticAddFilters_DoesNotAugmentTheQueryForAdmins($isAdmin, $isDev)
    {
        $user = $this->createPartialMock('\\User', ['isAdminForModule', 'isDeveloperForModule']);
        $user->method('isAdminForModule')->willReturn($isAdmin);
        $user->method('isDeveloperForModule')->willReturn($isDev);

        $filter = $this->createPartialMock('\\' . \Elastica\Query\BoolQuery::class, ['addMust']);
        $filter->expects($this->never())->method('addMust');

        $email = $this->createMock('\\Email');
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();

        $strategy = new \EmailsVisibility($email);
        $strategy->elasticAddFilters($user, $filter, $provider);
    }
}
