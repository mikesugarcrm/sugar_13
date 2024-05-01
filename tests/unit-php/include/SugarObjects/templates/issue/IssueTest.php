<?php

declare(strict_types=1);
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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarObjects\templates\issue;

use PHPUnit\Framework\TestCase;

/**
 * Class IssueTest
 * @coversDefaultClass \Issue
 */
class IssueTest extends TestCase
{
    /**
     * @covers ::isResolved
     * @dataProvider providerIsResolved
     */
    public function testIsResolved(?string $status, bool $expected)
    {
        $bean = $this->createMockIssue();
        $bean->status = $status;
        $this->assertEquals($expected, $bean->isResolved());
    }

    public function providerIsResolved(): array
    {
        return [
            ['Closed', true],
            ['Rejected', true],
            ['Duplicate', true],
            ['New', false],
            ['Pending Input', false],
            ['Assigned', false],
            [null, false],
        ];
    }


    /**
     * @covers ::isNewlyResolved
     * @dataProvider providerIsNewlyResolved
     */
    public function testIsNewlyResolved(bool $isResolved, string $status, ?array $fetchedStatus, bool $expected)
    {
        $bean = $this->createMockIssue(['isResolved']);
        $bean->expects($this->once())
            ->method('isResolved')
            ->willReturn($isResolved);
        $bean->status = $status;
        $bean->fetched_row = $fetchedStatus;
        $this->assertEquals($expected, $bean->isNewlyResolved());
    }

    public function providerIsNewlyResolved(): array
    {
        return [
            [false, 'New', ['status' => 'Irrelevant'], false],
            [true, 'Closed', ['status' => 'Closed'], false],
            [true, 'Closed', ['status' => 'Rejected'], false],
            [true, 'Closed', ['status' => 'New'], true],
            [true, 'Closed', [], true],
            [true, 'Closed', null, true],
        ];
    }

    public function providerGetHoursBetween()
    {
        return [
            [
                'start' => '9/1/2019 08:00:00',
                'end' => '9/4/2019 14:00:00',
                'expect' => 78,
            ],
            [
                'start' => '9/4/2019 08:00:00',
                'end' => '9/1/2019 14:00:00',
                'expect' => 0,
            ],
            [
                'start' => '8/1/2019 08:00:00',
                'end' => '8/4/2019 14:00:00',
                'expect' => 78,
            ],
            [
                'start' => '2019-08-30 09:15:00',
                'end' => '2019-09-04 17:30:00',
                'expect' => 128.25,
            ],
            [
                'start' => '9/25/2019 00:00:00',
                'end' => '9/25/2019 17:15:00',
                'expect' => 17.25,
            ],
            [
                'start' => '9/25/2019 08:00:00',
                'end' => '9/25/2019 08:00:00',
                'expect' => 0,
            ],
        ];
    }

    /**
     * @covers ::getHoursBetween
     * @dataProvider providerGetHoursBetween
     */
    public function testGetHoursBetween(string $start, string $end, float $expect)
    {
        $startDT = new \SugarDateTime($start);
        $endDT = new \SugarDateTime($end);

        $bean = $this->createMockIssue();
        $actual = $bean->getHoursBetween($startDT, $endDT);
        $this->assertEquals($expect, $actual['calendarHours']);
    }


    public function createMockIssue(array $methods = [])
    {
        return $this->createPartialMock(\Issue::class, $methods);
    }
}
