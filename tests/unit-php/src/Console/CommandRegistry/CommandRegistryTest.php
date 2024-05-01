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

namespace Sugarcrm\SugarcrmTestsUnit\Console\CommandRegistry;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Console\CommandRegistry\CommandRegistry;
use Sugarcrm\Sugarcrm\Console\Exception\CommandRegistryException;
use Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\InstanceCommandA;
use Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\InstanceStandaloneCommandA;
use Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\StandaloneCommandA;
use Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\SymfonyCommandA;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\CommandRegistry\CommandRegistry
 */
class CommandRegistryTest extends TestCase
{
    /**
     * @var CommandRegistry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->registry = new CommandRegistry();
    }

    /**
     * @covers ::addCommand
     * @covers ::addCommands
     * @covers ::getCommands
     * @dataProvider providerTestAddCommands
     */
    public function testAddCommands($mode, array $cmds, array $expected)
    {
        $this->registry->addCommands($cmds);
        $this->assertEquals($expected, $this->registry->getCommands($mode));
    }

    public function providerTestAddCommands()
    {
        return [
            [
                CommandRegistry::MODE_INSTANCE,
                [new InstanceCommandA('1'), new InstanceCommandA('2')],
                [new InstanceCommandA('1'), new InstanceCommandA('2')],
            ],
            [
                CommandRegistry::MODE_STANDALONE,
                [new StandaloneCommandA('1'), new StandaloneCommandA('2')],
                [new StandaloneCommandA('1'), new StandaloneCommandA('2')],
            ],
            [
                CommandRegistry::MODE_INSTANCE,
                [new InstanceCommandA('1'), new StandaloneCommandA('2')],
                [new InstanceCommandA('1')],
            ],
            [
                CommandRegistry::MODE_STANDALONE,
                [new InstanceCommandA('1'), new StandaloneCommandA('2')],
                [new StandaloneCommandA('2')],
            ],
            [
                CommandRegistry::MODE_INSTANCE,
                [
                    new InstanceCommandA('1'),
                    new StandaloneCommandA('2'),
                    new InstanceStandaloneCommandA('3'),
                ],
                [
                    new InstanceCommandA('1'),
                    new InstanceStandaloneCommandA('3'),
                ],
            ],
            [
                CommandRegistry::MODE_STANDALONE,
                [
                    new InstanceCommandA('1'),
                    new StandaloneCommandA('2'),
                    new InstanceStandaloneCommandA('3'),
                ],
                [
                    new StandaloneCommandA('2'),
                    new InstanceStandaloneCommandA('3'),
                ],
            ],
        ];
    }

    /**
     * @covers ::addSymfonyCommand
     * @covers ::getCommands
     */
    public function testAddSymfonyCommand()
    {
        $commandA = new SymfonyCommandA('instance');
        $commandB = new SymfonyCommandA('standalone');
        $commandC = new SymfonyCommandA('both');

        $this->registry
            ->addSymfonyCommand($commandA, CommandRegistry::MODE_INSTANCE)
            ->addSymfonyCommand($commandB, CommandRegistry::MODE_STANDALONE)
            ->addSymfonyCommand($commandC, [
                CommandRegistry::MODE_INSTANCE, CommandRegistry::MODE_STANDALONE,
            ]);

        $expected = [$commandA, $commandC];
        $this->assertSame($expected, $this->registry->getCommands(CommandRegistry::MODE_INSTANCE));

        $expected = [$commandB, $commandC];
        $this->assertSame($expected, $this->registry->getCommands(CommandRegistry::MODE_STANDALONE));
    }

    public function providerValidModes()
    {
        return [
            [CommandRegistry::MODE_INSTANCE],
            [CommandRegistry::MODE_STANDALONE],
        ];
    }

    /**
     * @covers ::validateMode
     * @dataProvider providerValidModes
     */
    public function testValidateMode($mode)
    {
        $this->assertSame($mode, $this->registry->validateMode($mode));
    }

    /**
     * @covers ::createAdapter
     * @dataProvider providerValidModes
     */
    public function testCreateAdapter($mode)
    {
        $command = new SymfonyCommandA('test');
        $adapter = TestReflection::callProtectedMethod(
            $this->registry,
            'createAdapter',
            [$command, $mode]
        );

        $this->assertInstanceOf(
            \Sugarcrm\Sugarcrm\Console\CommandRegistry\CommandInterface::class,
            $adapter
        );

        $this->assertInstanceOf(
            \Sugarcrm\Sugarcrm\Console\CommandRegistry\Adapter\CommandAdapterInterface::class,
            $adapter
        );
    }

    /**
     * @covers ::validateMode
     */
    public function testInValidModes()
    {
        $this->expectException(CommandRegistryException::class);
        $this->expectExceptionMessage("Invalid mode 'foobar' requested");

        $this->registry->validateMode('foobar');
    }

    /**
     * @covers ::createAdapter
     */
    public function testInvalidAdapter()
    {
        $this->expectException(CommandRegistryException::class);
        $this->expectExceptionMessage("No adapter available for 'foobar' mode");

        TestReflection::callProtectedMethod(
            $this->registry,
            'createAdapter',
            [new SymfonyCommandA('test'), 'foobar']
        );
    }
}
