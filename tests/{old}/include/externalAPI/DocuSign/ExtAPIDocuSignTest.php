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

use DocuSign\eSign as DocuSign;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ExtAPIDocuSign
 */
class ExtAPIDocuSignTest extends TestCase
{
    /**
     * @var \ExtAPIDocuSign|mixed
     */
    public $extApi;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->extApi = new ExtAPIDocuSign();
    }

    /**
     * @covers ::getClient
     */
    public function testGetClient()
    {
        $clientReceived = $this->extApi->getClient();
        $clientReceivedIsApiClient = $clientReceived instanceof DocuSign\Client\ApiClient;
        $this->assertEquals(true, $clientReceivedIsApiClient);
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticate()
    {
        $oauthToken = new DocuSign\Client\Auth\OAuthToken();
        $oauthToken->setAccessToken('testAccessToken');

        $mockClient = $this->getMockBuilder('DocuSignClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['generateAccessToken'])
            ->getMock();
        $mockClient->method('generateAccessToken')->willReturn([$oauthToken]);

        $userEapmId = '123';
        $mockUserEapmId = $this->getMockBuilder('EAPM')
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();
        $mockUserEapmId->method('save')->willReturn($userEapmId);
        $mockUserEapmId->id = $userEapmId;

        $mockAPI = $this->getMockBuilder('ExtAPIDocuSign')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient', 'getUserEAPM', 'getUser'])
            ->getMock();
        $mockAPI->method('getClient')->willReturn($mockClient);
        $mockAPI->method('getUserEAPM')->willReturn($mockUserEapmId);
        $userAccountName = 'test user account name';
        $mockAPI->method('getUser')->willReturn([
            'account_name' => $userAccountName,
        ]);

        $authenticateRes = $mockAPI->authenticate('testAuthCode');

        $this->assertEquals($mockUserEapmId->id, $authenticateRes['eapmId']);
        $this->assertEquals($oauthToken->getAccessToken(), $authenticateRes['access_token']);
    }

    /**
     * @covers ::getAccessToken
     */
    public function testGetAccessToken()
    {
        $userEapmId = '123';
        $mockUserEapmId = $this->getMockBuilder('EAPM')
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();
        $mockUserEapmId->method('save')->willReturn($userEapmId);
        $mockUserEapmId->id = $userEapmId;

        $eapmBean = BeanFactory::newBean('EAPM');
        $eapmBean->id = 'eapmid';
        $eapmBean->api_data = json_encode([
            'accessToken' => 'val',
        ]);

        $mockAPI = $this->getMockBuilder('ExtAPIDocuSign')
            ->disableOriginalConstructor()
            ->onlyMethods(['getEAPMBean', 'getUserEAPM', 'getUser'])
            ->getMock();
        $mockAPI->method('getEAPMBean')
            ->willReturn($eapmBean);

        $getAccessTokenRes = $mockAPI->getAccessToken('eapmid');
        $this->assertEquals('val', $getAccessTokenRes);
    }

    /**
     * @covers ::refreshAccessTokenFromServer
     */
    public function testRefreshAccessTokenFromServer()
    {
        $oauthToken = new DocuSign\Client\Auth\OAuthToken();
        $oauthToken->setAccessToken('testAccessToken');
        $oauthToken->setExpiresIn('100');

        $mockClient = $this->getMockBuilder('DocuSignClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['refreshAccessToken'])
            ->getMock();
        $mockClient->method('refreshAccessToken')->willReturn([$oauthToken, 200]);

        $eapmBean = BeanFactory::newBean('EAPM');
        $eapmBean->api_data = json_encode([
            'refreshToken' => 'token',
        ]);

        $mockAPI = $this->getMockBuilder('ExtAPIDocuSign')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient'])
            ->getMock();
        $mockAPI->method('getClient')->willReturn($mockClient);

        $refreshRes = SugarTestReflection::callProtectedMethod($mockAPI, 'refreshAccessTokenFromServer', [$eapmBean]);

        $resIsToken = $refreshRes instanceof DocuSign\Client\Auth\OAuthToken;

        $this->assertEquals(true, $resIsToken);
    }
}
