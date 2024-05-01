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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\InactiveUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarSAMLUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarSAMLUserChecker
 */
class SugarSAMLUserCheckerTest extends TestCase
{
    /**
     * @var SugarLocalUserProvider
     */
    protected $localUserProvider;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var \User
     */
    protected $sugarUser;

    /**
     * @var SugarSAMLUserChecker
     */
    protected $samlUserChecker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->localUserProvider = $this->createMock(SugarLocalUserProvider::class);
        $this->user = $this->createMock(User::class);
        $this->sugarUser = $this->createMock(\User::class);

        $this->samlUserChecker = $this->getMockBuilder(SugarSAMLUserChecker::class)
            ->setMethods(['processCustomUserCreate'])
            ->setConstructorArgs([$this->localUserProvider])
            ->getMock();
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthFindsLocalUserCorrespondingToSAMLLoggedInUser()
    {
        $name = 'test@test.com';
        $field = 'email';
        $value = 'test@test.com';
        $provision = false;

        $user = $this->getUserMock($name, $field, $value, $provision);
        $foundUser = $this->createMock(User::class);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($value, $field)
            ->willReturn($foundUser);

        $foundUser->expects($this->once())
            ->method('getSugarUser')
            ->willReturn($this->sugarUser);

        $user->expects($this->once())
            ->method('setSugarUser')
            ->with($this->sugarUser);

        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthNotFoundLocalUserAndUserProvisioningIsNotSet()
    {
        $name = 'test@test.com';
        $field = 'email';
        $value = 'test@test.com';
        $provision = false;

        $user = $this->getUserMock($name, $field, $value, $provision);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($value, $field)
            ->will($this->throwException(new UsernameNotFoundException()));

        $this->expectException(AuthenticationException::class);
        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthNotFoundLocalUserAndUserProvisioningIsSet()
    {
        $name = 'test@test.com';
        $field = 'email';
        $value = 'test@test.com';
        $provision = true;
        $attributes = [
            'create' => [
                'title' => 'bar',
            ],
        ];

        $user = $this->getUserMock($name, $field, $value, $provision, $attributes);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($value, $field)
            ->will($this->throwException(new UsernameNotFoundException()));

        $this->localUserProvider->expects($this->once())
            ->method('createUser')
            ->with($this->equalTo($name), $this->arrayHasKey('title'))
            ->willReturn($this->sugarUser);

        $user->expects($this->once())
            ->method('setSugarUser')
            ->with($this->sugarUser);

        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostDoesNothingWhenLocalUserExistsAndIsInactive()
    {
        $name = 'test@test.com';
        $field = 'user_name';
        $value = 'max_jensen';
        $provision = true;

        $user = $this->getUserMock($name, $field, $value, $provision);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($value, $field)
            ->will($this->throwException(new InactiveUserException('Found inactive user')));

        $this->localUserProvider->expects($this->never())->method('createUser');
        $user->expects($this->never())->method('setSugarUser');

        $this->expectException(InactiveUserException::class);
        $this->expectExceptionMessage('Found inactive user');
        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostCreatesUserWithUsernameButNotWithSearchIdentityValue()
    {
        $name = 'test@test.com';
        $field = 'user_name';
        $value = 'max_jensen';
        $provision = true;

        $user = $this->getUserMock($name, $field, $value, $provision);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($value, $field)
            ->will($this->throwException(new UsernameNotFoundException()));

        $this->localUserProvider->expects($this->once())
            ->method('createUser')
            ->with($this->equalTo($name), $this->anything())
            ->willReturn($this->sugarUser);

        $user->expects($this->once())
            ->method('setSugarUser')
            ->with($this->sugarUser);

        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostCreatesUserAndCanNotOverrideFixedSystemAttributes()
    {
        $name = 'test@test.com';
        $field = 'email';
        $value = 'test@test.com';
        $provision = true;
        $attributes = [
            'create' => [
                'user_name' => 'foo', 'external_auth_only' => 0, 'is_admin' => true,
            ],
        ];

        $expectedAttributes = [
            'user_name' => 'foo',
            'last_name' => 'test@test.com',
            'email' => 'test@test.com',
            'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
            'status' => User::USER_STATUS_ACTIVE,
            'is_admin' => 0,
            'external_auth_only' => 1,
            'system_generated_password' => 0,
        ];

        $user = $this->getUserMock($name, $field, $value, $provision, $attributes);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($value, $field)
            ->will($this->throwException(new UsernameNotFoundException()));

        $this->localUserProvider->expects($this->once())
            ->method('createUser')
            ->with($this->equalTo($name), $this->equalTo($expectedAttributes))
            ->willReturn($this->sugarUser);

        $user->expects($this->once())
            ->method('setSugarUser')
            ->with($this->sugarUser);

        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostUpdatesUserCustomFieldsIfAnyUpdateFieldsExist()
    {
        $this->sugarUser->title = 'Manager';
        $this->sugarUser->department = 'Production';

        $attributes = [
            'create' => [],
            'update' => [
                'title' => 'Manager Updated',
                'department' => 'Production Updated',
                'missing_field' => 'some value here',
            ],
        ];
        $user = $this->getUserMock('john', 'email', 'john@test.com', false, $attributes);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->willReturn($user);

        $this->sugarUser->expects($this->once())->method('save');

        $updatedSugarUser = clone($this->sugarUser);
        $updatedSugarUser->title = 'Manager Updated';
        $updatedSugarUser->department = 'Production Updated';

        $user->expects($this->once())->method('setSugarUser')->with($updatedSugarUser);

        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostDoesNotUpdateUserCustomFieldsIfNoUpdateFields()
    {
        $this->sugarUser->title = 'Manager';
        $this->sugarUser->department = 'Production';

        $attributes = [
            'create' => [],
            'update' => [],
        ];
        $user = $this->getUserMock('john', 'email', 'john@test.com', false, $attributes);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->willReturn($user);

        $this->sugarUser->expects($this->never())->method('save');
        $user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);

        $this->samlUserChecker->checkPostAuth($user);
    }

    /**
     * @param string $nameIdentifier
     * @param string $identityField
     * @param string $identityValue
     * @param bool $provision
     * @param array $attributes
     * @param array $sugarUser
     * @return User|MockObject
     */
    protected function getUserMock(
        $nameIdentifier,
        $identityField,
        $identityValue,
        $provision,
        $attributes = [
            'create' => [],
            'update' => [],
        ],
        $sugarUser = null
    ) {

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUsername', 'getAttribute', 'setSugarUser', 'getSugarUser'])
            ->getMock();

        $map = [
            ['identityField', $identityField],
            ['identityValue', $identityValue],
            ['provision', $provision],
            ['attributes', $attributes],
        ];

        $sugarUser = $sugarUser ?: $this->sugarUser;
        $user->method('getSugarUser')->willReturn($sugarUser);

        $user->method('getUsername')->willReturn($nameIdentifier);
        $user->method('getAttribute')->will($this->returnValueMap($map));

        return $user;
    }
}
