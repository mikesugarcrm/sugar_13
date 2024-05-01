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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Dashboards;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DefaultDashboardInstaller
 */
class DefaultDashboardInstallerTest extends TestCase
{
    protected function setUp(): void
    {
        \BeanFactory::setBeanClass('Reports', ReportMock::class);
    }

    protected function tearDown(): void
    {
        \BeanFactory::unsetBeanClass('Reports');
    }

    /**
     * @covers ::buildDashboardsFromFiles
     * @dataProvider providerBuildDashboardsFromFiles
     * @param array $fileContents Contents of the default dashboard file.
     * @param string $dashboardsDir Dashboards directory (relative path).
     * @param string $dashboardDir Dashboard directory (relative path,
     *   including $dashboardsDir as a prefix).
     * @param string $dashboardFile Dashboard file (relative path).
     * @param string $module Module name.
     * @param string $layout Layout name.
     */
    public function testBuildDashboardsFromFiles(
        array  $fileContents,
        string $dashboardsDir,
        string $dashboardDir,
        string $dashboardFile,
        string $module,
        string $layout
    ) {

        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods([
                'buildDashboardFromFile',
                'getFileContents',
                'storeDashboard',
                'getNewDashboardBean',
                'getSubDirs',
                'getPhpFiles',
            ])
            ->getMock();

        $defaultDashboardInstaller->method('getFileContents')
            ->willReturn($fileContents);

        $defaultDashboardInstaller->method('storeDashboard');

        $defaultDashboardInstaller->method('getNewDashboardBean')
            ->willReturn('beanStub');

        $defaultDashboardInstaller->expects($this->once())
            ->method('getSubDirs')
            ->will($this->returnCallback(function ($input) {
                return [$input . 'testview'];
            }))
            ->with($dashboardsDir);

        $defaultDashboardInstaller->expects($this->once())
            ->method('getPhpFiles')
            ->will($this->returnCallback(function ($input) {
                return [$input . '/testview-dashboard.php'];
            }))
            ->with($dashboardDir);

        $defaultDashboardInstaller->expects($this->once())
            ->method('buildDashboardFromFile')
            ->with($this->equalTo($dashboardFile), $this->equalTo($module), $this->equalTo($layout));

        $defaultDashboardInstaller->buildDashboardsFromFiles([$module]);
    }

    public function providerBuildDashboardsFromFiles(): array
    {
        // basic dashboard
        $basicDashboardFileContents = [
            'name' => 'Test Module Dashboard Name',
            'metadata' => ['metadata' => 'test module dashboard metadata'],
        ];
        $basicDashboardsDir = 'modules/TestModule/dashboards/';
        $basicDashboardDir = 'modules/TestModule/dashboards/testview';

        // home dashboard
        $homeDashboardFileContents = [
            'name' => 'Home Module Dashboard Name',
            'metadata' => ['metadata' => 'Home module dashboard metadata'],
        ];
        $homeDashboardsDir = 'modules/Home/dashboards/';
        $homeDashboardDir = 'modules/Home/dashboards/testview';

        // dashboard with predefined ID
        $dashboardWithIDFileContents = [
            'id' => 'i-am-a-predefined-id',
            'name' => 'Test Module 2 Dashboard Name',
            'metadata' => ['metadata' => 'test module 2 dashboard metadata'],
        ];
        $dashboardsWithIDsDirectory = 'modules/TestModule2/dashboards/';
        $dashboardWithIDDirectory = 'modules/TestModule2/dashboards/testview';

        return [
            [
                $basicDashboardFileContents,
                $basicDashboardsDir,
                $basicDashboardDir,
                'modules/TestModule/dashboards/testview/testview-dashboard.php',
                'TestModule',
                'testview',
            ],
            [
                $homeDashboardFileContents,
                $homeDashboardsDir,
                $homeDashboardDir,
                'modules/Home/dashboards/testview/testview-dashboard.php',
                'Home',
                'testview',
            ],
            [
                $dashboardWithIDFileContents,
                $dashboardsWithIDsDirectory,
                $dashboardWithIDDirectory,
                'modules/TestModule2/dashboards/testview/testview-dashboard.php',
                'TestModule2',
                'testview',
            ],
        ];
    }

    /**
     * @covers ::buildDashboardFromFile
     * @dataProvider providerBuildDashboardFromFileNegativeCase
     * @param string $filePath File path.
     * @param string $module Module name.
     * @param string $layout Layout name.
     */
    public function testBuildDashboardFromFileDoesNothingIfNoFileContents(
        string $filePath,
        string $module,
        string $layout
    ) {

        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods([
                'getFileContents',
                'storeDashboard',
            ])
            ->getMock();

        $defaultDashboardInstaller->method('getFileContents')
            ->with($this->equalTo($filePath))
            ->willReturn([]);

        $defaultDashboardInstaller->expects($this->never())
            ->method('storeDashboard');

        $this->assertFalse($defaultDashboardInstaller->buildDashboardFromFile($filePath, $module, $layout));
    }

    /**
     * @covers ::buildDashboardFromFile
     * @dataProvider providerBuildDashboardFromFileNegativeCase
     * @param string $filePath File path.
     * @param string $module Module name.
     * @param string $layout Layout name.
     */
    public function testBuildDashboardFromFileDoesNothingIfDashboardAlreadyExists(
        string $filePath,
        string $module,
        string $layout
    ) {

        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods([
                'getFileContents',
                'getNewDashboardBean',
                'storeDashboard',
            ])
            ->getMock();

        $defaultDashboardInstaller->method('getFileContents')
            ->with($this->equalTo($filePath))
            ->willReturn(['id' => 'predefined-id']);

        $mockBean = $this->getMockBuilder(Dashboard::class)
            ->setMethods(['fetch'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockBean->method('fetch')
            ->with($this->equalTo('predefined-id'))
            ->willReturnSelf();
        $defaultDashboardInstaller->method('getNewDashboardBean')
            ->willReturn($mockBean);

        $defaultDashboardInstaller->expects($this->never())
            ->method('storeDashboard');

        $this->assertFalse($defaultDashboardInstaller->buildDashboardFromFile($filePath, $module, $layout));
    }

    public function providerBuildDashboardFromFileNegativeCase(): array
    {
        return [
            ['modules/TestModule/dashboards/testview/testview-dashboard.php', 'TestModule', 'testview'],
        ];
    }

    /**
     * @covers ::buildDashboardFromFile
     * @dataProvider providerBuildDashboardFromFile
     * @param string $filePath File path.
     * @param string $module Module name.
     * @param string $layout Layout name.
     * @param array $fileContents File contents.
     * @param array $expectedDashboardMeta Expected dashboard metadata.
     * @param array $expectedDashboardProperties Expected dashboard properties.
     */
    public function testBuildDashboardFromFile(
        string $filePath,
        string $module,
        string $layout,
        array  $fileContents,
        array  $expectedDashboardMeta,
        array  $expectedDashboardProperties
    ) {

        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods([
                'getFileContents',
                'getNewDashboardBean',
                'setupSavedReportDashlets',
                'storeDashboard',
            ])
            ->getMock();

        $defaultDashboardInstaller->method('getFileContents')
            ->with($this->equalTo($filePath))
            ->willReturn($fileContents);

        $mockBean = $this->getMockBuilder(Dashboard::class)
            ->setMethods(['fetch'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockBean->method('fetch')
            ->with($this->equalTo('predefined-id'))
            ->willReturn(false);
        $defaultDashboardInstaller->method('getNewDashboardBean')
            ->willReturn($mockBean);

        $defaultDashboardInstaller->expects($this->once())
            ->method('setupSavedReportDashlets')
            ->with($this->equalTo($expectedDashboardMeta));

        $defaultDashboardInstaller->expects($this->once())
            ->method('storeDashboard')
            ->with($this->equalTo($mockBean), $this->equalTo($expectedDashboardProperties));

        $this->assertTrue($defaultDashboardInstaller->buildDashboardFromFile($filePath, $module, $layout));
    }

    public function providerBuildDashboardFromFile(): array
    {
        return [
            [
                'modules/TestModule/dashboards/testview/testview-dashboard.php',
                'TestModule',
                'testview',
                [
                    'id' => 'predefined-id',
                    'name' => 'A Dashboard!',
                    'metadata' => ['some' => 'metadata'],
                ],
                ['some' => 'metadata'],
                [
                    'name' => 'A Dashboard!',
                    'dashboard_module' => 'TestModule',
                    'view_name' => 'testview',
                    'metadata' => '{"some":"metadata"}',
                    'default_dashboard' => true,
                    'team_id' => 1,
                    'id' => 'predefined-id',
                    'new_with_id' => true,
                ],
            ],
            [
                'modules/Home/dashboards/record/record-dashboard.php',
                'Home',
                'record',
                [
                    'id' => 'predefined-id',
                    'name' => 'A Dashboard!',
                    'metadata' => ['some' => 'metadata'],
                ],
                ['some' => 'metadata'],
                [
                    'name' => 'A Dashboard!',
                    'dashboard_module' => 'Home',
                    'view_name' => 'home',
                    'metadata' => '{"some":"metadata"}',
                    'default_dashboard' => true,
                    'team_id' => 1,
                    'id' => 'predefined-id',
                    'new_with_id' => true,
                ],
            ],
            [
                'modules/TestModule2/dashboards/testview/testview-dashboard.php',
                'TestModule2',
                'testview',
                [
                    'name' => 'A Dashboard With No ID!',
                    'metadata' => ['some' => 'metadata'],
                ],
                ['some' => 'metadata'],
                [
                    'name' => 'A Dashboard With No ID!',
                    'dashboard_module' => 'TestModule2',
                    'view_name' => 'testview',
                    'metadata' => '{"some":"metadata"}',
                    'default_dashboard' => true,
                    'team_id' => 1,
                ],
            ],
        ];
    }

    /**
     * @covers ::storeDashboard
     */
    public function testStoreDashboard()
    {
        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods()
            ->getMock();

        $dashboardBeanMock = $this->getMockBuilder('Dashboard')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $dashboardBeanMock->method('save');

        $properties = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $dashboardBeanMock->expects($this->once())
            ->method('save');

        $defaultDashboardInstaller->storeDashboard($dashboardBeanMock, $properties);

        $this->assertEquals($dashboardBeanMock->key1, 'value1');
        $this->assertEquals($dashboardBeanMock->key2, 'value2');
    }

    /**
     * Data provider for testSetupSavedReportDashlets
     */
    public function setupSavedReportDashletsProvider(): array
    {
        return [
            // regular dashboard
            [
                [
                    'components' => [
                        [
                            'rows' => [
                                [
                                    [
                                        'view' => [
                                            'type' => 'saved-reports-chart',
                                            'saved_report_key' => 'DEFAULT_REPORT_TITLE_16',
                                            'chart_type' => 'horizontal group by chart',
                                        ],
                                        'width' => 12,
                                    ],
                                ],
                                [
                                    [
                                        'view' => [
                                            'type' => 'opportunity-metrics',
                                            'label' => 'LBL_DASHLET_OPPORTUNITY_NAME',
                                        ],
                                        'width' => 12,
                                    ],
                                ],
                            ],
                            'width' => 12,
                        ],
                    ],
                ],
                [
                    'components' => [
                        [
                            'rows' => [
                                [
                                    [
                                        'view' => [
                                            'type' => 'saved-reports-chart',
                                            'saved_report_key' => 'DEFAULT_REPORT_TITLE_16',
                                            'chart_type' => 'horizontal group by chart',
                                            'label' => 'saved report',
                                            'saved_report' => 'saved report',
                                            'saved_report_id' => 'report id',
                                        ],
                                        'width' => 12,
                                    ],
                                ],
                                [
                                    [
                                        'view' => [
                                            'type' => 'opportunity-metrics',
                                            'label' => 'LBL_DASHLET_OPPORTUNITY_NAME',
                                        ],
                                        'width' => 12,
                                    ],
                                ],
                            ],
                            'width' => 12,
                        ],
                    ],
                ],
            ],
            // tabbed dashboard
            [
                // original metadata
                [
                    'tabs' => [
                        // tab 1
                        [
                            'name' => 'FIRST TAB',
                            'components' => [
                                [
                                    'rows' => [
                                        [
                                            [
                                                'width' => 4,
                                                'context' => [
                                                    'module' => 'Cases',
                                                ],
                                                'view' => [
                                                    'label' => 'saved report',
                                                    'type' => 'saved-reports-chart',
                                                    'module' => 'Cases',
                                                    'saved_report_id' => 'AN-ID',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // tab 2
                        [
                            'name' => 'SECOND TAB',
                            'components' => [
                                [
                                    'rows' => [
                                        [
                                            [
                                                'width' => 4,
                                                'context' => [
                                                    'module' => 'Cases',
                                                ],
                                                'view' => [
                                                    'label' => 'saved report',
                                                    'type' => 'saved-reports-chart',
                                                    'module' => 'Cases',
                                                    'saved_report_id' => 'ANOTHER-ID',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // expected results
                [
                    'tabs' => [
                        // tab 1
                        [
                            'name' => 'FIRST TAB',
                            'components' => [
                                [
                                    'rows' => [
                                        [
                                            [
                                                'width' => 4,
                                                'context' => [
                                                    'module' => 'Cases',
                                                ],
                                                'view' => [
                                                    'label' => 'saved report',
                                                    'type' => 'saved-reports-chart',
                                                    'module' => 'Cases',
                                                    'saved_report_id' => 'AN-ID',
                                                    'saved_report' => 'saved report',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        // tab 2
                        [
                            'name' => 'SECOND TAB',
                            'components' => [
                                [
                                    'rows' => [
                                        [
                                            [
                                                'width' => 4,
                                                'context' => [
                                                    'module' => 'Cases',
                                                ],
                                                'view' => [
                                                    'label' => 'saved report',
                                                    'type' => 'saved-reports-chart',
                                                    'module' => 'Cases',
                                                    'saved_report_id' => 'ANOTHER-ID',
                                                    'saved_report' => 'saved report',
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
    }

    /**
     * @covers ::setupSavedReportDashlets
     * @dataProvider setupSavedReportDashletsProvider
     * @param array $metadata Dashlet metadata.
     * @param array $expected Expected results.
     */
    public function testSetupSavedReportDashlets(array $metadata, array $expected)
    {
        $defaultDashboardInstaller = $this->createPartialMock(
            'DefaultDashboardInstaller',
            ['translateSavedReportTitle']
        );
        $defaultDashboardInstaller->method('translateSavedReportTitle')->willReturn('saved report');
        $defaultDashboardInstaller->setupSavedReportDashlets($metadata);
        $this->assertSame($expected, $metadata);
    }
}

class ReportMock
{
    public function retrieveReportIdByName($name)
    {
        return 'report id';
    }
}
