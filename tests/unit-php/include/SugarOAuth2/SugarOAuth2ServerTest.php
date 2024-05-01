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

use PHPUnit\Framework\TestCase;
use SugarApiExceptionNeedLogin;
use SugarApiExceptionNotFound;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SugarOAuth2Server
 */
class SugarOAuth2ServerTest extends TestCase
{
    /**
     * @var \SugarConfig
     */
    protected $config;

    /** @var array */
    protected $sugarConfig;

    /**
     * @var \User
     */
    protected $currentUser = null;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;

        $this->config = \SugarConfig::getInstance();
        $this->config->clearCache();

        if (!empty($GLOBALS['current_user'])) {
            $this->currentUser = $GLOBALS['current_user'];
        }

        $GLOBALS['current_user'] = $this->createMock(\User::class);
    }

    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->sugarConfig;
        $this->config->clearCache();

        $GLOBALS['current_user'] = $this->currentUser;
    }

    /**
     * Provides data for testGetOAuth2Server
     * @return array
     */
    public function getOAuth2ServerProvider()
    {
        return [
            'oldOAuthServer' => [
                'idmMode' => [],
                'platform' => 'base',
                'expectedServerClass' => \SugarOAuth2Server::class,
                'expectedStorageClass' => \SugarOAuth2Storage::class,
            ],
            'oidcOAuthServer' => [
                'idmMode' => [
                    'enabled' => true,
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://localhost:4444',
                    'idpUrl' => 'http://sugar.dolbik.dev/idm205idp/web/',
                    'stsKeySetId' => 'testkey2',
                ],
                'platform' => 'base',
                'expectedServerClass' => \SugarOAuth2ServerOIDC::class,
                'expectedStorageClass' => \SugarOAuth2StorageOIDC::class,
            ],
            'portalPlatform' => [
                'idmMode' => [
                    'enabled' => true,
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://localhost:4444',
                    'idpUrl' => 'http://sugar.dolbik.dev/idm205idp/web/',
                    'stsKeySetId' => 'testkey2',
                ],
                'platform' => 'portal',
                'expectedServerClass' => \SugarOAuth2Server::class,
                'expectedStorageClass' => \SugarOAuth2Storage::class,
            ],
        ];
    }

    /**
     * @param array $idmMode
     * @param string $platform
     * @param $expectedServerClass
     * @param $expectedStorageClass
     *
     * @dataProvider getOAuth2ServerProvider
     * @covers ::getOAuth2Server
     */
    public function testGetOAuth2Server(array $idmMode, string $platform, $expectedServerClass, $expectedStorageClass)
    {
        $configMock = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([\SugarConfig::getInstance()])
            ->setMethods(['isIDMModeEnabled'])
            ->getMock();
        $configMock->expects($this->any())
            ->method('isIDMModeEnabled')
            ->willReturn($idmMode['enabled'] ?? false);

        $oAuthServer = SugarOAuth2ServerMock::getOAuth2Server($platform, $configMock);
        $this->assertInstanceOf($expectedServerClass, $oAuthServer);
        $this->assertInstanceOf(
            $expectedStorageClass,
            TestReflection::getProtectedValue($oAuthServer, 'storage')
        );
    }

    /**
     * @covers ::getSudoToken
     */
    public function testGetSudoTokenStorageThrowException()
    {
        $storage = $this->createMock(\SugarOAuth2Storage::class);
        $ouath2Server = new \SugarOAuth2Server($storage, []);
        $storage->expects($this->once())
            ->method('loadUserFromName')
            ->with('testUser')
            ->willThrowException(new SugarApiExceptionNeedLogin());

        $this->expectException(SugarApiExceptionNotFound::class);
        $ouath2Server->getSudoToken('testUser', 'testClient', 'base');
    }

    /**
     * @covers ::getSudoToken
     */
    public function testGetSudoTokenStorageReturnNull()
    {
        $storage = $this->createMock(\SugarOAuth2Storage::class);
        $ouath2Server = new \SugarOAuth2Server($storage, []);
        $storage->expects($this->once())
            ->method('loadUserFromName')
            ->with('testUser')
            ->willReturn(null);

        $this->expectException(SugarApiExceptionNotFound::class);
        $ouath2Server->getSudoToken('testUser', 'testClient', 'base');
    }
}

/**
 * Mock for SugarOAuth2Server to prevent caching
 */
class SugarOAuth2ServerMock extends \SugarOAuth2Server
{
    /**
     * @param string $platform
     * @param Config $idpConfig
     *
     * @return \SugarOAuth2Server
     */
    public static function getOAuth2Server($platform = null, $idpConfig = null)
    {
        parent::$currentOAuth2Server = null;
        return parent::getOAuth2Server($platform, $idpConfig);
    }
}
