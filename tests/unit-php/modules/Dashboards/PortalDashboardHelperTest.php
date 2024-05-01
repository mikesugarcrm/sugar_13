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
use PortalDashboardHelper;
use SugarApiExceptionNotAuthorized;

/**
 * @coversDefaultClass \PortalDashboardHelper
 */
class PortalDashboardHelperTest extends TestCase
{
    private $helper;
    private $bean;
    private $platform;

    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(PortalDashboardHelper::class)
            ->onlyMethods(['isAdminUser'])
            ->getMock();
        $this->bean = $this->createMock('Dashboard');

        if (isset($_SESSION['platform'])) {
            $this->platform = $_SESSION['platform'];
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->platform)) {
            $_SESSION['platform'] = $this->platform;
        } elseif (isset($_SESSION['platform'])) {
            unset($_SESSION['platform']);
        }
    }

    /**
     * @covers ::removePortalDashboards
     */
    public function testRemovePortalDashboardsOnBasePlatform()
    {
        // Test using base platform
        $_SESSION['platform'] = 'base';

        $sugarQueryWhere = $this->createMock('SugarQuery_Builder_Where');
        $sugarQueryWhere->expects($this->once())
            ->method('notIn')
            ->with($this->equalTo('id'), $this->equalTo(PortalDashboardHelper::$portalDashboards));

        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();
        $sugarQuery->limit(20);
        $sugarQuery->expects($this->any())
            ->method('where')
            ->willReturn($sugarQueryWhere);

        $this->helper->removePortalDashboards($this->bean, 'before_filter', [$sugarQuery]);
        $this->assertEquals(22, $sugarQuery->limit);
    }

    /**
     * @covers ::removePortalDashboards
     */
    public function testRemovePortalDashboardsOnPortalPlatform()
    {
        // Test using portal platform
        $_SESSION['platform'] = 'portal';
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $sugarQuery->limit(20);
        $sugarQuery->expects($this->never())
            ->method($this->anything());
        $this->helper->removePortalDashboards($this->bean, 'before_filter', [$sugarQuery]);
        $this->assertEquals(20, $sugarQuery->limit);
    }

    /**
     * @covers ::removePortalDashboards
     */
    public function testRemovePortalDashboardsOnBasePlatformWithoutLimit()
    {
        // Test using base platform
        $_SESSION['platform'] = 'base';

        $sugarQueryWhere = $this->createMock('SugarQuery_Builder_Where');
        $sugarQueryWhere->expects($this->once())
            ->method('notIn')
            ->with($this->equalTo('id'), $this->equalTo(PortalDashboardHelper::$portalDashboards));

        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();
        $sugarQuery->expects($this->any())
            ->method('where')
            ->willReturn($sugarQueryWhere);

        $this->helper->removePortalDashboards($this->bean, 'before_filter', [$sugarQuery]);

        // Limit wasn't set before, and should continue to not be set
        $this->assertEquals(null, $sugarQuery->limit);
    }

    /**
     * @covers ::checkPortalDashboard
     * @dataProvider checkPortalDashboardProvider
     */
    public function testCheckPortalDashboard($isAdmin)
    {
        $this->helper->method('isAdminUser')->willReturn($isAdmin);
        $_SESSION['platform'] = 'portal';
        $this->helper->checkPortalDashboard(
            $this->bean,
            'before_retrieve',
            ['id' => '0ca2d773-0bb3-4bf3-ae43-68569968af57']
        );
        $this->assertTrue(true);

        $_SESSION['platform'] = 'base';
        if (!$isAdmin) {
            $this->expectException(SugarApiExceptionNotAuthorized::class);
        }
        $this->helper->checkPortalDashboard(
            $this->bean,
            'before_retrieve',
            ['id' => '0ca2d773-0bb3-4bf3-ae43-68569968af57']
        );
    }

    public function checkPortalDashboardProvider()
    {
        // Whether the user is an admin or not
        return [
            [true],
            [false],
        ];
    }
}
