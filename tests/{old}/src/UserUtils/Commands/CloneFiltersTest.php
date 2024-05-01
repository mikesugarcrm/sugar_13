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
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneFilters;
use Sugarcrm\Sugarcrm\UserUtils\Managers\FiltersManager;
use SugarTestHelper;

class CloneFiltersTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CloneFilters|mixed
     */
    public $cloneFilters;

    protected function setUp(): void
    {
        $this->cloneFilters = $this->getMockBuilder(CloneFilters::class)
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
        $manager = $this->getMockBuilder(FiltersManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['clone'])
            ->getMock();

        $this->cloneFilters->expects($this->once())->method('execute');
        $this->cloneFilters->execute();
        $this->cloneFilters->expects($this->once())->method('getManager');
        $this->cloneFilters->getManager();
        $manager->expects($this->once())->method('clone');
        $manager->clone();
    }
}
