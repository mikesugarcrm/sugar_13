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
use Sugarcrm\Sugarcrm\UserUtils\Commands\DeleteFilters;
use Sugarcrm\Sugarcrm\UserUtils\Managers\FiltersManager;
use SugarTestHelper;

class DeleteFiltersTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\DeleteFilters|mixed
     */
    public $deleteFilters;

    protected function setUp(): void
    {
        $this->deleteFilters = $this->getMockBuilder(DeleteFilters::class)
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
            ->onlyMethods(['delete'])
            ->getMock();

        $this->deleteFilters->expects($this->once())->method('execute');
        $this->deleteFilters->execute();
        $this->deleteFilters->expects($this->once())->method('getManager');
        $this->deleteFilters->getManager();
        $manager->expects($this->once())->method('delete');
        $manager->delete();
    }
}
