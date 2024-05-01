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

namespace Sugarcrm\SugarcrmTests\UserUtils;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerBasePayload;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerBasePayload
 */
class InvokerBasePayloadTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerBasePayload|mixed
     */
    public $invokerPayload;

    protected function setUp(): void
    {
        $this->invokerPayload = $this->getMockBuilder(InvokerBasePayload::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUsersFromRoles', 'getUsersFromTeams',])
            ->getMock();
        $this->invokerPayload->expects($this->any())
            ->method('getUsersFromRoles')
            ->will($this->returnValue(['user3', 'user4',]));
        $this->invokerPayload->expects($this->any())
            ->method('getUsersFromTeams')
            ->will($this->returnValue(['user5', 'user6',]));
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::setSourceUser
     */
    public function testSetSourceUser()
    {
        $this->invokerPayload->setSourceUser('1');
        $sourceUser = $this->invokerPayload->getSourceUser();
        $this->assertEquals('1', $sourceUser);
    }

    /**
     * @covers ::setDestinationUsers
     */
    public function testSetDestinationUsers()
    {
        $this->invokerPayload->setDestinationUsers(['user1', 'user12',]);
        $users = $this->invokerPayload->getDestinationUsers();
        $this->assertEquals(['user1', 'user12', 'user3', 'user4', 'user5', 'user6',], $users);
    }

    /**
     * @covers ::getDestinationUsers
     */
    public function testGetDestinationUsers()
    {
        $this->invokerPayload->setDestinationUsers(['user1', 'user2',]);
        $users = $this->invokerPayload->getDestinationUsers();
        $this->assertEquals(['user1', 'user2', 'user3', 'user4', 'user5', 'user6',], $users);
    }
}
