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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints as Assert;
use Sugarcrm\Sugarcrm\Security\Validator\Exception\ConstraintBuilderException;
use Symfony\Component\Validator\Constraints as AssertBasic;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder
 */
class ConstraintBuilderTest extends TestCase
{
    /**
     * @var ConstraintBuilder
     */
    protected $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->builder = new ConstraintBuilder();
    }

    /**
     * @covers ::build
     * @covers ::buildConstraint
     * @covers ::parseOptions
     * @covers ::isAssert
     * @covers ::getAssertClass
     * @dataProvider providerTestValidBuild
     */
    public function testValidBuild($constraints, $expected)
    {
        $this->assertEquals($expected, $this->builder->build($constraints));
    }

    public function providerTestValidBuild()
    {
        return [

            // empty/null constraints
            [
                '',
                [],
            ],
            [
                null,
                [],
            ],

            // basic constraint as string
            [
                'Assert\NotBlank',
                [
                    new AssertBasic\NotBlank(),
                ],
            ],

            // single constraint with options
            [
                [
                    'Assert\Range' => [
                        'min' => 5,
                        'max' => 6,
                    ],
                ],
                [
                    new AssertBasic\Range([
                        'min' => 5,
                        'max' => 6,
                    ]),
                ],
            ],

            // sugar constraint as string
            [
                'Assert\Mvc\ModuleName',
                [
                    new Assert\Mvc\ModuleName(),
                ],
            ],

            // multiple constraints with/without options
            [
                [
                    'Assert\NotBlank',
                    'Assert\Type' => [
                        'type' => 'string',
                    ],
                ],
                [
                    new AssertBasic\NotBlank(),
                    new AssertBasic\Type(['type' => 'string']),
                ],
            ],

            // full mix including collection
            [
                [
                    'Assert\Collection' => [
                        'fields' => [
                            'email' => [
                                'Assert\NotBlank',
                                'Assert\Email' => [
                                    'message' => 'foo',
                                ],
                            ],
                            'username' => 'Assert\Mvc\ModuleName',
                            'personal' => [
                                'Assert\Required' => [
                                    'Assert\NotBlank',
                                    'Assert\Email' => [
                                        'message' => 'bar',
                                    ],
                                ],
                            ],
                        ],
                        'allowMissingFields' => true,
                    ],
                ],
                [
                    new AssertBasic\Collection([
                        'fields' => [
                            'email' => [
                                new AssertBasic\NotBlank(),
                                new AssertBasic\Email(['message' => 'foo']),
                            ],
                            'username' => [
                                new Assert\Mvc\ModuleName(),
                            ],
                            'personal' => [
                                new AssertBasic\Required([
                                    new AssertBasic\NotBlank(),
                                    new AssertBasic\Email(['message' => 'bar']),
                                ]),
                            ],
                        ],
                        'allowMissingFields' => true,
                    ]),
                ],
            ],
        ];
    }

    /**
     * @covers ::build
     * @covers ::buildConstraint
     * @covers ::getAssertClass
     * @dataProvider providerTestInvalidBuild
     */
    public function testInvalidBuild($constraints, $msg)
    {
        $this->expectException(ConstraintBuilderException::class);
        $this->expectExceptionMessage($msg);

        $this->builder->build($constraints);
    }

    public function providerTestInvalidBuild()
    {
        return [
            [
                'Assert\Foobar',
                'Cannot find class for assert "Assert\Foobar"',
            ],
            [
                ['Assert\Foobar', 'foobar'],
                'Cannot find class for assert "Assert\Foobar"',
            ],
            [
                'Assertxxx\Foobar',
                'Invalid constraint "Assertxxx\Foobar", should start with "Assert\"',
            ],
            [
                ['Assertxxx\Foobar' => []],
                'Invalid constraint "Assertxxx\Foobar", should start with "Assert\"',
            ],
            [
                ['Assert\Range' => true],
                'Assert options expected to be an array, boolean given',
            ],
        ];
    }
}
