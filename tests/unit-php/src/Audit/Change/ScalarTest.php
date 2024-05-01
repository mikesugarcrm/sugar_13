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
use Sugarcrm\Sugarcrm\Audit\Change\Scalar;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\FieldChangeList
 */
class ScalarTest extends TestCase
{
    /**
     * @covers ::fromChanges
     * @dataProvider fromChangeProvider
     */
    public function testfromChange($scalarChange, $expectedChanges)
    {
        $scalarChanges = Scalar::getAuditFieldChanges($scalarChange);

        foreach ($scalarChanges as $i => $scalarChange) {
            $this->assertSame($scalarChange->getChangeArray(), $expectedChanges[$i]);
        }
    }

    public function fromChangeProvider()
    {
        return [
            //Data set 1
            [
                //Change
                [
                    'field_name' => 'mybool',
                    'data_type' => 'bool',
                    'before' => null,
                    'after' => true,
                ],
                //Expected changes
                [
                    [
                        'field_name' => 'mybool',
                        'data_type' => 'bool',
                        'before' => null,
                        'after' => true,
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
        $mock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Audit\Change\Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChangeArray'])
            ->getMock();
        $mock->expects($this->once())
            ->method('getChangeArray')
            ->willReturn($changeData);

        $this->assertSame($changeData, $mock->jsonSerialize());
    }
}
