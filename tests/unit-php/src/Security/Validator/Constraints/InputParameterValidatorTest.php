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

use Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\InputParameters;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\InputParametersValidator;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\InputParametersValidator
 */
class InputParameterValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new InputParametersValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new InputParameters());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new InputParameters());
        $this->assertNoViolation();
    }

    /**
     * @coversNothing
     */
    public function testExpectValidInputType()
    {
        $constraint = new InputParameters([
            'inputType' => 'foobar',
        ]);

        $this->expectException(ConstraintDefinitionException::class);
        $this->validator->validate('xyz', $constraint);
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($type, $value)
    {
        $constraint = new InputParameters([
            'inputType' => $type,
        ]);

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return [
            [Superglobals::GET, 'helloworld'],
            [Superglobals::POST, ['foo', 'bar']],
            [Superglobals::REQUEST, [['foo', 'bar']]],
        ];
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @covers ::getErrorCode
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($type, $code, $value, $msg, array $expectedViolations)
    {
        $violations = null;
        $constraint = new InputParameters([
            $msg => 'testMessage',
            'inputType' => $type,
        ]);

        $this->validator->validate($value, $constraint);

        foreach ($expectedViolations as $expectedViolation) {
            if (empty($violations)) {
                $violations = $this->buildViolation('testMessage')
                    ->setCode($code)
                    ->setInvalidValue($expectedViolation)
                    ->setParameter('%type%', $type);
            } else {
                $violations = $violations->buildNextViolation('testMessage')
                    ->setCode($code)
                    ->setInvalidValue($expectedViolation)
                    ->setParameter('%type%', $type);
            }
        }

        $violations->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return [

            // generic non-scalar tests
            [
                Superglobals::GET,
                InputParameters::ERROR_GET,
                new \stdClass(),
                'msgGeneric',
                [new \stdClass()],
            ],

            // null byte tests
            [
                Superglobals::GET,
                InputParameters::ERROR_GET,
                'test.php' . chr(0) . '.gif',
                'msgNullBytes',
                ['test.php' . chr(0) . '.gif'],
            ],
            [
                Superglobals::POST,
                InputParameters::ERROR_POST,
                chr(0) . '.gif',
                'msgNullBytes',
                [chr(0) . '.gif'],
            ],
            [
                Superglobals::REQUEST,
                InputParameters::ERROR_REQUEST,
                'test.php' . chr(0),
                'msgNullBytes',
                ['test.php' . chr(0)],
            ],
        ];
    }
}
