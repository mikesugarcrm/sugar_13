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

class OAuth2ApiTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        SugarTestHelper::tearDown();
    }

    public function testSudo()
    {
        $stdArgs = [
            'user_name' => 'unit_test_user',
            'client_id' => 'sugar',
            'platform' => 'base',
        ];

        // Non-admin attempting to sudo
        $service = $this->createMock('RestService');
        $service->user = $this->createPartialMock('User', ['isAdmin']);
        $service->user->expects($this->once())
            ->method('isAdmin')
            ->will($this->returnValue(false));

        $api = $this->createPartialMock('OAuth2Api', ['getOAuth2Server']);
        $api->expects($this->never())
            ->method('getOAuth2Server');

        $caughtException = false;
        try {
            $api->sudo($service, $stdArgs);
        } catch (SugarApiExceptionNotAuthorized $e) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException, 'Did not deny a non-admin user from sudoing');

        // Admin user that is already being sudo-ed
        $service->user = $this->createPartialMock('User', ['isAdmin']);
        $service->user->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));
        $_SESSION['sudo_for'] = 'other_unit_test_user';

        $caughtException = false;
        try {
            $api->sudo($service, $stdArgs);
        } catch (SugarApiExceptionNotAuthorized $e) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException, 'Did not deny an already sudoed user from sudoing');
        $_SESSION = [];

        // Deny the oauth2 request
        $oauth2 = $this->createMock(SugarOAuth2Server::class);
        $oauth2->expects($this->once())
            ->method('getSudoToken')
            ->will($this->returnValue(false));

        $api = $this->createPartialMock('OAuth2Api', ['getOAuth2Server']);
        $api->expects($this->once())
            ->method('getOAuth2Server')
            ->will($this->returnValue($oauth2));

        $caughtException = false;
        try {
            $api->sudo($service, $stdArgs);
        } catch (SugarApiExceptionRequestMethodFailure $e) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException, 'Did not fail when the token was false');

        // Try a successful run
        $oauth2 = $this->createMock(SugarOAuth2Server::class);
        $oauth2->expects($this->once())
            ->method('getSudoToken')
            ->will($this->returnValue(['access_token' => 'i_am_only_a_test']));

        $api = $this->createPartialMock('OAuth2Api', ['getOAuth2Server']);
        $api->expects($this->once())
            ->method('getOAuth2Server')
            ->will($this->returnValue($oauth2));

        $ret = $api->sudo($service, $stdArgs);
    }

    /**
     * @param array $info
     * @param boolean $expected
     * @param string $message
     *
     * @dataProvider clientVersionProvider
     */
    public function testIsSupportedClientVersion(array $info, $expected, $message)
    {
        $service = $this->createMock('RestService');
        $service->api_settings = [
            'minClientVersions' => [
                'the-client' => '1.2.0',
            ],
        ];
        $api = new OAuth2Api();

        $ret = $api->isSupportedClientVersion($service, $info);
        $this->assertEquals($expected, $ret, $message);
    }

    public function testLogoutApi()
    {
        $serviceBase = SugarTestRestUtilities::getRestServiceMock();

        $oauth2 = $this->createMock(SugarOAuth2Server::class);
        $oauth2->expects($this->once())
            ->method('unsetRefreshToken')
            ->with($this->equalTo('test_refresh'))
            ->will($this->returnValue(true));

        $api = $this->createPartialMock('OAuth2Api', ['getOAuth2Server']);
        $api->expects($this->once())
            ->method('getOAuth2Server')
            ->will($this->returnValue($oauth2));

        // ignore the warning triggered by setcookie()
        $this->iniSet('error_reporting', error_reporting() & ~E_WARNING);

        $api->logout($serviceBase, ['token' => 'test_token', 'refresh_token' => 'test_refresh']);
    }

    public function testLogoutApiWithOutRefreshToken()
    {
        $service = $this->createMock('RestService');
        $service
            ->expects($this->once())
            ->method('grabToken')
            ->will($this->returnValue('test_token'));

        $oauth2 = $this->createMock(SugarOAuth2Server::class);
        $oauth2->expects($this->once())
            ->method('unsetAccessToken')
            ->with($this->equalTo('test_token'))
            ->will($this->returnValue(true));

        $api = $this->createPartialMock('OAuth2Api', ['getOAuth2Server']);
        $api->expects($this->once())
            ->method('getOAuth2Server')
            ->will($this->returnValue($oauth2));

        // ignore the warning triggered by setcookie()
        $this->iniSet('error_reporting', error_reporting() & ~E_WARNING);

        $api->logout($service, []);
    }

    public static function clientVersionProvider()
    {
        return [
            [
                [
                    'some' => 'things',
                    'keep' => 'happening',
                ],
                true,
                'Check client version was pleased by the lack of version',
            ],
            [
                [
                    'client_info' => [
                        'app' => [
                            'name' => 'the-client',
                            'version' => '1.0.1',
                        ],
                    ],
                ],
                false,
                'Returned true on an out of date client',
            ],
            [
                [
                    'client_info' => [
                        'app' => [
                            'name' => 'the-client',
                            'version' => '1.2.0',
                        ],
                    ],
                ],
                true,
                'Returned false on an up to date client',
            ],
        ];
    }
}
