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
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Logger;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Logger
 */
class LoggerTest extends TestCase
{
    /**
     * @test
     */
    public function userDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createListener($id);
        $listener->userDeleted($id);
    }

    /**
     * @test
     */
    public function teamDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createListener($id);
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

        $listener = $this->createListener($id1, $id2, $id3);
        $listener->teamSetCreated($id1, [$id2, $id3]);
    }

    /**
     * @test
     */
    public function teamSetDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createListener($id);
        $listener->teamSetDeleted($id);
    }

    /**
     * @test
     */
    public function userAddedToTeam()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $listener = $this->createListener($id1, $id2);
        $listener->userAddedToTeam($id1, $id2);
    }

    /**
     * @test
     */
    public function userRemovedFromTeam()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $listener = $this->createListener($id1, $id2);
        $listener->userRemovedFromTeam($id1, $id2);
    }

    private function createListener(...$expectedSubstrings)
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with($this->logicalAnd(...array_map(function ($substring) {
                return $this->stringContains($substring);
            }, $expectedSubstrings)));

        return new Logger($logger);
    }
}
