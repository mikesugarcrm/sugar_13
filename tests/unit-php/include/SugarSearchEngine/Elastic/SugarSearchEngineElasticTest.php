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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarSearchEngine\Elastic;

use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;

/**
 * @coversDefaultClass \SugarSearchEngineElastic
 */
class SugarSearchEngineElasticTest extends TestCase
{
    /**
     * @covers ::search
     * @dataProvider providerTestSearch
     */
    public function testSearch($query, $offset, $limit, array $options)
    {
        $engine = $this->getEngineMock();

        // stub search
        $resultSet = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $engine->expects($this->any())
            ->method('search')
            ->will($this->returnValue($resultSet));

        /* wire frame testing */

        $engine->expects($this->once())
            ->method('term')
            ->with($this->equalTo($query));

        $engine->expects($this->once())
            ->method('offset')
            ->with($this->equalTo($offset));

        $engine->expects($this->once())
            ->method('limit')
            ->with($this->equalTo($limit));

        $engine->expects($this->once())
            ->method('highlighter')
            ->with($this->equalTo(true));

        if (isset($options['moduleFilter'])) {
            $engine->expects($this->once())
                ->method('from')
                ->with($this->equalTo($options['moduleFilter']));
        }

        // mock logger
        $logger = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();

        // tests search
        $sut = $this->getMockBuilder('SugarSearchEngineElastic')
            ->setConstructorArgs([[], $engine, $logger])
            ->setMethods()
            ->getMock();

        $this->assertInstanceOf(ResultSet::class, $sut->search($query, $offset, $limit, $options));
    }

    public function providerTestSearch()
    {
        return [
            [
                'find this',
                10,
                30,
                [],
            ],
            [
                'find this',
                10,
                30,
                [
                    'moduleFilter' => ['Accounts', 'Contacts'],
                ],
            ],
        ];
    }

    /**
     * @covers ::search
     */
    public function testSearchWithResponseException()
    {
        $engineMock = $this->getEngineMock();
        // stub search
        $requestMock = TestMockHelper::getObjectMock($this, Request::class);
        $responseMock = TestMockHelper::getObjectMock($this, Response::class);

        $engineMock->expects($this->any())
            ->method('search')
            ->will($this->throwException(new ResponseException($requestMock, $responseMock)));

        // mock logger
        $logger = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();

        // tests search
        $sut = $this->getMockBuilder('SugarSearchEngineElastic')
            ->setConstructorArgs([[], $engineMock, $logger])
            ->setMethods()
            ->getMock();

        $this->assertInstanceOf(ResultSet::class, $sut->search('abc', 10, 100, []));
    }

    /**
     * @covers ::search
     */
    public function testSearchWithNonResponseException()
    {
        $this->expectException(\Exception::class);

        $engineMock = $this->getEngineMock();

        // stub search
        $engineMock->expects($this->any())
            ->method('search')
            ->will($this->throwException(new \Exception()));

        // mock logger
        $logger = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();

        // tests search
        $sut = $this->getMockBuilder('SugarSearchEngineElastic')
            ->setConstructorArgs([[], $engineMock, $logger])
            ->setMethods()
            ->getMock();

        $sut->search('abc', 10, 100, []);
    }

    protected function getEngineMock()
    {
        $engineMock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable::class)
            ->setMethods(['isAvailable', 'search', 'term'])
            ->getMockForAbstractClass();

        $engineMock->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));

        return $engineMock;
    }
}
