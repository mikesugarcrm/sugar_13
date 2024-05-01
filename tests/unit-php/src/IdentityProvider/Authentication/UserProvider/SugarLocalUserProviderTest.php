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

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\InactiveUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\InvalidUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider
 */
class SugarLocalUserProviderTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $user;

    /**
     * @var MockObject
     */
    protected $emailAddress;

    /**
     * @var MockObject
     */
    protected $userProvider;

    /**
     * @var MockObject
     */
    protected $sugarQuery;

    /**
     * @var MockObject
     */
    protected $sugarQueryWhere;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->user = $this->createMock(\User::class);
        $this->emailAddress = $this->createMock(\EmailAddress::class);
        $this->user->emailAddress = $this->emailAddress;

        $this->userProvider = $this->getMockBuilder(SugarLocalUserProvider::class)
            ->setMethods(['createUserBean', 'getSugarQuery'])
            ->getMock();

        $this->userProvider->expects($this->any())
            ->method('createUserBean')
            ->willReturn($this->user);

        $this->sugarQuery = $this->createMock('SugarQuery');
        $this->sugarQueryWhere = $this->createMock('SugarQuery_Builder_Where');

        $this->sugarQuery->expects($this->any())
            ->method('where')
            ->willReturn($this->sugarQueryWhere);

        $this->userProvider->expects($this->any())
            ->method('getSugarQuery')
            ->willReturn($this->sugarQuery);
    }

    /**
     * @covers ::loadUserByUsername
     */
    public function testLoadUserByUsernameNoId()
    {
        $this->sugarQueryWhere->expects($this->once())
            ->method('equals')
            ->with($this->equalTo('user_name'), $this->equalTo($name = 'test'));

        $this->sugarQuery->expects($this->any())->method('getOne')->willReturn(null);

        $this->expectException(UsernameNotFoundException::class);
        $this->userProvider->loadUserByUsername($name);
    }

    /**
     * @covers ::loadUserByUsername
     */
    public function testLoadUserByUsernameInactive()
    {
        $this->sugarQueryWhere->expects($this->once())
            ->method('equals')
            ->with($this->equalTo('user_name'), $this->equalTo($name = 'test'));

        $this->sugarQuery->expects($this->any())->method('getOne')->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->will(
                $this->returnCallback(
                    function () use ($id) {
                        $this->user->id = $id;
                    }
                )
            );

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = User::USER_STATUS_INACTIVE;

        $this->expectException(InactiveUserException::class);
        $this->userProvider->loadUserByUsername($name);
    }

    /**
     * @covers ::loadUserByUsername
     * @covers ::setAllowInactive
     */
    public function testLoadUserAllowedInactive(): void
    {
        $this->sugarQueryWhere->expects($this->once())
            ->method('equals')
            ->with($this->equalTo('user_name'), $this->equalTo($name = 'test'));

        $this->sugarQuery->expects($this->any())->method('getOne')->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->will(
                $this->returnCallback(
                    function () use ($id) {
                        $this->user->id = $id;
                    }
                )
            );

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = User::USER_STATUS_INACTIVE;

        $this->userProvider->setAllowInactive(true);
        $this->userProvider->loadUserByUsername($name);
    }

    /**
     * @coversNothing
     * @return array
     */
    public function providerLoadUserByUsernameInvalid()
    {
        return [
            [1, 0],
            [0, 1],
        ];
    }

    /**
     * @dataProvider providerLoadUserByUsernameInvalid
     *
     * @covers ::loadUserByUsername
     * @param $isGroup
     * @param $portalOnly
     */
    public function testLoadUserByUsernameInvalid($isGroup, $portalOnly)
    {
        $this->sugarQueryWhere->expects($this->once())
            ->method('equals')
            ->with($this->equalTo('user_name'), $this->equalTo($name = 'test'));

        $this->sugarQuery->expects($this->any())->method('getOne')->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->will(
                $this->returnCallback(
                    function () use ($id) {
                        $this->user->id = $id;
                    }
                )
            );

        $this->user->is_group = $isGroup;
        $this->user->portal_only = $portalOnly;
        $this->user->status = User::USER_STATUS_ACTIVE;

        $this->expectException(InvalidUserException::class);
        $this->userProvider->loadUserByUsername($name);
    }

    /**
     * @covers ::loadUserByUsername
     */
    public function testLoadUserByUsername()
    {
        $this->emailAddress->expects($this->once())->method('handleLegacyRetrieve');

        $this->sugarQueryWhere->expects($this->once())
            ->method('equals')
            ->with($this->equalTo('user_name'), $this->equalTo($name = 'test'));

        $this->sugarQuery->expects($this->any())->method('getOne')->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->will(
                $this->returnCallback(
                    function () use ($id) {
                        $this->user->id = $id;
                    }
                )
            );

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = User::USER_STATUS_ACTIVE;
        $this->user->user_hash = 'user_hash';

        /** @var User $newUser */
        $newUser = $this->userProvider->loadUserByUsername($name);

        $this->assertInstanceOf(User::class, $newUser);
        $this->assertEquals($this->user->user_hash, $newUser->getPassword());
    }

    /**
     * @covers ::loadUserByField
     */
    public function testLoadUserByField()
    {
        $id = '123';
        $field = 'last_name';
        $value = 'Johnson';

        $this->emailAddress->expects($this->once())->method('handleLegacyRetrieve');

        $this->sugarQueryWhere->expects($this->once())
            ->method('equals')
            ->with($this->equalTo($field), $this->equalTo($value));

        $this->sugarQuery->expects($this->any())->method('getOne')->willReturn($id);

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->will(
                $this->returnCallback(
                    function () use ($id) {
                        $this->user->id = $id;
                    }
                )
            );

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = User::USER_STATUS_ACTIVE;
        $this->user->user_hash = 'user_hash';

        /** @var User $newUser */
        $newUser = $this->userProvider->loadUserByField($value, $field);

        $this->assertInstanceOf(User::class, $newUser);
        $this->assertEquals($this->user->user_hash, $newUser->getPassword());
    }

    /**
     * @covers ::loadUserByField
     */
    public function testLoadUserByFieldEmail()
    {
        $id = '123';

        $this->emailAddress->expects($this->once())->method('handleLegacyRetrieve');

        $this->user->expects($this->once())
            ->method('retrieve_by_email_address')
            ->with($this->equalTo('test@test.com'))
            ->will(
                $this->returnCallback(
                    function () use ($id) {
                        $this->user->id = $id;
                    }
                )
            );

        $this->user->expects($this->never())->method('retrieve');

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = User::USER_STATUS_ACTIVE;
        $this->user->user_hash = 'user_hash';

        /** @var User $newUser */
        $newUser = $this->userProvider->loadUserByField('test@test.com', 'email');

        $this->assertInstanceOf(User::class, $newUser);
        $this->assertEquals($this->user->user_hash, $newUser->getPassword());
    }

    /**
     * @covers ::refreshUser
     */
    public function testRefreshUserNonSupported()
    {
        $userMock = $this->createMock(UserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $this->userProvider->refreshUser($userMock);
    }

    /**
     * @covers ::refreshUser
     */
    public function testRefreshUser()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn($name = 'user_name');

        $user->emailAddress = $this->emailAddress;
        $this->emailAddress->expects($this->once())->method('handleLegacyRetrieve');

        $this->sugarQueryWhere->expects($this->once())
            ->method('equals')
            ->with($this->equalTo('user_name'), $this->equalTo($name));

        $this->sugarQuery->expects($this->any())->method('getOne')->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->will(
                $this->returnCallback(
                    function () use ($id) {
                        $this->user->id = $id;
                    }
                )
            );

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = 'Active';
        $this->user->user_hash = 'user_hash';

        $this->userProvider->refreshUser($user);
    }

    /**
     * @covers ::supportsClass
     */
    public function testSupportsClass()
    {
        $this->assertTrue($this->userProvider->supportsClass(User::class));
    }

    /**
     * @covers ::createUser
     */
    public function testCreateUser()
    {
        $this->user->expects($this->once())
            ->method('populateFromRow')
            ->with($this->callback(function ($fields) {
                $this->assertEquals('user1', $fields['user_name']);
                return true;
            }));
        $this->user->expects($this->once())->method('save');
        $this->user->expects($this->never())->method('retrieve');
        $this->userProvider->createUser('user1');
    }

    /**
     * @covers ::createUser
     */
    public function testCreateUserWithEmailInAttributes()
    {
        $emailAddress = $this->createMock('SugarEmailAddress');
        $this->user->emailAddress = $emailAddress;

        $emailAddress->expects($this->once())->method('addAddress')->with('test@test.com', true);
        $emailAddress->expects($this->once())->method('save');

        $this->userProvider->createUser('user1', ['email' => 'test@test.com']);
    }

    /**
     * @covers ::createUser
     */
    public function testCreateCollision()
    {
        $this->user
            ->expects($this->once())
            ->method('save')
            ->willThrowException($this->createMock(UniqueConstraintViolationException::class));
        $this->user
            ->expects($this->once())
            ->method('retrieve');

        $this->userProvider->createUser('user1');
    }
}
