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

namespace Sugarcrm\SugarcrmTestsUnit\Console\CommandRegistry\Adapter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\SymfonyCommandA;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\CommandRegistry\Adapter\AbstractCommandAdapter
 */
class AbstractCommandAdapterTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getCommand
     */
    public function testGetCommand()
    {
        $command = new SymfonyCommandA('test');
        $adapter = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Console\CommandRegistry\Adapter\AbstractCommandAdapter::class)
            ->setConstructorArgs([$command])
            ->getMockForAbstractClass();

        $this->assertSame($command, $adapter->getCommand());
    }

    /**
     * @covers ::__call
     */
    public function testOverload()
    {
        $command = new SymfonyCommandA('foobar');
        $adapter = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Console\CommandRegistry\Adapter\AbstractCommandAdapter::class)
            ->setConstructorArgs([$command])
            ->getMockForAbstractClass();

        $this->assertEquals('foobar', $adapter->getName());
    }
}
