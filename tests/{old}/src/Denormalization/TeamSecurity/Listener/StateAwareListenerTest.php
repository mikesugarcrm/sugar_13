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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\Listener;

use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use SplSubject;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Builder;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\StateAwareListener;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\StateAwareListener
 */
class StateAwareListenerTest extends TestCase
{
    /**
     * @test
     */
    public function userDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createStateAwareListener('userDeleted', $id);
        $listener->userDeleted($id);
    }

    /**
     * @test
     */
    public function teamDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createStateAwareListener('teamDeleted', $id);
        $listener->teamDeleted($id);
    }

    /**
     * @test
     */
    public function teamSetCreated()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();
        $id3 = Uuid::uuid1();

        $listener = $this->createStateAwareListener('teamSetCreated', $id1, [$id2, $id3]);
        $listener->teamSetCreated($id1, [$id2, $id3]);
    }

    /**
     * @test
     */
    public function teamSetDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createStateAwareListener('teamSetDeleted', $id);
        $listener->teamSetDeleted($id);
    }

    /**
     * @test
     */
    public function userAddedToTeam()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $listener = $this->createStateAwareListener('userAddedToTeam', $id1, $id2);
        $listener->userAddedToTeam($id1, $id2);
    }

    /**
     * @test
     */
    public function userRemovedFromTeam()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $listener = $this->createStateAwareListener('userRemovedFromTeam', $id1, $id2);
        $listener->userRemovedFromTeam($id1, $id2);
    }

    /**
     * @test
     */
    public function updateAndToString()
    {
        $listener1 = $this->createNamedListener('Test1()');
        $listener2 = $this->createNamedListener('Test2()');

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->exactly(2))
            ->method('createListener')
            ->will(
                $this->onConsecutiveCalls($listener1, $listener2)
            );

        $listener = new StateAwareListener($builder, $this->createMock(LoggerInterface::class));

        $this->assertSame('Test1()', (string)$listener);

        $subject = $this->createMock(SplSubject::class);
        $listener->update($subject);

        $this->assertSame('Test2()', (string)$listener);
    }

    private function createStateAwareListener($method, ...$args)
    {
        $listener = $this->createMock(Listener::class);
        $listener->expects($this->once())
            ->method($method)
            ->with(...$args);

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())
            ->method('createListener')
            ->willReturn($listener);

        return new StateAwareListener($builder, $this->createMock(LoggerInterface::class));
    }

    private function createNamedListener($name)
    {
        $listener = $this->createMock(Listener::class);
        $listener->method('__toString')
            ->willReturn($name);

        return $listener;
    }
}
