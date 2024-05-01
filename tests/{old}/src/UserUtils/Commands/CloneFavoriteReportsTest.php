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
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneFavoriteReports;
use Sugarcrm\Sugarcrm\UserUtils\Managers\GeneralManager;
use SugarTestHelper;

class CloneFavoriteReportsTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CloneFavoriteReports|mixed
     */
    public $cloneFavoriteReports;

    protected function setUp(): void
    {
        $this->cloneFavoriteReports = $this->getMockBuilder(CloneFavoriteReports::class)
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
            ->onlyMethods(['cloneFavoriteReports'])
            ->getMock();

        $this->cloneFavoriteReports->expects($this->once())->method('execute');
        $this->cloneFavoriteReports->execute();
        $this->cloneFavoriteReports->expects($this->once())->method('getManager');
        $this->cloneFavoriteReports->getManager();
        $manager->expects($this->once())->method('cloneFavoriteReports');
        $manager->cloneFavoriteReports();
    }
}
