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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\Sql;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Sql\OrderBy;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Sql\OrderByValidator;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\Sql\OrderByValidator
 */
class OrderByValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new OrderByValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new OrderBy());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new OrderBy());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), new OrderBy());
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value)
    {
        $constraint = new OrderBy();
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return [
            ['date_modified'],
            ['Accounts.date_modified'],
            ['0a0'],
            ['a0a'],
            ['0_a_0'],
            ['a_0_a'],
            ['a_.0_a'],
            ['a_0._a'],
            ['0a0.0a0'],
            ['a0a.a0a'],
            ['a0a_._a0a'],
            ['0dsd.f0'],
            ['dsd_1.f_2'],
            ['0a0.0a0'],
            ['a0.a0'],
            ['a0.0a'],
            ['0a.a0'],
            ['0a.0a'],
        ];
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $code, $msg)
    {
        $constraint = new OrderBy([
            'message' => 'testMessage',
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setInvalidValue($value)
            ->setParameter('%msg%', $msg)
            ->setCode($code)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return [
            [
                'date_modified (WHERE foo = bar)',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                0.0,
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                '12',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                12,
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                '0.0',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                '_._',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                '0_._0',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                'a_._0',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                'a0.0',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                '0.0a',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                'dd.dd.aa',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                'tbl.',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                '.col',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
            [
                'tbl.col1.',
                OrderBy::ERROR_ILLEGAL_FORMAT,
                'illegal format',
            ],
        ];
    }
}
