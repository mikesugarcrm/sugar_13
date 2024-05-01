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

namespace Sugarcrm\SugarcrmTestsUnit\inc\AccessControl;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\AccessConfigurator;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;

/**
 * Class AccessConfiguratorTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\AccessConfigurator
 */
class AccessConfiguratorTest extends TestCase
{
    protected $accessConfig = [
        'MODULES' => [
            'BusinessCenters' => ['SUGAR_SERVE'],
            'CampaignLog' => ['CURRENT'],
            'CampaignTrackers' => ['CURRENT'],
        ],
        'DASHLETS' => [
            'workbench' => ['SUGAR_SERVE'],
        ],
        'RECORDS' => [
            ['Dashboards' => ['c108bb4a-775a-11e9-b570-f218983a1c3e' => 'SUGAR_SERVE']],
            [
                'Reports' => [
                    ['protected_report_name1_id' => 'SUGAR_SERVE'],
                    ['protected_report_name2_id' => 'SUGAR_SERVE'],
                ],
            ],
        ],
        'FIELDS' => [
            'Accounts' => ['field1' => 'SUGAR_SERVE'],
        ],
        'WIDGETS' => [
            'advanced_forecast_id' => ['SUGAR_SELL'],
            'maps_widgets_id' => ['SUGAR_SELL_BUNDLE'],
        ],
    ];

    /**
     * @covers ::getAccessControlledList
     *
     * @dataProvider getAccessControlledListProvider
     */
    public function testGetAccessControlledList($key, $expected)
    {
        $configuratorMock = $this->getMockBuilder(AccessConfigurator::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAccessConfig'])
            ->getMock();

        $configuratorMock->expects($this->any())
            ->method('loadAccessConfig')
            ->will($this->returnValue($this->accessConfig));

        $this->assertSame($expected, $configuratorMock->getAccessControlledList($key, false));
    }

    public function getAccessControlledListProvider()
    {
        return [
            [
                'MODULES',
                [
                    'BusinessCenters' => ['SUGAR_SERVE'],
                    'CampaignLog' => ['CURRENT'],
                    'CampaignTrackers' => ['CURRENT'],
                ],
            ],
            [
                'DASHLETS',
                [
                    'workbench' => ['SUGAR_SERVE'],
                ],
            ],
            ['RECORDS',
                [
                    [
                        'Dashboards' => ['c108bb4a-775a-11e9-b570-f218983a1c3e' => 'SUGAR_SERVE'],
                    ],
                    [
                        'Reports' => [
                            ['protected_report_name1_id' => 'SUGAR_SERVE'],
                            ['protected_report_name2_id' => 'SUGAR_SERVE'],
                        ],
                    ],
                ],
            ],
            [
                'WIDGETS',
                [
                    'advanced_forecast_id' => ['SUGAR_SELL'],
                    'maps_widgets_id' => ['SUGAR_SELL_BUNDLE'],
                ],
            ],
            [
                'NOT_EXIST',
                [],
            ],
        ];
    }

    /**
     * @covers ::getNotAccessibleRecordListByLicenseTypes
     *
     * @dataProvider getNotAccessibleRecordListByLicenseTypesProvider
     */
    public function testGetAccessControlledModuleListByTypes($access_config, $types, $expected)
    {
        $configuratorMock = $this->getMockBuilder(AccessConfigurator::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAccessConfig'])
            ->getMock();

        $configuratorMock->expects($this->any())
            ->method('loadAccessConfig')
            ->will($this->returnValue($access_config));

        $this->assertSame($expected, $configuratorMock->getNotAccessibleRecordListByLicenseTypes($types, false));
    }

    public function getNotAccessibleRecordListByLicenseTypesProvider()
    {
        return [
            [
                [
                    'RECORDS' => [
                        'Reports' => [
                            'serve_only_1' => ['SUGAR_SERVE'],
                            'ent1' => ['CURRENT'],
                            'ent2' => ['CURRENT'],
                            'sell_ent' => ['SUGAR_SELL', 'CURRENT'],
                            'sell_serve' => ['SUGAR_SELL', 'SUGAR_SERVE'],
                        ],
                    ],
                ],
                ['SUGAR_SERVE'],
                [
                    'Reports' => [
                        'ent1',
                        'ent2',
                        'sell_ent',
                    ],
                ],
            ],
            [
                [
                    'RECORDS' => [
                        'Reports' => [
                            'serve_only_1' => ['SUGAR_SERVE'],
                            'ent1' => ['CURRENT'],
                            'ent2' => ['CURRENT'],
                            'sell_ent' => ['SUGAR_SELL', 'CURRENT'],
                            'sell_serve' => ['SUGAR_SELL', 'SUGAR_SERVE'],
                        ],
                    ],
                ],
                ['SUGAR_SELL'],
                [
                    'Reports' => [
                        'serve_only_1',
                        'ent1',
                        'ent2',
                    ],
                ],
            ],
            [
                [
                    'RECORDS' => [
                        'Reports' => [
                            'serve_only_1' => ['SUGAR_SERVE'],
                            'ent1' => ['CURRENT'],
                            'ent2' => ['CURRENT'],
                            'sell_ent' => ['SUGAR_SELL', 'CURRENT'],
                            'sell_serve' => ['SUGAR_SELL', 'SUGAR_SERVE'],
                        ],
                    ],
                ],
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                [
                    'Reports' => [
                        'ent1',
                        'ent2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::getNotAccessibleRecordListByLicenseTypes
     *
     * @dataProvider getAccessControlledModuleListProvider
     */
    public function testGetNotAccessControlledModuleListByTypes($access_config, $types, $expected)
    {
        $configuratorMock = $this->getMockBuilder(AccessConfigurator::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAccessConfig'])
            ->getMock();

        $configuratorMock->expects($this->any())
            ->method('loadAccessConfig')
            ->will($this->returnValue($access_config));

        $this->assertSame($expected, $configuratorMock->getNotAccessibleModuleListByLicenseTypes($types, false));
    }

    public function getAccessControlledModuleListProvider()
    {
        return [
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SELL', 'CURRENT'],
                    ],
                ],
                ['SUGAR_SERVE'],
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                    'Opportunities' => true,
                ],
            ],
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SELL', 'CURRENT'],
                    ],
                ],
                ['SUGAR_SERVE', 'CURRENT'],
                [],
            ],
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SELL', 'CURRENT'],
                    ],
                ],
                ['SUGAR_SELL'],
                [
                    'BusinessCenters' => true,
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
            ],
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SELL', 'CURRENT'],
                    ],
                ],
                ['NOT_IN_THE_LIST'],
                [
                    'BusinessCenters' => true,
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                    'Opportunities' => true,
                ],
            ],
        ];
    }

    /**
     * @covers ::getNotAccessibleModuleListByLicenseTypes
     */
    public function testGetNotAccessibleModuleListByLicenseTypes()
    {

        $configuratorMock = $this->getMockBuilder(AccessConfigurator::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAccessConfig'])
            ->getMock();

        $configuratorMock->expects($this->any())
            ->method('loadAccessConfig')
            ->will($this->returnValue($this->accessConfig));

        $this->assertNotEmpty($configuratorMock->getNotAccessibleModuleListByLicenseTypes(['SUGAR_SERVE'], false));
    }
}
