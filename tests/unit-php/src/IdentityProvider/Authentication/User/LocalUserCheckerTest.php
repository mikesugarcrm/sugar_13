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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\ExternalAuthUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\LocalUserChecker;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\LocalUserChecker
 */
class LocalUserCheckerTest extends TestCase
{
    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var User|MockObject
     */
    protected $user;

    /**
     * @var Lockout|MockObject
     */
    protected $lockout;

    protected function setUp(): void
    {
        $this->sugarUser = $this->createMock(\User::class);
        $this->user = $this->createMock(User::class);
        $this->lockout = $this->createMock(Lockout::class);
    }

    /**
     * @return array
     */
    public function withExternalOnlyAuthProvider()
    {
        return [
            'int' => [1],
            'bool' => [true],
            'string' => ['true'],
        ];
    }

    /**
     * @covers ::checkPreAuth
     * @dataProvider withExternalOnlyAuthProvider
     *
     * @param mixed $externalAuthOnlyValue
     */
    public function testCheckPreAuthOfUserWithExternalAuthOnly($externalAuthOnlyValue)
    {
        $this->sugarUser->external_auth_only = $externalAuthOnlyValue;

        $this->user->method('getSugarUser')->willReturn($this->sugarUser);

        $this->lockout->method('isEnabled')->willReturn(false);

        $checker = new LocalUserChecker($this->lockout);

        $this->expectException(ExternalAuthUserException::class);
        $checker->checkPreAuth($this->user);
    }

    /**
     * @return array
     */
    public function withoutExternalOnlyAuthProvider()
    {
        return [
            'zero' => [0],
            'null' => [null],
            'false' => [false],
        ];
    }

    /**
     * @covers ::checkPreAuth
     * @dataProvider withoutExternalOnlyAuthProvider
     *
     * @param mixed $externalAuthOnlyValue
     */
    public function testAuthenticateUserWithoutWithExternalAuthOnly($externalAuthOnlyValue)
    {
        $this->sugarUser->external_auth_only = $externalAuthOnlyValue;

        $this->user->method('getSugarUser')->willReturn($this->sugarUser);

        $this->lockout->method('isEnabled')->willReturn(false);

        $checker = new LocalUserChecker($this->lockout);

        $this->assertEmpty($checker->checkPreAuth($this->user));
    }
}
