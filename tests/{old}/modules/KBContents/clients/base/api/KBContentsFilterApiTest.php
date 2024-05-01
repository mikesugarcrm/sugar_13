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

class KBContentsFilterApiTest extends TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var KBContentsFilterApi
     */
    protected $api = null;

    /**
     * @var KBContentsMock
     */
    protected $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = $this->createPartialMock('KBContentsFilterApi', ['getElasticQueryBuilder']);
        $this->bean = SugarTestKBContentUtilities::createBean();
    }

    protected function tearDown(): void
    {
        $this->service = null;
        $this->api = null;

        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    /**
     * Data Provider for testFilterList
     *
     * @return array
     */
    public function dataProviderForFilterList()
    {
        /**
         * @param $args
         * @param $isElasticCalled
         */
        return [
            [
                [
                    'module' => 'KBContents',
                    'filter' => [
                        [
                            'kbdocument_body' => [
                                '$contains' => 'test',
                            ],
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'module' => 'KBContents',
                    'filter' => [
                        [
                            'kbdocument_body' => [
                                '$not_contains' => 'test',
                            ],
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'module' => 'KBContents',
                ],
                false,
            ],
        ];
    }

    /**
     * Test asserts that filterByContainingExcludingWords method is called if kbdocument_body filter parameter set.
     *
     * @dataProvider dataProviderForFilterList
     * @param array $args
     * @param boolean $expected flag if filterByContainingExcludingWords method was called
     */
    public function testFilterList($args, $expected)
    {
        $api = $this->createPartialMock('KBContentsFilterApi', ['filterByContainingExcludingWords']);
        if ($expected) {
            $api->expects($this->once())->method('filterByContainingExcludingWords')->with(
                $this->equalTo($this->service)
            )->willReturn([]);
        } else {
            $api->expects($this->never())->method('filterByContainingExcludingWords')->with(
                $this->equalTo($this->service)
            );
        }
        $api->filterList($this->service, $args);
    }

    /**
     * Data provider for testFilterByContainingExcludingWords
     *
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                [
                    [
                        'kbdocument_body' => [
                            '$contains' => 'test',
                        ],
                    ],
                ],
            ],
            [
                [
                    [
                        'kbdocument_body' => [
                            '$not_contains' => 'test',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test asserts that filterByContainingExcludingWords method returns right parameters.
     *
     * @dataProvider dataProvider
     */
    public function testFilterByContainingExcludingWords($filter)
    {
        $builderMock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $resultSetMock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->api->expects($this->once())->method('getElasticQueryBuilder')->will($this->returnValue($builderMock));
        $builderMock->expects($this->any())->method('executeSearch')->will($this->returnValue($resultSetMock));

        $result = $this->api->filterList($this->service, [
            'module' => $this->bean->module_name,
            'record' => $this->bean->id,
            'filter' => $filter,
            'order_by' => 'name',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertIsArray($result['records']);
    }
}
