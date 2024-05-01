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

namespace Sugarcrm\SugarcrmTestsUnit\Filters;

use ServiceBase;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Filter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Filter
 */
class FilterTest extends TestCase
{
    public function filterProvider()
    {
        return [
            'only name equals' => [
                'Accounts',
                [
                    [
                        'name' => 'test',
                    ],
                ],
                [
                    [
                        'name' => 'test',
                    ],
                ],
            ],
            'name and phone equals' => [
                'Accounts',
                [
                    [
                        'name' => 'test',
                        'phone_office' => '123-456-7890',
                    ],
                ],
                [
                    [
                        'name' => 'test',
                        'phone_office' => '123-456-7890',
                    ],
                ],
            ],
            'name and phone with operands' => [
                'Accounts',
                [
                    [
                        'name' => [
                            '$starts' => 'test',
                        ],
                        'phone_office' => [
                            '$equals' => '123-456-7890',
                        ],
                    ],
                ],
                [
                    [
                        'name' => [
                            '$starts' => 'test',
                        ],
                        'phone_office' => [
                            '$equals' => '123-456-7890',
                        ],
                    ],
                ],
            ],
            'operand: $or' => [
                'Accounts',
                [
                    [
                        '$or' => [
                            [
                                'name' => [
                                    '$starts' => 'test',
                                ],
                            ],
                            [
                                'name' => [
                                    '$ends' => 'test',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        '$or' => [
                            [
                                'name' => [
                                    '$starts' => 'test',
                                ],
                            ],
                            [
                                'name' => [
                                    '$ends' => 'test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'operand: $and' => [
                'Accounts',
                [
                    [
                        '$and' => [
                            [
                                'name' => [
                                    '$starts' => 'test',
                                ],
                            ],
                            [
                                'name' => [
                                    '$ends' => 'test',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        '$and' => [
                            [
                                'name' => [
                                    '$starts' => 'test',
                                ],
                            ],
                            [
                                'name' => [
                                    '$ends' => 'test',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'operand: $creator' => [
                'Accounts',
                [
                    [
                        '$creator' => '',
                    ],
                ],
                [
                    [
                        '$creator' => '',
                    ],
                ],
            ],
            'operand: $creator with _this' => [
                'Accounts',
                [
                    [
                        '$creator' => '_this',
                    ],
                ],
                [
                    [
                        '$creator' => '_this',
                    ],
                ],
            ],
            'operand: $creator with link' => [
                'Accounts',
                [
                    [
                        '$creator' => 'opportunities',
                    ],
                ],
                [
                    [
                        '$creator' => 'opportunities',
                    ],
                ],
            ],
            'operand: $favorite' => [
                'Accounts',
                [
                    [
                        '$favorite' => '',
                    ],
                ],
                [
                    [
                        '$favorite' => '',
                    ],
                ],
            ],
            'operand: $favorite with _this' => [
                'Accounts',
                [
                    [
                        '$favorite' => '_this',
                    ],
                ],
                [
                    [
                        '$favorite' => '_this',
                    ],
                ],
            ],
            'operand: $favorite with link' => [
                'Accounts',
                [
                    [
                        '$favorite' => 'opportunities',
                    ],
                ],
                [
                    [
                        '$favorite' => 'opportunities',
                    ],
                ],
            ],
            'operand: $following' => [
                'Accounts',
                [
                    [
                        '$following' => '',
                    ],
                ],
                [
                    [
                        '$following' => '',
                    ],
                ],
            ],
            'operand: $owner' => [
                'Accounts',
                [
                    [
                        '$owner' => '',
                    ],
                ],
                [
                    [
                        '$owner' => '',
                    ],
                ],
            ],
            'operand: $owner with _this' => [
                'Accounts',
                [
                    [
                        '$owner' => '_this',
                    ],
                ],
                [
                    [
                        '$owner' => '_this',
                    ],
                ],
            ],
            'operand: $owner with link' => [
                'Accounts',
                [
                    [
                        '$owner' => 'opportunities',
                    ],
                ],
                [
                    [
                        '$owner' => 'opportunities',
                    ],
                ],
            ],
            'operand: $tracker' => [
                'Accounts',
                [
                    [
                        '$tracker' => '-7 DAY',
                    ],
                ],
                [
                    [
                        '$tracker' => '-7 DAY',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::apiSerialize
     * @covers ::doFilters
     * @covers ::doFilter
     * @covers ::doField
     * @covers       \Sugarcrm\Sugarcrm\Filters\Field\Field::apiSerialize
     * @covers       \Sugarcrm\Sugarcrm\Filters\Operand\Operand::apiSerialize
     * @dataProvider filterProvider
     */
    public function testApiSerialize(
        string $module,
        array  $filterDef,
        array  $expected
    ) {

        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $filter = new Filter($module, $filterDef);

        $actual = $filter->apiSerialize($api);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::apiUnserialize
     * @covers ::doFilters
     * @covers ::doFilter
     * @covers ::doField
     * @covers       \Sugarcrm\Sugarcrm\Filters\Field\Field::apiUnserialize
     * @covers       \Sugarcrm\Sugarcrm\Filters\Operand\Operand::apiUnserialize
     * @dataProvider filterProvider
     */
    public function testApiUnserialize(
        string $module,
        array  $filterDef,
        array  $expected
    ) {

        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $filter = new Filter($module, $filterDef);

        $actual = $filter->apiUnserialize($api);

        $this->assertEquals($expected, $actual);
    }
}
