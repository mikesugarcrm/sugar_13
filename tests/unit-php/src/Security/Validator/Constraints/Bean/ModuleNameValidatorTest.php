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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\Bean;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean\ModuleName;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean\ModuleNameValidator
 */
class ModuleNameValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean\ModuleNameValidator::class)
            ->setMethods(['isValidModule'])
            ->getMock();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new ModuleName());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new ModuleName());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), new ModuleName());
    }

    /**
     * @covers ::validate
     */
    public function testValidValues()
    {
        $constraint = new ModuleName();

        // Validation fully relies on BeanFactory so we just stub its result
        $this->validator->method('isValidModule')
            ->willReturn(true);

        $this->validator->validate('Accounts', $constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testInvalidValues()
    {
        $constraint = new ModuleName([
            'message' => 'testMessage',
        ]);

        // Validation fully relies on BeanFactory so we just stub its result
        $this->validator->method('isValidModule')
            ->willReturn(false);

        $this->validator->validate('Foobar', $constraint);

        $this->buildViolation('testMessage')
            ->setInvalidValue('Foobar')
            ->setParameter('%module%', 'Foobar')
            ->setCode(ModuleName::ERROR_UNKNOWN_MODULE)
            ->assertRaised();
    }
}
