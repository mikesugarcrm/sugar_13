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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Subscriber;

use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Subscriber\SugarOnAuthLockoutSubscriber;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Subscriber\SugarOnAuthLockoutSubscriber
 */
class SugarOnAuthLockoutSubscriberTest extends TestCase
{
    /**
     * @var AuthenticationEvent|MockObject
     */
    protected $event = null;

    /**
     * @var UsernamePasswordToken|MockObject
     */
    protected $token = null;

    /**
     * @var Lockout|MockObject
     */
    protected $lockout = null;

    /**
     * @var User|MockObject
     */
    protected $user = null;

    /**
     * @var UserProviderInterface|MockObject
     */
    protected $userProvider = null;

    /**
     * @var \TimeDate|MockObject
     */
    protected $timeDate;

    /**
     * @var SugarOnAuthLockoutSubscriber
     */
    protected $subscriber = null;

    /**
     * @return array
     * @see UserLockoutListenerTest::testHandlingFailure
     */
    public function handlingFailureProvider()
    {
        return [
            'incrementLoginFailed' => [
                'username' => 'userName1',
                'method' => 'incrementLoginFailed',
                'userLoginFailed' => 1,
                'lockoutExpirationLogin' => 3,
                'count' => $this->never(),
            ],
            'lockUser' => [
                'username' => 'userName2',
                'method' => 'lockout',
                'userLoginFailed' => 3,
                'lockoutExpirationLogin' => 3,
                'count' => $this->exactly(2),
            ],
        ];
    }

    /**
     * Data provider for testCleaningFailureLoggins
     * @return array
     * @see UserLockoutListenerTest::testCleaningFailureLoggins
     */
    public function lockoutPreference()
    {
        return [
            'lockoutAndLoginFailed' => ['lockout' => 1, 'loginfailed' => 3],
            'lockout' => ['lockout' => 1, 'loginfailed' => 0],
            'loginFailed' => ['lockout' => null, 'loginfailed' => 3],
        ];
    }

    /**
     * @covers ::onFailure
     * @dataProvider handlingFailureProvider
     * @param string $username
     * @param string $method
     * @param integer $userLoginFailed
     * @param integer $lockoutExpirationLogin
     * @param InvokedCount $count
     */
    public function testHandlingFailure($username, $method, $userLoginFailed, $lockoutExpirationLogin, $count)
    {
        $this->lockout
            ->method('isEnabled')
            ->willReturn(true);

        $this->lockout->expects($count)
            ->method('getTimeDate')
            ->willReturn($this->timeDate);

        $this->timeDate->expects($count)
            ->method('nowDb')
            ->willReturn('2017-02-13 01:01:01');

        $this->token
            ->method('getUsername')
            ->willReturn($username);

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($username)
            ->willReturn($this->user);

        $this->user
            ->method('getLoginFailed')
            ->willReturn($userLoginFailed);

        $this->lockout
            ->method('getFailedLoginsCount')
            ->willReturn($lockoutExpirationLogin);

        $this->user
            ->expects($this->once())
            ->method($method);

        $this->subscriber->onFailure($this->event);
    }

    /**
     * @covers ::onFailure
     */
    public function testNoUserOnFailure()
    {
        $username = 'userName2';
        $this->lockout
            ->method('isEnabled')
            ->willReturn(true);

        $this->token
            ->method('getUsername')
            ->willReturn($username);

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($username)
            ->willReturn(null);

        $this->lockout->expects($this->never())->method('getFailedLoginsCount');
        $this->user->expects($this->never())->method('getLoginFailed');
        $this->user->expects($this->never())->method('lockout');
        $this->user->expects($this->never())->method('incrementLoginFailed');

        $this->subscriber->onFailure($this->event);
    }

    /**
     * @covers ::onFailure
     */
    public function testDisabledFailure()
    {
        $this->lockout
            ->method('isEnabled')
            ->willReturn(false);

        $this->token->expects($this->once())->method('getUsername')->willReturn('username');
        $this->userProvider->expects($this->once())->method('loadUserByUsername')->willReturn(null);
        $this->lockout->expects($this->never())->method('getFailedLoginsCount');
        $this->user->expects($this->never())->method('getLoginFailed');
        $this->user->expects($this->never())->method('lockout');
        $this->user->expects($this->never())->method('incrementLoginFailed');

        $this->subscriber->onFailure($this->event);
    }

    /**
     * @covers       ::onSuccess
     * @dataProvider lockoutPreference
     * @param $lockout
     * @param $loginFailed
     */
    public function testCleaningFailureLoggins($lockout, $loginFailed)
    {
        $this->lockout->method('isEnabled')->willReturn(true);
        $this->token->method('getUser')->willReturn($this->user);

        $this->user
            ->method('getLoginFailed')
            ->willReturn($loginFailed);
        $this->user
            ->method('getLockout')
            ->willReturn($lockout);

        $this->user
            ->expects($this->once())
            ->method('clearLockout');

        $this->subscriber->onSuccess($this->event);
    }

    /**
     * @covers       ::onSuccess
     * @dataProvider lockoutPreference
     */
    public function testCleanFailureLoggins()
    {
        $this->lockout->method('isEnabled')->willReturn(true);
        $this->token->method('getUser')->willReturn($this->user);

        $this->user
            ->method('getLoginFailed')
            ->willReturn(0);
        $this->user
            ->method('getLockout')
            ->willReturn(null);

        $this->user
            ->expects($this->never())
            ->method('clearLockout');

        $this->subscriber->onSuccess($this->event);
    }

    /**
     * @covers       ::onSuccess
     */
    public function testDisabledSuccess()
    {
        $this->lockout->method('isEnabled')->willReturn(false);

        $this->token->expects($this->never())->method('getUser');

        $this->user->expects($this->never())->method('getLoginFailed');
        $this->user->expects($this->never())->method('getLockout');
        $this->user->expects($this->never())->method('clearLockout');

        $this->subscriber->onSuccess($this->event);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->timeDate = $this->createMock(\TimeDate::class);

        $this->userProvider = $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = $this->getMockBuilder(AuthenticationEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->token = $this->getMockBuilder(UsernamePasswordToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockout = $this->getMockBuilder(Lockout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->event
            ->method('getAuthenticationToken')
            ->willReturn($this->token);

        $this->subscriber = new SugarOnAuthLockoutSubscriber($this->lockout, $this->userProvider);

        TestReflection::setProtectedValue($this->subscriber, 'logger', $this->createMock(\LoggerManager::class));
    }
}
