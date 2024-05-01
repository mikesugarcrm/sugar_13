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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Queue;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Queue\QueueManager
 */
class QueueManagerTest extends TestCase
{
    /**
     * @covers ::getQueueCountModule
     */
    public function testGetQueueCountModule()
    {
        $module = 'Accounts';
        $container = $this->getContainerMock();

        $dbManager = $this->getMockBuilder('\DBManager')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $queueManager = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Queue\QueueManager::class)
            ->setConstructorArgs([[], $container, $dbManager])
            ->setMethods([])
            ->getMock();

        $res = $queueManager->getQueueCountModule($module);
        $this->assertEquals($res, 0);
    }

    /**
     * Get QueueManagerTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Queue\QueueManager
     */
    protected function getQueueManagerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Queue\QueueManager::class)
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
