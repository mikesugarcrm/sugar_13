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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Delimited;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\DelimitedValidator;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\DelimitedValidator
 */
class DelimitedValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new DelimitedValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $constraint = new Delimited([
            'constraints' => [new NotBlank()],
        ]);
        $this->validator->validate(null, $constraint);
        $this->assertNoViolation();
        $this->assertSame([], $constraint->getFormattedReturnValue());
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $constraint = new Delimited([
            'constraints' => [new NotBlank()],
        ]);
        $this->validator->validate('', $constraint);
        $this->assertNoViolation();
        $this->assertSame([], $constraint->getFormattedReturnValue());
    }

    /**
     * @covers ::validate
     */
    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $constraint = new Delimited([
            'constraints' => new NotBlank(),
        ]);
        $this->validator->validate(new \stdClass(), $constraint);
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestWalkConstraints
     */
    public function testWalkConstraints($value, $delimiter, array $expected)
    {
        $constraints = [
            new NotNull(),
            new NotBlank(),
            new Range(['min' => 1]),
        ];

        $delimited = new Delimited([
            'constraints' => $constraints,
            'delimiter' => $delimiter,
        ]);

        $i = 0;
        foreach (explode($delimiter, $value) as $k => $v) {
            $this->expectValidateValueAt($i++, '[' . $k . ']', $v, $constraints);
        }

        $this->validator->validate($value, $delimited);
        $this->assertNoViolation();
        $this->assertSame($expected, $delimited->getFormattedReturnValue());
    }

    public function providerTestWalkConstraints()
    {
        return [
            [
                'test',
                ',',
                [
                    'test',
                ],
            ],
            [
                'test1,test2',
                ',',
                [
                    'test1',
                    'test2',
                ],
            ],
            [
                'test1;test2',
                ';',
                [
                    'test1',
                    'test2',
                ],
            ],
            [
                'test1::test2',
                '::',
                [
                    'test1',
                    'test2',
                ],
            ],
        ];
    }
}
