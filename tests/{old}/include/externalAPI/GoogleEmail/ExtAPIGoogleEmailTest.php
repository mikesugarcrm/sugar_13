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

use Google\Service\Gmail;
use PHPUnit\Framework\TestCase;

class ExtAPIGoogleEmailTest extends TestCase
{
    private const FAKE_ACCESS_TOKEN = [
        'access_token' => 'fake_access_token',
        'token_type' => 'Bearer',
        'expires_in' => 1234,
        'created' => 1624448365,
    ];

    /**
     * @covers ::getClient
     */
    public function testGetClient()
    {
        $mockAPI = $this->getMockBuilder('ExtAPIGoogleEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['getGoogleOauth2Config'])
            ->getMock();

        $mockConnectorSettings = [
            'name' => 'Google',
            'eapm' => [
                'enabled' => true,
                'only' => true,
            ],
            'order' => 12,
            'properties' => [
                'oauth2_client_id' => 'fake_client_id',
                'oauth2_client_secret' => 'fake_client_secret',
            ],
            'redirect_uri' => 'https://www.fake.com',
        ];

        $mockAPI->method('getGoogleOauth2Config')
            ->willReturn($mockConnectorSettings);

        $client = $mockAPI->getClient();
        $this->assertContains(Gmail::MAIL_GOOGLE_COM, $client->getScopes());
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticate()
    {
        $mockAPI = $this->getMockBuilder('ExtAPIGoogleEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient', 'saveToken', 'getEmailAddress'])
            ->getMock();
        $mockAPI->method('saveToken')->willReturn('fake_eapm_id');
        $mockAPI->method('getEmailAddress')->willReturn('fake_email_address');

        $mockClient = $this->getMockBuilder(\Google\Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAccessTokenWithAuthCode', 'getAccessToken'])
            ->getMock();
        $mockClient->method('getAccessToken')->willReturn(self::FAKE_ACCESS_TOKEN);

        $mockAPI->method('getClient')->willReturn($mockClient);

        $this->assertEquals(
            [
                'token' => json_encode(self::FAKE_ACCESS_TOKEN),
                'eapmId' => 'fake_eapm_id',
                'emailAddress' => 'fake_email_address',
                'userName' => 'fake_email_address',
            ],
            $mockAPI->authenticate('fake_authorization_code')
        );
    }

    /**
     * @covers ::saveToken
     */
    public function testSaveToken()
    {
        $mockEapmBean = $this->getMockBuilder('EAPM')
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();
        $mockEapmBean->expects($this->once())->method('save');

        $mockAPI = $this->getMockBuilder('ExtAPIGoogleEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['getEAPMBean'])
            ->getMock();
        $mockAPI->method('getEAPMBean')->willReturn($mockEapmBean);

        SugarTestReflection::callProtectedMethod($mockAPI, 'saveToken', [self::FAKE_ACCESS_TOKEN]);
        $this->assertEquals(json_encode(self::FAKE_ACCESS_TOKEN), $mockEapmBean->api_data);
    }

    /**
     * @covers ::revokeToken
     */
    public function testRevokeToken()
    {
        $mockEapmBean = $this->getMockBuilder('EAPM')
            ->disableOriginalConstructor()
            ->onlyMethods(['mark_deleted'])
            ->getMock();
        $mockEapmBean->id = 'fake_eapm_id';
        $mockEapmBean->api_data = 'fake_access_token';
        $mockEapmBean->expects($this->once())->method('mark_deleted');

        $mockClient = $this->getMockBuilder(\Google\Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setAccessToken', 'revokeToken'])
            ->getMock();
        $mockClient->expects($this->once())->method('setAccessToken')->with('fake_access_token');

        $mockAPI = $this->getMockBuilder('ExtAPIGoogleEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['getEAPMBean', 'getClient'])
            ->getMock();
        $mockAPI->method('getEAPMBean')->willReturn($mockEapmBean);
        $mockAPI->method('getClient')->willReturn($mockClient);

        $this->assertEquals(true, $mockAPI->revokeToken('fake_eapm_id'));
    }
}
