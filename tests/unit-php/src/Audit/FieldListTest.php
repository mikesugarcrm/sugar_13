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

namespace Sugarcrm\SugarcrmTestsUnit\Audit;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Audit\Change;
use Sugarcrm\Sugarcrm\Audit\Change\Email;
use Sugarcrm\Sugarcrm\Audit\Change\Scalar;
use Sugarcrm\Sugarcrm\Audit\FieldChangeList;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\FieldChangeList
 */
class FieldListTest extends TestCase
{
    /**
     * @covers ::fromChanges
     * @dataProvider fromChangesProvider
     */
    public function testfromChanges($changes, $expectedClasses)
    {
        $fl = FieldChangeList::fromChanges($changes);

        foreach ($fl as $i => $field) {
            $this->assertInstanceOf($expectedClasses[$i], $field);
        }
    }

    public function fromChangesProvider()
    {
        return [
            //Data set 1
            [
                //Changes
                [
                    'bool1' => [
                        'field_name' => 'bool1',
                        'data_type' => 'bool',
                        'before' => false,
                        'after' => true,
                    ],
                    'email' => [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => 'id-1234',
                        'after' => 'id-5678',
                    ],
                ],
                //Expected Classes
                [
                    Scalar::class,
                    Email::class,
                ],
            ],
        ];
    }

    /**
     * @covers ::jsonSerialize
     */
    public function testToJson()
    {
        $mockField1 = $this->createMock(Change::class);
        $mockField1->expects($this->once())->method('jsonSerialize')->willReturn('string value');

        $mockField2 = $this->createMock(Change::class);
        $mockField2->expects($this->once())->method('jsonSerialize')->willReturn(['foo' => 'bar']);

        $fb = new FieldChangeList($mockField1, $mockField2);

        $json = $fb->jsonSerialize();

        $this->assertSame(['string value', ['foo' => 'bar']], $json);
    }

    /**
     * @covers ::getChangesList
     * @dataProvider getChangesListProvider
     */
    public function testGetChangesList($changes, $expected)
    {

        $fl = FieldChangeList::fromChanges($changes);

        $changeList = $fl->getChangesList();

        foreach ($changeList as $i => $field) {
            $this->assertSame($expected[$i], $field);
        }
    }

    public function getChangesListProvider()
    {
        return [
            //Data set 1
            [
                //Changes
                [
                    'bool1' => [
                        'field_name' => 'bool1',
                        'data_type' => 'bool',
                        'before' => false,
                        'after' => true,
                    ],
                    'email' => [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => 'id-1234',
                        'after' => 'id-5678',
                    ],
                ],
                //Expected Output Changes
                [
                    [
                        'field_name' => 'bool1',
                        'data_type' => 'bool',
                        'before' => false,
                        'after' => true,
                    ],
                    [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => 'id-1234',
                        'after' => null,
                    ],
                    [
                        'field_name' => 'email',
                        'data_type' => 'email',
                        'before' => null,
                        'after' => 'id-4567',
                    ],
                ],
            ],
        ];
    }
}
