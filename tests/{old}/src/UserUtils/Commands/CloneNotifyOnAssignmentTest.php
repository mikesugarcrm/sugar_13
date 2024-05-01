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

namespace Sugarcrm\SugarcrmTests\UserUtils\Commands;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneNotifyOnAssignment;
use Sugarcrm\Sugarcrm\UserUtils\Managers\GeneralManager;
use SugarTestHelper;

class CloneNotifyOnAssignmentTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CloneNotifyOnAssignment|mixed
     */
    public $cloneNotifyOnAssignment;

    protected function setUp(): void
    {
        $this->cloneNotifyOnAssignment = $this->getMockBuilder(CloneNotifyOnAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getManager', 'execute'])
            ->getMock();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::testExecute
     */
    public function testExecute()
    {
        $manager = $this->getMockBuilder(GeneralManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['cloneNotifyOnAssignment'])
            ->getMock();

        $this->cloneNotifyOnAssignment->expects($this->once())->method('execute');
        $this->cloneNotifyOnAssignment->execute();
        $this->cloneNotifyOnAssignment->expects($this->once())->method('getManager');
        $this->cloneNotifyOnAssignment->getManager();
        $manager->expects($this->once())->method('cloneNotifyOnAssignment');
        $manager->cloneNotifyOnAssignment();
    }
}
