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

use FilterApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarSearchEngine;
use UnifiedSearchApi;

/**
 * @coversDefaultClass \UnifiedSearchApi
 */
class UnifiedSearchApiTest extends TestCase
{
    private const DEFAULT_MODULE_LIST = 'Calls, Meetings, Accounts, Contacts';

    /**
     * @var \ServiceBase|MockObject
     */
    protected $api;

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        $this->api = $this->createMock(\ServiceBase::class);
    }

    /**
     * @covers ::globalSearchSpot
     *
     * @throws \SugarApiExceptionError
     * @throws \SugarApiExceptionInvalidParameter
     * @throws \SugarApiExceptionNotAuthorized
     */
    public function testValidBehaviorWithNonEmptyQuery()
    {
        $unifiedSearchApiMock = $this->getUnifiedSearchApiMock(['processEmptyQuery']);
        $searchEngineMock = $this->getSearchEngineMock(['search']);

        $args = [
            'module_list' => self::DEFAULT_MODULE_LIST,
            'q' => 'foo',
        ];

        $searchEngineMock->expects($this->once())->method('search')->willReturn([]);
        $unifiedSearchApiMock->expects($this->never())->method('processEmptyQuery');

        $options = $unifiedSearchApiMock->parseSearchOptions($this->api, $args);
        $unifiedSearchApiMock->globalSearchSpot($this->api, $args, $searchEngineMock, $options);
    }

    /**
     * @covers ::globalSearchSpot
     *
     * @throws \SugarApiExceptionError
     * @throws \SugarApiExceptionInvalidParameter
     * @throws \SugarApiExceptionNotAuthorized
     */
    public function testValidBehaviorWithCustomWhere()
    {
        $unifiedSearchApiMock = $this->getUnifiedSearchApiMock(['processEmptyQuery']);
        $searchEngineMock = $this->getSearchEngineMock(['search']);

        $args = [
            'module_list' => self::DEFAULT_MODULE_LIST,
            'q' => '',
        ];

        $searchEngineMock->expects($this->once())->method('search')->willReturn([]);
        $unifiedSearchApiMock->expects($this->never())->method('processEmptyQuery');

        $options = $unifiedSearchApiMock->parseSearchOptions($this->api, $args);
        $options['custom_where'] = 'bar';

        $unifiedSearchApiMock->globalSearchSpot($this->api, $args, $searchEngineMock, $options);
    }

    /**
     * @covers ::globalSearchSpot
     *
     * @throws \SugarApiExceptionError
     * @throws \SugarApiExceptionInvalidParameter
     * @throws \SugarApiExceptionNotAuthorized
     */
    public function testValidBehaviorWithEmptyQuery()
    {
        $unifiedSearchApiMock = $this->getUnifiedSearchApiMock(['isModuleAccessAllowed']);
        $searchEngineMock = $this->getSearchEngineMock(['search']);
        $filterApiMock = $this->getFilterApiMock(['filterList']);
        TestReflection::setProtectedValue($unifiedSearchApiMock, 'filterApi', $filterApiMock);

        $args = [
            'module_list' => self::DEFAULT_MODULE_LIST,
            'q' => '',
        ];

        $moduleArray = array_map('trim', explode(',', self::DEFAULT_MODULE_LIST));

        $searchEngineMock->expects($this->never())->method('search');
        $filterApiMock->expects($this->exactly(count($moduleArray)))
            ->method('filterList')
            ->willReturn(['records' => [['foo' => 'bar']]]);
        $unifiedSearchApiMock->expects($this->exactly(count($moduleArray)))
            ->method('isModuleAccessAllowed')
            ->willReturn(true);

        $options = $unifiedSearchApiMock->parseSearchOptions($this->api, $args);
        $data = $unifiedSearchApiMock->globalSearchSpot($this->api, $args, $searchEngineMock, $options);

        $this->assertArrayHasKey('records', $data);
        $this->assertCount(count($moduleArray), $data['records']);

        foreach ($data['records'] as $record) {
            $this->assertArrayHasKey('foo', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertTrue(in_array($record['_module'], $moduleArray));
            $this->assertArrayHasKey('_search', $record);
            $this->assertEquals(['score' => 1], $record['_search']);
        }
    }

    /**
     * @param null|array $methods
     * @return UnifiedSearchApi|MockObject
     */
    protected function getUnifiedSearchApiMock($methods = null)
    {
        return $this->getMockBuilder(UnifiedSearchApi::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param null|array $methods
     * @return SugarSearchEngine|MockObject
     */
    protected function getSearchEngineMock($methods = null)
    {
        return $this->getMockBuilder(SugarSearchEngine::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param null|array $methods
     * @return FilterApi|MockObject
     */
    protected function getFilterApiMock($methods = null)
    {
        return $this->getMockBuilder(FilterApi::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
