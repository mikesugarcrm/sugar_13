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
use ProductConsoleHelper;
use SugarApiExceptionNotAuthorized;

/**
 * @coversDefaultClass \ProductConsoleHelper
 */
class ProductConsoleHelperTest extends TestCase
{
    private $bean;

    protected function setUp(): void
    {
        $this->bean = $this->createMock('Dashboard');
    }

    /**
     * @covers ::removeRenewalsConsole
     */
    public function testRemoveRenewalsConsoleUsingRevenueLineItems()
    {
        // Set up the helper mock
        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
        ]);
        $helper->expects($this->once())
            ->method('useRevenueLineItems')
            ->willReturn(true);

        // Set up the SugarQuery mock
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $sugarQuery->limit(20);
        $sugarQuery->expects($this->never())
            ->method($this->anything());

        // Run removeRenewalsConsole and assert that the query limit wasn't changed
        $helper->removeRenewalsConsole($this->bean, 'before_filter', [$sugarQuery]);
        $this->assertEquals(20, $sugarQuery->limit);
    }

    /**
     * @covers ::removeRenewalsConsole
     */
    public function testRemoveRenewalsConsoleNotUsingRevenueLineItems()
    {
        // Set up the helper mock
        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
        ]);
        $helper->expects($this->once())
            ->method('useRevenueLineItems')
            ->willReturn(false);

        // Set up the SugarQuery_Builder_Where mock
        $sugarQueryWhere = $this->createMock('SugarQuery_Builder_Where');
        $sugarQueryWhere->expects($this->once())
            ->method('notEquals')
            ->with($this->equalTo('id'), $this->equalTo(ProductConsoleHelper::$renewalsConsoleId));

        // Set up the SugarQuery mock
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();
        $sugarQuery->limit(20);
        $sugarQuery->expects($this->any())
            ->method('where')
            ->willReturn($sugarQueryWhere);

        // Run removeRenewalsConsole and assert that the query limit was increased
        $helper->removeRenewalsConsole($this->bean, 'before_filter', [$sugarQuery]);
        $this->assertEquals(21, $sugarQuery->limit);
    }

    /**
     * @covers ::removeRenewalsConsole
     */
    public function testRemoveRenewalsConsoleNotUsingRevenueLineItemsWithoutLimit()
    {
        // Set up the helper mock
        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
        ]);
        $helper->expects($this->once())
            ->method('useRevenueLineItems')
            ->willReturn(false);

        // Set up the SugarQuery_Builder_Where mock
        $sugarQueryWhere = $this->createMock('SugarQuery_Builder_Where');
        $sugarQueryWhere->expects($this->once())
            ->method('notEquals')
            ->with($this->equalTo('id'), $this->equalTo(ProductConsoleHelper::$renewalsConsoleId));

        // Set up the SugarQuery mock
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();
        $sugarQuery->expects($this->any())
            ->method('where')
            ->willReturn($sugarQueryWhere);

        // Run removeRenewalsConsole and assert that the query limit was increased
        $helper->removeRenewalsConsole($this->bean, 'before_filter', [$sugarQuery]);
        $this->assertEquals(null, $sugarQuery->limit);
    }

    public function checkRenewalsConsoleProvider()
    {
        return [
            [
                'rli' => true,
                'aw' => false,
            ],
            [
                'rli' => false,
                'aw' => true,
            ],
        ];
    }

    /**
     * @covers ::checkRenewalsConsole
     * @dataProvider checkRenewalsConsoleProvider
     */
    public function testCheckRenewalsConsole($rli, $aw)
    {
        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
            'isAdminWork',
        ]);

        $helper->expects($this->once())
            ->method('useRevenueLineItems')
            ->willReturn($rli);

        // isAdminWork will only be called if useRevenueLineItems returns true
        $helper->expects($this->any())
            ->method('isAdminWork')
            ->willReturn($aw);

        $helper->checkRenewalsConsole(
            $this->bean,
            'before_retrieve',
            ['id' => 'da438c86-df5e-11e9-9801-3c15c2c53980']
        );

        // Kinda needed for this to not be a risky test
        $this->assertTrue(true);
    }

    /**
     * @covers ::checkRenewalsConsole
     */
    public function testCheckRenewalsConsoleFailure()
    {
        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
            'isAdminWork',
        ]);

        $helper->expects($this->once())
            ->method('useRevenueLineItems')
            ->willReturn(false);

        $helper->expects($this->once())
            ->method('isAdminWork')
            ->willReturn(false);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $helper->checkRenewalsConsole(
            $this->bean,
            'before_retrieve',
            ['id' => 'da438c86-df5e-11e9-9801-3c15c2c53980']
        );
    }
}
