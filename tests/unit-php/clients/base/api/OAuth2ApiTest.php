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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OAuth2Api
 */
class OAuth2ApiTest extends TestCase
{
    /**
     * @var \ServiceBase | MockObject
     */
    private $serviceBase;

    /**
     * @var \User | MockObject
     */
    private $user;

    /**
     * @var \OAuth2Api | MockObject
     */
    private $api;

    /**
     * @var \SugarOAuth2ServerOIDC | MockObject
     */
    private $oauth2Server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceBase = $this->getMockBuilder(\ServiceBase::class)
            ->onlyMethods(['validatePlatform', 'execute', 'handleException'])
            ->getMock();
        $this->user = $this->createMock(\User::class);
        $this->serviceBase->user = $this->user;

        $this->oauth2Server = $this->createMock(\SugarOAuth2ServerOIDC::class);
        $this->api = $this->getMockBuilder(\OAuth2Api::class)
            ->onlyMethods(['getOAuth2Server'])
            ->getMock();
        $this->api->method('getOAuth2Server')->willReturn($this->oauth2Server);
    }

    /**
     * @return array[]
     */
    public function sudoWithNeedRefreshProvider(): array
    {
        return [
            'refreshNeeded' => [
                'needRefresh' => '1',
                'expectedNeedRefresh' => true,
            ],
            'refreshNotNeeded' => [
                'needRefresh' => '0',
                'expectedNeedRefresh' => false,
            ],
        ];
    }

    /**
     * @param string $needRefresh
     * @param bool $expectedNeedRefresh
     *
     * @dataProvider sudoWithNeedRefreshProvider
     *
     * @covers ::sudo
     */
    public function testSudoWithNeedRefresh(string $needRefresh, bool $expectedNeedRefresh): void
    {
        $args = [
            'user_name' => 'unit_test_user',
            'client_id' => 'rest',
            'platform' => 'test',
            'needRefresh' => $needRefresh,
        ];

        $token = ['access_token' => 'access', 'refresh_token' => 'refresh'];

        $this->user->expects($this->once())->method('isAdmin')->willReturn(true);

        $this->oauth2Server->expects($this->once())
            ->method('getSudoToken')
            ->with('unit_test_user', 'rest', 'test', false, $expectedNeedRefresh)
            ->willReturn($token);

        $result = $this->api->sudo($this->serviceBase, $args);
        $this->assertEquals($token, $result);
    }
}
