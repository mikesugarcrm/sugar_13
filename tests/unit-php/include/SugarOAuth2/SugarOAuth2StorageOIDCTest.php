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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarOAuth2;

use AuthenticationController;
use PHPUnit\Framework\TestCase;
use SugarApiExceptionNeedLogin;
use SugarOAuth2StorageOIDC;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

require_once 'include/utils.php';
require_once 'include/SugarCache/SugarCache.php';

/**
 * @coversDefaultClass \SugarOAuth2StorageOIDC
 */
class SugarOAuth2StorageOIDCTest extends TestCase
{
    /**
     * @var SugarOAuth2StorageOIDC
     */
    protected $storageMock;

    /**
     * @var AuthenticationController
     */
    protected $authController;

    protected function setUp(): void
    {
        $this->authController = $this->createMock(AuthenticationController::class);

        $this->storageMock = $this->getMockBuilder(SugarOAuth2StorageOIDC::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthController', 'getTranslatedMessage', 'getPlatformStore', 'getClientDetails'])
            ->getMock();

        $this->storageMock->method('getAuthController')->willReturn($this->authController);

        $GLOBALS['log'] = $this->getMockBuilder(\LoggerManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
    }

    public function providerCheckUserCredentialsFailedLogin()
    {
        return [
            [false],
            [[]],
        ];
    }

    /**
     * @covers ::checkUserCredentials
     *
     * @dataProvider providerCheckUserCredentialsFailedLogin
     * @param mixed $loginResult
     */
    public function testCheckUserCredentialsFailedLogin($loginResult)
    {
        $this->authController->method('login')
            ->with(
                'user',
                'password',
                ['passwordEncrypted' => false, 'noRedirect' => true, 'noHooks' => true]
            )
            ->willReturn($loginResult);

        $this->expectException(SugarApiExceptionNeedLogin::class);
        $this->storageMock->checkUserCredentials('sugar', 'user', 'password');
    }

    /**
     * @covers ::checkUserCredentials
     */
    public function testCheckUserCredentialsSuccessfulLogin()
    {
        $loginResult = [
            'user_id' => '123',
            'scope' => null,
        ];
        $this->authController->method('login')
            ->with(
                'user',
                'password',
                ['passwordEncrypted' => false, 'noRedirect' => true, 'noHooks' => true]
            )
            ->willReturn($loginResult);
        $this->assertEquals($loginResult, $this->storageMock->checkUserCredentials('sugar', 'user', 'password'));
    }

    /**
     * @covers ::checkUserCredentials
     */
    public function testCheckUserCredentialsLoginException()
    {
        $this->authController->method('login')
            ->with(
                'user',
                'password',
                ['passwordEncrypted' => false, 'noRedirect' => true, 'noHooks' => true]
            )
            ->willThrowException(new AuthenticationException());

        $this->expectException(SugarApiExceptionNeedLogin::class);
        $this->storageMock->checkUserCredentials('sugar', 'user', 'password');
    }

    /**
     * @covers ::checkUserCredentials
     */
    public function testCheckUserCredentialsPortalStore(): void
    {
        $this->storageMock->setPlatform('portal');

        $platformStore = $this->createMock(\SugarOAuth2StoragePortal::class);

        $this->storageMock->expects($this->once())->method('getPlatformStore')->willReturn($platformStore);

        $platformStore->expects($this->once())
            ->method('checkUserCredentials')
            ->with($this->storageMock, 'sugar', 'user', 'password');

        $this->storageMock->checkUserCredentials('sugar', 'user', 'password');
    }

    /**
     * @return array
     */
    public function hasPortalStoreProvider(): array
    {
        return [
            'portalStoreClass' => [
                'platform' => 'portal',
                'clientInfo' => null,
                'expectedResult' => true,
            ],
            'noPortalStoreClass' => [
                'platform' => 'base',
                'clientInfo' => null,
                'expectedResult' => false,
            ],
            'noPortalStoreClassValidPortalClientInfo' => [
                'platform' => 'base',
                'clientInfo' => [
                    'client_type' => 'support_portal',
                ],
                'expectedResult' => true,
            ],
            'noPortalStoreClassInvalidPortalClientInfo' => [
                'platform' => 'base',
                'clientInfo' => [
                    'client_type' => 'support_base',
                ],
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @param $platform
     * @param $clientInfo
     * @param $expectedResult
     *
     * @covers ::hasPortalStore
     *
     * @dataProvider hasPortalStoreProvider
     */
    public function testHasPortalStoreWithPlatformStoreClass($platform, $clientInfo, $expectedResult): void
    {
        $this->storageMock->setPlatform($platform);
        $this->storageMock->method('getClientDetails')->willReturn($clientInfo);
        $this->assertEquals($expectedResult, $this->storageMock->hasPortalStore('client_id'));
    }
}
