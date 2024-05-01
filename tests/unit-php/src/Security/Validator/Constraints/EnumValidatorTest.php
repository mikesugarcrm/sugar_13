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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Enum;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\EnumValidator;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\EnumValidator
 */
class EnumValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new EnumValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new Enum());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new Enum());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testNotArrayValueIsInvalid()
    {
        $constraint = new Enum([
            'message' => 'testMessage',
        ]);

        $this->validator->validate('test', $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('%msg%', 'is not array')
            ->setCode(Enum::ERROR_IS_NOT_ARRAY)
            ->setInvalidValue('test')
            ->assertRaised();
    }

    /**
     * @covers ::validate
     */
    public function testArrayWithNotAllowedValue()
    {
        $constraint = new Enum([
            'message' => 'testMessage',
            'allowedValues' => ['test'],
        ]);

        $this->validator->validate(['not_allowed'], $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('%msg%', 'disallowed values')
            ->setCode(Enum::ERROR_USED_NOT_ALLOWED_VALUE)
            ->setInvalidValue('not_allowed')
            ->assertRaised();
    }
}
