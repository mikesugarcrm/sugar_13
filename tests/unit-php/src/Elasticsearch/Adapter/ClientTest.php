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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Adapter;

use Elastica\Response;
use Exception;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;
use Sugarcrm\Sugarcrm\Elasticsearch\Logger;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client
 */
class ClientTest extends TestCase
{
    protected $config = ['host' => 'localhost', 'port' => '9200'];
    protected $logger;

    /**
     * @covers ::__construct
     * @covers ::setLogger
     * @covers ::parseConfig
     *
     * @param bool $isMts
     * @param string|null $expectedValue
     * @return void
     *
     * @dataProvider providerTestClientHeader
     */
    public function testConstructor(bool $isMts, ?string $expectedValue) : void
    {
        $client = $this->getTestClient($isMts);
        $this->assertSame($this->logger, TestReflection::getProtectedValue($client, '_logger'));

        // test header
        $config = $client->getConfig();
        $this->assertSame($config['connections'][0]['headers']['SEARCHVERSION'] ?? null, $expectedValue);
    }

    public function providerTestClientHeader() : array
    {
        return [
            'not MTS, no search header' => [false, null],
            'MTS, returns the greatest support version' => [true, '{"open": ["2.7"]}'],
        ];
    }

    /**
     * @covers ::setConfig
     * @covers ::getConfig
     * @covers ::getElasticServerVersion
     * @covers ::getAllowedVersions
     *
     * @dataProvider providerTestSettersAndGetters
     */
    public function testSettersAndGetters($version)
    {
        $client = $this->getTestClient();
        $client->setConfig($this->config);

        $this->assertSame($this->config['host'], $client->getConfig()['host']);
        $this->assertSame($this->config['host'], $client->getConfig('host'));
        $this->assertSame($this->config['port'], $client->getConfig('port'));

        TestReflection::setProtectedValue($client, 'version', $version);
        $this->assertSame($version, $client->getElasticServerVersion(false, false));

        $this->assertTrue(in_array($version, $client->getAllowedVersions()));
    }

    public function providerTestSettersAndGetters()
    {
        return [
            ['5.4'],
            ['5.6'],
            ['6.x'],
            ['7.x'],
        ];
    }

    /**
     * @covers ::checkEsVersion
     *
     * @dataProvider providerTestCheckVersion
     */
    public function testCheckVersion(string $version, bool $isOpenSearch, bool $expected)
    {
        $client = $this->getTestClient();
        TestReflection::setProtectedValue($client, 'openSearch', $isOpenSearch);
        $this->assertSame($expected, TestReflection::callProtectedMethod($client, 'checkEsVersion', [$version]));
    }

    public function providerTestCheckVersion()
    {
        return [
            //elasticSearch
            // 9.x is not supported
            ['9.0.0', false, false],
            // 8.x is supported
            ['8.3.0', false, true],
            // 7.x is supported
            ['7.9.0', false, true],
            //6.0.x is supported
            ['6.0.0', false, true],
            ['6.0.9', false, true],
            ['6.9', false, true],
            // version 5.4 to 5.6.x are supported
            ['5.6.0', false, true],
            ['5.6.9', false, true],
            ['5.4.0', false, true],
            ['5.4.9', false, true],
            ['5.4', false, true],
            ['5.5.0', false, true],
            ['5.5', false, true],
            // 1.x and 2.x are not supported
            ['1.7', false, false],
            ['2.3.1', false, false],
            // --- OpenSearch
            ['2.8', true, false],
            ['2.7', true, true],
            ['2.3', true, true],
            ['1.0', true, true],
            ['0.9', true, false],
        ];
    }

    /**
     * @covers ::getElasticServerVersion
     * @dataProvider providerTestGetVersion
     */
    public function testGetVersion(string $responseString, string $expected)
    {
        $clientMock = $this->getClientMock(['ping']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));

        $this->assertSame($expected, $clientMock->getElasticServerVersion(true, false));
    }

    public function providerTestGetVersion(): array
    {
        return [
            [
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0"
                  }
                }',
                '6.0.0',
            ],
            [
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4"
                  }
                }',
                '5.4',
            ],
            [
                '{
                  "status" : 200,
                  "name" : "OSInstance",
                  "version" : {
                    "distribution" : "opensearch",
                    "minimum_wire_compatibility_version" : "7.10.0",
                    "minimum_index_compatibility_version" : "7.0.0"
                  },
                  "tagline" : "The OpenSearch Project: https://opensearch.org/"
                }',
                '7.0.0',
            ],
        ];
    }

    /**
     * @covers ::getElasticServerVersion
     *
     * @dataProvider providerTestGetVersionException
     */
    public function testGetElasticServerVersionException(?string $responseString)
    {
        $clientMock = $this->getClientMock(['ping']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));

        $this->expectException(Exception::class);
        $clientMock->getElasticServerVersion(true, false);
    }

    public function providerTestGetVersionException(): array
    {
        return [
            [
                '{
                  "status" : 401,
                  "name" : "not_authorized",
                  "version" : {
                    "number" : "6.0.0"
                  }
                }',
            ],
            [
                '{
                  "status" : 200,
                  "name" : "no_version",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : ""
                  }
                }',
            ],
            // no response
            [null],
        ];
    }

    /**
     * @covers ::isAvailable
     * @covers ::verifyConnectivity
     * @covers ::loadAvailability
     * @covers ::updateAvailability
     * @covers ::processDataResponse
     *
     * @dataProvider providerTestIsAvailable
     */
    public function testIsAvailable($force, $isSearchEngineAvallble, $responseString, $expected)
    {
        $clientMock = $this->getClientMock(['ping', 'isSearchEngineAvailable', 'saveAdminStatus']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));
        $clientMock->expects($this->any())
            ->method('isSearchEngineAvailable')
            ->will($this->returnValue($isSearchEngineAvallble));

        $clientMock->expects($this->any())
            ->method('saveAdminStatus');

        $this->assertSame($expected, $clientMock->isAvailable($force));
    }


    public function t1()
    {
        return [
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "OSInstance",
                  "cluster_name" : "897790359321:br-9835",
                  "cluster_uuid" : "ibL_fHgRS3uCgXCfwqIQLw",
                  "version" : {
                    "distribution" : "opensearch",
                    "number" : "2.7.0",
                    "minimum_wire_compatibility_version" : "7.10.0",
                    "minimum_index_compatibility_version" : "7.0.0"
                  },
                  "tagline" : "The OpenSearch Project: https://opensearch.org/"
                }',
                true,
            ],
        ];
    }

    public function providerTestIsAvailable()
    {
        return [
            // ES 6.x support
            // no force update
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, all good
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, new ES status is good
            [
                true,
                false,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // ES 5.6.x support
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.6.9",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.6.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            [
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, all good
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // force update, new ES status is good
            [
                true,
                false,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ],
            // update to not available
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // bad status
            [
                true,
                false,
                '{
                  "status" : 401,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // ES version 1.7, not supported
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "1.7",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // ES version 2.3, not supported
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "2.3.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // ES version 5.3, not supported
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.3.0.",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ],
            // ES 7.9
            [
                true,
                true,
                '{
                    "status" : 200,
                    "name": "87e15e8de0f7",
                    "cluster_name": "docker-cluster",
                    "cluster_uuid": "9CCmo2fsTyG5A1FncD7jKA",
                    "version": {
                    "number": "7.9.2",
                    "build_flavor": "default",
                    "build_type": "docker",
                    "build_hash": "d34da0ea4a966c4e49417f2da2f244e3e97b4e6e",
                    "build_date": "2020-09-23T00:45:33.626720Z",
                    "build_snapshot": false,
                    "lucene_version": "8.6.2",
                    "minimum_wire_compatibility_version": "6.8.0",
                    "minimum_index_compatibility_version": "6.0.0-beta1"
                    },
                    "tagline": "You Know, for Search"
                }',
                true,
            ],
            // OpenSearch
            [
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "OSInstance",
                  "cluster_name" : "897790359321:br-9835",
                  "cluster_uuid" : "ibL_fHgRS3uCgXCfwqIQLw",
                  "version" : {
                    "distribution" : "opensearch",
                    "number" : "2.7",
                    "minimum_wire_compatibility_version" : "7.10.0",
                    "minimum_index_compatibility_version" : "7.0.0"
                  },
                  "tagline" : "The OpenSearch Project: https://opensearch.org/"
                }',
                true,
            ],
        ];
    }


    /**
     * @covers ::verifyConnectivity
     * @covers ::onConnectionFailure
     */
    public function testVerifyConnectivityHandleException()
    {
        $clientMock = $this->getClientMock(['ping']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->throwException(new Exception()));

        $status = $clientMock->verifyConnectivity(false);
        $this->assertSame(Client::CONN_FAILURE, $status);
    }

    /**
     * @covers ::request
     */
    public function testRequestException()
    {
        $clientMock = $this->getClientMock(['isAvailable']);
        $clientMock->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(false));

        $this->expectException(Exception::class);
        $clientMock->request('/');
    }

    /**
     * @covers ::isOpenSearch
     * @dataProvider providerIsOpenSearch
     * @param string $responseString
     * @param bool $expected
     */
    public function testIsOpenSearch(string $responseString, bool $expected): void
    {
        $clientMock = $this->getClientMock(['ping']);
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));

        $this->assertSame($expected, $clientMock->isOpenSearch());
    }

    public function providerIsOpenSearch(): array
    {
        return [
            [
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "6.0.0"
                  }
                }',
                false,
            ],
            [
                '{
                  "status" : 200,
                  "name" : "OSInstance",
                  "version" : {
                    "distribution" : "opensearch",
                    "minimum_wire_compatibility_version" : "7.10.0",
                    "minimum_index_compatibility_version" : "7.0.0"
                  },
                  "tagline" : "The OpenSearch Project: https://opensearch.org/"
                }',
                true,
            ],
        ];
    }

    /**
     * @return Client Mock object
     */
    protected function getClientMock(array $methods = null)
    {
        $this->setLogger();
        $mock = TestMockHelper::getObjectMock($this, Client::class, $methods);
        $mock->setLogger($this->logger);
        return $mock;
    }

    /**
     * to get real Client instance
     * @return Client
     */
    protected function getTestClient(?bool $isMts = null)
    {
        $this->setLogger();
        $client = new Client($this->config, $this->logger, $isMts);
        return $client;
    }

    /**
     * set logger
     */
    protected function setLogger()
    {
        $logMgr = \LoggerManager::getLogger();
        // don't record anything in the log
        $logMgr->setLevel('off');
        $this->logger = new Logger($logMgr);
    }
}
