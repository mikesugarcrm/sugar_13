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

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder
 */
class AuthProviderManagerBuilderTest extends TestCase
{
    /** @var array */
    protected $beanList;

    protected function setUp(): void
    {
        parent::setUp();
        $this->beanList = $GLOBALS['beanList'] ?? null;
        $GLOBALS['beanList'] = [
            'Administration' => MockAdministration::class,
        ];
    }

    protected function tearDown(): void
    {
        $GLOBALS['beanList'] = $this->beanList;
        parent::tearDown();
    }

    /**
     * @covers ::buildAuthProviders
     */
    public function testBuildAuthProviders()
    {
        $data = $this->getConfig();
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('get')
            ->with($this->equalTo('passwordHash'), $this->isEmpty())
            ->willReturn([]);
        $config->expects($this->once())
            ->method('getSAMLConfig')
            ->willReturn($data['auth']['saml']);
        $config->expects($this->once())
            ->method('getLdapConfig')
            ->willReturn($data['auth']['ldap']);
        $config->expects($this->once())
            ->method('getIDMModeConfig')
            ->willReturn($data['auth']['idm_mode']);
        /** @var AuthProviderManagerBuilder $managerBuilder */
        $managerBuilder = $this->getMockBuilder(AuthProviderManagerBuilder::class)
            ->setConstructorArgs([$config])
            ->onlyMethods(['getDIContainer'])
            ->getMock();
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                [LoggerInterface::class, $this->createMock(LoggerInterface::class)],
            ]
        );
        $managerBuilder->method('getDIContainer')->willReturn($container);
        $manager = $managerBuilder->buildAuthProviders();
        $this->assertInstanceOf(AuthenticationProviderManager::class, $manager);
    }

    /**
     * @coversNothing
     * @return mixed
     */
    protected function getConfig()
    {
        $sugar_config = [];
        $sugar_config['auth'] = [];
        $sugar_config['auth']['ldap'] = [
            'adapter_config' => [
                'host' => '127.0.0.1',
                'port' => 389,
            ],
            'adapter_connection_protocol_version' => 3,
            'baseDn' => '',
            'searchDn' => '',
            'searchPassword' => '',
            'dnString' => '',
            'uidKey' => 'userPrincipalName',
            'filter' => '({uid_key}={username})',
        ];

        $stsUrl = 'http://sts.url';
        $sugar_config['auth']['idm_mode'] = [
            'clientId' => 'clientId',
            'clientSecret' => 'clientSecret',
            'stsUrl' => $stsUrl,
            'redirectUri' => 'http://sugar.url',
            'urlAuthorize' => $stsUrl . '/oauth2/auth',
            'urlAccessToken' => $stsUrl . '/oauth2/token',
            'urlResourceOwnerDetails' => $stsUrl . '/oauth2/introspect',
            'urlUserInfo' => $stsUrl . '/userinfo',
            'urlRevokeToken' => $stsUrl . '/oauth2/revoke',
            'urlKeys' => $stsUrl . '/keys/setId',
            'keySetId' => 'setId',
            'idpUrl' => 'http://idp.url',
            'http_client' => [
                'retry_count' => 5,
                'delay_strategy' => 'exponential',
            ],
            'tid' => 'srn:cloud:sugar:eu:0000000001:tenant',
        ];

        $sugar_config['auth']['saml']['Okta'] = [
            'strict' => false,
            'debug' => true,
            'sp' => [
                'entityId' => 'http://localhost:8000/saml/metadata',
                'assertionConsumerService' => [
                    'url' => 'http://localhost:8000/saml/acs',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => 'http://localhost:8000/saml/logout',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => 'x509cert',
                'privateKey' => 'key',
            ],

            'idp' => [
                'entityId' => 'http://www.okta.com/exk7y9w6b9H1jG46H0h7',
                'singleSignOnService' => [
                    'url' => 'https://dev-178368.oktapreview.com/app/sugarcrmdev280437_testidp_1/exk7y9w6b9H1jG46H0h7/sso/saml',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'singleLogoutService' => [
                    'url' => 'https://dev-178368.oktapreview.com/app/sugarcrmdev280437_testidp_1/exk7y9w6b9H1jG46H0h7/slo/saml',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => 'x509cert',
            ],
        ];

        $sugar_config['auth']['saml']['OneLogin'] = [
            'strict' => false,
            'debug' => true,
            'sp' => [
                'entityId' => 'idpdev',
                'assertionConsumerService' => [
                    'url' => 'http://localhost:8000/saml/acs',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => 'http://localhost:8000/saml/logout',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => 'x509cert',
                'privateKey' => 'key',
            ],

            'idp' => [
                'entityId' => 'https://app.onelogin.com/saml/metadata/619509',
                'singleSignOnService' => [
                    'url' => 'https://ddolbik-dev.onelogin.com/trust/saml2/http-post/sso/619509',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'singleLogoutService' => [
                    'url' => 'https://ddolbik-dev.onelogin.com/trust/saml2/http-redirect/slo/619509',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => 'x509cert',
            ],
        ];

        $sugar_config['auth']['saml']['ADFS'] = [
            'strict' => false,
            'debug' => true,
            'sp' => [
                'entityId' => '6a227274-ade1-4529-9163-2cf8c4ed8ae2',
                'assertionConsumerService' => [
                    'url' => 'http://localhost:8000/saml/acs',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => 'http://localhost:8000/saml/logout',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => 'x509cert',
                'privateKey' => 'key',
            ],

            'idp' => [
                'entityId' => 'https://sts.windows.net/813dd852-6578-4014-9b75-afb27ac33c28',
                'singleSignOnService' => [
                    'url' => 'https://login.microsoftonline.com/813dd852-6578-4014-9b75-afb27ac33c28/saml2',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'singleLogoutService' => [
                    'url' => 'https://login.microsoftonline.com/813dd852-6578-4014-9b75-afb27ac33c28/saml2',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => 'x509cert',
            ],
            'security' => [
                'lowercaseUrlencoding' => true,
            ],
        ];
        return $sugar_config;
    }
}

class MockAdministration
{
    public $settings = [];

    public function retrieveSettings($category = false, $clean = false)
    {
        return $this;
    }
}
