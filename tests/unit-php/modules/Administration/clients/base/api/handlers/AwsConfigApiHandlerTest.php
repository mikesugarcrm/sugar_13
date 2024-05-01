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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api\handlers;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \AwsConfigApiHandler
 */
class AwsConfigApiHandlerTest extends TestCase
{
    /**
     * @covers ::updateAWSDomainsOnCSP()
     */
    public function testUpdateAWSDomainsOnCSPWithEmptySettings()
    {
        $awsConfigApi = $this->getAwsConfigApiHandlerMock([
            'saveToCSP',
        ]);
        $awsConfigApi->updateAWSDomainsOnCSP([], []);

        $awsConfigApi->expects($this->never())->method('saveToCSP');
    }

    public function updateAWSDomainsOnCSPProvider(): array
    {
        return [
            // user sets new url
            [
                'settings' => [
                    'aws_connect_url' => 'https://example.my.connect.aws/ccp-v2',
                ],
                'oldSettings' => [
                    'aws_connect_url' => '',
                ],
                'allowListDomains' => [
                    'example.my.connect.aws',
                    '*.example.com',
                ],
                'domainsToRemove' => [],
                'domainsToAppend' => [
                    'example.my.connect.aws',
                    '*.example.com',
                ],
            ],
            // user changes url
            [
                'settings' => [
                    'aws_connect_url' => 'https://example.my.connect.aws/ccp-v2',
                ],
                'oldSettings' => [
                    'aws_connect_url' => 'https://example-old.my.connect.aws/ccp-v2',
                ],
                'allowListDomains' => [
                    'example.my.connect.aws',
                    '*.example.com',
                ],
                'domainsToRemove' => [
                    'example-old.my.connect.aws',
                ],
                'domainsToAppend' => [
                    'example.my.connect.aws',
                    '*.example.com',
                ],
            ],
            // user removes url
            [
                'settings' => [
                    'aws_connect_url' => '',
                ],
                'oldSettings' => [
                    'aws_connect_url' => 'https://example-old.my.connect.aws/ccp-v2',
                ],
                'allowListDomains' => [
                    '*.example.com',
                ],
                'domainsToRemove' => [
                    '*.example.com',
                    'example-old.my.connect.aws',
                ],
                'domainsToAppend' => [],
            ],
        ];
    }

    /**
     * @covers ::updateAWSDomainsOnCSP()
     *
     * @dataProvider updateAWSDomainsOnCSPProvider
     */
    public function testUpdateAWSDomainsOnCSP($settings, $oldSettings, $allowListDomains, $domainsToRemove, $domainsToAppend)
    {
        $awsConfigApi = $this->getAwsConfigApiHandlerMock([
            'saveToCSP',
            'getAWSAllowListDomains',
        ]);

        $awsConfigApi->method('getAWSAllowListDomains')->willReturn($allowListDomains);

        $awsConfigApi->expects($this->once())->method('saveToCSP')->with($domainsToRemove, $domainsToAppend);
        $awsConfigApi->updateAWSDomainsOnCSP($settings, $oldSettings);
    }

    /**
     * @covers ::updateAWSDomainsOnCSPForPortal()
     */
    public function testUpdateAWSDomainsOnCSPForPortalWithEmptySettings()
    {
        $awsConfigApi = $this->getAwsConfigApiHandlerMock([
            'saveToCSP',
        ]);
        $awsConfigApi->updateAWSDomainsOnCSPForPortal([]);

        $awsConfigApi->expects($this->never())->method('saveToCSP');
    }

    public function updateAWSDomainsOnCSPForPortalProvider(): array
    {
        return [
            // user enables Portal Chat
            [
                'settings' => [
                    'aws_connect_enable_portal_chat' => true,
                ],
                'allowListDomains' => [
                    'wss://*.example.com',
                ],
                'domainsToRemove' => [],
                'domainsToAppend' => [
                    'wss://*.example.com',
                ],
            ],
            // user disables Portal Chat
            [
                'settings' => [
                    'aws_connect_enable_portal_chat' => false,
                ],
                'allowListDomains' => [
                    'wss://*.example.com',
                ],
                'domainsToRemove' => [
                    'wss://*.example.com',
                ],
                'domainsToAppend' => [],
            ],
        ];
    }

    /**
     * @covers ::updateAWSDomainsOnCSPForPortal()
     *
     * @dataProvider updateAWSDomainsOnCSPForPortalProvider
     */
    public function testUpdateAWSDomainsOnCSPForPortal($settings, $allowListDomains, $domainsToRemove, $domainsToAppend)
    {
        $awsConfigApi = $this->getAwsConfigApiHandlerMock([
            'saveToCSP',
            'getAWSAllowListDomainsForPortal',
        ]);

        $awsConfigApi->method('getAWSAllowListDomainsForPortal')->willReturn($allowListDomains);

        $awsConfigApi->expects($this->once())->method('saveToCSP')->with($domainsToRemove, $domainsToAppend);
        $awsConfigApi->updateAWSDomainsOnCSPForPortal($settings);
    }

    public function testValidateUrls()
    {
        $awsConfigApi = $this->getAwsConfigApiHandlerMock();

        $this->assertTrue(
            TestReflection::callProtectedMethod($awsConfigApi, 'validateUrls', ['http://www.test.com'])
        );
        $this->assertFalse(
            TestReflection::callProtectedMethod($awsConfigApi, 'validateUrls', ['www.test.com'])
        );
        $this->assertFalse(
            TestReflection::callProtectedMethod($awsConfigApi, 'validateUrls', ['http://www.test.com', 'www.test.com'])
        );
    }

    protected function getAwsConfigApiHandlerMock($methods = [])
    {
        return $this->getMockBuilder('AwsConfigApiHandler')
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
    }
}
