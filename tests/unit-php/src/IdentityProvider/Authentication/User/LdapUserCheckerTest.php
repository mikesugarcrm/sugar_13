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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\LdapUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\LdapUserChecker
 */
class LdapUserCheckerTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $provider;

    /**
     * @var MockObject
     */
    protected $sugarUser;

    /**
     * @var Entry
     */
    protected $entry;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Lockout
     */
    protected $lockout;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(SugarLocalUserProvider::class);
        $this->sugarUser = $this->createMock(\User::class);

        $this->lockout = new Lockout();
        $this->entry = new Entry('user_dn', ['sn' => ['user_sn']]);
        $this->user = new User('user1', 'pass', ['entry' => $this->entry]);
        $this->user->setSugarUser($this->sugarUser);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthUserExists()
    {
        $this->provider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('user1'))
            ->willReturn($this->user);

        $checker = new LdapUserChecker($this->lockout, $this->provider, []);

        $checker->checkPostAuth($this->user);

        $this->assertEquals($this->sugarUser, $this->user->getSugarUser());
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthUserDoesNotExistLdapDisabled()
    {
        $this->provider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('user1'))
            ->willThrowException(new UsernameNotFoundException());
        $checker = new LdapUserChecker($this->lockout, $this->provider, []);

        $this->expectException(UsernameNotFoundException::class);
        $checker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthUserDoesNotExistLdapEnabled()
    {
        $this->provider->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('user1'))
            ->willThrowException(new UsernameNotFoundException());
        $this->provider->expects($this->once())
            ->method('createUser')
            ->with($this->equalTo('user1'), $this->equalTo([
                'last_name' => 'user_sn',
                'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
                'status' => User::USER_STATUS_ACTIVE,
                'is_admin' => 0,
                'external_auth_only' => 1,
            ]))
            ->willReturn($this->sugarUser);

        $checker = new LdapUserChecker($this->lockout, $this->provider, [
            'autoCreateUser' => true,
            'user' => [
                'mapping' => [
                    'sn' => 'last_name',
                ],
            ],
        ]);
        $checker->checkPostAuth($this->user);
        $this->assertEquals($this->sugarUser, $this->user->getSugarUser());
    }
}
