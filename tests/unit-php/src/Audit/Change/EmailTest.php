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

namespace Sugarcrm\SugarcrmTestsUnit\Audit\Change;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Audit\Change\Email;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\FieldChangeList
 */
class EmailTest extends TestCase
{
    /**
     * @covers ::fromChanges
     * @dataProvider fromChangeProvider
     */
    public function testfromChange($change, $expectedChanges)
    {
        $emailChanges = Email::getAuditFieldChanges($change);

        foreach ($emailChanges as $i => $emailChange) {
            $this->assertSame($emailChange->getChangeArray(), $expectedChanges[$i]);
        }
    }

    public function fromChangeProvider()
    {
        return [
            //Data set 1
            [
                //Change
                [
                    'field_name' => 'email',
                    'data_type' => 'email',
                    'before' => [
                        [
                            'id' => 'eabr-id-1',
                            'email_address' => 'foo@example.com',
                            'email_address_id' => 'ea-id-1',
                        ],
                        [
                            'id' => 'eabr-id-2',
                            'email_address' => 'foo2@example.com',
                            'email_address_id' => 'ea-id-2',
                        ],
                    ],
                    'after' => [
                        [
                            'id' => 'eabr-id-1',
                            'email_address' => 'foo@example.com',
                            'email_address_id' => 'ea-id-1',
                        ],
                        [
                            'id' => 'eabr-id-3',
                            'email_address' => 'foo3@example.com',
                            'email_address_id' => 'ea-id-3',
                        ],
                    ],
                ],
                //Expected changes
                [
                    [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => 'ea-id-2',
                        'after' => null,
                    ],
                    [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => null,
                        'after' => 'ea-id-3',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::jsonSerialize
     */
    public function testToJson()
    {
        $changeData = ['foo' => 'bar'];
        $mock = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChangeArray'])
            ->getMock();
        $mock->expects($this->once())
            ->method('getChangeArray')
            ->willReturn($changeData);

        $this->assertSame($changeData, $mock->jsonSerialize());
    }
}
