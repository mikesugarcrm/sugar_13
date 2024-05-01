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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User
 */
class UserTest extends TestCase
{
    /**
     * @var \TimeDate|MockObject
     */
    protected $timeDate = null;

    /**
     * @var User|MockObject
     */
    protected $user = null;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser = null;

    /**
     * @var \EmailAddress|MockObject
     */
    protected $emailAddress;

    /**
     * @covers ::setSugarUser
     * @covers ::getSugarUser
     */
    public function testSetGetSugarUser()
    {
        $this->assertInstanceOf(\User::class, $this->user->getSugarUser());
    }

    /**
     * @covers ::setPasswordExpired
     * @covers ::isCredentialsNonExpired
     */
    public function testPasswordExpired()
    {
        $this->user->setPasswordExpired(false);
        $this->assertTrue($this->user->isCredentialsNonExpired());
    }

    /**
     * @covers ::getPasswordType
     */
    public function testGetPasswordTypeSystem()
    {
        $this->sugarUser->system_generated_password = 1;
        $this->assertEquals(User::PASSWORD_TYPE_SYSTEM, $this->user->getPasswordType());
    }

    /**
     * @covers ::getPasswordType
     */
    public function testGetPasswordTypeUser()
    {
        $this->sugarUser->system_generated_password = null;
        $this->assertEquals(User::PASSWORD_TYPE_USER, $this->user->getPasswordType());
    }

    /**
     * @covers ::setPasswordLastChangeDate
     * @covers ::getPasswordLastChangeDate
     */
    public function testPasswordLastChangeDate()
    {
        $this->user->setPasswordLastChangeDate('test');
        $this->assertEquals('test', $this->user->getPasswordLastChangeDate());
    }

    /**
     * @covers ::allowUpdateDateModified
     */
    public function testAllowUpdateDateModified()
    {
        $this->user->allowUpdateDateModified(false);
        $this->assertFalse($this->sugarUser->update_date_modified);
    }

    /**
     * @return array
     * @see testIncrementLoginFailed
     * @see testGetLoginFailed
     */
    public function userLoginFailedProvider()
    {
        return [
            'integer' => ['value' => 1, 'expected' => 1, 'incremented' => 2],
            'string' => ['value' => '2', 'expected' => 2, 'incremented' => 3],
            'empty' => ['value' => null, 'expected' => 0, 'incremented' => 1],
        ];
    }

    /**
     * @covers       ::getLoginFailed
     * @param mixed $value
     * @param int $expected
     * @dataProvider userLoginFailedProvider
     */
    public function testGetLoginFailed($value, $expected)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('loginfailed')
            ->willReturn($value);

        $result = $this->user->getLoginFailed();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     * @see testGetLockout
     */
    public function userLockoutProvider()
    {
        return [
            'empty' => ['value' => null, 'expected' => false],
            'false' => ['value' => false, 'expected' => false],
            'zero' => ['value' => 0, 'expected' => false],
            'true' => ['value' => true, 'expected' => true],
            'one' => ['value' => 1, 'expected' => true],
        ];
    }

    /**
     * @covers ::getLockout
     * @param mixed $value
     * @param int $expected
     * @dataProvider userLockoutProvider
     */
    public function testGetLockout($value, $expected)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('lockout')
            ->willReturn($value);

        $result = $this->user->getLockout();

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::clearLockout
     */
    public function testClearLockout()
    {
        $this->sugarUser
            ->expects($this->exactly(2))
            ->method('setPreference')
            ->withConsecutive(
                ['lockout', '', 0, 'global'],
                ['loginfailed', 0, 0, 'global']
            );

        $this->sugarUser
            ->expects($this->once())
            ->method('savePreferencesToDB');

        $this->user->clearLockout();
    }

    /**
     * @covers ::lockout
     */
    public function testLockout()
    {
        $this->sugarUser
            ->expects($this->exactly(3))
            ->method('setPreference')
            ->withConsecutive(
                ['lockout', '1', 0, 'global'],
                ['logout_time', $logoutTime = '2017-02-13 01:01:01', 0, 'global'],
                ['loginfailed', 0, 0, 'global']
            );

        $this->sugarUser
            ->expects($this->once())
            ->method('savePreferencesToDB');

        $this->user->lockout($logoutTime);
    }

    /**
     * @covers       ::incrementLoginFailed
     * @dataProvider userLoginFailedProvider
     * @param $value
     * @param $unused
     * @param $incrementExpected
     */
    public function testIncrementLoginFailed($value, $unused, $incrementExpected)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('loginfailed')
            ->willReturn($value);

        $this->sugarUser
            ->expects($this->exactly(2))
            ->method('setPreference')
            ->withConsecutive(
                ['lockout', '', 0, 'global'],
                ['loginfailed', $incrementExpected, 0, 'global']
            );
        $this->sugarUser
            ->expects($this->once())
            ->method('savePreferencesToDB');

        $this->user->incrementLoginFailed();
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->timeDate = $this->getMockBuilder(\TimeDate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailAddress = $this->getMockBuilder(\EmailAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTimeDate'])
            ->getMock();
        $this->user->setAttribute('test_internal_attribute', 'internal_test');

        $this->sugarUser = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sugarUser->emailAddress = $this->emailAddress;
        $this->sugarUser->test_sugar_user_attribute = 'sugar_user_attribute';

        $this->user->setSugarUser($this->sugarUser);
        $this->user->method('getTimeDate')->willReturn($this->timeDate);
    }

    public function providerGetAttribute()
    {
        return [
            ['email', 'test@test.test'],
            ['test_internal_attribute', 'internal_test'],
            ['test_sugar_user_attribute', 'sugar_user_attribute'],
        ];
    }

    /**
     * @covers ::getAttribute
     * @dataProvider providerGetAttribute
     * @param $name
     * @param $expected
     */
    public function testGetAttribute($name, $expected)
    {
        $this->emailAddress->expects($this->any())
            ->method('getPrimaryAddress')
            ->with($this->sugarUser)
            ->willReturn('test@test.test');

        $this->assertEquals($expected, $this->user->getAttribute($name));
    }

    /**
     * @covers ::isServiceAccount
     */
    public function testIsServiceAccount(): void
    {
        $this->assertFalse($this->user->isServiceAccount());
    }
}
