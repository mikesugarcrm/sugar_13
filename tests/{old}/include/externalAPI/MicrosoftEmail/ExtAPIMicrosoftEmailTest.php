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
use League\OAuth2\Client\Provider\GenericProvider;

class ExtAPIMicrosoftEmailTest extends TestCase
{
    /**
     * @covers ::authenticate
     */
    public function testAuthenticate()
    {
        $mockAPI = $this->getMockBuilder('ExtAPIMicrosoftEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['saveToken', 'getEmailAddress', 'getUserName'])
            ->getMock();
        $mockAPI->method('saveToken')->willReturn('fake_eapm_id');
        $mockAPI->method('getEmailAddress')->willReturn('fake_email_address');
        $mockAPI->method('getUserName')->willReturn('fake_user_name');

        $mockProvider = $this->getMockBuilder(GenericProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccessToken'])
            ->getMock();
        $mockProvider->method('getAccessToken')->willReturn('fake_access_token');

        SugarTestReflection::setProtectedValue($mockAPI, 'provider', $mockProvider);

        $this->assertEquals(
            [
                'token' => 'fake_access_token',
                'eapmId' => 'fake_eapm_id',
                'emailAddress' => 'fake_email_address',
                'userName' => 'fake_user_name',
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

        $mockAPI = $this->getMockBuilder('ExtAPIMicrosoftEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['getEAPMBean'])
            ->getMock();
        $mockAPI->method('getEAPMBean')->willReturn($mockEapmBean);

        SugarTestReflection::callProtectedMethod($mockAPI, 'saveToken', ['fake_access_token']);
        $this->assertEquals('fake_access_token', $mockEapmBean->api_data);
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
        $mockEapmBean->expects($this->once())->method('mark_deleted');

        $mockAPI = $this->getMockBuilder('ExtAPIMicrosoftEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['getEAPMBean'])
            ->getMock();
        $mockAPI->method('getEAPMBean')->with('fake_eapm_id')->willReturn($mockEapmBean);

        $this->assertEquals(true, $mockAPI->revokeToken('fake_eapm_id'));
    }

    /**
     * @covers ::getAccessToken
     * @dataProvider providerGetAccessToken
     * @param string $apiData The mock api_data for the EAPM bean
     * @param bool $shouldRefresh true if we expect the token to be refreshed; false otherwise
     * @param string $expected the expected access token returned
     */
    public function testGetAccessToken($apiData, $shouldRefresh, $expected)
    {
        $mockEapmBean = $this->getMockBuilder('EAPM')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEapmBean->id = 'fake_eapm_id';
        $mockEapmBean->api_data = $apiData;

        $mockAPI = $this->getMockBuilder('ExtAPIMicrosoftEmail')
            ->disableOriginalConstructor()
            ->onlyMethods(['getEAPMBean', 'refreshToken'])
            ->getMock();
        $mockAPI->method('getEAPMBean')->with('fake_eapm_id')->willReturn($mockEapmBean);
        $mockAPI->method('refreshToken')->with('fake_eapm_id')->willReturn('fake_access_token_refreshed');

        if ($shouldRefresh) {
            $mockAPI->expects($this->once())->method('refreshToken');
        } else {
            $mockAPI->expects($this->never())->method('refreshToken');
        }

        $actual = SugarTestReflection::callProtectedMethod($mockAPI, 'getAccessToken', ['fake_eapm_id']);
        $this->assertEquals($expected, $actual);
    }

    public function providerGetAccessToken()
    {
        return [
            [
                json_encode([
                    'refresh_token' => 'fake_refresh_token',
                    'expires' => time() - 9999,
                    'access_token' => 'fake_access_token',
                ]),
                true,
                'fake_access_token_refreshed',
            ],
            [
                json_encode([
                    'refresh_token' => 'fake_refresh_token',
                    'expires' => time() + 9999,
                    'access_token' => 'fake_access_token',
                ]),
                false,
                'fake_access_token',
            ],
        ];
    }
}
