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

namespace Sugarcrm\SugarcrmTestsUnit\Marketing;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Marketing\MarketingExtrasContent
 */
class MarketingExtrasContentTest extends TestCase
{
    protected $extrasContent;

    protected $marketingContentConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->extrasContent = $this->getMarketingExtrasContentMock([
            'getMarketingContentConfig',
            'getQueryParams',
            'isContentDisplayable',
        ]);

        $this->marketingContentConfig = [
            'url' => 'https://www.sugarcrm.com/',
            'static_url' => 'include/static.html',
        ];

        $this->extrasContent->method('getMarketingContentConfig')
            ->willReturn($this->marketingContentConfig);

        $this->extrasContent->method('getQueryParams')
            ->willReturn([
                'flavor' => 'ent',
                'version' => '10.1.0',
            ]);
    }

    /**
     * @covers ::getMarketingExtrasContentUrl()
     */
    public function testGetMarketingExtrasContentUrlWhenReachable()
    {
        $this->extrasContent->method('isContentDisplayable')
            ->willReturn(true);

        $expected = $this->marketingContentConfig['url'] . '?flavor=ent&version=10.1.0';
        $actual = $this->extrasContent->getMarketingExtrasContentUrl();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::getMarketingExtrasContentUrl()
     */
    public function testGetMarketingExtrasContentUrlWhenNotReachable()
    {
        $this->extrasContent->method('isContentDisplayable')
            ->willReturn(false);

        $expected = $this->marketingContentConfig['static_url'];
        $actual = $this->extrasContent->getMarketingExtrasContentUrl();

        $this->assertEquals($expected, $actual);
    }

    protected function getMarketingExtrasContentMock($methods = [])
    {
        return $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Marketing\MarketingExtrasContent::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
    }
}
