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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\PermanentLockedUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\TemporaryLockedUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout
 */
class LockoutTest extends TestCase
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
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var \TimeDate|MockObject
     */
    protected $timeDate;

    /**
     * @var \SugarDateTime|MockObject
     */
    protected $sugarDateTime;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->lockout = $this->getMockBuilder(Lockout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigValue', 'getAppString', 'getTimeDate'])
            ->getMock();
        $this->timeDate = $this->createMock(\TimeDate::class);
        $this->sugarDateTime = $this->createMock(\SugarDateTime::class);

        $this->lockout->expects($this->any())
            ->method('getTimeDate')
            ->willReturn($this->timeDate);

        $this->user = $this->createMock(User::class);
        $this->sugarUser = $this->createMock(\User::class);
        $this->user->expects($this->any())
            ->method('getSugarUser')
            ->willReturn($this->sugarUser);
    }

    /**
     * @covers ::isEnabled
     */
    public function testIsEnabled()
    {
        $this->lockout->expects($this->once())
            ->method('getConfigValue')
            ->with($this->equalTo('lockoutexpiration'), $this->equalTo(Lockout::LOCKOUT_DISABLED))
            ->willReturn(Lockout::LOCK_TYPE_TIME);
        $this->assertTrue($this->lockout->isEnabled());
    }

    /**
     * @covers ::throwLockoutException
     * @dataProvider providerThrowLockoutExceptionTimeType
     * @param $logoutTime
     * @param $config
     * @param $expireTime
     * @param $nowTime
     * @param $exceptionMessage
     * @param $appStringKey
     * @param $appStringValue
     */
    public function testThrowLockoutExceptionTimeType(
        $logoutTime,
        $config,
        $expireTime,
        $nowTime,
        $exceptionMessage,
        $appStringKey,
        $appStringValue
    ) {

        $this->lockout->expects($this->exactly(3))
            ->method('getConfigValue')
            ->withConsecutive(
                [$this->equalTo('lockoutexpiration'), $this->equalTo(Lockout::LOCKOUT_DISABLED)],
                [$this->equalTo('lockoutexpirationtime')],
                [$this->equalTo('lockoutexpirationtype')]
            )
            ->willReturnOnConsecutiveCalls(
                Lockout::LOCK_TYPE_TIME,
                $config['lockoutexpirationtime'],
                $config['lockoutexpirationtype']
            );

        $this->sugarUser->expects($this->once())
            ->method('getPreference')
            ->with($this->equalTo('logout_time'))
            ->willReturn($logoutTime);

        $this->timeDate->expects($this->once())
            ->method('fromDb')
            ->with($this->equalTo($logoutTime))
            ->willReturn($this->sugarDateTime);

        $this->sugarDateTime->expects($this->once())
            ->method('modify')
            ->with($this->stringContains('minutes'))
            ->willReturnSelf();

        $this->sugarDateTime->expects($this->once())
            ->method('asDb')
            ->willReturn($expireTime);

        $this->lockout->expects($this->exactly(3))
            ->method('getAppString')
            ->withConsecutive(
                [$this->equalTo('LBL_LOGIN_ATTEMPTS_OVERRUN')],
                [$this->equalTo('LBL_LOGIN_LOGIN_TIME_ALLOWED')],
                [$this->equalTo($appStringKey)]
            )
            ->willReturnOnConsecutiveCalls(
                'Too many failed login attempts.',
                'You can try logging in again in ',
                $appStringValue
            );

        $this->timeDate->expects($this->once())
            ->method('nowDb')
            ->willReturn($nowTime);

        $this->expectException(TemporaryLockedUserException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->lockout->throwLockoutException($this->user);
    }

    /**
     * @return array
     * @see testThrowLockoutExceptionTimeType
     */
    public function providerThrowLockoutExceptionTimeType()
    {
        return [
            'loggingIn2days' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1440],
                'expireTime' => '2017-02-15 01:01:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 2days.',
                'appStringKey' => 'LBL_LOGIN_LOGIN_TIME_DAYS',
                'appStringValue' => 'days.',
            ],
            'loggingIn2hours' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 60],
                'expireTime' => '2017-02-13 03:01:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 2h.',
                'appStringKey' => 'LBL_LOGIN_LOGIN_TIME_HOURS',
                'appStringValue' => 'h.',
            ],
            'loggingIn2minutes' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:03:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 2min.',
                'appStringKey' => 'LBL_LOGIN_LOGIN_TIME_MINUTES',
                'appStringValue' => 'min.',
            ],
            'loggingIn20seconds' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:03:01',
                'nowTime' => '2017-02-13 01:02:41',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 20sec.',
                'appStringKey' => 'LBL_LOGIN_LOGIN_TIME_SECONDS',
                'appStringValue' => 'sec.',
            ],
        ];
    }

    /**
     * @covers ::throwLockoutException
     */
    public function testThrowLockoutExceptionTimeTypeNoLogOutTime()
    {
        $this->lockout->expects($this->once())
            ->method('getConfigValue')
            ->with($this->equalTo('lockoutexpiration'), $this->equalTo(Lockout::LOCKOUT_DISABLED))
            ->willReturn(Lockout::LOCK_TYPE_TIME);

        $this->sugarUser->expects($this->once())
            ->method('getPreference')
            ->with($this->equalTo('logout_time'))
            ->willReturn(null);

        $this->lockout->expects($this->once())
            ->method('getAppString')
            ->with($this->equalTo('LBL_LOGIN_ATTEMPTS_OVERRUN'))
            ->willReturn('Too many failed login attempts.');

        $this->expectException(TemporaryLockedUserException::class);
        $this->expectExceptionMessage('Too many failed login attempts.');

        $this->lockout->throwLockoutException($this->user);
    }

    /**
     * @covers ::throwLockoutException
     */
    public function testThrowLockoutExceptionPermanentType()
    {
        $this->lockout->expects($this->once())
            ->method('getConfigValue')
            ->with($this->equalTo('lockoutexpiration'), $this->equalTo(Lockout::LOCKOUT_DISABLED))
            ->willReturn(Lockout::LOCK_TYPE_PERMANENT);

        $this->lockout->expects($this->exactly(2))
            ->method('getAppString')
            ->withConsecutive(
                [$this->equalTo('LBL_LOGIN_ATTEMPTS_OVERRUN')],
                [$this->equalTo('LBL_LOGIN_ADMIN_CALL')]
            )
            ->willReturnOnConsecutiveCalls(
                'Too many failed login attempts.',
                'Test'
            );

        $this->expectException(PermanentLockedUserException::class);
        $this->expectExceptionMessage('Too many failed login attempts.');

        $this->lockout->throwLockoutException($this->user);
    }

    /**
     * @covers ::isUserLocked
     */
    public function testIsUserLockedPermanent()
    {
        $this->lockout->expects($this->once())
            ->method('getConfigValue')
            ->with($this->equalTo('lockoutexpiration'), $this->equalTo(Lockout::LOCKOUT_DISABLED))
            ->willReturn(Lockout::LOCK_TYPE_PERMANENT);
        $this->user->expects($this->once())
            ->method('getLockout')
            ->willReturn(true);

        $this->assertTrue($this->lockout->isUserLocked($this->user));
    }

    /**
     * @covers ::isUserLocked
     */
    public function testIsUserLockedTime()
    {
        $this->lockout->expects($this->exactly(3))
            ->method('getConfigValue')
            ->withConsecutive(
                [$this->equalTo('lockoutexpiration'), $this->equalTo(Lockout::LOCKOUT_DISABLED)],
                [$this->equalTo('lockoutexpirationtime')],
                [$this->equalTo('lockoutexpirationtype')]
            )
            ->willReturnOnConsecutiveCalls(
                Lockout::LOCK_TYPE_TIME,
                2,
                1440
            );

        $this->sugarUser->expects($this->once())
            ->method('getPreference')
            ->with($this->equalTo('logout_time'))
            ->willReturn('2017-02-13 01:01:01');

        $this->timeDate->expects($this->once())
            ->method('fromDb')
            ->with($this->equalTo('2017-02-13 01:01:01'))
            ->willReturn($this->sugarDateTime);

        $this->sugarDateTime->expects($this->once())
            ->method('modify')
            ->with($this->stringContains('minutes'))
            ->willReturnSelf();

        $this->sugarDateTime->expects($this->once())
            ->method('asDb')
            ->willReturn('2017-02-15 01:01:01');

        $this->timeDate->expects($this->once())
            ->method('nowDb')
            ->willReturn('2017-02-13 01:01:01');

        $this->assertTrue($this->lockout->isUserLocked($this->user));
    }

    /**
     * @covers ::getFailedLoginsCount
     */
    public function testGetFailedLoginsCount()
    {
        $this->lockout->expects($this->once())
            ->method('getConfigValue')
            ->with($this->equalTo('lockoutexpirationlogin'), $this->identicalTo(0))
            ->willReturn($count = 3);

        $this->assertEquals($count, $this->lockout->getFailedLoginsCount());
    }
}
