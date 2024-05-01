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
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Dashboard
 */
class DashboardTest extends TestCase
{
    /**
     * @covers ::processLegacyMetadataWithAcl
     *
     * @dataProvider processLegacyMetadataWithAclProvider
     */
    public function testProcessLegacyMetadataWithAcl($metadata, $allowedAccess, $expected)
    {
        $workBenchType = 'twitter';
        $dashboardMock = $this->getMockBuilder('Dashboard')
            ->setMethods(['allowedToAccessDashlet'])
            ->disableOriginalConstructor()
            ->getMock();

        $dashboardMock->expects($this->any())
            ->method('allowedToAccessDashlet')
            ->willReturnCallback(function ($label) use ($allowedAccess, $workBenchType) {
                if ($allowedAccess || $label != $workBenchType) {
                    return true;
                }
                return false;
            });

        $md = json_decode($metadata);
        $result = TestReflection::callProtectedMethod($dashboardMock, 'processLegacyMetadataWithAcl', [$md]);

        $this->assertSame($expected, json_encode($result));
    }

    /**
     * @covers ::processMetadataWithAcl
     *
     * @dataProvider processMetadataWithAclProvider
     */
    public function testProcessMetadataWithAcl($metadata, $allowedAccess, $expected)
    {
        $workBenchType = 'twitter';
        $dashboardMock = $this->getMockBuilder('Dashboard')
            ->setMethods(['allowedToAccessDashlet'])
            ->disableOriginalConstructor()
            ->getMock();

        $dashboardMock->expects($this->any())
            ->method('allowedToAccessDashlet')
            ->willReturnCallback(function ($label) use ($allowedAccess, $workBenchType) {
                if ($allowedAccess || $label != $workBenchType) {
                    return true;
                }
                return false;
            });

        $md = json_decode($metadata);
        $result = TestReflection::callProtectedMethod($dashboardMock, 'processMetadataWithAcl', [$md]);

        $this->assertSame($expected, json_encode($result));
    }

    public function processLegacyMetadataWithAclProvider()
    {
        return [
            // @codingStandardsIgnoreStart
            [
                '{"components":[{"rows":[[{"view":{"type":"twitter","label":"LBL_DASHLET_WORKBENCH","limit":20},"width":12}]]},{"rows":[[{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12}],[{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]],"width":8}]}',
                true,
                '{"components":[{"rows":[[{"view":{"type":"twitter","label":"LBL_DASHLET_WORKBENCH","limit":20},"width":12}]]},{"rows":[[{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12}],[{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]],"width":8}]}',
            ],
            [
                '{"components":[{"rows":[[{"view":{"type":"twitter","label":"LBL_DASHLET_WORKBENCH","limit":20},"width":12}]]},{"rows":[[{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12}],[{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]],"width":8}]}',
                false,
                '{"components":[{"rows":[]},{"rows":[[{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12}],[{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]],"width":8}]}',
            ],
            // @codingStandardsIgnoreEnd
        ];
    }

    public function processMetadataWithAclProvider()
    {
        return [
            // @codingStandardsIgnoreStart
            [
                '{"dashlets":[{"view":{"type":"twitter","label":"LBL_DASHLET_WORKBENCH","limit":20},"width":12},{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12},{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]}',
                true,
                '{"dashlets":[{"view":{"type":"twitter","label":"LBL_DASHLET_WORKBENCH","limit":20},"width":12},{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12},{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]}',
            ],
            [
                '{"dashlets":[{"view":{"type":"twitter","label":"LBL_DASHLET_WORKBENCH","limit":20},"width":12},{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12},{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]}',
                false,
                '{"dashlets":[{"view":{"type":"sales-pipeline","label":"LBL_DASHLET_PIPLINE_NAME","visibility":"user"},"width":12},{"view":{"type":"bubblechart","label":"LBL_DASHLET_TOP10_SALES_OPPORTUNITIES_NAME","filter_duration":"current","visibility":"user"},"width":12}]}',
            ],
            // @codingStandardsIgnoreEnd
        ];
    }
}
