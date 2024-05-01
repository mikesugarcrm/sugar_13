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

namespace Sugarcrm\SugarcrmTests\Util\Runner;

use PHPUnit\Framework\MockObject\MockObject;
use Sugarcrm\Sugarcrm\Util\Runner\Dot as RunnerDot;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Testing is all found beans will be forwarded to rebuild, and check is outputted same count of dots.
 *
 * @covers \Sugarcrm\Sugarcrm\Util\Runner\Dot
 */
class DotTest extends TestCase
{
    /** @var \SugarBean[] */
    protected $listOfBeans = [];

    /** @var RunnerDot|\MockObject */
    protected $dotRunner = null;

    /** @var \Sugarcrm\Sugarcrm\Util\Runner\RunnableInterface|MockObject */
    protected $runnable = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->runnable = $this->createPartialMock(
            \Sugarcrm\Sugarcrm\Util\Runner\RunnableInterface::class,
            ['getBeans', 'execute']
        );
        $this->dotRunner = new RunnerDot($this->runnable);

        for ($i = 0; $i < 3; $i++) {
            $bean = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->getMock();

            $bean->id = Uuid::uuid1();
            $this->listOfBeans[] = $bean;
        }
    }

    /**
     * Testing is all found beans will be forwarded to rebuild, and check is outputted same count of dots.
     *
     * @covers \Sugarcrm\Sugarcrm\Util\Runner\Dot::run
     */
    public function testRun()
    {
        $this->runnable->method('getBeans')
            ->willReturn($this->listOfBeans);

        $this->runnable->expects($this->exactly(count($this->listOfBeans)))
            ->method('execute')
            ->withConsecutive(
                [$this->listOfBeans[0]],
                [$this->listOfBeans[1]],
                [$this->listOfBeans[2]]
            );

        $this->expectOutputString(str_repeat('. ', count($this->listOfBeans)));

        $this->dotRunner->run();
    }
}
