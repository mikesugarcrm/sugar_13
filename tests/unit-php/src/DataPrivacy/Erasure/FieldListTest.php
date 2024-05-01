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

namespace Sugarcrm\SugarcrmTestsUnit\DataPrivacy\Erasure;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SugarBean;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Email;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Scalar;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList
 */
class FieldListTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct()
     * @covers ::createEmailField()
     * @covers ::createScalarField()
     * @covers ::fromArray()
     */
    public function fromArray()
    {
        $this->assertEquals(new FieldList(
            new Scalar('name'),
            new Email('the-id')
        ), FieldList::fromArray([
            'name',
            [
                'field_name' => 'email',
                'id' => 'the-id',
            ],
        ]));
    }

    /**
     * @test
     * @covers ::fromArray()
     */
    public function fromArrayFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        FieldList::fromArray([[]]);
    }

    /**
     * @test
     * @covers ::__construct()
     * @covers ::jsonSerialize()
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $this->assertEquals([
            'name',
            [
                'field_name' => 'email',
                'id' => 'the-id',
            ],
        ], (new FieldList(
            new Scalar('name'),
            new Email('the-id')
        ))->jsonSerialize());
    }

    /**
     * @test
     * @covers ::with()
     */
    public function with()
    {
        $name1 = new Scalar('name-1');
        $name2 = new Scalar('name-2');
        $email1 = new Email('the-id-1');
        $email2 = new Email('the-id-2');
        $list1 = new FieldList($name1, $email1);
        $list2 = new FieldList($name2, $email2);

        $this->assertEquals(new FieldList(
            $name1,
            $email1,
            $name2,
            $email2
        ), $list1->with($list2));
    }

    /**
     * @test
     * @covers ::without()
     */
    public function without()
    {
        $name1 = new Scalar('name-1');
        $name2 = new Scalar('name-2');
        $email1 = new Email('the-id-1');
        $email2 = new Email('the-id-2');
        $list1 = new FieldList($name1, $email1);
        $list2 = new FieldList($name1, $name2, $email1, $email2);

        $this->assertEquals(new FieldList(
            $name2,
            $email2
        ), $list2->without($list1));
    }

    /**
     * @test
     * @covers ::erase()
     */
    public function erase()
    {
        $bean = $this->createMock(SugarBean::class);
        $field1 = $this->createMock(Field::class);
        $field1->expects($this->once())
            ->method('erase')
            ->with($bean);
        $field2 = $this->createMock(Field::class);
        $field2->expects($this->once())
            ->method('erase')
            ->with($bean);

        $list = new FieldList($field1, $field2);
        $list->erase($bean);
    }
}
