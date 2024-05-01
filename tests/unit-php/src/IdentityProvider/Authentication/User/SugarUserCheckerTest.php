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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\User;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\TemporaryLockedUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarUserChecker;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarUserChecker
 */
class SugarUserCheckerTest extends TestCase
{
    /**
     * @var Lockout|MockObject
     */
    protected $lockout;

    /**
     * @var User|MockObject
     */
    protected $user;

    /**
     * @var SugarUserChecker
     */
    protected $checker;

    /**
     * @covers ::checkPreAuth
     */
    public function testLockedUser()
    {
        $this->lockout
            ->method('isEnabled')
            ->willReturn(true);
        $this->lockout
            ->method('isUserLocked')
            ->willReturn(true);

        $this->lockout->expects($this->once())
            ->method('throwLockoutException')
            ->with($this->isInstanceOf(User::class))
            ->willThrowException(new TemporaryLockedUserException('test'));

        $this->expectException(TemporaryLockedUserException::class);
        $this->checker->checkPreAuth($this->user);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->lockout = $this->getMockBuilder(Lockout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checker = new SugarUserChecker($this->lockout);
    }
}
