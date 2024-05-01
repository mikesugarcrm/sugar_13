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
use Sugarcrm\Sugarcrm\Audit\Formatter\Enum;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\Formatter\Email
 */
class EnumTest extends TestCase
{
    /**
     * @covers ::formatRows
     * @dataProvider formatRowsProvider
     */
    public function testFormatRows($rows, $expectedOutput)
    {
        $enumFormatter = new Enum();
        $enumFormatter->formatRows($rows);

        $this->assertSame($expectedOutput, $rows);
    }

    public function formatRowsProvider()
    {
        return [
            //Data set 1
            [
                //rows
                [
                    [
                        'field_name' => 'enum1',
                        'data_type' => 'enum',
                        'before' => '^foo^',
                        'after' => 'bar^',
                    ],
                    [
                        'field_name' => 'multienum1',
                        'data_type' => 'multienum',
                        'before' => null,
                        'after' => '^foo^,^bar^',
                    ],
                    [
                        'field_name' => 'foo',
                        'data_type' => 'bool',
                        'before' => null,
                        'after' => true,
                    ],
                ],
                //Expected formated changes
                [
                    [
                        'field_name' => 'enum1',
                        'data_type' => 'enum',
                        'before' => ['foo'],
                        'after' => ['bar'],
                    ],
                    [
                        'field_name' => 'multienum1',
                        'data_type' => 'multienum',
                        'before' => null,
                        'after' => ['foo', 'bar'],
                    ],
                    [
                        'field_name' => 'foo',
                        'data_type' => 'bool',
                        'before' => null,
                        'after' => true,
                    ],
                ],
            ],
        ];
    }
}
