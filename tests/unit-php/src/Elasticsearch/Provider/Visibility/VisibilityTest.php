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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\Visibility;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility
 */
class VisibilityTest extends TestCase
{
    /**
     * @covers ::buildMapping
     * @dataProvider buildMappingProvider
     */
    public function testBuildMapping(array $strategies, string $field, array $expected)
    {
        $visibilityMock = $this->getVisibilityMock(['getModuleStrategies']);

        $visibilityMock->expects($this->any())
            ->method('getModuleStrategies')
            ->will($this->returnValue($strategies));

        $mapping = new Mapping('moduleName');
        $visibilityMock->buildMapping($mapping);

        $commonField = Mapping::PREFIX_COMMON . $field;
        $this->assertNotEmpty($mapping->getProperty($commonField));

        $multifieldProperty = $mapping->getProperty($commonField);
        $this->assertSame($expected, TestReflection::getProtectedValue($multifieldProperty, 'fields'));
    }

    public function buildMappingProvider()
    {
        return [
            [
                [$this->getACLVisibilityMock()],
                'owner_id',
                ['owner' => ['type' => 'keyword']],
            ],
            [
                [$this->getACLVisibilityMock(), $this->getTargetModuleDeveloperVisibilityMock()],
                'owner_id',
                ['owner' => ['type' => 'keyword']],
            ],
            [
                [$this->getACLVisibilityMock(), $this->getTeamBasedACLVisibilityMock()],
                'owner_id',
                ['owner' => ['type' => 'keyword']],
            ],
            [
                [$this->getACLVisibilityMock(), $this->getTeamBasedACLVisibilityMock()],
                'acl_team_set_id',
                ['set' => ['type' => 'keyword']],
            ],
        ];
    }

    /**
     * get Mock object of Visibility
     * @param array|null $methods
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getVisibilityMock(array $methods = null)
    {
        return $this->getMockBuilder(Visibility::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * get Mock object of ACLVisibility
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getACLVisibilityMock()
    {
        $mock = $this->getMockBuilder(\ACLVisibility::class)
            ->disableOriginalConstructor()
            ->setMethods(['implementsTBA'])
            ->getMock();

        $mock->expects($this->any())
            ->method('implementsTBA')
            ->will($this->returnValue(false));

        return $mock;
    }

    /**
     * get Mock object of TargetModuleDeveloperVisibility
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTargetModuleDeveloperVisibilityMock()
    {
        $mock = $this->getMockBuilder(\TargetModuleDeveloperVisibility::class)
            ->disableOriginalConstructor()
            ->setMethods(['implementsTBA'])
            ->getMock();

        $mock->expects($this->any())
            ->method('implementsTBA')
            ->will($this->returnValue(false));

        return $mock;
    }

    /**
     * get Mock object of TeamBasedACLVisibility
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTeamBasedACLVisibilityMock()
    {
        return $this->getMockBuilder(\TeamBasedACLVisibility::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();
    }
}
