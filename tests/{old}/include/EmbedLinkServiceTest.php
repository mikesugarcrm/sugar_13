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

class EmbedLinkServiceTest extends TestCase
{
    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_NoLinksInText_ReturnsNoEmbedData()
    {
        $service = new EmbedLinkService();
        $actual = $service->get('foo bar');
        $this->assertEquals(0, safeCount($actual['embeds']), 'Should not return any embed data');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_TwoImageLinksInText_ReturnsTwoImageEmbedData()
    {
        $service = new EmbedLinkService();
        $actual = $service->get('http://www.foo.com/images/bar.jpg https://www.sugarcrm.com/logo/logo.gif');
        $this->assertEquals(2, safeCount($actual['embeds']), 'Should return two embed data');
        $this->assertEquals('image', $actual['embeds'][0]['type'], 'Should return image type data');
        $this->assertEquals('image', $actual['embeds'][1]['type'], 'Should return image type data');
        $this->assertEquals(
            'http://www.foo.com/images/bar.jpg',
            $actual['embeds'][0]['src'],
            'Should have the image url'
        );
        $this->assertEquals(
            'https://www.sugarcrm.com/logo/logo.gif',
            $actual['embeds'][1]['src'],
            'Should have the image url'
        );
    }

    /**
     * Test regexp that finds all URLs in an input text
     *
     * @dataProvider findAllUrls_DataProvider
     * @covers       EmbedLinkService::findAllUrls
     * @param $input
     * @param $count
     */
    public function testFindAllUrls_InputText_ReturnsCorrectResults($input, $count)
    {
        $service = new EmbedLinkTestServiceProxy();
        $actual = $service->findAllUrls($input);
        $this->assertEquals($count, count($actual));
    }

    /**
     * Data Providers
     */
    public function findAllUrls_DataProvider()
    {
        return [
            ['input' => 'http://www.foobar.com', 'count' => 1],
            ['input' => 'foo bar', 'count' => 0],
            ['input' => 'foo www.foobar.com bar', 'count' => 1],
            ['input' => 'foo www.bar.com:8888/123/test?q=fdfad&i=fdafdas bar', 'count' => 1],
            ['input' => 'foo https://www.bar.com/123/test?q=fdfad&i=fdafdas bar', 'count' => 1],
            ['input' => 'foo https://www.bar.com bar http://www.foo.uk.co/', 'count' => 2],
            ['input' => 'foo.com', 'count' => 0],
            ['input' => 'foo@bar.com', 'count' => 0],
            ['input' => 'foo.bar.com', 'count' => 0],
            ['input' => 'http://test.foobar.com', 'count' => 1],
            ['input' => 'https://WWW.FOOBAR.COM/', 'count' => 1],
            ['input' => 'http://www.youtube.com/watch?v=N2u44-zZYdo&list=PL37ZVnwpeshF7AHpbZt33aW0brYJyNftx http://www.youtube.com/watch?v=BY0-AI1Sxy0&list=PL37ZVnwpeshF7AHpbZt33aW0brYJyNftx', 'count' => 2],
        ];
    }
}

class EmbedLinkTestServiceProxy extends EmbedLinkService
{
    public function __call($name, $args)
    {
        return call_user_func_array([$this, $name], $args);
    }
}
