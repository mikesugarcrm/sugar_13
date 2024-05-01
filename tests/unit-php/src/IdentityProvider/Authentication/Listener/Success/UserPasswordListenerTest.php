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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Listener\Success;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\UserPasswordListener;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\ServiceAccount;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\UserPasswordListener
 */
class UserPasswordListenerTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $listener;

    /**
     * @var MockObject
     */
    protected $token;

    /**
     * @var MockObject
     */
    protected $sugarUser;

    /**
     * @var MockObject
     */
    protected $config;

    /**
     * @var MockObject
     */
    protected $timeDate;

    /**
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * @var MockObject
     */
    protected $user;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sugarUser = $this->createMock(\User::class);

        $this->user = $this->createMock(User::class);
        $this->user->expects($this->any())
            ->method('getSugarUser')
            ->willReturn($this->sugarUser);

        $this->token = $this->createMock(UsernamePasswordToken::class);

        $this->event = new AuthenticationEvent($this->token);
        $this->config = $this->createMock(\SugarConfig::class);
        $this->timeDate = $this->createMock(\TimeDate::class);

        $this->listener = $this->getMockBuilder(UserPasswordListener::class)
            ->setMethods(['getTimeDate', 'getSugarConfig', 'setSessionVariable'])
            ->getMock();

        $this->listener->expects($this->any())
            ->method('getTimeDate')
            ->willReturn($this->timeDate);

        $this->listener->expects($this->any())
            ->method('getSugarConfig')
            ->willReturn($this->config);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithServiceAccount(): void
    {
        $serviceUser = new ServiceAccount('test', 'test', []);
        $serviceUser->setSugarUser($this->sugarUser);

        $this->token->expects($this->once())->method('getUser')->willReturn($serviceUser);
        $this->sugarUser->expects($this->never())->method('save');
        $this->listener->expects($this->never())->method('setSessionVariable');

        $this->listener->execute($this->event);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteCheckTimeLastDateExistNotExpired()
    {
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->listener->expects($this->never())
            ->method('setSessionVariable');

        $now = $this->createMock(\SugarDateTime::class);
        $now->ts = 1;

        $lastChange = $this->createMock(\SugarDateTime::class);
        $lastChange->ts = 2;
        $lastChange->expects($this->once())
            ->method('get')
            ->with($this->equalTo('+1 days'))
            ->willReturnSelf();

        $this->user->expects($this->exactly(2))
            ->method('getPasswordType')
            ->willReturn(User::PASSWORD_TYPE_USER);

        $this->config->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expiration'), $this->identicalTo(0)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtype'), $this->equalTo(1)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtime'), $this->equalTo(1)]
            )
            ->willReturnOnConsecutiveCalls(
                User::PASSWORD_EXPIRATION_TYPE_TIME,
                1,
                1
            );

        $this->timeDate->expects($this->once())
            ->method('getNow')
            ->willReturn($now);

        $this->user->expects($this->once())
            ->method('getPasswordLastChangeDate')
            ->willReturn('2017-11-20 00:00:00 /*user password change date*/');

        $this->timeDate->expects($this->once())
            ->method('fromDb')
            ->with($this->equalTo('2017-11-20 00:00:00 /*user password change date*/'))
            ->willReturn($lastChange);

        $this->listener->execute($this->event);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteCheckTimeLastDateNotExistExpired()
    {
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $now = $this->createMock(\SugarDateTime::class);
        $now->ts = 1;

        $lastChange = $this->createMock(\SugarDateTime::class);
        $lastChange->ts = 0;
        $lastChange->expects($this->once())
            ->method('get')
            ->with($this->equalTo('+1 days'))
            ->willReturnSelf();

        $this->user->expects($this->exactly(2))
            ->method('getPasswordType')
            ->willReturn(User::PASSWORD_TYPE_USER);

        $this->config->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expiration'), $this->identicalTo(0)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtype'), $this->equalTo(1)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtime'), $this->equalTo(1)]
            )
            ->willReturnOnConsecutiveCalls(
                User::PASSWORD_EXPIRATION_TYPE_TIME,
                1,
                1
            );

        $this->timeDate->expects($this->once())
            ->method('nowDb')
            ->willReturn('2017-11-20 01:00:00 /*current time*/');
        $this->timeDate->expects($this->once())
            ->method('getNow')
            ->willReturn($now);

        $this->user->expects($this->once())
            ->method('getPasswordLastChangeDate')
            ->willReturn('');

        $this->user->expects($this->once())
            ->method('setPasswordLastChangeDate')
            ->with($this->equalTo('2017-11-20 01:00:00 /*current time*/'));

        $this->user->expects($this->once())
            ->method('allowUpdateDateModified')
            ->with($this->isFalse());

        $this->sugarUser->expects($this->once())
            ->method('save');

        $this->timeDate->expects($this->once())
            ->method('fromDb')
            ->with($this->equalTo('2017-11-20 01:00:00 /*current time*/'))
            ->willReturn($lastChange);

        $this->listener->expects($this->exactly(2))
            ->method('setSessionVariable')
            ->withConsecutive(
                [$this->equalTo('expiration_label'), $this->equalTo('LBL_PASSWORD_EXPIRATION_TIME')],
                [$this->equalTo('hasExpiredPassword'), $this->equalTo('1')]
            );

        $this->listener->execute($this->event);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteCheckAttempts()
    {
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->user->expects($this->exactly(2))
            ->method('getPasswordType')
            ->willReturn(User::PASSWORD_TYPE_USER);

        $this->config->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expiration'), $this->identicalTo(0)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationlogin'), $this->isNull()]
            )
            ->willReturnOnConsecutiveCalls(
                User::PASSWORD_EXPIRATION_TYPE_LOGIN,
                0
            );

        $this->sugarUser->expects($this->once())
            ->method('getPreference')
            ->with($this->equalTo('loginexpiration'))
            ->willReturn(1);

        $this->sugarUser->expects($this->once())
            ->method('setPreference')
            ->with($this->equalTo('loginexpiration'), $this->equalTo(2));

        $this->user->expects($this->once())
            ->method('allowUpdateDateModified')
            ->with($this->isFalse());

        $this->sugarUser->expects($this->once())
            ->method('save');

        $this->listener->expects($this->exactly(2))
            ->method('setSessionVariable')
            ->withConsecutive(
                [$this->equalTo('expiration_label'), $this->equalTo('LBL_PASSWORD_EXPIRATION_LOGIN')],
                [$this->equalTo('hasExpiredPassword'), $this->equalTo('1')]
            );

        $this->listener->execute($this->event);
    }
}
