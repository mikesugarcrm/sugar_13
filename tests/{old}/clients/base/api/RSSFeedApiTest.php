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
use Sugarcrm\Sugarcrm\Security\ValueObjects\ExternalResource;

class RSSFeedApiTest extends TestCase
{
    protected static $rssFeedMaxEntries = null;

    protected static $api;

    public static function setUpBeforeClass(): void
    {
        self::$api = new RSSFeedApi();
    }

    protected function setUp(): void
    {
        global $sugar_config;
        if (isset($sugar_config['rss_feed_max_entries'])) {
            self::$rssFeedMaxEntries = $sugar_config['rss_feed_max_entries'];
        }
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        if (self::$rssFeedMaxEntries) {
            $sugar_config['rss_feed_max_entries'] = self::$rssFeedMaxEntries;
        }
    }

    /**
     * Tests validation of an RSS Feed URL
     *
     * @param string $url An URL for testing
     * @param boolean $expect True or false for validation
     * @dataProvider providerTestValidateFeedUrl
     */
    public function testValidateFeedUrl($url, $expect)
    {
        try {
            self::$api->validateFeedUrl($url);
            $valid = true;
        } catch (SugarApiException $e) {
            $valid = false;
        }

        $this->assertEquals($valid, $expect);
    }

    /**
     * Tests feed limit max settings
     *
     * @param array $args Mock array of args that could specify a feed limit
     * @param mixed $configMax A value to set the sugar_config max value to
     * @param int $expect The expected value to test against
     * @dataProvider providerTestGetFeedLimit
     */
    public function testGetFeedLimit($args, $configMax, $expect)
    {
        global $sugar_config;
        $sugar_config['rss_feed_max_entries'] = $configMax;

        $limit = self::$api->getFeedLimit($args);
        $this->assertEquals($limit, $expect);
    }

    /**
     * Tests that an XML string is parseable
     *
     * @param string $data XML String data
     * @param boolean $expect Expected result
     * @dataProvider providerTestGetFeedXMLObject
     */
    public function testGetFeedXMLObject($data, $expect)
    {
        try {
            $rss = self::$api->getFeedXMLObject($data);
        } catch (SugarApiException $e) {
            $rss = null;
        }

        $test = $rss !== null && $rss instanceof SimpleXMLElement;
        $this->assertEquals($test, $expect);
    }

    /**
     * Tests that a parse XML object yields proper and expected results
     *
     * @param string $data The XML string to parse
     * @param int $limit The limit of entries to return
     * @param array Expected result
     * @dataProvider providerTestGetParsedXMLData
     */
    public function testGetParsedXMLData($data, $limit, $expect)
    {
        $rss = self::$api->getFeedXMLObject($data);
        $result = self::$api->getParsedXMLData($rss, $limit);
        $this->assertEquals($expect, $result);
    }

    /**
     * Tests the full API method to ensure it works from end to end as expected
     */
    public function testGetFeed()
    {
        // Mock our API object so that the getFeedContent returns a known value
        $api = $this->createPartialMock('RSSFeedApi', ['getFeedContent']);
        $api->expects($this->once())
            ->method('getFeedContent')
            ->will($this->returnValue($this->getXMLWithHeader()));

        // Setup the args to pass
        $args = [
            'feed_url' => 'http://www.sugarcrm.com/feed.xml',
            'limit' => 4,
        ];

        // Call the API
        $actual = $api->getFeed(SugarTestRestUtilities::getRestServiceMock(), $args);

        // Get our test data for comparison...
        $specs = $this->providerTestGetParsedXMLData();

        // And build what the result would look like from the API
        $expect = ['feed' => $specs[1]['expect']];

        // Test it
        $this->assertEquals($actual, $expect);
    }

    public function providerTestValidateFeedUrl()
    {
        return [
            [
                'url' => 'http://some.feed.url/',
                'expect' => true,
            ],
            [
                'url' => 'https://some.feed.url/feed.xml',
                'expect' => true,
            ],
            [
                'url' => 'ftp://some.feed.url/feed.xml',
                'expect' => false,
            ],
            [
                'url' => 'some.feed.url/feed.xml',
                'expect' => false,
            ],
            [
                'url' => 'feed.xml',
                'expect' => false,
            ],
        ];
    }

    public function providerTestGetFeedLimit()
    {
        return [
            [
                'args' => ['limit' => 10],
                'configMax' => null,
                'expect' => 10,
            ],
            [
                'args' => ['limit' => 30],
                'configMax' => null,
                'expect' => 20,
            ],
            [
                'args' => [],
                'configMax' => 30,
                'expect' => 5,
            ],
            [
                'args' => ['limit' => 20],
                'configMax' => 10,
                'expect' => 10,
            ],
            [
                'args' => [],
                'configMax' => 3,
                'expect' => 3,
            ],
        ];
    }

    public function providerTestGetFeedXMLObject()
    {
        return [
            [
                'data' => $this->getXMLWithHeader(),
                'expect' => true,
            ],
            [
                'data' => $this->getXMLWithoutHeader(),
                'expect' => true,
            ],
            [
                'data' => $this->getMalformedXML(),
                'expect' => false,
            ],
        ];
    }

    public function providerTestGetParsedXMLData()
    {
        return [
            [
                'data' => $this->getXMLWithHeader(),
                'limit' => 10,
                'expect' => [
                    'title' => 'Test RSSFeedApi',
                    'link' => 'http://www.sugarcrm.com',
                    'description' => 'Test Desc',
                    'publication_date' => 'Tue, 3 Aug 2014 13:38:55 -0800',
                    'entries' => [
                        [
                            'title' => 'RSS Feed 0',
                            'description' => 'Feed 0 Desc',
                            'link' => 'http://www.sugarcrm.com/feed0.xml',
                            'publication_date' => 'Tue, 10 Aug 2014 13:38:55 -0800',
                            'source' => 'FooBar News',
                            'author' => 'Dr. Seuss',
                        ],
                        [
                            'title' => 'RSS Feed 1',
                            'description' => 'Feed 1 Desc',
                            'link' => 'http://www.sugarcrm.com/feed1.xml',
                            'publication_date' => 'Tue, 17 Aug 2014 13:38:55 -0800',
                            'source' => 'MSNBC',
                            'author' => '',
                        ],
                        [
                            'title' => 'RSS Feed 2',
                            'description' => 'Feed 2 Desc',
                            'link' => 'http://www.sugarcrm.com/feed2.xml',
                            'publication_date' => 'Tue, 24 Aug 2014 13:38:55 -0800',
                            'source' => '',
                            'author' => 'Shasta McNasty',
                        ],
                        [
                            'title' => 'RSS Feed 3',
                            'description' => 'Feed 3 Desc',
                            'link' => 'http://www.sugarcrm.com/feed3.xml',
                            'publication_date' => '',
                            'source' => '',
                            'author' => '',
                        ],
                        [
                            'title' => 'RSS Feed 4',
                            'description' => 'Feed 4 Desc',
                            'link' => 'http://www.sugarcrm.com/feed4.xml',
                            'publication_date' => '',
                            'source' => '',
                            'author' => '',
                        ],
                        [
                            'title' => 'RSS Feed 5',
                            'description' => 'Feed 5 Desc',
                            'link' => 'http://www.sugarcrm.com/feed5.xml',
                            'publication_date' => '',
                            'source' => '',
                            'author' => '',
                        ],
                    ],
                ],
            ],
            [
                'data' => $this->getXMLWithHeader(),
                'limit' => 4,
                'expect' => [
                    'title' => 'Test RSSFeedApi',
                    'link' => 'http://www.sugarcrm.com',
                    'description' => 'Test Desc',
                    'publication_date' => 'Tue, 3 Aug 2014 13:38:55 -0800',
                    'entries' => [
                        [
                            'title' => 'RSS Feed 0',
                            'description' => 'Feed 0 Desc',
                            'link' => 'http://www.sugarcrm.com/feed0.xml',
                            'publication_date' => 'Tue, 10 Aug 2014 13:38:55 -0800',
                            'source' => 'FooBar News',
                            'author' => 'Dr. Seuss',
                        ],
                        [
                            'title' => 'RSS Feed 1',
                            'description' => 'Feed 1 Desc',
                            'link' => 'http://www.sugarcrm.com/feed1.xml',
                            'publication_date' => 'Tue, 17 Aug 2014 13:38:55 -0800',
                            'source' => 'MSNBC',
                            'author' => '',
                        ],
                        [
                            'title' => 'RSS Feed 2',
                            'description' => 'Feed 2 Desc',
                            'link' => 'http://www.sugarcrm.com/feed2.xml',
                            'publication_date' => 'Tue, 24 Aug 2014 13:38:55 -0800',
                            'source' => '',
                            'author' => 'Shasta McNasty',
                        ],
                        [
                            'title' => 'RSS Feed 3',
                            'description' => 'Feed 3 Desc',
                            'link' => 'http://www.sugarcrm.com/feed3.xml',
                            'publication_date' => '',
                            'source' => '',
                            'author' => '',
                        ],
                    ],
                ],
            ],
            [
                'data' => $this->getXMLWithoutHeader(),
                'limit' => 10,
                'expect' => [
                    'title' => '',
                    'link' => '',
                    'description' => '',
                    'publication_date' => '',
                    'entries' => [
                        [
                            'title' => 'RSS Feed 0',
                            'description' => 'Feed 0 Desc',
                            'link' => 'http://www.sugarcrm.com/feed0.xml',
                            'publication_date' => 'Tue, 10 Aug 2014 13:38:55 -0800',
                            'source' => 'Hotwire',
                            'author' => 'Joe Shmoe',
                        ],
                        [
                            'title' => 'RSS Feed 1',
                            'description' => 'Feed 1 Desc',
                            'link' => 'http://www.sugarcrm.com/feed1.xml',
                            'publication_date' => 'Tue, 17 Aug 2014 13:38:55 -0800',
                            'source' => '',
                            'author' => 'Mickey Mouse',
                        ],
                        [
                            'title' => 'RSS Feed 2',
                            'description' => 'Feed 2 Desc',
                            'link' => 'http://www.sugarcrm.com/feed2.xml',
                            'publication_date' => 'Tue, 24 Aug 2014 13:38:55 -0800',
                            'source' => 'Home Depot',
                            'author' => '',
                        ],
                        [
                            'title' => 'RSS Feed 3',
                            'description' => 'Feed 3 Desc',
                            'link' => 'http://www.sugarcrm.com/feed3.xml',
                            'publication_date' => '',
                            'source' => '',
                            'author' => '',
                        ],
                        [
                            'title' => 'RSS Feed 4',
                            'description' => 'Feed 4 Desc',
                            'link' => 'http://www.sugarcrm.com/feed4.xml',
                            'publication_date' => '',
                            'source' => '',
                            'author' => '',
                        ],
                        [
                            'title' => 'RSS Feed 5',
                            'description' => 'Feed 5 Desc',
                            'link' => 'http://www.sugarcrm.com/feed5.xml',
                            'publication_date' => '',
                            'source' => '',
                            'author' => '',
                        ],
                    ],
                ],
            ],
            [
                'data' => $this->getXMLWithoutHeader(),
                'limit' => 3,
                'expect' => [
                    'title' => '',
                    'link' => '',
                    'description' => '',
                    'publication_date' => '',
                    'entries' => [
                        [
                            'title' => 'RSS Feed 0',
                            'description' => 'Feed 0 Desc',
                            'link' => 'http://www.sugarcrm.com/feed0.xml',
                            'publication_date' => 'Tue, 10 Aug 2014 13:38:55 -0800',
                            'source' => 'Hotwire',
                            'author' => 'Joe Shmoe',
                        ],
                        [
                            'title' => 'RSS Feed 1',
                            'description' => 'Feed 1 Desc',
                            'link' => 'http://www.sugarcrm.com/feed1.xml',
                            'publication_date' => 'Tue, 17 Aug 2014 13:38:55 -0800',
                            'source' => '',
                            'author' => 'Mickey Mouse',
                        ],
                        [
                            'title' => 'RSS Feed 2',
                            'description' => 'Feed 2 Desc',
                            'link' => 'http://www.sugarcrm.com/feed2.xml',
                            'publication_date' => 'Tue, 24 Aug 2014 13:38:55 -0800',
                            'source' => 'Home Depot',
                            'author' => '',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getXMLWithHeader()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">

<channel>
  <title>Test RSSFeedApi</title>
  <link>http://www.sugarcrm.com</link>
  <description>Test Desc</description>
  <pubDate>Tue, 3 Aug 2014 13:38:55 -0800</pubDate>
  <item>
    <title>RSS Feed 0</title>
    <link>http://www.sugarcrm.com/feed0.xml</link>
    <description>Feed 0 Desc</description>
    <pubDate>Tue, 10 Aug 2014 13:38:55 -0800</pubDate>
    <source>FooBar News</source>
    <author>Dr. Seuss</author>
  </item>
  <item>
    <title>RSS Feed 1</title>
    <link>http://www.sugarcrm.com/feed1.xml</link>
    <description>Feed 1 Desc</description>
    <pubDate>Tue, 17 Aug 2014 13:38:55 -0800</pubDate>
    <source>MSNBC</source>
  </item>
  <item>
    <title>RSS Feed 2</title>
    <link>http://www.sugarcrm.com/feed2.xml</link>
    <description>Feed 2 Desc</description>
    <pubDate>Tue, 24 Aug 2014 13:38:55 -0800</pubDate>
    <author>Shasta McNasty</author>
  </item>
  <item>
    <title>RSS Feed 3</title>
    <link>http://www.sugarcrm.com/feed3.xml</link>
    <description>Feed 3 Desc</description>
  </item>
  <item>
    <title>RSS Feed 4</title>
    <link>http://www.sugarcrm.com/feed4.xml</link>
    <description>Feed 4 Desc</description>
  </item>
  <item>
    <title>RSS Feed 5</title>
    <link>http://www.sugarcrm.com/feed5.xml</link>
    <description>Feed 5 Desc</description>
  </item>
</channel>

</rss>
XML;
    }

    protected function getXMLWithoutHeader()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <entry>
        <title>RSS Feed 0</title>
        <link>http://www.sugarcrm.com/feed0.xml</link>
        <description>Feed 0 Desc</description>
        <pubDate>Tue, 10 Aug 2014 13:38:55 -0800</pubDate>
        <source>Hotwire</source>
        <author>Joe Shmoe</author>
    </entry>
    <entry>
        <title>RSS Feed 1</title>
        <link>http://www.sugarcrm.com/feed1.xml</link>
        <description>Feed 1 Desc</description>
        <pubDate>Tue, 17 Aug 2014 13:38:55 -0800</pubDate>
        <author>Mickey Mouse</author>
    </entry>
    <entry>
        <title>RSS Feed 2</title>
        <link>http://www.sugarcrm.com/feed2.xml</link>
        <description>Feed 2 Desc</description>
        <pubDate>Tue, 24 Aug 2014 13:38:55 -0800</pubDate>
        <source>Home Depot</source>
    </entry>
    <entry>
        <title>RSS Feed 3</title>
        <link>http://www.sugarcrm.com/feed3.xml</link>
        <description>Feed 3 Desc</description>
    </entry>
    <entry>
        <title>RSS Feed 4</title>
        <link>http://www.sugarcrm.com/feed4.xml</link>
        <description>Feed 4 Desc</description>
    </entry>
    <entry>
        <title>RSS Feed 5</title>
        <link>http://www.sugarcrm.com/feed5.xml</link>
        <description>Feed 5 Desc</description>
    </entry>
</rss>
XML;
    }

    protected function getMalformedXML()
    {
        return <<<XML
<?xml version="1.0" ?>
<rss version="2.0">
    <channel><item><foo></item></channel>
    </foo>
</rss>
XML;
    }
}
