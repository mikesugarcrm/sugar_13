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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \MarketingExtrasApi
 */
class MarketingExtrasApiTest extends TestCase
{
    private static $expectedContentUrl = 'https://www.example.com/exciting-content';
    private static $frenchContentUrl = 'https://www.example.com/exciting-content-in-french';
    private static $expectedImageUrl = 'https://www.example.com/image.jpg';

    /**
     * @covers ::getMarketingExtras()
     * @dataProvider providerGetMarketingExtras
     */
    public function testGetMarketingExtras(
        string  $marketingUrl,
        ?string $selectedLang,
        bool    $marketingException,
        string  $imageUrl,
        bool    $imageException
    ) {

        $api = $this->getMarketingExtrasApiMock(
            [
                'getMarketingExtrasService',
                'parseArgs',
            ]
        );
        $marketingExtras = $this->getMarketingExtrasMock(
            [
                'getMarketingContentUrl',
                'getBackgroundImageUrl',
            ]
        );
        $api->method('getMarketingExtrasService')
            ->willReturn($marketingExtras);

        $getMarketingUrl = $marketingExtras->expects($this->once())
            ->method('getMarketingContentUrl')
            ->with($this->equalTo($selectedLang));

        $api->expects($this->once())
            ->method('parseArgs')
            ->willReturn(['language' => $selectedLang]);

        if ($marketingException) {
            $getMarketingUrl->will($this->throwException(new \Exception()));
        } else {
            $getMarketingUrl->willReturn($marketingUrl);
        }

        $getImageUrl = $marketingExtras->expects($this->once())
            ->method('getBackgroundImageUrl');

        if ($imageException) {
            $getImageUrl->will($this->throwException(new \Exception()));
        } else {
            $getImageUrl->willReturn($imageUrl);
        }

        $marketingContent = $api->getMarketingExtras($this->getRestServiceMock(), [$selectedLang]);

        $this->assertEquals(['content_url' => $marketingUrl, 'image_url' => $imageUrl], $marketingContent);
    }

    public function providerGetMarketingExtras()
    {
        return [
            // marketing extras on, normal result URL, default language, no exception
            [
                MarketingExtrasApiTest::$expectedContentUrl,
                null,
                false,
                MarketingExtrasApiTest::$expectedImageUrl,
                false,
            ],

            // marketing extras on, French result URL, French language, no exception
            [
                MarketingExtrasApiTest::$frenchContentUrl,
                'fr_FR',
                false,
                MarketingExtrasApiTest::$expectedImageUrl,
                false,
            ],

            // marketing extras off, empty results URL, language doesn't matter, no exception
            [
                '',
                null,
                false,
                '',
                false,
            ],

            // marketing extras on, normal content URL, default language, no content exception, image exception
            [
                MarketingExtrasApiTest::$expectedContentUrl,
                null,
                false,
                '',
                true,
            ],

            // marketing extras on, content exception is thrown, no image exception
            [
                '',
                null,
                true,
                MarketingExtrasApiTest::$expectedImageUrl,
                false,
            ],
        ];
    }

    protected function getMarketingExtrasApiMock($methods = null)
    {
        return $this->getMockBuilder('MarketingExtrasApi')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getMarketingExtrasMock($methods = null)
    {
        return $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\Marketing\MarketingExtras::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getRestServiceMock($methods = null)
    {
        return $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
