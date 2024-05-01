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

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ExtAPIDropbox
 */
class ExtAPIDropboxTest extends TestCase
{
    /**
     * @var \ExtAPIDropbox
     */
    public $extApi;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->extApi = new ExtAPIDropbox();
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticate()
    {
        $oAuthToken = [
            'access_token' => 'testAccessToken',
            'refresh_token' => 'testRefreshToken',
            'expires_in' => 200,
            'account_id' => 'testAccountId',
        ];
        $client = new DropboxClient();
        $client->setAccessToken($oAuthToken);

        $mockClient = $this->getMockBuilder('DropboxClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['generateAccessToken'])
            ->getMock();
        $mockClient->method('generateAccessToken')->willReturn($oAuthToken);

        $userEapmId = 'testId';
        $mockUserEapmId = $this->getMockBuilder('EAPM')
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();
        $mockUserEapmId->method('save')->willReturn($userEapmId);
        $mockUserEapmId->id = $userEapmId;

        $mockApi = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient', 'getUserEAPM'])
            ->getMock();
        $mockApi->method('getClient')->willReturn($mockClient);
        $mockApi->method('getUserEAPM')->willReturn($mockUserEapmId);

        $authenticateRes = $mockApi->authenticate('testAuthCode');

        $this->assertEquals($mockUserEapmId->id, $authenticateRes['eapmId']);
        $this->assertEquals($client->getAccessToken()['access_token'], $authenticateRes['access_token']);
    }

    /**
     * @covers ::getAccessToken
     */
    public function testRefreshToken()
    {
        $token = [
            'access_token' => 'testAccessToken',
            'refresh_token' => 'testRefreshToken',
        ];

        $client = new DropboxClient();
        $client->setAccessToken($token);

        $refreshedToken = [
            'access_token' => 'newAccessToken',
            'refresh_token' => 'newRefreshToken',
        ];

        $mockClient = $this->getMockBuilder('DropboxClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccessToken', 'getRefreshToken', 'refreshToken'])
            ->getMock();
        $mockClient->method('getAccessToken')->willReturn($refreshedToken);
        $mockClient->method('getRefreshToken')->willReturn($token['refresh_token']);
        $mockClient->method('refreshToken')->willReturn($refreshedToken);

        $mockApi = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['saveToken'])
            ->getMock();
        $mockApi->method('saveToken')->willReturn('eapmId');
        $res = SugarTestReflection::callProtectedMethod($mockApi, 'refreshToken', [$mockClient]);

        $this->assertEquals($refreshedToken, $res);
    }

    /**
     * @covers ::downloadFile
     */
    public function testDownloadFile()
    {
        $fileId = 'testFileId';
        $mockClient = $this->getMockBuilder('DropboxClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccessToken', 'call'])
            ->getMock();
        $mockClient->method('getAccessToken')->willReturn([
            'access_token' => 'testToken',
        ]);
        $mockClient->method('call')->willReturn('testFileContent');


        $mockApi = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient'])
            ->getMock();
        $mockApi->method('getClient')->willReturn($mockClient);

        $res = $mockApi->downloadFile($fileId);

        $this->assertEquals('testFileContent', $res);
    }

    /**
     * @covers ::createFolder
     */
    public function testCreateFolder()
    {
        $data = [
            'name' => 'My files',
        ];
        $mockClient = $this->getMockBuilder('DropboxClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccessToken', 'call'])
            ->getMock();
        $mockClient->method('getAccessToken')->willReturn([
            'access_token' => 'testToken',
        ]);
        $mockClient->method('call')->willReturn(['metadata' => ['id' => 'testId',],]);


        $mockApi = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient'])
            ->getMock();
        $mockApi->method('getClient')->willReturn($mockClient);

        $res = $mockApi->createFolder($data);

        $this->assertEquals(['metadata' => ['id' => 'testId',],], $res);
    }

    /**
     * @covers ::deleteFile
     */
    public function testDeleteFile()
    {
        $data = [
            'name' => 'testDoc.docx',
        ];
        $mockClient = $this->getMockBuilder('DropboxClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccessToken', 'call'])
            ->getMock();
        $mockClient->method('getAccessToken')->willReturn([
            'access_token' => 'testToken',
        ]);
        $mockClient->method('call')->willReturn(['metadata' => ['id' => 'testId',],]);


        $mockApi = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient'])
            ->getMock();
        $mockApi->method('getClient')->willReturn($mockClient);

        $res = $mockApi->deleteFile($data);

        $this->assertEquals(['metadata' => ['id' => 'testId',],], $res);
    }

    /**
     * @covers ::getSharedLink
     */
    public function testgetSharedLink()
    {
        $data = [
            'name' => 'My files',
        ];
        $mockClient = $this->getMockBuilder('DropboxClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccessToken', 'call'])
            ->getMock();
        $mockClient->method('getAccessToken')->willReturn([
            'access_token' => 'testToken',
        ]);
        $mockClient->method('call')->willReturn([
            'id' => 'testId',
        ]);


        $mockApi = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient'])
            ->getMock();
        $mockApi->method('getClient')->willReturn($mockClient);

        $res = $mockApi->getSharedLink($data);

        $this->assertEquals(['id' => 'testId',], $res);
    }

    /**
     * @covers ::uploadFile
     */
    public function testuploadFile()
    {
        $data = [
            'name' => 'My files',
        ];
        $content = 'testContent';
        $mockClient = $this->getMockBuilder('DropboxClient')
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccessToken', 'call'])
            ->getMock();
        $mockClient->method('getAccessToken')->willReturn([
            'access_token' => 'testToken',
        ]);
        $mockClient->method('call')->willReturn(['id' => 'testId',]);


        $mockApi = $this->getMockBuilder('ExtAPIDropbox')
            ->disableOriginalConstructor()
            ->onlyMethods(['getClient'])
            ->getMock();
        $mockApi->method('getClient')->willReturn($mockClient);

        $res = $mockApi->uploadFile($content, $data);

        $this->assertEquals(['id' => 'testId',], $res);
    }
}
