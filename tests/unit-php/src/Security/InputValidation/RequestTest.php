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

namespace Sugarcrm\SugarcrmTestsUnit\Security\InputValidation;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Security\InputValidation\Exception\SuperglobalException;
use Sugarcrm\Sugarcrm\Security\InputValidation\Exception\ViolationException;
use Sugarcrm\Sugarcrm\Security\InputValidation\Request;
use Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals;
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder;
use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\InputValidation\Request
 */
class RequestTest extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConstraintBuilder
     */
    protected $constraintBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->validator = Validator::create();
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        // setup ConstraintBuilder with additional fixture namespaces
        $this->constraintBuilder = $builder = new ConstraintBuilder();
        $builder->setNamespaces(array_merge(
            $builder->getNamespaces(),
            ['Sugarcrm\SugarcrmTestsUnit\Security\InputValidation']
        ));
    }

    /**
     * @covers ::getValidInput
     * @covers ::getValidInputGet
     * @covers ::getValidInputPost
     * @covers ::getValidInputRequest
     * @dataProvider providerTestGetValidInput
     */
    public function testGetValidInput(array $get, array $post, $key, $call, $constraint, $default, $expected)
    {
        $superglobals = new Superglobals($get, $post, $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $this->assertEquals($expected, $request->$call($key, $constraint, $default));
    }

    public function providerTestGetValidInput()
    {
        return [
            // $_GET test
            [
                ['foo' => 'VALID'],
                [],
                'foo',
                'getValidInputGet',
                [
                    'Assert\EqualTo' => [
                        'value' => 'VALID',
                    ],
                ],
                null,
                'VALID',
            ],
            // $_POST test
            [
                [],
                ['foo' => 'VALID'],
                'foo',
                'getValidInputPost',
                [
                    'Assert\EqualTo' => [
                        'value' => 'VALID',
                    ],
                ],
                null,
                'VALID',
            ],
            // $_REQUEST test
            [
                ['foo' => 'INVALID'],
                ['foo' => 'VALID'],
                'foo',
                'getValidInputRequest',
                [
                    'Assert\EqualTo' => [
                        'value' => 'VALID',
                    ],
                ],
                null,
                'VALID',
            ],
            // Test without null constraint
            [
                ['foo' => 'VALID'],
                [],
                'foo',
                'getValidInputGet',
                null,
                null,
                'VALID',
            ],
            // Test default
            [
                [],
                [],
                'foo',
                'getValidInputGet',
                null,
                'default',
                'default',
            ],
            // Some actual tests
            [
                ['bwcFrame' => '1'],
                [],
                'bwcFrame',
                'getValidInputGet',
                [
                    'Assert\Type' => ['type' => 'numeric'],
                    'Assert\Range' => ['min' => 0, 'max' => 1],
                ],
                null,
                '1',
            ],
            [
                ['bwcFrame' => '0'],
                [],
                'bwcFrame',
                'getValidInputGet',
                [
                    'Assert\Type' => ['type' => 'numeric'],
                    'Assert\Range' => ['min' => 0, 'max' => 1],
                ],
                null,
                '0',
            ],
            /* Test relies on global state - skipping for now
            array(
                array('module' => 'Accounts'),
                array(),
                'module',
                'getValidInputRequest',
                'Assert\Mvc\ModuleName',
                null,
                'Accounts',
            ),*/
            [
                ['current_page_by_query' => 'a:1:{s:3:"foo";s:3:"bar";}'],
                [],
                'current_page_by_query',
                'getValidInputRequest',
                'Assert\PhpSerialized',
                null,
                ['foo' => 'bar'],
            ],
            [
                ['current_page_by_query' => 'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30='],
                [],
                'current_page_by_query',
                'getValidInputRequest',
                ['Assert\PhpSerialized' => ['base64Encoded' => true]],
                null,
                ['foo' => 'bar'],
            ],
            [
                ['lvso' => 'DESC'],
                [],
                'lvso',
                'getValidInputRequest',
                'Assert\Sql\OrderDirection',
                null,
                'DESC',
            ],
            [
                ['lvso' => 'ASC'],
                [],
                'lvso',
                'getValidInputRequest',
                'Assert\Sql\OrderDirection',
                null,
                'ASC',
            ],
            [
                ['record' => '40a30045-2ab7-9c96-766d-563a3bb0d7ef'],
                [],
                'record',
                'getValidInputRequest',
                'Assert\Guid',
                null,
                '40a30045-2ab7-9c96-766d-563a3bb0d7ef',
            ],
            [
                ['column' => 'foobar'],
                [],
                'column',
                'getValidInputRequest',
                'Assert\ComponentName',
                null,
                'foobar',
            ],
            [
                ['ids' => '12345,67890'],
                [],
                'ids',
                'getValidInputRequest',
                [
                    'Assert\Delimited',
                ],
                null,
                [
                    '12345',
                    '67890',
                ],
            ],
            [
                ['records' => '40a30045-2ab7,9c96-766d-563a3bb0d7ef'],
                [],
                'records',
                'getValidInputRequest',
                [
                    'Assert\Delimited' => [
                        'Assert\NotBlank',
                        'Assert\Guid',
                    ],
                ],
                null,
                [
                    '40a30045-2ab7',
                    '9c96-766d-563a3bb0d7ef',
                ],
            ],
        ];
    }

    /**
     * @covers ::getValidInput
     * @dataProvider providerTestGetInvalidInput
     */
    public function testGetInvalidInput($data, $constraint)
    {
        $superglobals = new Superglobals(['data' => $data], [], $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);

        $this->expectException(ViolationException::class);
        $request->getValidInput(Superglobals::GET, 'data', $constraint);
    }

    public function providerTestGetInvalidInput()
    {
        return [
            [
                'xxx' . chr(0),
                null,
            ],
            [
                ['xxx' . chr(0)],
                null,
            ],
        ];
    }

    /**
     * @covers ::getValidInput
     */
    public function testGetValidInputViolationException()
    {
        $superglobals = new Superglobals(['foo' => 'bar'], [], $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);

        $this->expectException(ViolationException::class);
        $request->getValidInput(Superglobals::GET, 'foo', 'Assert\FailingConstraint');
    }

    /**
     * @covers ::getValidInput
     */
    public function testGetValidInputSuperglobalException()
    {
        $this->expectException(SuperglobalException::class);
        $superglobals = new Superglobals([], [], $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $request->getValidInput('foo', 'bar');
    }

    /**
     * @covers ::getValidInput
     */
    public function testSoftFailNoException()
    {
        $superglobals = new Superglobals(['foo' => 'VALID'], [], $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $request->setSoftFail(true);
        $request->getValidInput(Superglobals::GET, 'foo', [
            'Assert\FailingConstraint',
            'Assert\EqualTo' => [
                'value' => 'VALID',
            ],
            'Assert\FailingConstraint',
        ]);
        $request->setSoftFail(false);

        $this->assertEquals(2, count($request->getViolations()));
    }
}

class FailingConstraint extends Constraint
{
    public $message = 'Failed';

    public function getTargets()
    {
        return [self::PROPERTY_CONSTRAINT, self::CLASS_CONSTRAINT];
    }
}

class FailingConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $this->context->addViolation($constraint->message, []);
    }
}
