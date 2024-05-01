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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Escalation;

use Escalation;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass Escalation
 */
class EscalationTest extends TestCase
{
    /**
     * @covers ::handleParentEscalation()
     *
     * @dataProvider handleParentEscalationProvider
     */
    public function testHandleParentEscalation($stateChanges, $expectedCount, $callArgs)
    {
        $escalation = $this->getEscalationMock(['setParentEscalation']);
        $escalation->parent_id = 'test_id';
        $escalation->parent_type = 'test_type';

        TestReflection::setProtectedValue($escalation, 'stateChanges', $stateChanges);

        $escalation->expects($this->exactly($expectedCount))
            ->method('setParentEscalation')
            ->withConsecutive(...$callArgs);

        $escalation->handleParentEscalation();
    }

    public function handleParentEscalationProvider(): array
    {
        return [
            [
                'stateChanges' => [
                    'name' => '123',
                ],
                'expectedCount' => 1,
                'callArgs' => [['test_type', 'test_id']],
            ],
            [
                'stateChanges' => [
                    'name' => '123',
                    'parent_type' => [
                        'before' => 'Accounts',
                        'after' => 'Cases',
                    ],
                    'parent_id' => [
                        'before' => '123',
                        'after' => '456',
                    ],
                ],
                'expectedCount' => 2,
                'callArgs' => [
                    ['Accounts', '123'],
                    ['Cases', '456'],
                ],
            ],
            [
                'stateChanges' => [
                    'name' => '123',
                    'parent_id' => [
                        'before' => '123',
                        'after' => '456',
                    ],
                ],
                'expectedCount' => 2,
                'callArgs' => [
                    ['test_type', '123'],
                    ['test_type', '456'],
                ],
            ],
        ];
    }

    /**
     * @covers ::isParentEscalated()
     *
     * @dataProvider isParentEscalatedProvider
     */
    public function testIsParentEscalated($queryResult, $expectedCount)
    {
        $escalation = $this->getEscalationMock(['getNonClosedEscalationsForParent']);

        $escalation->method('getNonClosedEscalationsForParent')->willReturn($queryResult);

        $actual = $escalation->isParentEscalated('123');

        $this->assertEquals($expectedCount, $actual);
    }

    public function isParentEscalatedProvider(): array
    {
        return [
            [
                'queryResult' => [],
                'expected' => false,
            ],
            [
                'queryResult' => ['123'],
                'expected' => true,
            ],
        ];
    }

    protected function getEscalationMock($methods = [])
    {
        return $this->getMockBuilder('Escalation')
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
    }
}
