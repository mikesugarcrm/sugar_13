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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\JSON;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\JSONValidator;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\JSONValidator
 */
class JSONValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new JSONValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new JSON());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new JSON());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), new JSON());
    }

    /**
     * @covers ::validate
     * @covers       \Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueTrait::getFormattedReturnValue
     * @covers       \Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueTrait::setFormattedReturnValue
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value, $htmlDecode, $assoc, $expectedValue)
    {
        $options = [];
        if ($htmlDecode !== null) {
            $options['htmlDecode'] = $htmlDecode;
        }
        if ($assoc !== null) {
            $options['assoc'] = $assoc;
        }
        $constraint = new JSON($options);
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
        if (is_object($expectedValue)) {
            $this->assertEquals($expectedValue, $constraint->getFormattedReturnValue());
        } else {
            $this->assertSame($expectedValue, $constraint->getFormattedReturnValue());
        }
    }

    public function providerTestValidValues()
    {
        return [
            //Basic JSON array
            ['["a", "b", "c"]', null, null, ['a', 'b', 'c']],
            //Associated JSON array
            ['{"a":"foo", "b":1, "c":true}', null, null, ['a' => 'foo', 'b' => 1, 'c' => true]],
            //Associated JSON array with assoc false
            ['{"a":"foo", "b":1, "c":true}', null, false, (object)['a' => 'foo', 'b' => 1, 'c' => true]],
            //HTML encoded
            ['[&quot;a&quot;, &quot;b&quot;, &quot;c&quot;]', true, null, ['a', 'b', 'c']],
            //HTML encoded AND not assoc
            [
                '{&quot;a&quot;:&quot;foo&quot;, &quot;b&quot;:1, &quot;c&quot;:true}',
                true,
                false,
                (object)['a' => 'foo', 'b' => 1, 'c' => true],
            ],
        ];
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $code, $msg)
    {
        $constraint = new JSON([
            'message' => 'testMessage',
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('%msg%', $msg)
            ->setCode($code)
            ->setInvalidValue($value)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return [
            [
                "This isn't json....",
                JSON::ERROR_JSON_DECODE,
                'json_decode error',
            ],
        ];
    }
}
