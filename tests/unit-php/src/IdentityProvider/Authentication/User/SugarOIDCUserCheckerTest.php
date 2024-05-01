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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarOIDCUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarOIDCUserChecker
 */
class SugarOIDCUserCheckerTest extends TestCase
{
    /**
     * @var SugarLocalUserProvider|MockObject
     */
    protected $localUserProvider;

    /**
     * @var SugarOIDCUserChecker
     */
    protected $userChecker;

    /**
     * @var User|MockObject
     */
    protected $user;

    /**
     * @var User|MockObject
     */
    protected $foundUser;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var \EmailAddress|MockObject
     */
    protected $emailAddress;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->localUserProvider = $this->createMock(SugarLocalUserProvider::class);
        $this->userChecker = new SugarOIDCUserChecker($this->localUserProvider);
        $this->user = $this->createMock(User::class);
        $this->foundUser = $this->createMock(User::class);
        $this->sugarUser = $this->createMock(\User::class);
        $this->emailAddress = $this->createMock(\EmailAddress::class);
        $this->sugarUser->emailAddress = $this->emailAddress;
        $this->user->method('isCredentialsNonExpired')->willReturn(true);

        $this->sugarUser->db = $this->getMockBuilder('\DBManager')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @covers ::checkPostAuth
     * @dataProvider checkPostAuthEmailModifiedProvider
     *
     * @param $oidc
     * @param $fieldDefs
     * @param $old
     * @param $expected
     */
    public function testCheckPostAuthEmailModified($oidc, $fieldDefs, $old, $expected)
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls($oidc['data'], $oidc['identify']);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($oidc['identify']['value'], $oidc['identify']['field'])
            ->willReturn($this->foundUser);
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        foreach (array_diff_key($old, ['email' => 1]) as $field => $value) {
            $this->sugarUser->{$field} = $value;
        }
        $this->emailAddress->expects($this->once())
            ->method('getPrimaryAddress')
            ->with($this->sugarUser)
            ->willReturn($old['email']);
        $this->sugarUser->expects($this->once())->method('getFieldDefinitions')->willReturn($fieldDefs);
        $this->sugarUser->expects($this->once())->method('save');

        $this->emailAddress->expects($this->once())
            ->method('addAddress')
            ->with($this->equalTo($expected['email']), $this->isTrue());

        $this->emailAddress->expects($this->once())->method('save');

        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);

        foreach (array_diff_key($expected, ['email' => 1]) as $field => $value) {
            $this->assertEquals($expected[$field], $this->sugarUser->{$field});
        }
    }

    /**
     * @return array
     */
    public function checkPostAuthEmailModifiedProvider()
    {
        return [
            'existing-user' => [
                [
                    'data' => ['user_name' => 'test', 'first_name' => 'new_first_name', 'email' => 'new@test.lh'],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'first_name' => [
                        'name' => 'first_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'test', 'first_name' => 'old_first_name', 'email' => 'old@test.lh'],
                ['user_name' => 'test', 'first_name' => 'new_first_name', 'email' => 'new@test.lh'],
            ],
            'long-attribute-value' => [
                [
                    'data' => ['user_name' => 'test', 'last_name' => 'longer____than____30____characters', 'email' => 'new@test.lh'],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'last_name' => [
                        'name' => 'last_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'test', 'last_name' => 'old_last_name', 'email' => 'old@test.lh'],
                ['user_name' => 'test', 'last_name' => 'longer____than____30____charac', 'email' => 'new@test.lh'],
            ],
        ];
    }

    /**
     * @covers ::checkPostAuth
     * @dataProvider checkPostAuthEmailNotModifiedProvider
     *
     * @param $oidc
     * @param $fieldDefs
     * @param $old
     * @param $expected
     */
    public function testCheckPostAuthEmailNotModified($oidc, $fieldDefs, $old, $expected)
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls($oidc['data'], $oidc['identify']);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($oidc['identify']['value'], $oidc['identify']['field'])
            ->willReturn($this->foundUser);
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        foreach ($old as $field => $value) {
            $this->sugarUser->{$field} = $value;
        }
        $this->sugarUser->expects($this->once())->method('getFieldDefinitions')->willReturn($fieldDefs);
        $this->sugarUser->expects($this->once())->method('save');

        $this->emailAddress->expects($this->once())
            ->method('getPrimaryAddress')
            ->with($this->sugarUser)
            ->willReturn($old['email']);
        $this->emailAddress->expects($this->never())->method('addAddress');
        $this->emailAddress->expects($this->never())->method('save');

        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);

        foreach ($expected as $field => $value) {
            $this->assertEquals($expected[$field], $this->sugarUser->{$field});
        }
    }

    /**
     * @return array
     */
    public function checkPostAuthEmailNotModifiedProvider()
    {
        return [
            'existing-user' => [
                [
                    'data' => [
                        'user_name' => 'user_name.new',
                        'first_name' => 'new_first_name',
                        'email' => 'old@test.lh',
                    ],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'first_name' => [
                        'name' => 'first_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'user_name.old', 'first_name' => 'old_first_name', 'email' => 'old@test.lh'],
                ['user_name' => 'user_name.old', 'first_name' => 'new_first_name', 'email' => 'old@test.lh'],
            ],
            'long-attribute-value' => [
                [
                    'data' => ['user_name' => 'test', 'last_name' => 'longer____than____30____characters', 'email' => 'old@test.lh'],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'last_name' => [
                        'name' => 'last_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'test', 'last_name' => 'old_last_name', 'email' => 'old@test.lh'],
                ['user_name' => 'test', 'last_name' => 'longer____than____30____charac', 'email' => 'old@test.lh'],
            ],
        ];
    }

    /**
     * @covers ::checkPostAuth
     * @dataProvider checkPostAuthNotModifiedProvider
     *
     * @param $oidc
     * @param $fieldDefs
     * @param $old
     * @param $expected
     */
    public function testCheckPostAuthNotModified($oidc, $fieldDefs, $old, $expected)
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls($oidc['data'], $oidc['identify']);

        $this->user->expects($this->once())
            ->method('getAttributes')
            ->willReturn($oidc['attributes']);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with($oidc['identify']['value'], $oidc['identify']['field'])
            ->willReturn($this->foundUser);
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        foreach ($old as $field => $value) {
            $this->sugarUser->{$field} = $value;
        }
        $this->sugarUser->expects($this->once())->method('getFieldDefinitions')->willReturn($fieldDefs);
        $this->sugarUser->expects($this->never())->method('save');

        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);

        foreach ($expected as $field => $value) {
            $this->assertEquals($expected[$field], $this->sugarUser->{$field});
        }
    }

    /**
     * @return array
     */
    public function checkPostAuthNotModifiedProvider()
    {
        return [
            'existing-user' => [
                [
                    'data' => [
                        'user_name' => 'test',
                        'first_name' => 'old_first_name',
                        'email' => 'old@test.lh',
                    ],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                    'attributes' => [
                        'ext' => [
                            'is_user_manager' => false,
                        ],
                    ],
                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'first_name' => [
                        'name' => 'first_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'test', 'first_name' => 'old_first_name'],
                ['user_name' => 'test', 'first_name' => 'old_first_name', 'isIdmUserManager' => false],
            ],
            'check_keep_old_user_name' => [
                [
                    'data' => [
                        'user_name' => 'user_name.new',
                        'first_name' => 'old_first_name',
                        'email' => 'old@test.lh',
                    ],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                    'attributes' => [],
                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'first_name' => [
                        'name' => 'first_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'user_name.old', 'first_name' => 'old_first_name'],
                ['user_name' => 'user_name.old', 'first_name' => 'old_first_name', 'isIdmUserManager' => null],
            ],
            'existing-user-no-oidc-email' => [
                [
                    'data' => ['user_name' => 'test', 'first_name' => 'old_first_name'],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                    'attributes' => [],
                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'first_name' => [
                        'name' => 'first_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'test', 'first_name' => 'old_first_name'],
                ['user_name' => 'test', 'first_name' => 'old_first_name'],
            ],
            'long-attribute-value' => [
                [
                    'data' => ['user_name' => 'test', 'last_name' => 'longer____than____30____characters', 'email' => 'old@test.lh'],
                    'identify' => ['field' => 'id', 'value' => 'seed_sally_id'],
                    'attributes' => [],

                ],
                [
                    'user_name' => [
                        'name' => 'user_name',
                        'type' => 'username',
                        'dbType' => 'varchar',
                        'len' => '60',
                    ],
                    'last_name' => [
                        'name' => 'last_name',
                        'dbType' => 'varchar',
                        'type' => 'name',
                        'len' => '30',
                    ],
                ],
                ['user_name' => 'test', 'last_name' => 'longer____than____30____charac'],
                ['user_name' => 'test', 'last_name' => 'longer____than____30____charac'],
            ],
        ];
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithOidcUserUpdatedAtLaterThanLastLogin(): void
    {
        $this->user->expects($this->once())
            ->method('hasAttribute')
            ->withConsecutive([$this->equalTo('updated_at')])
            ->willReturnOnConsecutiveCalls(true);
        $this->user->expects($this->exactly(3))
            ->method('getAttribute')
            ->withConsecutive(
                [$this->equalTo('oidc_data')],
                [$this->equalTo('oidc_identify')],
                [$this->equalTo('updated_at')]
            )
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'sally'],
                ['field' => 'id', 'value' => 'seed_sally_id'],
                strtotime('2030-12-12 00:00:00')
            );

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willReturn($this->foundUser);
        $this->sugarUser->last_login = '2010-10-10 00:00:00';
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        $this->sugarUser->expects($this->once())->method('getFieldDefinitions');

        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithOidcUserUpdatedAtEarlierThanLastLogin(): void
    {
        $this->user->expects($this->once())
            ->method('hasAttribute')
            ->withConsecutive([$this->equalTo('updated_at')])
            ->willReturnOnConsecutiveCalls(true);
        $this->user->expects($this->exactly(3))
            ->method('getAttribute')
            ->withConsecutive(
                [$this->equalTo('oidc_data')],
                [$this->equalTo('oidc_identify')],
                [$this->equalTo('updated_at')]
            )
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'sally'],
                ['field' => 'id', 'value' => 'seed_sally_id'],
                strtotime('2000-12-12 00:00:00')
            );

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willReturn($this->foundUser);
        $this->sugarUser->last_login = '2010-10-10 00:00:00';
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        $this->sugarUser->expects($this->never())->method('getFieldDefinitions');

        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithoutOidcUserUpdatedAt(): void
    {
        $this->user->expects($this->once())
            ->method('hasAttribute')
            ->withConsecutive([$this->equalTo('updated_at')])
            ->willReturnOnConsecutiveCalls(false);
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(
                [$this->equalTo('oidc_data')],
                [$this->equalTo('oidc_identify')]
            )
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'sally'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willReturn($this->foundUser);
        $this->sugarUser->last_login = '2010-10-10 00:00:00';
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        $this->sugarUser->expects($this->once())->method('getFieldDefinitions');

        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithNotExistingUser()
    {
        $expectedAttributes = [
            'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
            'status' => User::USER_STATUS_ACTIVE,
            'is_admin' => 0,
            'external_auth_only' => 1,
            'system_generated_password' => 0,
            'id' => 'seed_sally_id',
            'user_name' => 'test',
        ];
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'test'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );
        $this->user->method('getSrn')->willReturn('srn:cluster:idm:eu:0000000001:user:seed_sally_id');
        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willThrowException(new UsernameNotFoundException());
        $this->localUserProvider->expects($this->once())
            ->method('createUser')
            ->with('test', $expectedAttributes)
            ->willReturn($this->sugarUser);
        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithInactiveUserInSugarAndActiveInCloud(): void
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'test', 'status' => 'Active'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );
        $this->user->method('getSrn')->willReturn('srn:cluster:idm:eu:0000000001:user:seed_sally_id');
        $this->sugarUser->expects($this->once())->method('getFieldDefinitions')->willReturn([
            'user_name' => [
                'name' => 'user_name',
                'type' => 'username',
                'dbType' => 'varchar',
                'len' => '60',
            ],
            'status' => [
                'name' => 'status',
                'type' => 'status',
                'dbType' => 'varchar',
                'len' => '60',
            ],
        ]);

        $this->sugarUser->status = 'Inactive';

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willThrowException(new InactiveUserException('', 0, null, $this->sugarUser));

        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);

        $this->assertEquals(User::USER_STATUS_ACTIVE, $this->sugarUser->status);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithInactiveUserInSugarAndInactiveInCloud(): void
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'test', 'status' => 'Inactive'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );
        $this->user->method('getSrn')->willReturn('srn:cluster:idm:eu:0000000001:user:seed_sally_id');

        $this->sugarUser->status = 'Inactive';

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willThrowException(new InactiveUserException('', 0, null, $this->sugarUser));

        $this->user->expects($this->never())->method('setSugarUser');

        $this->expectException(InactiveUserException::class);
        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     * @covers ::setAllowInactive
     */
    public function testCheckPostAuthWithInactiveUserButAllowInactive(): void
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'test', 'status' => 'Inactive'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );
        $this->user->method('getSrn')->willReturn('srn:cluster:idm:eu:0000000001:user:seed_sally_id');

        $this->sugarUser->status = 'Inactive';

        $this->sugarUser->expects($this->once())
            ->method('getFieldDefinitions')
            ->willReturn([
                'status' => [
                    'name' => 'status',
                    'type' => 'status',
                    'dbType' => 'varchar',
                    'len' => '60',
                ],
            ]);

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willThrowException(new InactiveUserException('', 0, null, $this->sugarUser));

        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);

        $this->userChecker->setAllowInactive(true);
        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithInactiveUserInSugarNoUserInException(): void
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'test', 'status' => 'Active'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );
        $this->user->method('getSrn')->willReturn('srn:cluster:idm:eu:0000000001:user:seed_sally_id');

        $this->sugarUser->status = 'Inactive';

        $this->localUserProvider->expects($this->once())
            ->method('loadUserByField')
            ->with('seed_sally_id', 'id')
            ->willThrowException(new InactiveUserException('', 0, null, null));

        $this->user->expects($this->never())->method('setSugarUser');

        $this->expectException(InactiveUserException::class);
        $this->userChecker->checkPostAuth($this->user);
    }
}
