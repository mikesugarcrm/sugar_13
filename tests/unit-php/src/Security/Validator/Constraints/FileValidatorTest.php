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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\File;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\FileValidator;
use Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\FileValidator
 */
class FileValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @var string
     */
    protected $customExistedFile;

    /**
     * {@inheritDoc
     */
    protected function tearDown(): void
    {
        if (!empty($this->customExistedFile) && file_exists($this->customExistedFile)) {
            unlink($this->customExistedFile);
        }
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new FileValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new File());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new File());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testExpectsStringCompatibleType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), new File());
    }

    /**
     * @covers ::validate
     */
    public function testExpectsBaseDirsToBeSet()
    {
        $constraint = new File();
        $constraint->baseDirs = [];

        $this->expectException(ConstraintDefinitionException::class);
        $this->validator->validate('xxx', $constraint);
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value, array $baseDirs, $expected)
    {
        $constraint = new File([
            'baseDirs' => $baseDirs,
        ]);
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
        $this->assertSame($expected, $constraint->getFormattedReturnValue());
    }

    public function providerTestValidValues()
    {
        return [
            [
                __DIR__ . '/Fixtures/basedir1/exists.txt',
                [
                    __DIR__ . '/Fixtures/basedir1',
                ],
                __DIR__ . '/Fixtures/basedir1/exists.txt',
            ],
            [
                __DIR__ . '/Fixtures/basedir2/exists.txt',
                [
                    __DIR__ . '/Fixtures/basedir1',
                    __DIR__ . '/Fixtures/basedir2',
                ],
                __DIR__ . '/Fixtures/basedir2/exists.txt',
            ],
        ];
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, array $baseDirs, $code, $msg)
    {
        $constraint = new File([
            'message' => 'testMessage',
            'baseDirs' => $baseDirs,
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
                'doesnotexist.php',
                [
                    __DIR__ . '/Fixtures/basedir1',
                ],
                File::ERROR_FILE_NOT_FOUND,
                'file not found',
            ],
            [
                'modules/Accounts/vardefs.php' . chr(0) . '.gif',
                [
                    __DIR__ . '/Fixtures/basedir1',
                    __DIR__ . '/Fixtures/basedir2',
                ],
                File::ERROR_NULL_BYTES,
                'null bytes detected',
            ],
            [
                __DIR__ . '/Fixtures/basedir1/exists2.txt',
                [
                    __DIR__ . '/Fixtures/basedir2',
                ],
                File::ERROR_OUTSIDE_BASEDIR,
                'file outside basedir',
            ],
        ];
    }

    /**
     * @covers ::validate
     */
    public function testValidateCustomFileExists()
    {
        $instanceDir = realpath(SUGAR_BASE_DIR);
        // Dont move file creation to set up because test bootstrap is run after test object created
        $this->customExistedFile = $instanceDir . '/custom/fileExist.txt';
        $constraint = new File([
            'baseDirs' => [
                $instanceDir . '/custom',
            ],
        ]);

        touch($this->customExistedFile);
        $this->validator->validate($this->customExistedFile, $constraint);
        $this->assertNoViolation();
    }
}
