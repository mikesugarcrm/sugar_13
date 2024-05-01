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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\UserProvider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider
 */
class SugarOIDCUserProviderTest extends TestCase
{
    /**
     * @var SugarOIDCUserProvider
     */
    protected $userProvider = null;

    /**
     * @var SugarLocalUserProvider|MockObject
     */
    protected $localUserProvider = null;

    protected function setUp(): void
    {
        $this->localUserProvider = $this->createMock(SugarLocalUserProvider::class);
        $this->userProvider = new SugarOIDCUserProvider($this->localUserProvider);
    }

    /**
     * @covers ::loadUserByUsername
     */
    public function testLoadUserByUsername()
    {
        $user = $this->userProvider->loadUserByUsername('testUser');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('testUser', $user->getUsername());
    }

    /**
     * @covers ::refreshUser
     */
    public function testRefreshUser()
    {
        $user = new User('testUser');
        $refreshedUser = $this->userProvider->refreshUser($user);
        $this->assertEquals($user, $refreshedUser);
    }

    /**
     * Provides data for testSupportsClass
     * @return array
     */
    public function supportsClassProvider()
    {
        return [
            'validClass' => [
                'className' => User::class,
                'expectedResult' => true,
            ],
            'invalidClass' => [
                'className' => \User::class,
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @param $className
     * @param $expectedResult
     *
     * @dataProvider supportsClassProvider
     *
     * @covers ::supportsClass
     */
    public function testSupportsClass($className, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->userProvider->supportsClass($className));
    }

    /**
     * @covers ::loadUserByField
     */
    public function testLoadUserByField()
    {
        $this->localUserProvider->expects($this->once())->method('loadUserByField')->with('value', 'field');
        $this->userProvider->loadUserByField('value', 'field');
    }

    /**
     * Provides data for testLoadUserBySrn
     * @return array
     */
    public function loadUserBySrnProvider(): array
    {
        return [
            'UserAccount' => [
                'srn' => 'srn:cluster:iam::0000000001:user:seed_sally_id',
                'isServiceAccount' => false,
            ],
            'ServiceAccount' => [
                'srn' => 'srn:cluster:iam::0000000001:sa:seed_sa_id',
                'isServiceAccount' => true,
            ],
        ];
    }

    /**
     * @param string $srn
     * @param bool $isServiceAccount
     *
     * @covers ::loadUserBySrn
     *
     * @dataProvider loadUserBySrnProvider
     */
    public function testLoadUserBySrn(string $srn, bool $isServiceAccount): void
    {
        /** @var User $user */
        $user = $this->userProvider->loadUserBySrn($srn);
        $this->assertEquals($srn, $user->getSrn());
        $this->assertEquals($isServiceAccount, $user->isServiceAccount());
    }

    /**
     * @return array
     * @see testSetAllowInactive
     */
    public function setAllowInactiveProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @covers ::setAllowInactive
     * @param bool $allowInactive
     *
     * @dataProvider SetAllowInactiveProvider
     */
    public function testSetAllowInactive(bool $allowInactive): void
    {
        $this->localUserProvider->expects($this->once())->method('setAllowInactive')->with($allowInactive);
        $this->userProvider->setAllowInactive($allowInactive);
    }
}
