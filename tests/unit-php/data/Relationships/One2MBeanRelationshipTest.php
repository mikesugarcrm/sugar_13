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

namespace Sugarcrm\SugarcrmTestsUnit\data\Relationships;

use Link2;
use LoggerManager;
use One2MBeanRelationship;
use PHPUnit\Framework\TestCase;
use SugarBean;
use SugarQuery;

/**
 * @coversDefaultClass One2MBeanRelationship
 */
class One2MBeanRelationshipTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['log'] = $this->createMock(LoggerManager::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
    }

    public function loadRHSDataProvider()
    {
        return [
            [[], ''],
            [['test-id' => ['id' => 'test-id']], 'test-id'],
        ];
    }

    /**
     * @dataProvider loadRHSDataProvider
     * @covers ::load
     */
    public function testLoadRHS($expected, $rhsId)
    {
        $link = $this->createMock(Link2::class);
        $link->expects($this->any())
            ->method('getSide')
            ->willReturn('RHS');
        $bean = $this->createMock(SugarBean::class);
        $bean->rhs_id = $rhsId;
        $link->expects($this->any())
            ->method('getFocus')
            ->willReturn($bean);
        $def = [
            'rhs_key' => 'rhs_id',
            'name' => 'test-link',
            'lhs_module' => 'test-lhs-module',
            'rhs_module' => 'test-rhs-module',
        ];
        $relationship = $this->getMockBuilder(One2MBeanRelationship::class)
            ->setMethods(['getLinkedDefForModuleByRelationship'])
            ->setConstructorArgs([$def])
            ->getMock();

        $result = $relationship->load($link);
        $this->assertEquals($expected, $result['rows']);
    }

    /**
     * @covers ::load
     */
    public function testLoadLHS()
    {
        $link = $this->createMock(Link2::class);
        $link->expects($this->any())
            ->method('getSide')
            ->willReturn('LHS');
        $def = [
            'name' => 'test-link',
            'lhs_module' => 'test-lhs-module',
            'rhs_module' => 'test-rhs-module',
        ];
        $relationship = $this->getMockBuilder(One2MBeanRelationship::class)
            ->setMethods(['getLinkedDefForModuleByRelationship', 'getSugarQuery'])
            ->setConstructorArgs([$def])
            ->getMock();
        $sugarQuery = $this->createMock(SugarQuery::class);
        $sugarQuery->expects($this->once())
            ->method('execute')
            ->willReturn([['id' => 'id-1', 'name' => 'test name']]);
        $relationship->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sugarQuery);
        $result = $relationship->load($link);
        $this->assertEquals(['id-1' => ['id' => 'id-1', 'name' => 'test name']], $result['rows']);
    }
}
