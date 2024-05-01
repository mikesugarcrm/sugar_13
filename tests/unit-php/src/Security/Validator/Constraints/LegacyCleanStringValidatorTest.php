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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\LegacyCleanString;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\LegacyCleanStringValidator;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\LegacyCleanStringValidator
 */
class LegacyCleanStringValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new LegacyCleanStringValidator();
    }

    /**
     * @coversNothing
     */
    public function testExpectValidFilter()
    {
        $constraint = new LegacyCleanString([
            'filter' => 'foobar',
        ]);

        $this->expectException(ConstraintDefinitionException::class);
        $this->validator->validate('xyz', $constraint);
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($filter, $value)
    {
        $constraint = new LegacyCleanString([
            'filter' => $filter,
        ]);

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestValidValues
     */
    public function testValidValuesRecursive($filter, $value)
    {
        $recursiveValue = [$value, [$value, $value]];

        $constraint = new LegacyCleanString([
            'filter' => $filter,
        ]);

        $this->validator->validate($recursiveValue, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return [
            [LegacyCleanString::STANDARD, 'teststring'],
            [LegacyCleanString::STANDARD, 'foo_bar@sugar-crm.com'],
            [LegacyCleanString::STANDARD, '4.2.1'],
            [LegacyCleanString::STANDARDSPACE, 'more foo_bar@sugar-crm.com'],
            [LegacyCleanString::FILE, 'FileName-v1.0_latest.zip'],
            [LegacyCleanString::NUMBER, '987654'],
            [LegacyCleanString::NUMBER, '-987654'],
            [LegacyCleanString::NUMBER, '98-7654'],
            [LegacyCleanString::SQL_COLUMN_LIST, 'date(d),now_time.x'],
            [LegacyCleanString::PATH_NO_URL, '/etc/passwd'],
            [LegacyCleanString::PATH_NO_URL, '../../etc/passwd'],
            // TODO .. to be completed
        ];
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($filter, $value)
    {
        $constraint = new LegacyCleanString([
            'message' => 'testMessage',
            'filter' => $filter,
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setCode(LegacyCleanString::FILTER_ERROR)
            ->setParameter('%filter%', $filter)
            ->setCause($filter)
            ->setInvalidValue($value)
            ->assertRaised();
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValuesRecursive($filter, $value)
    {
        $recursiveValue = [$value, [$value, $value]];

        $constraint = new LegacyCleanString([
            'message' => 'testMessage',
            'filter' => $filter,
        ]);

        $this->validator->validate($recursiveValue, $constraint);

        $this->buildViolation('testMessage')
            ->setCode(LegacyCleanString::FILTER_ERROR)
            ->setParameter('%filter%', $filter)
            ->setCause($filter)
            ->setInvalidValue($value)
            ->buildNextViolation('testMessage')
            ->setCode(LegacyCleanString::FILTER_ERROR)
            ->setParameter('%filter%', $filter)
            ->setCause($filter)
            ->setInvalidValue($value)
            ->buildNextViolation('testMessage')
            ->setCode(LegacyCleanString::FILTER_ERROR)
            ->setParameter('%filter%', $filter)
            ->setCause($filter)
            ->setInvalidValue($value)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return [
            // non strings should always fail regardless the filter
            [LegacyCleanString::STANDARD, null],
            [LegacyCleanString::STANDARD, new \stdClass()],
            [LegacyCleanString::STANDARD, 17],
            [LegacyCleanString::STANDARD, 23.69],

            // filter specific failures
            [LegacyCleanString::STANDARD, 'teststring!'],
            [LegacyCleanString::STANDARD, 'teststring!'],
            [LegacyCleanString::STANDARD, 'test string'],
            [LegacyCleanString::STANDARD, '4.2$1'],
            [LegacyCleanString::STANDARDSPACE, 'test, string'],
            [LegacyCleanString::FILE, '/etc/passwd'],
            [LegacyCleanString::FILE, '../goback.php'],
            [LegacyCleanString::NUMBER, '987654.123'],
            [LegacyCleanString::NUMBER, '987654,123'],
            [LegacyCleanString::NUMBER, 'abc'],
            [LegacyCleanString::SQL_COLUMN_LIST, 'date(d)"'],
            [LegacyCleanString::PATH_NO_URL, 'https://www.google.com'],
            [LegacyCleanString::PATH_NO_URL, 'file:///etc/passwd'],
            // TODO .. to be completed
        ];
    }
}
