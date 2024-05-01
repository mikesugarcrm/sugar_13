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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Users\views;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\Security\InputValidation\Request;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @coversDefaultClass \UsersViewImpersonation
 */
class UsersViewImpersonationTest extends TestCase
{
    /**
     * @var MockObject | \UsersViewImpersonation
     */
    private $viewMock;

    /**
     * @var MockObject | Request
     */
    private $request;

    public function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->viewMock = $this->getMockBuilder(\UsersViewImpersonation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['introspectAccessToken'])
            ->getMock();

        $issuer = $this->createMock(\User::class);
        $issuer->id = '12345';

        TestReflection::setProtectedValue($this->viewMock, 'request', $this->request);
        TestReflection::setProtectedValue($this->viewMock, 'issuer', $issuer);

        parent::setUp();
    }

    public function checkImpersonatedUserRequirementsProvider(): array
    {
        $introspectionWithExpiredIat = new IntrospectToken('token', 'tenant', '');
        $introspectionWithExpiredIat->setAttribute('iat', time() - 40);

        $introspectionWithoutUser = new IntrospectToken('token', 'tenant', '');
        $introspectionWithoutUser->setAttribute('iat', time());

        $user1 = new User();
        $introspectionWithUserNoSudoer = new IntrospectToken('token', 'tenant', '');
        $introspectionWithUserNoSudoer->setAttribute('iat', time());
        $introspectionWithUserNoSudoer->setUser($user1);

        $user2 = new User();
        $introspectionWithUserWithSudoer = new IntrospectToken('token', 'tenant', '');
        $introspectionWithUserWithSudoer->setAttribute('iat', time());
        $user2->setAttribute('sudoer', 'srn:dev::1234567890:user:1234');
        $introspectionWithUserWithSudoer->setUser($user2);

        $user3 = new User();
        $introspectionWithUserTheSameAsIssuer = new IntrospectToken('token', 'tenant', '');
        $introspectionWithUserTheSameAsIssuer->setAttribute('iat', time());
        $user3->setAttribute('sudoer', 'srn:dev::1234567890:user:12345');
        $introspectionWithUserTheSameAsIssuer->setUser($user3);
        $sugarUser = $this->createMock(\User::class);
        $sugarUser->id = '12345';
        $user3->setSugarUser($sugarUser);

        $user4 = new User();
        $introspectionWithSudoerDifferentThanIssuer = new IntrospectToken('token', 'tenant', '');
        $introspectionWithSudoerDifferentThanIssuer->setAttribute('iat', time());
        $user4->setAttribute('sudoer', 'srn:dev:iam::1234567890:user:123456');
        $introspectionWithSudoerDifferentThanIssuer->setUser($user4);
        $sugarUser = $this->createMock(\User::class);
        $sugarUser->id = '123456';
        $user4->setSugarUser($sugarUser);

        $user5 = new User();
        $introspectionWithInvalidSudoerSrn = new IntrospectToken('token', 'tenant', '');
        $introspectionWithInvalidSudoerSrn->setAttribute('iat', time());
        $user5->setAttribute('sudoer', 'srn:dev:iam::1234567890:user');
        $introspectionWithInvalidSudoerSrn->setUser($user5);
        $sugarUser = $this->createMock(\User::class);
        $sugarUser->id = '123456';
        $user5->setSugarUser($sugarUser);


        return [
            'no token in request' => [
                'token' => '',
                'introspect' => null,
            ],
            'no introspection result' => [
                'token' => '123',
                'introspect' => null,
            ],
            'token with expired iat' => [
                'token' => '123',
                'introspect' => $introspectionWithExpiredIat,
            ],
            'token without user' => [
                'token' => '123',
                'introspect' => $introspectionWithoutUser,
            ],
            'token without sudoer' => [
                'token' => '123',
                'introspect' => $introspectionWithUserNoSudoer,
            ],
            'token with sudoer no sugar user' => [
                'token' => '123',
                'introspect' => $introspectionWithUserWithSudoer,
            ],
            'token with sudoer sugar user the same as issuer' => [
                'token' => '123',
                'introspect' => $introspectionWithUserTheSameAsIssuer,
            ],
            'token with sudoer different than issuer' => [
                'token' => '123',
                'introspect' => $introspectionWithSudoerDifferentThanIssuer,
            ],
            'token with invalid sudoer srn' => [
                'token' => '123',
                'introspect' => $introspectionWithInvalidSudoerSrn,
            ],
        ];
    }

    /**
     * @param string $token
     * @param TokenInterface|null $introspectResult
     * @return void
     *
     * @dataProvider checkImpersonatedUserRequirementsProvider
     * @covers ::isImpersonationAllowed
     */
    public function testIsImpersonationAllowedWithErrors(string $token, ?TokenInterface $introspectResult): void
    {
        $this->request->expects($this->once())
            ->method('getValidInputPost')
            ->with('access_token')
            ->willReturn($token);

        $this->viewMock->method('introspectAccessToken')->with($token)->willReturn($introspectResult);

        $result = TestReflection::callProtectedMethod($this->viewMock, 'isImpersonationAllowed');
        $this->assertFalse($result);
    }

    /**
     * @return void
     *
     * @covers ::isImpersonationAllowed
     */
    public function testIsImpersonationAllowed(): void
    {
        $token = '123';

        $user = new User();
        $introspectResult = new IntrospectToken('token', 'tenant', '');
        $introspectResult->setAttribute('iat', time());
        $user->setAttribute('sudoer', 'srn:dev:iam::1234567890:user:12345');
        $introspectResult->setUser($user);
        $sugarUser = $this->createMock(\User::class);
        $sugarUser->id = '123456';
        $user->setSugarUser($sugarUser);

        $this->request->expects($this->once())
            ->method('getValidInputPost')
            ->with('access_token')
            ->willReturn($token);

        $this->viewMock->method('introspectAccessToken')->with($token)->willReturn($introspectResult);

        $result = TestReflection::callProtectedMethod($this->viewMock, 'isImpersonationAllowed');
        $this->assertTrue($result);
    }
}
