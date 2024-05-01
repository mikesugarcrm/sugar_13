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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\PhpSerialized;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\PhpSerializedValidator;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\PhpSerializedValidator
 */
class PhpSerializedValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new PhpSerializedValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new PhpSerialized());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new PhpSerialized());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), new PhpSerialized());
    }

    /**
     * @covers ::validate
     * @covers       \Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueTrait::getFormattedReturnValue
     * @covers       \Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueTrait::setFormattedReturnValue
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value, $base64Encoded, $htmlEncoded, $unserialized)
    {
        $constraint = new PhpSerialized();
        $constraint->base64Encoded = $base64Encoded;
        $constraint->htmlEncoded = $htmlEncoded;
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
        $this->assertSame($unserialized, $constraint->getFormattedReturnValue());
    }

    public function providerTestValidValues()
    {
        return [

            // plain serialized strings
            ['N;', false, false, null],
            ['b:0;', false, false, false],
            ['b:1;', false, false, true],
            ['i:10;', false, false, 10],
            ['d:12.199999999999999;', false, false, 12.2],
            ['s:6:"String";', false, false, 'String'],
            ['a:1:{s:3:"foo";s:3:"bar";}', false, false, ['foo' => 'bar']],

            // base64 encoded tests
            [
                'Tjs=',
                true,
                false,
                null,
            ],
            [
                'YjowOw==',
                true,
                false,
                false,
            ],
            [
                'YjoxOw==',
                true,
                false,
                true,
            ],
            [
                'aToxMDs=',
                true,
                false,
                10,
            ],
            [
                'ZDoxMi4xOTk5OTk5OTk5OTk5OTk7',
                true,
                false,
                12.2,
            ],
            [
                'czo2OiJTdHJpbmciOw==',
                true,
                false,
                'String',
            ],
            [
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30=',
                true,
                false,
                ['foo' => 'bar'],
            ],
            [
                's:28:&quot;&lt;div class=&quot;link&quot;&gt;Link&lt;/div&gt;&quot;;',
                false,
                true,
                '<div class="link">Link</div>',
            ],
            [
                'czoyODomcXVvdDsmbHQ7ZGl2IGNsYXNzPSZxdW90O2xpbmsmcXVvdDsmZ3Q7TGluayZsdDsvZGl2Jmd0OyZxdW90Ozs=',
                true,
                true,
                '<div class="link">Link</div>',
            ],
        ];
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $code, $msg)
    {
        $constraint = new PhpSerialized([
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
                'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ],
            [
                'O:8:',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ],
            [
                'mambojambo',
                PhpSerialized::ERROR_UNSERIALIZE,
                'unserialize error',
            ],
            [
                'C:6:"FooBar":3:{baz}',
                PhpSerialized::ERROR_OBJECT_NOT_ALLOWED,
                'object(s) not allowed',
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";r:4;}',
                PhpSerialized::ERROR_REFERENCE_NOT_ALLOWED,
                'reference(s) not allowed',
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";R:1;}',
                PhpSerialized::ERROR_REFERENCE_NOT_ALLOWED,
                'reference(s) not allowed',
            ],
        ];
    }
}
