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

namespace Sugarcrm\SugarcrmTestsUnit\Audit\Formatter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Audit\Formatter\Subject;
use Sugarcrm\Sugarcrm\Security\Subject\Formatter as SubFormatter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\Formatter\Email
 */
class SubjectTest extends TestCase
{
    /**
     * @covers ::formatRows
     * @dataProvider formatRowsProvider
     */
    public function testFormatRows($rows, $expectedSubjects, $returnSubjects, $expectedOutput)
    {
        $mockFormatter = $this->createMock(SubFormatter::class);
        $mockFormatter->expects($this->once())
            ->method('formatBatch')
            ->with($expectedSubjects)
            ->willReturn($returnSubjects);

        $subjectFormatter = new Subject($mockFormatter);
        $subjectFormatter->formatRows($rows);

        $this->assertSame($expectedOutput, $rows);
    }

    public function formatRowsProvider()
    {
        return [
            //Data set 1
            [
                //rows
                [
                    'row-id-1' => [
                        'field_name' => 'f1',
                        'data_type' => 'varchar',
                        'before' => 'foo',
                        'after' => 'bar',
                    ],
                    'row-id-2' => [
                        'field_name' => 'f2',
                        'data_type' => 'varchar',
                        'before' => 'foo',
                        'after' => null,
                        'source' => [
                            'subject' => 'marketo',
                        ],
                    ],
                    'row-id-3' => [
                        'field_name' => 'f3',
                        'data_type' => 'varchar',
                        'before' => null,
                        'after' => 'bar',
                        'source' => [
                            'subject' => [
                                'type' => 'User',
                                'id' => 'user-id-1',
                            ],
                        ],
                    ],
                ],
                //Expected subjects pulled from mock rows
                [
                    'row-id-2' => 'marketo',
                    'row-id-3' => [
                        'type' => 'User',
                        'id' => 'user-id-1',
                    ],
                ],
                //Mock formatted subjects
                [
                    'row-id-2' => ['type' => 'marketo'],
                    'row-id-3' => [
                        'type' => 'User',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'id' => 'user-id-1',
                    ],
                ],
                //Expected formated rows
                [
                    'row-id-1' => [
                        'field_name' => 'f1',
                        'data_type' => 'varchar',
                        'before' => 'foo',
                        'after' => 'bar',
                    ],
                    'row-id-2' => [
                        'field_name' => 'f2',
                        'data_type' => 'varchar',
                        'before' => 'foo',
                        'after' => null,
                        'source' => [
                            'subject' => ['type' => 'marketo'],
                        ],
                    ],
                    'row-id-3' => [
                        'field_name' => 'f3',
                        'data_type' => 'varchar',
                        'before' => null,
                        'after' => 'bar',
                        'source' => [
                            'subject' => [
                                'type' => 'User',
                                'first_name' => 'John',
                                'last_name' => 'Doe',
                                'id' => 'user-id-1',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
