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
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneSugarEmailClient;
use Sugarcrm\Sugarcrm\UserUtils\Managers\GeneralManager;
use SugarTestHelper;

class CloneSugarEmailClientTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Sugarcrm\Sugarcrm\UserUtils\Commands\CloneSugarEmailClient|mixed
     */
    public $cloneSugarEmailClient;

    protected function setUp(): void
    {
        $this->cloneSugarEmailClient = $this->getMockBuilder(CloneSugarEmailClient::class)
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
            ->onlyMethods(['cloneSugarEmailClient'])
            ->getMock();

        $this->cloneSugarEmailClient->expects($this->once())->method('execute');
        $this->cloneSugarEmailClient->execute();
        $this->cloneSugarEmailClient->expects($this->once())->method('getManager');
        $this->cloneSugarEmailClient->getManager();
        $manager->expects($this->once())->method('cloneSugarEmailClient');
        $manager->cloneSugarEmailClient();
    }
}
