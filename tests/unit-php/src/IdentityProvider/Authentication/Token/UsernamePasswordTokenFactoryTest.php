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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Sugarcrm\IdentityProvider\Authentication\Token\MixedUsernamePasswordToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\UsernamePasswordTokenFactory;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\UsernamePasswordTokenFactory
 */
class UsernamePasswordTokenFactoryTest extends TestCase
{
    /**
     * @var UsernamePasswordTokenFactory
     */
    protected $tokenFactory = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->tokenFactory = new UsernamePasswordTokenFactory(
            'username',
            'password',
            ['passwordEncrypted' => false, 'tenant' => 'srn:tenant']
        );
    }

    /**
     * @covers ::createLocalAuthenticationToken
     */
    public function testCreateLocalAuthenticationToken()
    {
        $token = $this->tokenFactory->createLocalAuthenticationToken();

        $this->assertInstanceOf(UsernamePasswordToken::class, $token);
        $this->assertEquals('username', $token->getUsername());
        $this->assertEquals('5f4dcc3b5aa765d61d8327deb882cf99', $token->getCredentials());
        $this->assertEquals(AuthProviderManagerBuilder::PROVIDER_KEY_LOCAL, $token->getProviderKey());
    }

    /**
     * @covers ::createLdapAuthenticationToken
     */
    public function testCreateLdapAuthenticationToken()
    {
        $token = $this->tokenFactory->createLdapAuthenticationToken();

        $this->assertInstanceOf(UsernamePasswordToken::class, $token);
        $this->assertEquals('username', $token->getUsername());
        $this->assertEquals('password', $token->getCredentials());
        $this->assertEquals(AuthProviderManagerBuilder::PROVIDER_KEY_LDAP, $token->getProviderKey());
    }

    /**
     * @covers ::createMixedAuthenticationToken
     */
    public function testCreateMixedAuthenticationToken()
    {
        $token = $this->tokenFactory->createMixedAuthenticationToken();

        $this->assertInstanceOf(MixedUsernamePasswordToken::class, $token);
        $this->assertEquals('username', $token->getUsername());
        $this->assertEquals('password', $token->getCredentials());
        $this->assertEquals(AuthProviderManagerBuilder::PROVIDER_KEY_MIXED, $token->getProviderKey());
    }
}
