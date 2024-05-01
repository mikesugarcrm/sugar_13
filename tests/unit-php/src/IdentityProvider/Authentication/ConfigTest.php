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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication;

use OneLogin\Saml2\Constants;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var \SugarConfig
     */
    protected $config;

    /** @var array */
    protected $sugarConfig;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;
        $this->config = \SugarConfig::getInstance();
        $this->config->clearCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->sugarConfig;
        $this->config->clearCache();
    }

    /**
     * @covers ::get
     *
     * @dataProvider getProvider
     */
    public function testGet($key, $value, $isIdmAttriubute, $idmSettings, $default, $expected)
    {
        if (!$isIdmAttriubute) {
            $GLOBALS['sugar_config'][$key] = $value;
        }
        $configMock = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([\SugarConfig::getInstance()])
            ->setMethods(['getIdmSettings'])
            ->getMock();

        $idmSettingsMock = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $idmSettingsMock->settings = $idmSettings;

        $configMock->expects($this->any())
            ->method('getIdmSettings')
            ->willReturn($idmSettingsMock);

        $this->assertEquals($expected, $configMock->get($key, $default));
    }

    public function getProvider()
    {
        return [
            'non idm attribute' => [
                'non-idm-attribute-key',
                'non-idm-attribute-value',
                false,
                [],
                null,
                'non-idm-attribute-value',
            ],
            'idm attribute, key exists' => [
                'idm_mode.idm-key',
                '',
                true,
                ['idm_mode_idm-key' => 'string value'],
                null,
                'string value',
            ],
            'idm attribute, key does not exist' => [
                'idm_mode.idm-key',
                'string value',
                true,
                ['idm_mode_randon-key' => 'string value'],
                null,
                null,
            ],
            'idm attribute array value, key exists' => [
                'idm_mode.http_client',
                '',
                true,
                ['idm_mode_http_client' => ['cleint1' => 'https://sugarcrm.com', 'cleint2' => 'google.com']],
                null,
                ['cleint1' => 'https://sugarcrm.com', 'cleint2' => 'google.com'],
            ],
        ];
    }

    public function getSAMLConfigDataProvider()
    {
        return [
            'no override in config' => [
                [
                    'default' => 'config',
                ],
                ['default' => 'config'],
                [],
            ],
            'saml config provided' => [
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' =>
                                'config_site_url/index.php?platform%3Dbase%26module%3DUsers%26action%3DAuthenticate',
                        ],
                    ],
                ],
                ['default' => 'config'],
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' =>
                                'config_site_url/index.php?platform%3Dbase%26module%3DUsers%26action%3DAuthenticate',
                        ],
                    ],
                ],
            ],
            'saml config and sugar custom settings provided' => [
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'foo' => 'bar',
                        'sugarCustom' => [
                            'useXML' => true,
                            'id' => 'first_name',
                        ],
                    ],
                ],
                ['default' => 'config'],
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'foo' => 'bar',
                        'sugarCustom' => [
                            'useXML' => true,
                            'id' => 'first_name',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $expectedConfig
     * @param array $defaultConfig
     * @param array $configValues
     *
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigDataProvider
     */
    public function testGetSAMLConfig(
        array $expectedConfig,
        array $defaultConfig,
        array $configValues
    ) {

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getSAMLDefaultConfig'])
            ->getMock();
        $config->expects($this->any())
            ->method('get')
            ->withConsecutive(
                ['SAML', []]
            )
            ->willReturnOnConsecutiveCalls(
                $configValues
            );
        $config->expects($this->once())
            ->method('getSAMLDefaultConfig')
            ->willReturn($defaultConfig);
        $samlConfig = $config->getSAMLConfig();
        $this->assertEquals($expectedConfig, $samlConfig);
    }

    /**
     * Checks default config when it created from SugarCRM config values.
     *
     * @covers ::getSAMLConfig
     */
    public function testGetSAMLDefaultConfig()
    {
        $expectedConfig = [
            'strict' => false,
            'debug' => false,
            'sp' => [
                'entityId' => 'SAML_issuer',
                'assertionConsumerService' => [
                    'url' => 'site_url/index.php?module=Users&action=Authenticate',
                    'binding' => Constants::BINDING_HTTP_POST,
                ],
                'singleLogoutService' => [
                    'url' => 'site_url/index.php?module=Users&action=Logout',
                    'binding' => Constants::BINDING_HTTP_REDIRECT,
                ],
                'NameIDFormat' => Constants::NAMEID_EMAIL_ADDRESS,
                'x509cert' => 'SAML_REQUEST_SIGNING_X509',
                'privateKey' => 'SAML_REQUEST_SIGNING_PKEY',
                'provisionUser' => 'SAML_provisionUser',
            ],

            'idp' => [
                'entityId' => 'SAML_idp_entityId',
                'singleSignOnService' => [
                    'url' => 'SAML_loginurl',
                    'binding' => Constants::BINDING_HTTP_REDIRECT,
                ],
                'singleLogoutService' => [
                    'url' => 'SAML_SLO',
                    'binding' => Constants::BINDING_HTTP_REDIRECT,
                ],
                'x509cert' => 'SAML_X509Cert',
            ],

            'security' => [
                'authnRequestsSigned' => true,
                'logoutRequestSigned' => true,
                'logoutResponseSigned' => true,
                'signatureAlgorithm' => 'SAML_REQUEST_SIGNING_METHOD',
                'validateRequestId' => true,
            ],
        ];
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $config->method('get')
            ->willReturnMap(
                [
                    ['SAML_request_signing_pkey', null, 'SAML_REQUEST_SIGNING_PKEY'],
                    ['site_url', null, 'site_url'],
                    ['SAML_loginurl', null, 'SAML_loginurl'],
                    ['SAML_issuer', 'php-saml', 'SAML_issuer'],
                    ['SAML_request_signing_x509', '', 'SAML_REQUEST_SIGNING_X509'],
                    ['SAML_request_signing_x509', null, 'SAML_REQUEST_SIGNING_X509'],
                    ['SAML_request_signing_pkey', '', 'SAML_REQUEST_SIGNING_PKEY'],
                    ['SAML_provisionUser', true, 'SAML_provisionUser'],
                    ['SAML_idp_entityId', 'SAML_loginurl', 'SAML_idp_entityId'],
                    ['SAML_SLO', null, 'SAML_SLO'],
                    ['SAML_X509Cert', null, 'SAML_X509Cert'],
                    [
                        'SAML_request_signing_method',
                        XMLSecurityKey::RSA_SHA256,
                        'SAML_REQUEST_SIGNING_METHOD',
                    ],
                    ['SAML', [], []],
                    ['SAML_sign_authn', false, true],
                    ['SAML_sign_logout_request', false, true],
                    ['SAML_sign_logout_response', false, true],
                    ['saml.validate_request_id', false, true],
                ]
            );
        $this->assertEquals($expectedConfig, $config->getSAMLConfig());
    }

    public function getSAMLConfigIdpStoredValuesProperlyEscapeProvider()
    {
        return [
            ['https://test.local', 'https://test.local'],
            ['https://test.local?idp1=test', 'https://test.local?idp1=test'],
            ['https://test.local/idp=test&idp1=test', 'https://test.local/idp=test&idp1=test'],
            ['https://test.local/idp=test&amp;idp1=test', 'https://test.local/idp=test&idp1=test'],
        ];
    }

    /**
     * @param string $storedValue
     * @param string $expectedValue
     *
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigIdpStoredValuesProperlyEscapeProvider
     */
    public function testGetSAMLConfigIdpStoredValuesProperlyEscape($storedValue, $expectedValue)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $config->expects($this->any())->method('get')
            ->will($this->returnValueMap(
                [
                    ['SAML_loginurl', null, $storedValue],
                    ['SAML_SLO', null, $storedValue],
                    ['SAML_issuer', 'php-saml', $storedValue],
                    ['SAML_idp_entityId', $expectedValue, $storedValue],
                    ['SAML', [], []],
                ]
            ));

        $samlConfig = $config->getSAMLConfig();

        $this->assertArrayHasKey('idp', $samlConfig);
        $this->assertArrayHasKey('singleSignOnService', $samlConfig['idp']);
        $this->assertArrayHasKey('singleLogoutService', $samlConfig['idp']);
        $this->assertArrayHasKey('entityId', $samlConfig['idp']);
        $this->assertArrayHasKey('url', $samlConfig['idp']['singleSignOnService']);
        $this->assertArrayHasKey('url', $samlConfig['idp']['singleLogoutService']);

        $this->assertArrayHasKey('sp', $samlConfig);
        $this->assertArrayHasKey('entityId', $samlConfig['sp']);

        $this->assertEquals($expectedValue, $samlConfig['idp']['singleSignOnService']['url'], 'SSO url invalid');
        $this->assertEquals($expectedValue, $samlConfig['idp']['singleLogoutService']['url'], 'SLO url invalid');
        $this->assertEquals($expectedValue, $samlConfig['idp']['entityId'], 'IdP Entity ID invalid');
        $this->assertEquals($expectedValue, $samlConfig['sp']['entityId'], 'SugarCRM Entity ID invalid');
    }

    /**
     * @covers ::getLdapConfig
     */
    public function testGetLdapConfigNoLdap()
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(false);

        $this->assertEmpty($config->getLdapConfig());
    }

    public function getLdapConfigDataProvider()
    {
        return [
            'regular LDAP' => [
                [
                    'user' => [
                        'mapping' => [
                            'givenName' => 'first_name',
                            'sn' => 'last_name',
                            'mail' => 'email1',
                            'telephoneNumber' => 'phone_work',
                            'facsimileTelephoneNumber' => 'phone_fax',
                            'mobile' => 'phone_mobile',
                            'street' => 'address_street',
                            'l' => 'address_city',
                            'st' => 'address_state',
                            'postalCode' => 'address_postalcode',
                            'c' => 'address_country',
                        ],
                    ],
                    'adapter_config' => [
                        'host' => '127.0.0.1',
                        'port' => '389',
                        'options' => [
                            'network_timeout' => 60,
                            'timelimit' => 60,
                        ],
                        'encryption' => Config::LDAP_ENCRYPTION_NONE,
                    ],
                    'adapter_connection_protocol_version' => 3,
                    'baseDn' => 'dn',
                    'uidKey' => 'uidKey',
                    'filter' => '({uid_key}={username})',
                    'dnString' => null,
                    'entryAttribute' => 'ldap_bind_attr',
                    'autoCreateUser' => true,
                    'searchDn' => 'admin',
                    'searchPassword' => 'test',
                    'groupMembership' => true,
                    'groupDn' => 'group,group_dn',
                    'groupAttribute' => 'group_attr',
                    'userUniqueAttribute' => 'ldap_group_user_attr',
                    'includeUserDN' => true,
                ],
                [
                    ['ldap_encryption', 'none', 'none'],
                    ['ldap_hostname', '127.0.0.1', '127.0.0.1'],
                    ['ldap_port', 389, 389],
                    ['ldap_base_dn', '', 'dn'],
                    ['ldap_login_attr', '', 'uidKey'],
                    ['ldap_login_filter', '', ''],
                    ['ldap_bind_attr', null, 'ldap_bind_attr'],
                    ['ldap_auto_create_users', false, true],
                    ['ldap_authentication', null, true],
                    ['ldap_admin_user', null, 'admin'],
                    ['ldap_admin_password', null, 'test'],
                    ['ldap_group', null, true],
                    ['ldap_group_name', null, 'group'],
                    ['ldap_group_dn', null, 'group_dn'],
                    ['ldap_group_attr', null, 'group_attr'],
                    ['ldap_group_user_attr', null, 'ldap_group_user_attr'],
                    ['ldap_group_attr_req_dn', false, '1'],
                ],
            ],
            'LDAP over SSL' => [
                [
                    'user' => [
                        'mapping' => [
                            'givenName' => 'first_name',
                            'sn' => 'last_name',
                            'mail' => 'email1',
                            'telephoneNumber' => 'phone_work',
                            'facsimileTelephoneNumber' => 'phone_fax',
                            'mobile' => 'phone_mobile',
                            'street' => 'address_street',
                            'l' => 'address_city',
                            'st' => 'address_state',
                            'postalCode' => 'address_postalcode',
                            'c' => 'address_country',
                        ],
                    ],
                    'adapter_config' => [
                        'host' => '127.0.0.1',
                        'port' => 636,
                        'options' => [
                            'network_timeout' => 60,
                            'timelimit' => 60,
                        ],
                        'encryption' => Config::LDAP_ENCRYPTION_SSL,
                    ],
                    'adapter_connection_protocol_version' => 3,
                    'baseDn' => 'dn',
                    'uidKey' => 'uidKey',
                    'filter' => '({uid_key}={username})',
                    'dnString' => null,
                    'entryAttribute' => 'ldap_bind_attr',
                    'autoCreateUser' => true,
                    'searchDn' => 'admin',
                    'searchPassword' => 'test',
                    'groupMembership' => true,
                    'groupDn' => 'group,group_dn',
                    'groupAttribute' => 'group_attr',
                    'userUniqueAttribute' => 'ldap_group_user_attr',
                    'includeUserDN' => true,
                ],
                [
                    ['ldap_encryption', 'none', 'ssl'],
                    ['ldap_hostname', '127.0.0.1', 'ldaps://127.0.0.1'],
                    ['ldap_port', 389, 636],
                    ['ldap_base_dn', '', 'dn'],
                    ['ldap_login_attr', '', 'uidKey'],
                    ['ldap_login_filter', '', ''],
                    ['ldap_bind_attr', null, 'ldap_bind_attr'],
                    ['ldap_auto_create_users', false, true],
                    ['ldap_authentication', null, true],
                    ['ldap_admin_user', null, 'admin'],
                    ['ldap_admin_password', null, 'test'],
                    ['ldap_group', null, true],
                    ['ldap_group_name', null, 'group'],
                    ['ldap_group_dn', null, 'group_dn'],
                    ['ldap_group_attr', null, 'group_attr'],
                    ['ldap_group_user_attr', null, 'ldap_group_user_attr'],
                    ['ldap_group_attr_req_dn', false, '1'],
                ],
            ],
            'LDAP with TLS' => [
                [
                    'user' => [
                        'mapping' => [
                            'givenName' => 'first_name',
                            'sn' => 'last_name',
                            'mail' => 'email1',
                            'telephoneNumber' => 'phone_work',
                            'facsimileTelephoneNumber' => 'phone_fax',
                            'mobile' => 'phone_mobile',
                            'street' => 'address_street',
                            'l' => 'address_city',
                            'st' => 'address_state',
                            'postalCode' => 'address_postalcode',
                            'c' => 'address_country',
                        ],
                    ],
                    'adapter_config' => [
                        'host' => '127.0.0.1',
                        'port' => 389,
                        'options' => [
                            'network_timeout' => 60,
                            'timelimit' => 60,
                        ],
                        'encryption' => Config::LDAP_ENCRYPTION_TLS,
                    ],
                    'adapter_connection_protocol_version' => 3,
                    'baseDn' => 'dn',
                    'uidKey' => 'uidKey',
                    'filter' => '({uid_key}={username})',
                    'dnString' => null,
                    'entryAttribute' => 'ldap_bind_attr',
                    'autoCreateUser' => true,
                    'searchDn' => 'admin',
                    'searchPassword' => 'test',
                    'groupMembership' => true,
                    'groupDn' => 'group,group_dn',
                    'groupAttribute' => 'group_attr',
                    'userUniqueAttribute' => 'ldap_group_user_attr',
                    'includeUserDN' => true,
                ],
                [
                    ['ldap_encryption', 'none', 'tls'],
                    ['ldap_hostname', '127.0.0.1', '127.0.0.1'],
                    ['ldap_port', 389, 389],
                    ['ldap_base_dn', '', 'dn'],
                    ['ldap_login_attr', '', 'uidKey'],
                    ['ldap_login_filter', '', ''],
                    ['ldap_bind_attr', null, 'ldap_bind_attr'],
                    ['ldap_auto_create_users', false, true],
                    ['ldap_authentication', null, true],
                    ['ldap_admin_user', null, 'admin'],
                    ['ldap_admin_password', null, 'test'],
                    ['ldap_group', null, true],
                    ['ldap_group_name', null, 'group'],
                    ['ldap_group_dn', null, 'group_dn'],
                    ['ldap_group_attr', null, 'group_attr'],
                    ['ldap_group_user_attr', null, 'ldap_group_user_attr'],
                    ['ldap_group_attr_req_dn', false, '1'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getLdapConfigDataProvider
     * @covers ::getLdapConfig
     */
    public function testGetLdapConfig($expected, $returnValueMap)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled', 'getLdapSetting'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(true);
        $config->expects($this->exactly(17))
            ->method('getLdapSetting')
            ->willReturnMap($returnValueMap);

        $this->assertEquals($expected, $config->getLdapConfig());
    }

    /**
     * Provides data for testGetLdapConfigWithDifferentFilters.
     * @return array
     */
    public function getLdapConfigWithDifferentFiltersProvider()
    {
        return [
            'emptyConfigFilter' => [
                'configFilter' => '',
                'expectedFilter' => '({uid_key}={username})',
            ],
            'notEmptyConfigFilterWithBrackets' => [
                'configFilter' => '(objectClass=person)',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithoutBrackets' => [
                'configFilter' => 'objectClass=person',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithOneBracketsAndSpaces' => [
                'configFilter' => '  objectClass=person) ',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithOneBracketsAndSpecCharacters' => [
                'configFilter' => "\n\x0B" . '    (objectClass=person' . "\t\n\r\0",
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
        ];
    }

    /**
     * @param string $configFilter
     * @param string $expectedFilter
     *
     * @covers ::getLdapConfig
     * @dataProvider getLdapConfigWithDifferentFiltersProvider
     */
    public function testGetLdapConfigWithDifferentFilters($configFilter, $expectedFilter)
    {
        /** @var \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled', 'getLdapSetting'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(true);

        $config->method('getLdapSetting')->willReturnMap([['ldap_login_filter', '', $configFilter]]);
        $result = $config->getLdapConfig();
        $this->assertEquals($expectedFilter, $result['filter']);
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
                                'discovery' => 60,
                                'authz' => 2 * 60,
                                'remoteIdpResponseParsed' => 60,
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
                            'discovery' => 60,
                            'authz' => 2 * 60,
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
            'discoveryURLInConfig' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                        'discoveryUrl' => 'https://discovery.url',
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
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
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
                    'discoveryUrl' => 'https://discovery.url',
                ],
            ],
            'discoveryURLNotInConfigStagingTenant' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:stage:sugar:eu:0000000001:tenant',
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
                    'tid' => 'srn:stage:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:stage:iam:::permission:crm.sa',
                        'srn:stage:iam:::permission:tenant.crm.sa',
                    ],
                    'discoveryUrl' => 'https://discovery-stage.service.sugarcrm.com/',
                ],
            ],
            'discoveryURLNotInConfigProductionTenant' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cloud:sugar:eu:0000000001:tenant',
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
                    'tid' => 'srn:cloud:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cloud:iam:::permission:crm.sa',
                        'srn:cloud:iam:::permission:tenant.crm.sa',
                    ],
                    'discoveryUrl' => 'https://discovery.service.sugarcrm.com/',
                ],
            ],
            'discoveryURLNotInConfigUnknownPartitionTenant' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:prod:sugar:eu:0000000001:tenant',
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
                    'tid' => 'srn:prod:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:prod:iam:::permission:crm.sa',
                        'srn:prod:iam:::permission:tenant.crm.sa',
                    ],
                ],
            ],
            'serviceAccountPermissionsInConfig' => [
                'sugarConfig' => [
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'keySetId',
                        'tid' => 'srn:cloud:sugar:eu:0000000001:tenant',
                        'serviceAccountPermissions' => [
                            'srn:cloud:iam:::permission:new',
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
                    'tid' => 'srn:cloud:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                    'profileUrls' => ['changePassword' => 'http://login.sugarcrm.local/password/change'],
                    'caching' => [
                        'ttl' => [
                            'introspectToken' => 10,
                            'userInfo' => 10,
                            'keySet' => 24 * 60 * 60,
                            'discovery' => 24 * 60 * 60,
                            'authz' => 15 * 60,
                            'remoteIdpResponseParsed' => 60,
                        ],
                    ],
                    'crmOAuthScope' => '',
                    'requestedOAuthScopes' => [],
                    'allowedSAs' => [],
                    'serviceAccountPermissions' => [
                        'srn:cloud:iam:::permission:new',
                    ],
                    'discoveryUrl' => 'https://discovery.service.sugarcrm.com/',
                ],
            ],
        ];
    }

    /**
     * @covers ::getIDMModeConfig
     *
     * @param $sugarConfig
     * @param $expected
     *
     * @dataProvider getIDMModeConfigProvider
     */
    public function testGetIDMModeConfig($sugarConfig, $expected)
    {
        $GLOBALS['sugar_config']['site_url'] = 'http://site.url/';
        $configMock = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([\SugarConfig::getInstance()])
            ->setMethods(['getIdmSettings'])
            ->getMock();

        $idmSettingsMock = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($sugarConfig[Config::IDM_MODE_KEY])) {
            foreach ($sugarConfig[Config::IDM_MODE_KEY] as $key => $value) {
                $idmSettingsMock->settings[Config::IDM_MODE_KEY . '_' . $key] = $value;
            }
        }

        $configMock->expects($this->any())
            ->method('getIdmSettings')
            ->willReturn($idmSettingsMock);

        $this->assertEquals($expected, $configMock->getIDMModeConfig());
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

    /**
     * @covers ::isIDMModeEnabled
     *
     * @param $sugarConfig
     * @param $expected
     *
     * @dataProvider isIDMModeEnabledProvider
     */
    public function testIsIDMModeEnabled($sugarConfig, $expected)
    {
        $GLOBALS['sugar_config']['site_url'] = 'http://site.url/';
        $configMock = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([\SugarConfig::getInstance()])
            ->setMethods(['getIdmSettings'])
            ->getMock();

        $idmSettingsMock = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($sugarConfig[Config::IDM_MODE_KEY])) {
            foreach ($sugarConfig[Config::IDM_MODE_KEY] as $key => $value) {
                $idmSettingsMock->settings[Config::IDM_MODE_KEY . '_' . $key] = $value;
            }
        }

        $configMock->expects($this->any())
            ->method('getIdmSettings')
            ->willReturn($idmSettingsMock);

        $this->assertEquals($expected, $configMock->isIDMModeEnabled());
    }

    /**
     * @covers ::getIDMModeDisabledModules
     */
    public function testGetIDMModeDisabledModules()
    {
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $config = new Config($sugarConfig);

        $this->assertEquals(['Users', 'Employees'], $config->getIDMModeDisabledModules());
    }

    /**
     * Provides data for testBuildCloudConsoleUrl
     *
     * @return array
     */
    public function buildCloudConsoleUrlProvider()
    {
        return [
            'path-key-found' => [
                'userManagement',
                [],
                'E0900101-726A-49CD-A1C6-B6D518BFBF8A',
                [
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [
                        'userManagement' => '/management/users/',
                    ],
                    'tid' => 'srn:cloud:iam:eu:0000000001:tenant',
                ],
                'http://console.sugarcrm.local/management/users?tenant_hint=srn%3Acloud%3Aiam%3Aeu%3A0000000001%3Atenant&user_hint=srn%3Acloud%3Aiam%3A%3A0000000001%3Auser%3AE0900101-726A-49CD-A1C6-B6D518BFBF8A',
            ],
            'path-key-not-found' => [
                'some-unknown-route',
                [],
                'E0900101-726A-49CD-A1C6-B6D518BFBF8A',
                [
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [],
                    'tid' => 'srn:cloud:iam:eu:0000000001:tenant',
                ],
                'http://console.sugarcrm.local?tenant_hint=srn%3Acloud%3Aiam%3Aeu%3A0000000001%3Atenant&user_hint=srn%3Acloud%3Aiam%3A%3A0000000001%3Auser%3AE0900101-726A-49CD-A1C6-B6D518BFBF8A',
            ],
            'path-key-found-and-3-parts-exist' => [
                'userManagement',
                [
                    'a',
                    'some-id',
                    'policies',
                ],
                'E0900101-726A-49CD-A1C6-B6D518BFBF8A',
                [
                    'cloudConsoleUrl' => 'http://foo.bar',
                    'cloudConsoleRoutes' => [
                        'userManagement' => 'management/users',
                    ],
                    'tid' => 'srn:cloud:iam:eu:0000000001:tenant',
                ],
                'http://foo.bar/management/users/a/some-id/policies?tenant_hint=srn%3Acloud%3Aiam%3Aeu%3A0000000001%3Atenant&user_hint=srn%3Acloud%3Aiam%3A%3A0000000001%3Auser%3AE0900101-726A-49CD-A1C6-B6D518BFBF8A',
            ],
            'no-parts-url-has-slashes' => [
                'userManagement',
                [],
                'E0900101-726A-49CD-A1C6-B6D518BFBF8A',
                [
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local//',
                    'cloudConsoleRoutes' => [],
                    'tid' => 'srn:cloud:iam:eu:0000000001:tenant',
                ],
                'http://console.sugarcrm.local?tenant_hint=srn%3Acloud%3Aiam%3Aeu%3A0000000001%3Atenant&user_hint=srn%3Acloud%3Aiam%3A%3A0000000001%3Auser%3AE0900101-726A-49CD-A1C6-B6D518BFBF8A',
            ],
            'parts-with-non-url-characters' => [
                'userManagement',
                [
                    'user',
                    'Имя',
                ],
                'E0900101-726A-49CD-A1C6-B6D518BFBF8A',
                [
                    'cloudConsoleUrl' => 'http://foo.bar',
                    'cloudConsoleRoutes' => [],
                    'tid' => 'srn:cloud:iam:eu:0000000001:tenant',
                ],
                'http://foo.bar/user/%D0%98%D0%BC%D1%8F?tenant_hint=srn%3Acloud%3Aiam%3Aeu%3A0000000001%3Atenant&user_hint=srn%3Acloud%3Aiam%3A%3A0000000001%3Auser%3AE0900101-726A-49CD-A1C6-B6D518BFBF8A',
            ],
            'without-user-id' => [
                'forgotPassword',
                [],
                '',
                [
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [
                        'forgotPassword' => '/forgot/password/',
                    ],
                    'tid' => 'srn:cloud:iam:eu:0000000001:tenant',
                ],
                'http://console.sugarcrm.local/forgot/password?tenant_hint=srn%3Acloud%3Aiam%3Aeu%3A0000000001%3Atenant',
            ],
        ];
    }

    /**
     * @param string $pathKey
     * @param array|null $parts
     * @param array $idmModeConfig
     * @param array $userId
     * @param string $result
     *
     * @dataProvider buildCloudConsoleUrlProvider
     * @covers ::buildCloudConsoleUrl
     */
    public function testBuildCloudConsoleUrl($pathKey, $parts, $userId, $idmModeConfig, $result)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIDMModeConfig'])
            ->getMock();
        $config->method('getIDMModeConfig')->willReturn($idmModeConfig);

        $this->assertEquals($result, $config->buildCloudConsoleUrl($pathKey, $parts, $userId));
    }

    /**
     * @covers ::getIDMModeDisabledFields
     *
     * @dataProvider ProviderTestIDMModeDisabledFields
     */
    public function testIDMModeDisabledFields(array $varDefFields, array $exceptionNames, array $expectedList)
    {
        $config = $this->getMockBuilder(Config::class)
            ->setMethods(['getUserVardef'])
            ->disableOriginalConstructor()
            ->getMock();
        $config->method('getUserVardef')->willReturn($varDefFields);

        $this->assertEquals($expectedList, $config->getIDMModeDisabledFields($exceptionNames));
    }

    public function ProviderTestIDMModeDisabledFields() : array
    {
        $varDefFields = [
            'pwd_last_changed' => [
                'name' => 'pwd_last_changed',
            ],
            'user_name' => [
                'name' => 'user_name',
                'idm_mode_disabled' => true,
            ],
            'id' => [
                'name' => 'id',
            ],
            'first_name' => [
                'name' => 'first_name',
                'idm_mode_disabled' => true,
            ],
            'sugar_login' => [
                'name' => 'sugar_login',
            ],
            'email' => [
                'name' => 'email',
                'idm_mode_disabled' => true,
            ],
        ];
        return [
            'no exception names' =>
            [
                $varDefFields,
                [],
                [
                    'user_name' => $varDefFields['user_name'],
                    'first_name' => $varDefFields['first_name'],
                    'email' => $varDefFields['email'],
                ],
            ],
            '\'email\' is in the exception name list' =>
            [
                $varDefFields,
                ['email'],
                [
                    'user_name' => $varDefFields['user_name'],
                    'first_name' => $varDefFields['first_name'],
                ],
            ],
        ];
    }
    /**
     * @return array
     */
    public function setIDMModeDataProvider(): array
    {
        return [
            [false],
            [['clientId' => 'mangoOIDCClientId']],
            [
                [
                    'clientId' => 'mangoOIDCClientId',
                    'allowedSAs' => [
                        'srn:cloud:iam:us-west-2:9999999999:sa:user-sync',
                        'srn:cloud:iam:us-west-2:1234567890:sa:custom-sa',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::setIDMMode
     * @dataProvider setIDMModeDataProvider
     */
    public function testSetIDMMode($setIDMModeConfig): void
    {
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods([
                'getIdmSettings',
                'refreshCache',
                'getIdmModeData',
                'refreshIdmSettings',
                'toggleCatalog',
                'setPushNotification',
            ])
            ->setConstructorArgs([$this->createMock('\SugarConfig')])
            ->getMock();

        $idmSettingsMock = $this->getMockBuilder(\Administration::class)
            ->onlyMethods(['saveSetting'])
            ->disableOriginalConstructor()
            ->getMock();
        $idmSettingsMock->settings[Config::IDM_MODE_KEY . '_' . 'enabled'] = true;

        $configMock->expects($this->any())
            ->method('getIdmSettings')
            ->willReturn($idmSettingsMock);

        $idmConfigSettings = [
            'enabled' => true,
            'clientId' => 'testLocal',
            'clientSecret' => 'testLocalSecret',
            'stsUrl' => 'http://sts.sugarcrm.local',
            'idpUrl' => 'http://login.sugarcrm.local',
            'stsKeySetId' => 'keySetId',
            'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
        ];

        $configMock->expects($this->once())
            ->method('toggleCatalog')
            ->with(is_array($setIDMModeConfig));

        $configMock->expects($this->once())
            ->method('setPushNotification')
            ->with(is_array($setIDMModeConfig));

        $configMock->expects($this->any())
            ->method('getIdmModeData')
            ->willReturn($idmConfigSettings);

        $configMock->expects($this->once())
            ->method('refreshCache');
        $configMock->setIDMMode($setIDMModeConfig);
    }

    /**
     * @return array
     */
    public function isSpecialBeanActionProvider(): array
    {
        return [
            [
                'isGroup' => '1',
                'isPortal' => null,
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => true,
            ],
            [
                'isGroup' => null,
                'isPortal' => '1',
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => true,
            ],
            [
                'isGroup' => null,
                'isPortal' => null,
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => false,
            ],
            [
                'isGroup' => '0',
                'isPortal' => '0',
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => false,
            ],
            [
                'isGroup' => false,
                'isPortal' => false,
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => false,
            ],
            [
                'isGroup' => true,
                'isPortal' => true,
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => true,
            ],
            [
                'isGroup' => '1',
                'isPortal' => '1',
                'moduleName' => 'Calls',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => false,
            ],
            [
                'isGroup' => null,
                'isPortal' => null,
                'moduleName' => 'Users',
                'request' => ['usertype' => 'portal'],
                'canBeAuthenticated' => true,
                'result' => true,
            ],
            [
                'isGroup' => null,
                'isPortal' => null,
                'moduleName' => 'Users',
                'request' => ['usertype' => 'group'],
                'canBeAuthenticated' => true,
                'result' => true,
            ],
            [
                'isGroup' => null,
                'isPortal' => null,
                'moduleName' => 'Users',
                'request' => ['usertype' => 'foo'],
                'canBeAuthenticated' => true,
                'result' => false,
            ],
            [
                'isGroup' => true,
                'isPortal' => true,
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => true,
            ],
            [
                'isGroup' => '0',
                'isPortal' => '0',
                'moduleName' => 'Users',
                'request' => [],
                'canBeAuthenticated' => false,
                'result' => false,
            ],
            [
                'isGroup' => '1',
                'isPortal' => '1',
                'moduleName' => 'Calls',
                'request' => ['usertype' => 'group'],
                'canBeAuthenticated' => true,
                'result' => false,
            ],
            [
                'isGroup' => '0',
                'isPortal' => '0',
                'moduleName' => 'Employees',
                'request' => [],
                'canBeAuthenticated' => true,
                'result' => false,
            ],
            [
                'isGroup' => '0',
                'isPortal' => '0',
                'moduleName' => 'Employees',
                'request' => [],
                'canBeAuthenticated' => false,
                'result' => true,
            ],
        ];
    }

    /**
     * @covers ::isSpecialBeanAction
     * @dataProvider isSpecialBeanActionProvider
     *
     * @param mixed $isGroup
     * @param mixed $isPortal
     * @param string $moduleName
     * @param array $request
     * @param bool $canBeAuthenticated
     * @param bool $result
     */
    public function testIsSpecialBeanAction(
        $isGroup,
        $isPortal,
        string $moduleName,
        array $request,
        bool $canBeAuthenticated,
        bool $result
    ): void {

        $config = new Config($this->createMock(\SugarConfig::class));
        if ($moduleName == 'Employees') {
            $bean = $this->createMock(\Employee::class);
            $bean->expects($this->once())
                ->method('canBeAuthenticated')
                ->willReturn($canBeAuthenticated);
        } else {
            $bean = $this->createMock(\SugarBean::class);
        }
        $bean->is_group = $isGroup;
        $bean->portal_only = $isPortal;
        $bean->module_name = $moduleName;
        $this->assertEquals($result, $config->isSpecialBeanAction($bean, $request));
    }
}
