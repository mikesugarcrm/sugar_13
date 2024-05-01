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
use Sugarcrm\Sugarcrm\Audit\Formatter\Email;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\Formatter\Email
 */
class EmailTest extends TestCase
{
    /**
     * @covers ::formatRows
     * @dataProvider formatRowsProvider
     */
    public function testFormatRows($rows, $idAddressMap, $expectedOutput)
    {
        $emailFormatter = $this->getMockBuilder(Email::class)
            ->setMethods(['getEmailAddressesForIds'])
            ->getMock();
        $emailFormatter->expects($this->once())
            ->method('getEmailAddressesForIds')
            ->willReturn($idAddressMap);

        $emailFormatter->formatRows($rows);

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
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => 'ea-id-1',
                        'after' => null,
                    ],
                    [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => null,
                        'after' => 'ea-id-2',
                    ],
                    [
                        'field_name' => 'foo',
                        'data_type' => 'bool',
                        'before' => null,
                        'after' => true,
                    ],
                ],
                //email id=>address map
                [
                    'ea-id-1' => 'foo@example.com',
                    'ea-id-2' => 'bar@example.com',

                ],
                //Expected formated changes
                [
                    [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => [
                            'id' => 'ea-id-1',
                            'email_address' => 'foo@example.com',
                        ],
                        'after' => null,
                    ],
                    [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => null,
                        'after' => [
                            'id' => 'ea-id-2',
                            'email_address' => 'bar@example.com',
                        ],
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
