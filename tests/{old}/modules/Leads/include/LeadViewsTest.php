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
use Sugarcrm\Sugarcrm\MetaData\ViewdefManager;

/**
 * @coversDefaultClass \LeadViews
 */
class LeadViewsTest extends TestCase
{
    /**
     * @covers ::toggleConvertDashboardProductDashlets
     * @dataProvider toggleConvertDashboardProductDashletsProvider
     */
    public function testToggleConvertDashboardProductDashlets($beforeMeta, $enableDashlets, $expectedMeta)
    {
        $leadViewsMock = $this->getMockBuilder(LeadViews::class)
            ->onlyMethods(['getViewdefManager', 'getConvertDashboardMeta', 'saveConvertDashboardMeta'])
            ->disableOriginalConstructor()
            ->getMock();

        $viewdefManagerMock = $this->getMockBuilder(ViewdefManager::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $leadViewsMock->expects($this->once())
            ->method('getViewdefManager')
            ->willReturn($viewdefManagerMock);

        $leadViewsMock->expects($this->once())
            ->method('getConvertDashboardMeta')
            ->with($viewdefManagerMock)
            ->willReturn($beforeMeta);

        $leadViewsMock->expects($this->once())
            ->method('saveConvertDashboardMeta')
            ->with($viewdefManagerMock, $expectedMeta);

        $leadViewsMock->toggleConvertDashboardProductDashlets($enableDashlets);
    }

    /**
     * Dataprovider for testToggleConvertDashboardProductDashlets
     * @return array
     */
    public function toggleConvertDashboardProductDashletsProvider()
    {
        return [
            // Test enabling dashlets
            [
                [
                    'components' => [],
                ],
                true,
                [
                    'components' => [
                        [
                            'view' => 'product-catalog',
                        ],
                        [
                            'view' => 'product-quick-picks',
                        ],
                    ],
                ],
            ],
            // Test disabling dashlets
            [
                [
                    'components' => [
                        [
                            'view' => 'product-catalog',
                        ],
                        [
                            'view' => 'product-quick-picks',
                        ],
                    ],
                ],
                false,
                [
                    'components' => [],
                ],
            ],
            // Test that it does not duplicate dashlets
            [
                [
                    'components' => [
                        [
                            'view' => 'product-catalog',
                        ],
                        [
                            'view' => 'product-quick-picks',
                        ],
                    ],
                ],
                true,
                [
                    'components' => [
                        [
                            'view' => 'product-catalog',
                        ],
                        [
                            'view' => 'product-quick-picks',
                        ],
                    ],
                ],
            ],
        ];
    }
}
