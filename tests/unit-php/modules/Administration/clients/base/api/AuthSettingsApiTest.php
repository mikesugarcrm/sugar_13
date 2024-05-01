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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject;
use RestService;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SugarApiExceptionMissingParameter;
use SugarApiExceptionNotAuthorized;
use SugarApiExceptionNotFound;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use User;

require_once 'modules/Administration/clients/base/api/AuthSettingsApi.php';

/**
 * @coversDefaultClass \AuthSettingsApi
 * Class AuthSettingsApiTest
 * @package Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api
 */
class AuthSettingsApiTest extends TestCase
{
    /**
     * @var mixed|\AuthSettingsApi&\PHPUnit\Framework\MockObject\MockObject
     */
    public $api;
    /**
     * @var User|MockObject
     */
    private $currentUser;

    /**
     * @var RestService|MockObject
     */
    private $service;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Lockout|MockObject
     */
    private $lockout;

    /**
     * @var AdministrationApi|MockObject
     */
    private $administrationApi;

    /**
     * @var array
     */
    private $sugar_config_bak;

    /**
     * @return array
     * @see testResultAuthSettings
     */
    public function expectedAuthSettings(): array
    {
        $ldapBase = [
            'adapter_config' => [
                'host' => '127.0.0.1',
                'port' => '389',
                'options' => [
                    'network_timeout' => 60,
                    'timelimit' => 60,
                ],
                'encryption' => 'none',
            ],
            'baseDn' => 'baseDn-value',
            'uidKey' => 'uidKey-value',
            'filter' => 'filter-value',
            'entryAttribute' => 'entryAttribute-value',
            'autoCreateUser' => 0,
            'user' => [
                'mapping' => [],
            ],
        ];
        $ldapBaseExpects = [
            'config' => [
                'auto_create_users' => false,
                'server' => '127.0.0.1:389',
                'user_dn' => 'baseDn-value',
                'user_filter' => 'filter-value',
                'login_attribute' => 'uidKey-value',
                'bind_attribute' => 'entryAttribute-value',

                'authentication' => false,
                'authentication_user_dn' => '',
                'authentication_password' => '',

                'group_membership' => false,
                'group_dn' => '',
                'group_name' => '',
                'group_attribute' => '',
                'user_unique_attribute' => '',
                'include_user_dn' => false,
            ],
            'attribute_mapping' => [],
        ];
        $samlConfig = [
            'strict' => 1,
            'debug' => 0,
            'sp' => [
                'entityId' => 'sp-entityId-value',
                'assertionConsumerService' => [
                    'url' => 'http://assertionConsumerService/url',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => 'http://singleLogoutService/url',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => 'SAML_request_signing_x509-value',
                'privateKey' => 'SAML_request_signing_pkey-value',
                'provisionUser' => 0,
                'sugarCustom' => [
                    'id' => 'user_name',
                ],
            ],

            'idp' => [
                'entityId' => 'http://saml-server/simplesaml/saml2/idp/metadata.php',
                'singleSignOnService' => [
                    'url' => 'http://singleSignOnService/url',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'singleLogoutService' => [
                    'url' => 'http://singleLogoutService/url',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => 'SAML_X509Cert-value',
            ],

            'security' => [
                'authnRequestsSigned' => 1,
                'logoutRequestSigned' => 0,
                'logoutResponseSigned' => '1',
                'signatureAlgorithm' => XMLSecurityKey::RSA_SHA512,
                'validateRequestId' => null,
            ],
            'user_mapping' => [],
        ];

        $samlBaseExpects = [
            'config' => [
                'sp_entity_id' => 'sp-entityId-value',
                'request_signing_cert' => 'SAML_request_signing_x509-value',
                'request_signing_pkey' => 'SAML_request_signing_pkey-value',
                'provision_user' => false,
                'same_window' => true,
                'idp_entity_id' => 'http://saml-server/simplesaml/saml2/idp/metadata.php',
                'idp_sso_url' => 'http://singleSignOnService/url',
                'idp_slo_url' => 'http://singleLogoutService/url',
                'x509_cert' => 'SAML_X509Cert-value',
                'sign_authn_request' => true,
                'sign_logout_request' => false,
                'sign_logout_response' => true,
                'request_signing_method' => 'RSA_SHA512',
                'validate_request_id' => false,
                'user_identity_field' => 'user_name',
            ],
            'attribute_mapping' => [],
        ];
        $adminSettingsMap = [
            'honeypot_on' => ['honeypot_on', false, true],
        ];
        $passwordSetting = [
            'minpwdlength' => 6,
            'maxpwdlength' => 0,
            'oneupper' => true,
            'onelower' => true,
            'onenumber' => true,
            'onespecial' => false,
            'SystemGeneratedPasswordON' => true,
            'generatepasswordtmpl' => 'c4d8d2d2-5ccb-11e8-ac41-685b35ac4c74',
            'lostpasswordtmpl' => 'c4e093be-5ccb-11e8-b7fb-685b35ac4c74',
            'customregex' => '',
            'regexcomment' => '',
            'forgotpasswordON' => true,
            'linkexpiration' => true,
            'linkexpirationtime' => 24,
            'linkexpirationtype' => 60,
            'userexpiration' => '0',
            'userexpirationtime' => '',
            'userexpirationtype' => '1',
            'userexpirationlogin' => '',
            'systexpiration' => 1,
            'systexpirationtime' => 7,
            'systexpirationtype' => '0',
            'systexpirationlogin' => '',
            'lockoutexpiration' => 0,
            'lockoutexpirationtime' => '',
            'lockoutexpirationtype' => '1',
            'lockoutexpirationlogin' => '',
            'LockoutDurationMins' => 0,
        ];
        $localConfExpPassReq = [
            'minimum_length' => $passwordSetting['minpwdlength'],
            'maximum_length' => $passwordSetting['maxpwdlength'],
            'require_upper' => $passwordSetting['oneupper'],
            'require_lower' => $passwordSetting['onelower'],
            'require_number' => $passwordSetting['onenumber'],
            'require_special' => $passwordSetting['onespecial'],
        ];
        $localConfExpExpirDisabled = [
            'time' => 0,
            'attempt' => 0,
        ];

        $localConfExpExpirTime = [
            'time' => 2 * 7 * 3600 * 24,
            'attempt' => 0,
        ];

        $localConfExpExpirAttempt = [
            'time' => 0,
            'attempt' => 100,
        ];

        $loginLockoutDisabled = [
            'type' => Lockout::LOCKOUT_DISABLED,
            'attempt' => 0,
            'time' => 0,
        ];

        return [
            'localDisabledExpiration' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => [
                            'userexpiration' => '',
                        ] + $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                ],
            ],
            'localTimeExpiration' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => [
                            'userexpiration' => '1',
                            'userexpirationtime' => '2',
                            'userexpirationtype' => '7',
                        ] + $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirTime,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                ],
            ],
            'localAttemptExpiration' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => [
                            'userexpiration' => '2',
                            'userexpirationlogin' => '100',
                        ] + $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirAttempt,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                ],
            ],
            'localTimeLockout' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => array_replace(
                        $passwordSetting,
                        [
                            'lockoutexpiration' => Lockout::LOCK_TYPE_TIME,
                            'lockoutexpirationlogin' => 3,
                            'LockoutDurationMins' => 60,
                        ]
                    ),
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => [
                            'type' => Lockout::LOCK_TYPE_TIME,
                            'attempt' => 3,
                            'time' => 3600,
                        ],
                    ],
                ],
            ],
            'localPermanentLockout' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => array_replace(
                        $passwordSetting,
                        [
                            'lockoutexpiration' => Lockout::LOCK_TYPE_PERMANENT,
                            'lockoutexpirationlogin' => 4,
                            'LockoutDurationMins' => 2,
                        ]
                    ),
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => [
                            'type' => Lockout::LOCK_TYPE_PERMANENT,
                            'attempt' => 4,
                            'time' => 120,
                        ],
                    ],
                ],
            ],
            'localTokenLifeTime' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => [
                            'userexpiration' => '',
                        ] + $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [
                        'access_token_lifetime' => '7200',
                        'refresh_token_lifetime' => '86400',
                    ],
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                    'token_lifetime' => [
                        'access_token_lifetime' => 7200,
                        'refresh_token_lifetime' => 86400,
                    ],
                ],
            ],


            'ldap' => [
                'in' => [
                    'ldapConfig' => $ldapBase,
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'ldap'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                    'ldap' => $ldapBaseExpects,
                ],
            ],
            'ldapSSL' => [
                'in' => [
                    'ldapConfig' => array_replace_recursive(
                        $ldapBase,
                        [
                            'adapter_config' => [
                                'encryption' => 'ssl',
                            ],
                        ]
                    ),
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'ldap'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                    'ldap' => array_replace_recursive(
                        $ldapBaseExpects,
                        [
                            'config' => [
                                'server' => 'ldaps://127.0.0.1:389',
                            ],
                        ]
                    ),
                ],
            ],
            'ldapAuth' => [
                'in' => [
                    'ldapConfig' => array_replace_recursive(
                        $ldapBase,
                        [
                            'searchDn' => 'authentication_user_dn-value',
                            'searchPassword' => 'authentication_password-value',
                        ]
                    ),
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'ldap'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                    'ldap' => array_replace_recursive(
                        $ldapBaseExpects,
                        [
                            'config' => [
                                'authentication' => true,
                                'authentication_user_dn' => 'authentication_user_dn-value',
                                'authentication_password' => 'authentication_password-value',
                            ],
                        ]
                    ),
                ],
            ],
            'ldapGroup' => [
                'in' => [
                    'ldapConfig' => array_replace_recursive(
                        $ldapBase,
                        [
                            'groupMembership' => true,
                            'groupAttribute' => 'group_attribute-value',
                            'userUniqueAttribute' => 'user_unique_attribute-value',
                            'includeUserDN' => true,
                        ]
                    ),
                    'ldapSugarConfigMap' => [
                        ['ldap_group_dn', '', 'ldap_group_dn-value'],
                        ['ldap_group_name', '', 'ldap_group_name-value'],
                    ],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => false,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'ldap'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                    'ldap' => array_replace_recursive(
                        $ldapBaseExpects,
                        [
                            'config' => [
                                'group_membership' => true,
                                'group_dn' => 'ldap_group_dn-value',
                                'group_name' => 'ldap_group_name-value',
                                'group_attribute' => 'group_attribute-value',
                                'user_unique_attribute' => 'user_unique_attribute-value',
                                'include_user_dn' => true,
                            ],
                        ]
                    ),
                ],
            ],
            'saml_sha512' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => $samlConfig,
                    'authenticationClass' => 'IdMSAMLAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => true,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'saml'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                    'saml' => $samlBaseExpects,
                ],
            ],
            'samlSha256AndAttributeMapping' => [
                'in' => [
                    'ldapConfig' => [],
                    'ldapSugarConfigMap' => [],
                    'samlConfig' => array_replace_recursive(
                        $samlConfig,
                        [
                            'security' => [
                                'signatureAlgorithm' => XMLSecurityKey::RSA_SHA256,
                            ],
                            'user_mapping' => [
                                'key-1' => 'val-1',
                                'key-2' => 'val-2',
                            ],
                            'sp' => [
                                'sugarCustom' => [
                                    'id' => '',
                                ],
                            ],
                        ]
                    ),
                    'authenticationClass' => 'IdMSAMLAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                    'saml_same_window' => true,
                    'token_lifetime' => [],
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'saml'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                    'saml' => array_replace_recursive(
                        $samlBaseExpects,
                        [
                            'config' => [
                                'request_signing_method' => 'RSA_SHA256',
                                'user_identity_field' => 'email',
                            ],
                            'attribute_mapping' => [
                                [
                                    'source' => 'key-1',
                                    'destination' => 'val-1',
                                    'overwrite' => true,
                                ],
                                [
                                    'source' => 'key-2',
                                    'destination' => 'val-2',
                                    'overwrite' => true,
                                ],
                            ],
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * @covers ::authSettings
     * @dataProvider expectedAuthSettings
     * @param array $in
     * @param array $expected
     */
    public function testResultAuthSettings(array $in, array $expected): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);
        $this->lockout->method('getLockType')->willReturn($in['passwordSetting']['lockoutexpiration']);
        $this->lockout->method('getFailedLoginsCount')->willReturn($in['passwordSetting']['lockoutexpirationlogin']);
        $this->lockout->method('getLockoutDurationMins')->willReturn($in['passwordSetting']['LockoutDurationMins']);

        $this->api
            ->method('getLdapSetting')
            ->will($this->returnValueMap($in['ldapSugarConfigMap']));

        $this->config->method('getLdapConfig')->willReturn($in['ldapConfig']);
        $this->config->method('getSAMLConfig')->willReturn($in['samlConfig']);
        $this->config->method('get')
            ->will($this->returnValueMap([
                ['passwordsetting', [], $in['passwordSetting']],
                ['authenticationClass', 'IdMSugarAuthenticate', $in['authenticationClass']],
                ['SAML_SAME_WINDOW', false, $in['saml_same_window']],
                ['oauth2', [], $in['token_lifetime']],
            ]));
        $this->api->method('get')
            ->will($this->returnValueMap($in['adminSettingsMap']));

        $result = $this->api->authSettings($this->service, []);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::authSettings
     */
    public function testNoAdminRequest(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(false);

        $this->config->expects($this->never())->method('getLdapConfig');
        $this->config->expects($this->never())->method('getSAMLConfig');
        $this->config->expects($this->never())->method('get');

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->authSettings($this->service, []);
    }

    /**
     * @covers ::authSettings
     */
    public function testAuthorizedRequest(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        unset($GLOBALS['current_user']);

        $this->config->expects($this->never())->method('getLdapConfig');
        $this->config->expects($this->never())->method('getSAMLConfig');
        $this->config->expects($this->never())->method('get');

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->authSettings($this->service, []);
    }

    /**
     * @covers ::authSettings
     */
    public function testAuthSettingsMigrationDisabled(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = false;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())->method('getLdapConfig');
        $this->config->expects($this->never())->method('getSAMLConfig');
        $this->config->expects($this->never())->method('get');

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->api->authSettings($this->service, []);
    }

    /**
     * @return array
     */
    public function switchOnIdmModeExceptionDataProvider(): array
    {
        return [
            [[]],
            [['idmMode' => false]],
            'empty config' => [['idmMode' => []]],
            'config without enabled parameter' => [['idmMode' => ['some_idm_config']]],
        ];
    }

    /**
     * @covers ::switchOnIdmMode
     */
    public function testSwitchOnIdmMode(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->once())
            ->method('setIDMMode')
            ->with($this->equalTo(['enabled' => true]));
        $this->config->expects($this->once())
            ->method('getIDMModeConfig')
            ->willReturn([]);
        $this->api->switchOnIdmMode($this->service, ['idmMode' => ['enabled' => true]]);
    }

    /**
     * @covers ::switchOnIdmMode
     * @dataProvider switchOnIdmModeExceptionDataProvider
     */
    public function testSwitchOnIdmModeException($args): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->api->switchOnIdmMode($this->service, $args);
    }

    /**
     * @covers ::switchOnIdmMode
     */
    public function testSwitchOnIdmModeUnauthorized(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(false);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->switchOnIdmMode($this->service, ['idmMode' => ['enabled' => true]]);
    }

    /**
     * @covers ::switchOnIdmMode
     */
    public function testSwitchOnIdmModeMigrationDisabled(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = false;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->api->switchOnIdmMode($this->service, ['idmMode' => ['enabled' => true]]);
    }

    /**
     * @covers ::switchOffIdmMode
     */
    public function testSwitchOffIdmMode(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->once())
            ->method('setIDMMode')
            ->with($this->equalTo(false));
        $this->config->expects($this->once())
            ->method('getIDMModeConfig')
            ->willReturn([]);
        $this->administrationApi->expects($this->once())
            ->method('disableIdmMigration')
            ->with($this->service, []);
        $this->api->switchOffIdmMode($this->service, []);
    }

    /**
     * @covers ::switchOffIdmMode
     */
    public function testSwitchOffIdmModeUnauthorized(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(false);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');
        $this->administrationApi->expects($this->never())->method('disableIdmMigration');

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->switchOffIdmMode($this->service, []);
    }

    /**
     * @covers ::switchOffIdmMode
     */
    public function testSwitchOffIdmModeMigrationDisabled(): void
    {
        $GLOBALS['sugar_config']['idmMigration'] = false;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');
        $this->administrationApi->expects($this->never())->method('disableIdmMigration');

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->api->switchOffIdmMode($this->service, []);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->sugar_config_bak = $GLOBALS['sugar_config'] ?? [];
        $this->service = $this->createMock(RestService::class);
        $this->currentUser = $this->createMock(User::class);
        $this->config = $this->createMock(Config::class);
        $this->lockout = $this->createMock(Lockout::class);
        $this->administrationApi = $this->createMock(\AdministrationApi::class);
        $this->api = $this->createPartialMock(
            \AuthSettingsApi::class,
            ['getAuthConfig', 'get', 'getLockout', 'getLdapSetting', 'getAdministrationApi']
        );
        $this->api->method('getAuthConfig')->willReturn($this->config);
        $this->api->method('getLockout')->willReturn($this->lockout);
        $this->api->method('getAdministrationApi')->willReturn($this->administrationApi);
        $GLOBALS['current_user'] = $this->currentUser;
        $GLOBALS['app_strings'] = ['EXCEPTION_NOT_AUTHORIZED' => 'EXCEPTION_NOT_AUTHORIZED'];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
        $GLOBALS['sugar_config'] = $this->sugar_config_bak;
        parent::tearDown();
    }
}
