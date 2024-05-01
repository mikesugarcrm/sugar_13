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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider\IdPAuthenticationProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\IdpUsernamePasswordToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider\IdPAuthenticationProvider
 */
class IdPAuthenticationProviderTest extends TestCase
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var IdPAuthenticationProvider
     */
    protected $provider = null;

    /**
     * @var SugarOIDCUserProvider|MockObject
     */
    protected $userProvider = null;

    /**
     * @var UserCheckerInterface|MockObject
     */
    protected $userChecker = null;

    /**
     * @var IdmProvider|MockObject
     */
    protected $oAuthProvider = null;

    /**
     * @var User\Mapping\SugarOidcUserMapping
     */
    protected $userMapping;

    /**
     * @var User
     */
    protected $user = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->userProvider = $this->createMock(SugarOIDCUserProvider::class);
        $this->oAuthProvider = $this->createMock(IdmProvider::class);
        $this->userMapping = new User\Mapping\SugarOidcUserMapping();
        $this->user = new User();
        $this->provider = new IdPAuthenticationProvider(
            $this->oAuthProvider,
            $this->userProvider,
            $this->userChecker,
            $this->userMapping,
            AuthProviderBasicManagerBuilder::PROVIDER_KEY_IDP
        );
    }

    /**
     * Checks supports logic.
     *
     * @covers ::supports
     */
    public function testSupportsWithSupportedToken()
    {
        $token = new IdpUsernamePasswordToken(
            'SRN:tenant',
            'test',
            'test',
            AuthProviderBasicManagerBuilder::PROVIDER_KEY_IDP
        );
        $this->assertTrue($this->provider->supports($token));
    }

    /**
     * Checks supports logic.
     *
     * @covers ::supports
     */
    public function testSupportsWithUnsupportedToken()
    {
        $token = new IdpUsernamePasswordToken(
            'srn:cluster:idm:eu:0000000001:tenant',
            'test',
            'test',
            'test'
        );
        $this->assertFalse($this->provider->supports($token));
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateWithUnsupportedToken()
    {
        $token = new UsernamePasswordToken('test', 'test', 'test');

        $this->expectException(ProviderNotFoundException::class);
        $this->provider->authenticate($token);
    }

    /**
     * Provides data for testAuthenticateWithLegacyTokenAuthenticationError
     *
     * @return array
     */
    public function authenticateWithLegacyTokenAuthenticationErrorProvider()
    {
        return [
            'emptyResponse' => [
                'data' => [],
            ],
            'notSuccessResponse' => [
                'data' => ['status' => 'error'],
            ],
            'noUserInResponse' => [
                'data' => [
                    'status' => 'success',
                    'user' => [],
                ],
            ],
        ];
    }

    /**
     * @param array $data
     *
     * @covers ::authenticate
     * @dataProvider authenticateWithLegacyTokenAuthenticationErrorProvider
     */
    public function testAuthenticateWithLegacyTokenAuthenticationError(array $data)
    {
        $token = new IdpUsernamePasswordToken(
            'srn:cluster:idm:eu:0000000001:tenant',
            'user',
            'password',
            AuthProviderBasicManagerBuilder::PROVIDER_KEY_IDP
        );

        $this->oAuthProvider->expects($this->once())
            ->method('remoteIdpAuthenticate')
            ->with('user', 'password')
            ->willReturn($data);

        $this->expectException(AuthenticationException::class);
        $this->provider->authenticate($token);
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateWithLegacyToken()
    {
        $authData = [
            'status' => 'success',
            'user' => [
                'sub' => 'srn:cluster:idm:eu:0000000001:user:seed_sally_id',
                'id_ext' => [
                    'id' => 'seed_sally_id',
                    'preferred_username' => 'test_name',
                    'address' => [
                        'street_address' => 'test_street',
                    ],
                ],
            ],
        ];
        $token = new IdpUsernamePasswordToken(
            'srn:cluster:idm:eu:0000000001:tenant',
            'user',
            'password',
            AuthProviderBasicManagerBuilder::PROVIDER_KEY_IDP
        );

        $this->oAuthProvider->expects($this->once())
            ->method('remoteIdpAuthenticate')
            ->with('user', 'password')
            ->willReturn($authData);

        $this->userProvider->expects($this->once())
            ->method('loadUserBySrn')
            ->with('srn:cluster:idm:eu:0000000001:user:seed_sally_id')
            ->willReturn($this->user);
        $this->userChecker->expects($this->once())->method('checkPostAuth')->with($this->user);

        $result = $this->provider->authenticate($token);

        $this->assertEquals($this->user, $result->getUser());
        $this->assertEquals('test_name', $this->user->getAttribute('oidc_data')['user_name']);
        $this->assertEquals('test_street', $this->user->getAttribute('oidc_data')['address_street']);
        $this->assertEquals('seed_sally_id', $this->user->getAttribute('oidc_identify')['value']);
    }
}
