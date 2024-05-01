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

namespace Sugarcrm\SugarcrmTestsUnit\src\DocumentMerge\Configuration;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DocumentMerge\Configuration\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $configurator;

    /**
     *
     * @var MockSugarConfig
     */
    private $sugarConfig;

    /**
     * Instantiate the configurator
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->sugarConfig = $this->createMock(\SugarConfig::class);

        $this->configurator = $this->getMockBuilder(Configuration::class)
            ->setConstructorArgs([$this->sugarConfig])
            ->onlyMethods(['getRegion'])
            ->getMock();

        $this->configurator->method('getRegion')
            ->withAnyParameters()
            ->willReturn('eu-west-1');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->configurator = null;
        $this->sugarConfig = null;
    }

    /**
     * Tests if there is a system key in config
     *
     * @return void
     */
    public function testGetSystemKey(): void
    {
        $this->sugarConfig
            ->method('get')
            ->with('unique_key', $this->anything())
            ->willReturn('1234567890');

        $systemKey = $this->configurator->getSystemKey();
        $this->assertEquals('1234567890', $systemKey);
    }

    /**
     * Tests if there is a system url in config
     *
     * @return void
     */
    public function testGetSystemUrl(): void
    {
        $this->sugarConfig
            ->method('get')
            ->with('site_url', $this->anything())
            ->willReturn('https://sugar.instance.com');

        $url = $this->configurator->getSystemUrl();
        $this->assertEquals('https://sugar.instance.com', $url);
    }

    /**
     * Test if we get a valid max retry number
     * @return void
     */
    public function testGetMaxRetries(): void
    {
        $this->sugarConfig
            ->method('get')
            ->with('document_merge', $this->anything())
            ->willReturn([
                'max_retries' => 10,
            ]);

        $maxRetries = $this->configurator->getMaxRetries();
        $this->assertEquals(10, $maxRetries);
    }

    public function testGetServiceURL(): void
    {
        $this->sugarConfig
            ->method('get')
            ->with('document_merge', $this->anything())
            ->willReturn([
                'service_urls' => [
                    'default' => 'https://document-merge-us-west-2-prod.service.sugarcrm.com',
                ],
            ]);

        $serviceUrl = $this->configurator->getServiceURL();
        $this->assertEquals('https://document-merge-us-west-2-prod.service.sugarcrm.com', $serviceUrl);
    }
}
