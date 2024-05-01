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

use Exception;
use M2MRelationship;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarTestReflection;

/**
 * @coversDefaultClass \M2MRelationship
 */
class M2MRelationshipTest extends TestCase
{
    /**
     * @var array
     */
    protected $def;

    protected function setUp(): void
    {
        $GLOBALS['log'] = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->def = [
            'join_key_rhs' => 'id-right',
            'join_key_lhs' => 'id-left',
            'name' => 'link-test',
            'lhs_module' => 'LeftModule',
            'rhs_module' => 'RightModule',
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
    }

    public function loadDataProvider()
    {
        return [
            'no relationship rows - left side' => [[], [], true],
            'no relationship rows - right side' => [[], [], false],
            'no ids in relationship rows - left side' => [
                [],
                [['field' => 'field-1'], ['field' => 'field-2']],
                true,
            ],
            'no ids in relationship rows - right side' => [
                [],
                [['field' => 'field-1'], ['field' => 'field-2']],
                false,
            ],
            'valid results - left side' => [
                [
                    'id-1' => ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-2' => ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                true,
            ],
            'valid results - right side' => [
                [
                    'id-1' => ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-2' => ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                false,
            ],
            'join key used - left side' => [
                [
                    'id-right-1' => ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-right-2' => ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                true,
            ],
            'join key used - right side' => [
                [
                    'id-left-1' => ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-left-2' => ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider loadDataProvider
     * @covers ::load
     */
    public function testLoad($expected, $queryResults, $linkIsLHS)
    {
        $relationship = $this->getMockBuilder('M2MRelationship')
            ->setMethods(['getSugarQuery', 'linkIsLHS', 'getLinkedDefForModuleByRelationship'])
            ->setConstructorArgs([$this->def])
            ->getMock();
        $sugarQuery = $this->createMock('SugarQuery');
        $sugarQuery->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($queryResults));
        $relationship->expects($this->once())
            ->method('getSugarQuery')
            ->will($this->returnValue($sugarQuery));
        $relationship->expects($this->once())
            ->method('linkIsLHS')
            ->will($this->returnValue($linkIsLHS));
        $rows = $relationship->load($this->createMock('Link2'), []);
        $this->assertArrayHasKey('rows', $rows);
        $this->assertEquals($expected, $rows['rows']);
    }

    /**
     * @covers ::getSugarQuery
     */
    public function testLoadException()
    {
        $relationship = $this->getMockBuilder('M2MRelationship')
            ->setMethods(['linkIsLHS', 'getLinkedDefForModuleByRelationship'])
            ->setConstructorArgs([$this->def])
            ->getMock();
        $relationship->expects($this->once())
            ->method('linkIsLHS')
            ->will($this->returnValue(true));

        $this->expectException(Exception::class);
        $relationship->load($this->createMock('Link2'), []);
    }

    /**
     * @covers ::setNewPrimary
     */
    public function testSetNewPrimaryFlagDoesNotSet()
    {
        /** @var M2MRelationship|MockObject $relationship */
        $relationship = $this->getMockBuilder(M2MRelationship::class)
            ->setMethods(['getLinkedDefForModuleByRelationship', 'removeRow'])
            ->setConstructorArgs([$this->def])
            ->getMock();

        $relationship->expects($this->once())
            ->method('removeRow');

        $lhs = $this->createMock(\SugarBean::class);
        $lhs->expects($this->once())
            ->method('load_relationship')
            ->willReturn(true);

        $rhs = $this->createMock(\SugarBean::class);
        $rhs->expects($this->once())
            ->method('load_relationship')
            ->willReturn(true);

        SugarTestReflection::setProtectedValue($relationship, 'lhsLink', 'not-empty');
        SugarTestReflection::setProtectedValue($relationship, 'rhsLink', 'not-empty');

        $result = $relationship->remove($lhs, $rhs);

        $this->assertTrue($result);
    }

    /**
     * @covers ::remove
     * @covers Link2::resetLoaded
     */
    public function testRemove()
    {
        /** @var M2MRelationship|MockObject $relationship */
        $relationship = $this->getMockBuilder(M2MRelationship::class)
            ->setMethods(['getLinkedDefForModuleByRelationship', 'removeRow'])
            ->setConstructorArgs([$this->def])
            ->getMock();

        /** @var \Link2|MockObject $leftLink */
        $leftLink = $this->createPartialMock(\Link2::class, ['query']);
        TestReflection::setProtectedValue($relationship, 'lhsLink', 'leftLink');

        /** @var \Link2|MockObject $rightLink */
        $rightLink = $this->createPartialMock(\Link2::class, ['query']);
        TestReflection::setProtectedValue($relationship, 'rhsLink', 'rightLink');

        $lhs = $this->createPartialMock(\SugarBean::class, ['call_custom_logic']);
        $lhs->leftLink = $leftLink;
        $lhs->id = 'lhs';

        $rhs = $this->createPartialMock(\SugarBean::class, ['call_custom_logic']);
        $rhs->rightLink = $rightLink;
        $rhs->id = 'rhs';

        $rightLinkRows = ['foo'];
        $leftLinkRows = ['bar'];

        TestReflection::setProtectedValue($rightLink, 'rows', $rightLinkRows);
        TestReflection::setProtectedValue($leftLink, 'rows', $leftLinkRows);

        // call_custom_logic should be able to get "rows" property from Link2
        $rhs->method('call_custom_logic')
            ->withConsecutive(['before_relationship_delete'], ['after_relationship_delete'])
            ->willReturnCallback(function ($hookName) use ($rightLink, $rightLinkRows) {
                $this->assertSame($rightLinkRows, $rightLink->rows);
            });

        $lhs->method('call_custom_logic')
            ->withConsecutive(['before_relationship_delete'], ['after_relationship_delete'])
            ->willReturnCallback(function ($hookName) use ($leftLink, $leftLinkRows) {
                $this->assertSame($leftLinkRows, $leftLink->rows);
            });

        $relationship->remove($lhs, $rhs);
    }
}
