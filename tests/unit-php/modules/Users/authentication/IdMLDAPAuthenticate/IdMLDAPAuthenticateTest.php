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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Users\authentication\IdMLDAPAuthenticate;

use PHPUnit\Framework\TestCase;
use Sugarcrm\IdentityProvider\Authentication\Token\MixedUsernamePasswordToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @coversDefaultClass \IdMLDAPAuthenticate
 */
class IdMLDAPAuthenticateTest extends TestCase
{
    /**
     * @covers ::loginAuthenticate
     */
    public function testLoginAuthenticate()
    {
        $auth = $this->getMockBuilder(\IdMLDAPAuthenticate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthProviderBuilder'])
            ->getMock();

        $builder = $this->createMock(AuthProviderManagerBuilder::class);

        $auth->expects($this->once())
            ->method('getAuthProviderBuilder')
            ->with($this->isInstanceOf(Config::class))
            ->willReturn($builder);

        $manager = $this->createMock(AuthenticationProviderManager::class);

        $builder->expects($this->once())
            ->method('buildAuthProviders')
            ->willReturn($manager);

        $token = $this->createMock(UsernamePasswordToken::class);

        $manager->expects($this->once())
            ->method('authenticate')
            ->with($this->callback(function ($token) {
                /** @var MixedUsernamePasswordToken $token */
                $this->assertInstanceOf(MixedUsernamePasswordToken::class, $token);
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('pass', $token->getCredentials());
                $this->assertCount(2, $token->getTokens());
                $this->assertEquals(AuthProviderManagerBuilder::PROVIDER_KEY_MIXED, $token->getProviderKey());

                return true;
            }))
            ->willReturn($token);

        $token->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->assertTrue($auth->loginAuthenticate('user', 'pass'));
    }
}
