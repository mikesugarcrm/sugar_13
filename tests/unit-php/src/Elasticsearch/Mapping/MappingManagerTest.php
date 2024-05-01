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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Mapping;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\ProviderCollection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager
 */
class MappingManagerTest extends TestCase
{
    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($modules, $mapping1, $mapping2)
    {
        $numCalls = safeCount($modules);
        $provider1 = $this->getProviderMock(['buildMapping']);
        $provider1->expects($this->exactly($numCalls))
            ->method('buildMapping')
            ->will($this->returnValue($mapping1));

        $provider2 = $this->getProviderMock(['buildMapping']);
        $provider2->expects($this->exactly($numCalls))
            ->method('buildMapping')
            ->will($this->returnValue($mapping2));

        $providers = new ProviderCollection($this->getContainerMock(), [$provider1, $provider2]);

        $mappingManager = $this->getMappingManagerMock();
        $mappingManager->buildMapping($providers, $modules);
    }

    public function providerTestBuildMapping()
    {
        return [
            [
                ['Accounts', 'Contacts', 'Leads'],
                ['mapping1' => ['type' => 'string']],
                ['mapping2' => ['type' => 'text']],
            ],
        ];
    }

    /**
     * Get Provider Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function getProviderMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get MappingManagerTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager
     */
    protected function getMappingManagerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get Container Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected function getContainerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Container::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
