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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\Mvc;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Mvc\ModuleName;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\Mvc\ModuleNameValidator
 */
class ModuleNameValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * List of modules as reported by globals
     * @var unknown
     */
    protected $moduleList = [
        'Accounts',
        'Contacts',
        'Leads',
        'MailMerge',
    ];

    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Security\Validator\Constraints\Mvc\ModuleNameValidator::class)
            ->setConstructorArgs([$this->moduleList])
            ->setMethods(['isValidBeanModule'])
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
     * @covers ::isValidModule
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value, $isBean)
    {
        $constraint = new ModuleName();

        $this->validator->method('isValidBeanModule')
            ->willReturn($isBean);

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return [

            // use bean validation
            ['Accounts', true],

            // module list validation
            ['MailMerge', false],

            // url rewrite for cache/jsLanguage uses app_strings
            ['app_strings', false],
        ];
    }

    /**
     * @covers ::validate
     * @covers ::isValidModule
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $isBean, $code)
    {
        $constraint = new ModuleName([
            'message' => 'testMessage',
        ]);

        $this->validator->method('isValidBeanModule')
            ->willReturn($isBean);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setInvalidValue($value)
            ->setParameter('%module%', $value)
            ->setCode($code)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return [
            [
                'FooBar',
                false,
                ModuleName::ERROR_UNKNOWN_MODULE,
            ],
        ];
    }
}
