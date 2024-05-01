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

class KBContentsApiTest extends TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var KBContentsApi
     */
    protected $api = null;

    /**
     * @var KBContentsMock
     */
    protected $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = $this->createPartialMock('KBContentsApi', ['getElasticQueryBuilder']);
        $this->bean = SugarTestKBContentUtilities::createBean();
    }

    protected function tearDown(): void
    {
        $this->service = null;
        $this->api = null;

        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    public function testRelatedDocuments()
    {
        $builderMock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setQuery', 'executeSearch', 'addFilter'])
            ->getMock();

        $resultSetMock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $builderMock->expects($this->any())->method('executeSearch')->will($this->returnValue($resultSetMock));
        $this->api->expects($this->any())->method('getElasticQueryBuilder')->will($this->returnValue($builderMock));

        $result = $this->api->relatedDocuments($this->service, [
            'module' => $this->bean->module_name,
            'record' => $this->bean->id,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertIsArray($result['records']);
    }
}
