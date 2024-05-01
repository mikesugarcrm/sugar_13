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

namespace Sugarcrm\SugarcrmTestsUnit\SearchEngine;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SearchEngine\HookHandler
 */
class HookHandlerTest extends TestCase
{
    /**
     * @covers ::indexBean
     * @dataProvider dataProviderTestIndexBean
     *
     * @param \SugarBean|null $bean
     * @param integer $count
     */
    public function testIndexBean($bean, $count)
    {
        $hook = $this->getMockBuilder(\Sugarcrm\Sugarcrm\SearchEngine\HookHandler::class)
            ->setMethods(['getSearchEngine', 'getLogger'])
            ->getMock();

        $hook->expects($this->exactly($count))
            ->method('getSearchEngine')
            ->will($this->returnValue($this->getSearchEngineMock($count)));

        $logger = $this->getMockBuilder('SugarNullLogger')
            ->getMock();

        $hook->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($logger));

        $hook->indexBean($bean, 'event', []);
    }

    public function dataProviderTestIndexBean()
    {
        return [
            [
                $this->getSugarBeanMock(),
                1,
            ],
            [
                null,
                0,
            ],
        ];
    }

    /**
     * Get SearchEngine mock
     * @param integer $callIndexBeanCount
     * @return \Sugarcrm\Sugarcrm\SearchEngine\SearchEngine
     */
    protected function getSearchEngineMock($callIndexBeanCount)
    {
        $engine = $this->createMock('\\' . \Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface::class);
        $engine->expects($this->exactly($callIndexBeanCount))
            ->method('indexBean');

        return $engine;
    }

    /**
     * Get SugarBean mock
     * @return \SugarBean
     */
    protected function getSugarBeanMock()
    {
        return $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }
}
