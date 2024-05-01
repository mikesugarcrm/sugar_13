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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Listener\Success\OIDC;

use Sugarcrm\Sugarcrm\IdentityProvider\SessionProxy;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\OIDC\UpdateUserLastLoginListener;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\OIDC\UpdateUserLastLoginListener
 */
class UpdateUserLastLoginListenerTest extends TestCase
{
    /**
     * @var UpdateUserLastLoginListener
     */
    protected $listener;

    /**
     * @var UsernamePasswordToken|MockObject
     */
    protected $token;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * @var SessionProxy|MockObject
     */
    protected $session;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sugarUser = $this->createMock(\User::class);
        $this->token = $this->createMock(UsernamePasswordToken::class);
        $this->event = new AuthenticationEvent($this->token);
        $this->session = $this->createMock(SessionProxy::class);
        $this->listener = new UpdateUserLastLoginListener($this->session);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWhenFirstLogin(): void
    {
        $this->session->method('get')
            ->with('oidc_login_action')
            ->willReturn(true);

        $user = new User('test', 'test', []);
        $user->setSugarUser($this->sugarUser);

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->sugarUser->expects($this->once())->method('updateLastLogin');

        $this->listener->execute($this->event);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWhenTokenIntrospection(): void
    {
        $this->session->method('get')
            ->with('oidc_login_action')
            ->willReturn(null);

        $user = new User('test', 'test', []);
        $user->setSugarUser($this->sugarUser);

        $this->token->expects($this->never())->method('getUser');
        $this->sugarUser->expects($this->never())->method('updateLastLogin');

        $this->listener->execute($this->event);
    }
}
