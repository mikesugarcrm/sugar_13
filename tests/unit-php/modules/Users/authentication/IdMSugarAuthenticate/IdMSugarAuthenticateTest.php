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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Users\authentication\IdMSugarAuthenticate;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \IdMSugarAuthenticate
 */
class IdMSugarAuthenticateTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $idmLocalAuth;

    /**
     * @var MockObject
     */
    protected $authProviderBuilder;

    /**
     * @var MockObject
     */
    protected $authProviderManager;

    /**
     * @var MockObject
     */
    protected $token;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->idmLocalAuth = $this->getMockBuilder(\IdMSugarAuthenticate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthProviderBuilder'])
            ->getMock();

        $this->authProviderBuilder = $this->createMock(AuthProviderManagerBuilder::class);

        $this->idmLocalAuth->expects($this->once())
            ->method('getAuthProviderBuilder')
            ->willReturn($this->authProviderBuilder);

        $this->authProviderManager = $this->createMock(AuthenticationProviderManager::class);

        $this->authProviderBuilder->expects($this->once())
            ->method('buildAuthProviders')
            ->willReturn($this->authProviderManager);

        $this->token = $this->createMock(UsernamePasswordToken::class);
    }

    /**
     * @covers IdMSugarAuthenticate::loginAuthenticate()
     */
    public function testLoginAuthenticateSuccess()
    {
        $this->authProviderManager->expects($this->once())
            ->method('authenticate')
            ->with($this->callback(function ($token) {
                $this->assertInstanceOf(UsernamePasswordToken::class, $token);
                $this->assertEquals('test', $token->getAttribute('rawPassword'));
                $this->assertFalse($token->getAttribute('isPasswordEncrypted'));
                return true;
            }))
            ->willReturn($this->token);

        $this->token->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->assertTrue(
            $this->idmLocalAuth->loginAuthenticate('test', 'test', false, ['passwordEncrypted' => false])
        );
    }
}
