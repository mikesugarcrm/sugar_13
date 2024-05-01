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

namespace Sugarcrm\SugarcrmTests\IdentityProvider\Authentication;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

class ConfigTest extends TestCase
{
    /**
     * @var mixed
     */
    public $currentUser;
    /**
     * @var \SugarConfig
     */
    protected $config;

    /** @var array */
    protected $sugarConfig;

    /** @var array */
    protected $currentIdmConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->currentIdmConfig = [];
        $this->currentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = \SugarTestUserUtilities::createAnonymousUser(false, true);
        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;
        $this->config = \SugarConfig::getInstance();
        $this->config->clearCache();
        $admin = \Administration::getSettings(Config::IDM_MODE_KEY, true);
        foreach ($admin->settings as $key => $value) {
            if (strpos($key, Config::IDM_MODE_KEY) === 0) {
                $key = str_replace(Config::IDM_MODE_KEY . '_', '', $key);
                $this->currentIdmConfig[$key] = $value;
            }
        }
        $this->cleanIdmModeData();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->sugarConfig;
        $this->config->clearCache();
        $this->cleanIdmModeData();
        $admin = \Administration::getSettings(Config::IDM_MODE_KEY, true);
        foreach ($this->currentIdmConfig as $key => $value) {
            $admin->saveSetting(Config::IDM_MODE_KEY, $key, $value);
        }

        $GLOBALS['current_user'] = $this->currentUser;
    }

    /**
     * Provides data for testGetIDMModeConfig
     *
     * @return array
     */
    public function getIDMModeConfigProvider()
    {
        return [
            'sugarConfigEmpty' => [
                'sugarConfig' => [
                    'site_url' => 'http://site.url/',
                ],
                'expected' => [],
            ],
            'IdMModeDisabled' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => false,
                    ],
                ],
                'expected' => [],
            ],
            'httpClientEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 86400,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'httpClientNotEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'http_client' => [
                            'retry_count' => 5,
                            'delay_strategy' => 'exponential',
                        ],
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'retry_count' => 5,
                        'delay_strategy' => 'exponential',
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 86400,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'cloudConsoleRoutesAreNotEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'http_client' => [
                            'retry_count' => 5,
                            'delay_strategy' => 'exponential',
                        ],
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                        'cloudConsoleRoutes' => [
                            'userManagement' => 'management/users',
                            'passwordManagement' => 'management/password',
                        ],
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'retry_count' => 5,
                        'delay_strategy' => 'exponential',
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [
                        'userManagement' => 'management/users',
                        'passwordManagement' => 'management/password',
                    ],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 86400,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'cachingEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'caching' => [],
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 86400,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'cachingNotEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'caching' => [
                            'ttl' => [
                                'introspectToken' => 20,
                                'discovery' => 40,
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 20,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 40,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'crmOAuthScopeNotEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'crmOAuthScope' => 'https://apis.sugarcrm.com/auth/crm',
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 86400,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => 'https://apis.sugarcrm.com/auth/crm',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'mangoScopesNotEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'crmOAuthScope' => '',
                        'requestedOAuthScopes' => [
                            'offline',
                            'https://apis.sugarcrm.com/auth/crm',
                            'profile',
                            'email',
                            'address',
                            'phone',
                        ],
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 86400,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [
                        'offline',
                        'https://apis.sugarcrm.com/auth/crm',
                        'profile',
                        'email',
                        'address',
                        'phone',
                    ],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'customSAsEnabled' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'allowedSAs' => [
                            'srn:cloud:iam:us-west-2:9999999999:sa:user-sync',
                            'srn:cloud:iam:us-west-2:1234567890:sa:custom-sa',
                        ],
                    ],
                ],
                'expected' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url/?module=Users&action=OAuth2CodeExchange',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlRevokeToken' => 'http://sts.sugarcrm.local/oauth2/revoke',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'headers' => [
                            'User-Agent' => 'http://site.url/',
                        ],
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 86400,
                            'authz' => 900,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [
                        'srn:cloud:iam:us-west-2:9999999999:sa:user-sync',
                        'srn:cloud:iam:us-west-2:1234567890:sa:custom-sa',
                    ],
                    'serviceAccountPermissions' => [
                        'srn:cluster:iam:::permission:crm.sa',
                        'srn:cluster:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $sugarConfig
     * @param $expected
     *
     * @dataProvider getIDMModeConfigProvider
     */
    public function testGetIDMModeConfig($sugarConfig, $expected)
    {
        $GLOBALS['sugar_config']['site_url'] = 'http://site.url/';
        $config = new Config(\SugarConfig::getInstance());
        if (!empty($sugarConfig['idm_mode']['enabled'])) {
            $config->setIDMMode($sugarConfig['idm_mode'], false);
        } elseif (!empty($sugarConfig['idm_mode'])) {
            foreach ($sugarConfig['idm_mode'] as $key => $value) {
                $admin = \Administration::getSettings(Config::IDM_MODE_KEY, true);
                $admin->saveSetting(Config::IDM_MODE_KEY, $key, $value);
            }
        }

        $this->assertEquals($expected, $config->getIDMModeConfig());
    }

    /**
     * Provides data for testIsIDMModeEnabled
     *
     * @return array
     */
    public function isIDMModeEnabledProvider()
    {
        return [
            'sugarConfigEmpty' => [
                'sugarConfig' => [
                    'site_url' => 'http://site.url/',
                ],
                'expected' => false,
            ],
            'enabledTrue' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                    ],
                    'site_url' => 'http://site.url/',
                ],
                'expected' => true,
            ],
            'sugarConfigNotEmpty' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                    ],
                    'site_url' => 'http://site.url/',
                ],
                'expected' => false,
            ],
        ];
    }

    protected function cleanIdmModeData()
    {
        $GLOBALS['db']->query("DELETE FROM config WHERE category = 'idm_mode'");
        \Administration::getSettings(Config::IDM_MODE_KEY, true);
    }
}
